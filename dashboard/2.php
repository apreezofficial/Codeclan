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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    $response = ['success' => true];

    if ($_POST['action'] === 'game_over') {
        $winner = $_POST['winner'];
        $board = json_decode($_POST['board'], true);
        $difficulty = $_POST['difficulty'] ?? 'easy';

        // Determine points based on result
        $points = $points_draw; // default for draw
        if ($winner === 'X') {
            $points = $points_win;
        } elseif ($winner === 'O') {
            $points = $points_lose;
        }

        $result = $winner === 'draw' ? 'draw' : ($winner === 'X' ? 'win' : 'lose');

        try {
            // Update game_results table
            $pdo->prepare("INSERT INTO game_results (game_id, user_id, score, result) VALUES (?, ?, ?, ?)")
                ->execute([$game_id, $user_id, $points, $result]);

            // Update user_stats table
            $games_won_increment = ($winner === 'X') ? 1 : 0;
            $pdo->prepare("
                INSERT INTO user_stats (user_id, xp, games_played, games_won) 
                VALUES (?, ?, 1, ?) 
                ON DUPLICATE KEY UPDATE 
                    xp = xp + ?,
                    games_played = games_played + 1,
                    games_won = games_won + ?,
                    updated_at = CURRENT_TIMESTAMP
            ")->execute([
                $user_id, 
                $points, 
                $games_won_increment, 
                $points, 
                $games_won_increment
            ]);
        } catch (Exception $e) {
            $response['success'] = false;
            $response['error'] = $e->getMessage();
        }
    }

    echo json_encode($response);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>⚡ Fast Tic Tac Toe</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: { 
                extend: { 
                    colors: { 
                        brand: "#6B21A8",
                        'brand-light': '#8B5CF6'
                    },
                    animation: {
                        'pop-in': 'pop-in 0.2s ease-out',
                        'pulse-slow': 'pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                    },
                    keyframes: {
                        'pop-in': {
                            '0%': { transform: 'scale(0.8)', opacity: '0' },
                            '100%': { transform: 'scale(1)', opacity: '1' },
                        },
                    }
                } 
            }
        };
    </script>
