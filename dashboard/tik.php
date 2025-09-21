<?php
include 'top_bar.php';

// Game config
$points_win  = 50;
$points_draw = 20;
$points_lose = 10;

// Initialize game in database
$stmt = $pdo->prepare("SELECT id FROM games WHERE title = ?");
$stmt->execute(['Tic Tac Toe']);
$game = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$game) {
    $pdo->prepare("INSERT INTO games (title, description) VALUES (?, ?)")
        ->execute(['Tic Tac Toe', 'Classic Tic Tac Toe game']);
    $game_id = $pdo->lastInsertId();
} else {
    $game_id = $game['id'];
}

// AJAX handler
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    if (!isset($_SESSION['board'])) {
        $_SESSION['board'] = array_fill(0, 9, '');
        $_SESSION['turn'] = 'X';
        $_SESSION['winner'] = null;
    }

    $response = ['success' => true];

    if ($_POST['action'] === 'move' && $_SESSION['winner'] === null) {
        $move = intval($_POST['move']);
        $difficulty = $_POST['difficulty'] ?? 'easy';

        if ($_SESSION['board'][$move] === '') {
            // Player move
            $_SESSION['board'][$move] = 'X';
            
            // Check winner after player move
            $result = checkGameState($pdo, $game_id, $user_id, $points_win, $points_draw, $points_lose);
            
            if (!$result['winner']) {
                // Computer move
                $computerMove = getComputerMove($_SESSION['board'], $difficulty);
                if ($computerMove !== null) {
                    $_SESSION['board'][$computerMove] = 'O';
                    $_SESSION['turn'] = 'X';
                    $response['computerMove'] = $computerMove;
                }
                
                // Check winner after computer move
                $result = checkGameState($pdo, $game_id, $user_id, $points_win, $points_draw, $points_lose);
            }

            $response = array_merge($response, $result);
        }
    } elseif ($_POST['action'] === 'reset') {
        $_SESSION['board'] = array_fill(0, 9, '');
        $_SESSION['turn'] = 'X';
        $_SESSION['winner'] = null;
        $response['board'] = $_SESSION['board'];
    }

    echo json_encode($response);
    exit;
}

function checkGameState($pdo, $game_id, $user_id, $points_win, $points_draw, $points_lose) {
    $winning_combos = [
        [0,1,2],[3,4,5],[6,7,8],
        [0,3,6],[1,4,7],[2,5,8],
        [0,4,8],[2,4,6]
    ];

    $response = ['board' => $_SESSION['board'], 'winner' => null];

    // Check for winner
    foreach ($winning_combos as $combo) {
        [$a,$b,$c] = $combo;
        if ($_SESSION['board'][$a] && 
            $_SESSION['board'][$a] === $_SESSION['board'][$b] && 
            $_SESSION['board'][$a] === $_SESSION['board'][$c]) {
            
            $_SESSION['winner'] = $_SESSION['board'][$a];
            $result = ($_SESSION['winner'] === 'X') ? 'win' : 'lose';
            $xpEarned = ($result === 'win') ? $points_win : $points_lose;

            // Update game_results
            $pdo->prepare("INSERT INTO game_results (game_id, user_id, score, result) VALUES (?, ?, ?, ?)")
                ->execute([$game_id, $user_id, $xpEarned, $result]);

            // Update user_stats
            $pdo->prepare("
                INSERT INTO user_stats (user_id, xp, games_played, games_won) 
                VALUES (?, ?, 1, ?) 
                ON DUPLICATE KEY UPDATE 
                    xp = xp + ?,
                    games_played = games_played + 1,
                    games_won = games_won + ?,
                    updated_at = CURRENT_TIMESTAMP
            ")->execute([$user_id, $xpEarned, $result === 'win' ? 1 : 0, $xpEarned, $result === 'win' ? 1 : 0]);

            $response['winner'] = $_SESSION['winner'];
            break;
        }
    }

    // Check for draw
    if (!$response['winner'] && !in_array('', $_SESSION['board'])) {
        $_SESSION['winner'] = 'draw';
        $response['winner'] = 'draw';

        $pdo->prepare("INSERT INTO game_results (game_id, user_id, score, result) VALUES (?, ?, ?, 'draw')")
            ->execute([$game_id, $user_id, $points_draw]);

        $pdo->prepare("
            INSERT INTO user_stats (user_id, xp, games_played) 
            VALUES (?, ?, 1) 
            ON DUPLICATE KEY UPDATE 
                xp = xp + ?,
                games_played = games_played + 1,
                updated_at = CURRENT_TIMESTAMP
        ")->execute([$user_id, $points_draw, $points_draw]);
    }

    return $response;
}

function getComputerMove($board, $difficulty) {
    $emptyCells = array_keys(array_filter($board, fn($cell) => $cell === ''));
    if (empty($emptyCells)) return null;

    if ($difficulty === 'hard') {
        // Minimax algorithm for optimal move
        $bestScore = -INF;
        $bestMove = null;

        foreach ($emptyCells as $cell) {
            $board[$cell] = 'O';
            $score = minimax($board, false);
            $board[$cell] = '';
            if ($score > $bestScore) {
                $bestScore = $score;
                $bestMove = $cell;
            }
        }
        return $bestMove;
    } elseif ($difficulty === 'medium') {
        // 50% chance of optimal move, 50% random
        if (rand(0, 1)) {
            return $emptyCells[array_rand($emptyCells)];
        }
        // Else use minimax
        $bestScore = -INF;
        $bestMove = null;

        foreach ($emptyCells as $cell) {
            $board[$cell] = 'O';
            $score = minimax($board, false);
            $board[$cell] = '';
            if ($score > $bestScore) {
                $bestScore = $score;
                $bestMove = $cell;
            }
        }
        return $bestMove;
    } else {
        // Easy: Random move
        return $emptyCells[array_rand($emptyCells)];
    }
}

function minimax($board, $isMaximizing) {
    $winning_combos = [
        [0,1,2],[3,4,5],[6,7,8],
        [0,3,6],[1,4,7],[2,5,8],
        [0,4,8],[2,4,6]
    ];

    // Check for terminal states
    foreach ($winning_combos as $combo) {
        [$a,$b,$c] = $combo;
        if ($board[$a] && $board[$a] === $board[$b] && $board[$a] === $board[$c]) {
            return $board[$a] === 'O' ? 1 : -1;
        }
    }
    if (!in_array('', $board)) return 0;

    if ($isMaximizing) {
        $bestScore = -INF;
        foreach (array_keys(array_filter($board, fn($cell) => $cell === '')) as $cell) {
            $board[$cell] = 'O';
            $score = minimax($board, false);
            $board[$cell] = '';
            $bestScore = max($score, $bestScore);
        }
        return $bestScore;
    } else {
        $bestScore = INF;
        foreach (array_keys(array_filter($board, fn($cell) => $cell === '')) as $cell) {
            $board[$cell] = 'X';
            $score = minimax($board, true);
            $board[$cell] = '';
            $bestScore = min($score, $bestScore);
        }
        return $bestScore;
    }
}
?>

<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <title>Tic Tac Toe</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: { extend: { colors: { brand: "#6B21A8" } } }
        };
    </script>
</head>
<body class="bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-gray-100 flex flex-col items-center py-10">
    <h1 class="text-3xl font-bold mb-6">🎮 Tic Tac Toe</h1>

    <div class="mb-4">
        <label for="difficulty" class="mr-2">Difficulty:</label>
        <select id="difficulty" class="px-2 py-1 bg-gray-800 text-white rounded">
            <option value="easy">Easy</option>
            <option value="medium">Medium</option>
            <option value="hard">Hard</option>
        </select>
        <button id="multiplayer" class="px-4 py-2 bg-gray-500 text-white rounded ml-2 opacity-50 cursor-not-allowed" disabled>
            Multiplayer (Coming Soon)
        </button>
    </div>

    <div id="board" class="grid grid-cols-3 gap-2 mb-6">
        <?php for ($i = 0; $i < 9; $i++): ?>
            <button data-index="<?= $i ?>" 
                    class="w-20 h-20 flex items-center justify-center text-3xl font-bold rounded-lg shadow bg-white dark:bg-gray-800"
                    disabled>
            </button>
        <?php endfor; ?>
    </div>

    <div id="status" class="mb-6 text-lg"></div>

    <button id="reset" class="px-6 py-2 bg-purple-700 text-white rounded-lg shadow hover:bg-purple-800">
        Reset Game
    </button>

    <script>
        const boardButtons = document.querySelectorAll('#board button');
        const status = document.getElementById('status');
        const resetButton = document.getElementById('reset');
        const difficultySelect = document.getElementById('difficulty');

        function updateBoard(board, winner) {
            boardButtons.forEach((btn, i) => {
                btn.textContent = board[i] || '';
                btn.className = `w-20 h-20 flex items-center justify-center text-3xl font-bold rounded-lg shadow ${
                    board[i] === 'X' ? 'bg-brand text-white' : 
                    board[i] === 'O' ? 'bg-gray-700 text-yellow-300' : 
                    'bg-white dark:bg-gray-800'
                }`;
                btn.disabled = winner || board[i] !== '';
            });

            if (winner === 'draw') {
                status.textContent = "😮 It's a Draw!";
            } else if (winner) {
                status.textContent = `🏆 Winner: ${winner}`;
            } else {
                status.textContent = "Your Turn (X)";
            }
        }

        function makeMove(index) {
            fetch('', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=move&move=${index}&difficulty=${difficultySelect.value}`
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    updateBoard(data.board, data.winner);
                }
            });
        }

        function resetGame() {
            fetch('', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'action=reset'
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    updateBoard(data.board, null);
                }
            });
        }

        boardButtons.forEach(btn => {
            btn.addEventListener('click', () => makeMove(btn.dataset.index));
        });

        resetButton.addEventListener('click', resetGame);

        // Initial board state
        updateBoard(<?php echo json_encode($_SESSION['board']); ?>, <?php echo json_encode($_SESSION['winner']); ?>);
    </script>
</body>
</html>