</head>
<body class="bg-gradient-to-br from-purple-900 via-gray-900 to-indigo-900 dark:bg-gray-900 text-gray-100 flex flex-col items-center py-10 px-4 min-h-screen">
    <div class="max-w-md w-full">
        <h1 class="text-4xl md:text-5xl font-extrabold mb-2 text-center bg-clip-text text-transparent bg-gradient-to-r from-purple-400 to-pink-400">
            🎮 Tic Tac Toe
        </h1>
        <p class="text-center text-gray-300 mb-8">Play against the AI</p>

        <div class="mb-6 flex flex-col sm:flex-row items-center justify-center gap-4">
            <div class="flex items-center">
                <label for="difficulty" class="mr-2 text-sm font-medium text-gray-200 whitespace-nowrap">Difficulty:</label>
                <select id="difficulty" class="px-3 py-2 bg-gray-800 border border-gray-700 text-white rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition">
                    <option value="easy">Easy</option>
                    <option value="medium">Medium</option>
                    <option value="hard">Hard</option>
                </select>
            </div>
            <button id="multiplayer" class="px-4 py-2 bg-gray-600 text-white rounded-lg shadow hover:bg-gray-500 transition cursor-not-allowed opacity-60 text-sm" disabled>
                👥 Multiplayer (Coming Soon)
            </button>
        </div>

        <div id="board" class="grid grid-cols-3 gap-3 mb-6">
            <?php for ($i = 0; $i < 9; $i++): ?>
                <button data-index="<?= $i ?>" 
                        class="aspect-square flex items-center justify-center text-4xl md:text-5xl font-bold rounded-xl shadow-lg bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-100 transition-all duration-200 hover:shadow-xl hover:scale-105 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 focus:ring-offset-gray-900"
                        aria-label="Cell <?= $i + 1 ?>">
                </button>
            <?php endfor; ?>
        </div>

        <div id="status" class="mb-6 text-xl font-semibold text-center min-h-8"></div>

        <div class="flex justify-center">
            <button id="reset" class="px-8 py-3 bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white font-bold rounded-xl shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 focus:ring-offset-gray-900">
                🔄 Reset Game
            </button>
        </div>
    </div>

    <script>
        class TicTacToeGame {
            constructor(difficulty = 'easy') {
                this.board = Array(9).fill('');
                this.currentPlayer = 'X';
                this.winner = null;
                this.difficulty = difficulty;
            }

            makeMove(index) {
                if (this.board[index] !== '' || this.winner) {
                    return false;
                }

                // Player move
                this.board[index] = this.currentPlayer;

                // Check for winner or draw
                this.winner = this.checkWinner();
                if (this.winner) {
                    return true;
                }
                if (!this.board.includes('')) {
                    this.winner = 'draw';
                    return true;
                }

                // Computer's turn
                this.currentPlayer = 'O';
                const computerMove = this.getComputerMove();
                if (computerMove !== null) {
                    this.board[computerMove] = this.currentPlayer;
                    this.winner = this.checkWinner();
                    if (!this.winner && !this.board.includes('')) {
                        this.winner = 'draw';
                    }
                }
                this.currentPlayer = 'X';

                return true;
            }

            checkWinner() {
                const winningCombos = [
                    [0,1,2], [3,4,5], [6,7,8],
                    [0,3,6], [1,4,7], [2,5,8],
                    [0,4,8], [2,4,6]
                ];

                for (let combo of winningCombos) {
                    const [a, b, c] = combo;
                    if (this.board[a] && this.board[a] === this.board[b] && this.board[a] === this.board[c]) {
                        return this.board[a];
                    }
                }
                return null;
            }

            getComputerMove() {
                const emptyCells = this.board.map((cell, index) => cell === '' ? index : null).filter(index => index !== null);
                if (emptyCells.length === 0) return null;

                if (this.difficulty === 'hard') {
                    return this.minimaxMove(emptyCells);
                } else if (this.difficulty === 'medium' && Math.random() > 0.5) {
                    return this.minimaxMove(emptyCells);
                } else {
                    // Easy or 50% chance in medium
                    return emptyCells[Math.floor(Math.random() * emptyCells.length)];
                }
            }

            minimaxMove(emptyCells) {
                let bestScore = -Infinity;
                let bestMove = null;

                for (let cell of emptyCells) {
                    this.board[cell] = 'O';
                    let score = this.minimax(false);
                    this.board[cell] = '';
                    if (score > bestScore) {
                        bestScore = score;
                        bestMove = cell;
                    }
                }
                return bestMove;
            }

            minimax(isMaximizing) {
                const winner = this.checkWinner();
                if (winner === 'O') return 1;
                if (winner === 'X') return -1;
                if (!this.board.includes('')) return 0;

                if (isMaximizing) {
                    let bestScore = -Infinity;
                    for (let i = 0; i < 9; i++) {
                        if (this.board[i] === '') {
                            this.board[i] = 'O';
                            let score = this.minimax(false);
                            this.board[i] = '';
                            bestScore = Math.max(score, bestScore);
                        }
                    }
                    return bestScore;
                } else {
                    let bestScore = Infinity;
                    for (let i = 0; i < 9; i++) {
                        if (this.board[i] === '') {
                            this.board[i] = 'X';
                            let score = this.minimax(true);
                            this.board[i] = '';
                            bestScore = Math.min(score, bestScore);
                        }
                    }
                    return bestScore;
                }
            }

            reset() {
                this.board = Array(9).fill('');
                this.currentPlayer = 'X';
                this.winner = null;
            }
        }

        // Initialize game state
        const game = new TicTacToeGame('easy');
        const boardButtons = document.querySelectorAll('#board button');
        const status = document.getElementById('status');
        const resetButton = document.getElementById('reset');
        const difficultySelect = document.getElementById('difficulty');

        function updateBoardUI() {
            boardButtons.forEach((btn, i) => {
                btn.textContent = game.board[i] || '';
                btn.disabled = game.winner !== null;

                // Set dynamic classes
                let baseClasses = "aspect-square flex items-center justify-center text-4xl md:text-5xl font-bold rounded-xl shadow-lg transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 focus:ring-offset-gray-900 ";
                
                if (game.board[i] === 'X') {
                    btn.className = baseClasses + "bg-brand text-white scale-105 animate-pop-in";
                } else if (game.board[i] === 'O') {
                    btn.className = baseClasses + "bg-gray-700 text-yellow-300 scale-105 animate-pop-in";
                } else {
                    btn.className = baseClasses + "bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-100 hover:shadow-xl hover:scale-105";
                }
            });

            // Update status message
            if (game.winner === 'draw') {
                status.textContent = "😮 It's a Draw!";
                status.className = "mb-6 text-xl font-semibold text-center text-yellow-400 animate-pulse-slow";
            } else if (game.winner) {
                status.textContent = `🏆 Winner: ${game.winner}`;
                status.className = "mb-6 text-xl font-semibold text-center text-green-400 animate-pulse";
            } else {
                status.textContent = "Your Turn (X)";
                status.className = "mb-6 text-xl font-semibold text-center text-purple-300";
            }
        }

        function makeMove(index) {
            if (game.makeMove(index)) {
                updateBoardUI();
                // After the UI updates, send the final game state to the server if game is over
                if (game.winner) {
                    submitGameResultToServer();
                }
            }
        }

        function submitGameResultToServer() {
            fetch('', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=game_over&winner=${encodeURIComponent(game.winner)}&board=${encodeURIComponent(JSON.stringify(game.board))}&difficulty=${encodeURIComponent(difficultySelect.value)}`
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    console.log("✅ Game result saved to database.");
                } else {
                    console.error("❌ Failed to save game result:", data.error);
                }
            })
            .catch(err => {
                console.error("❌ Network error saving game result:", err);
            });
        }

        function resetGame() {
            game.difficulty = difficultySelect.value;
            game.reset();
            updateBoardUI();
        }

        // Event Listeners
        boardButtons.forEach(btn => {
            btn.addEventListener('click', () => makeMove(parseInt(btn.dataset.index)));
        });

        resetButton.addEventListener('click', resetGame);

        difficultySelect.addEventListener('change', () => {
            game.difficulty = difficultySelect.value;
        });

        // Initialize UI on page load
        updateBoardUI();
    </script>
</body>
</html>
