<?php
include 'top_bar.php';

// --- GAME CONFIG (will use db later ...)
$points_win  = 50;
$points_draw = 20;
$points_lose = 10;

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

// --- INIT SESSION STATE ---
if (!isset($_SESSION['board'])) {
    $_SESSION['board'] = array_fill(0, 9, '');
    $_SESSION['turn'] = 'X';
    $_SESSION['winner'] = null;
}

// --- HANDLE MOVE ---
if (isset($_POST['move']) && $_SESSION['winner'] === null) {
    $move = intval($_POST['move']);
    if ($_SESSION['board'][$move] === '') {
        $_SESSION['board'][$move] = $_SESSION['turn'];
        $_SESSION['turn'] = ($_SESSION['turn'] === 'X') ? 'O' : 'X';
    }
}

// --- CHECK WINNER ---
$winning_combos = [
    [0,1,2],[3,4,5],[6,7,8],
    [0,3,6],[1,4,7],[2,5,8],
    [0,4,8],[2,4,6]
];

foreach ($winning_combos as $combo) {
    [$a,$b,$c] = $combo;
    if ($_SESSION['board'][$a] &&
        $_SESSION['board'][$a] === $_SESSION['board'][$b] &&
        $_SESSION['board'][$a] === $_SESSION['board'][$c]) {
        
        $_SESSION['winner'] = $_SESSION['board'][$a];
        $result = ($_SESSION['winner'] === 'X') ? 'win' : 'lose';
        $xpEarned = ($result === 'win') ? $points_win : $points_lose;

        // Insert into game_results
        $pdo->prepare("INSERT INTO game_results (game_id, user_id, score, result) VALUES (?, ?, ?, ?)")
            ->execute([$game_id, $user_id, $xpEarned, $result]);

        // Update user_stats
        $pdo->prepare("
            INSERT INTO user_stats (user_id, xp, games_played, games_won) 
            VALUES (?, ?, 1, ?) 
            ON DUPLICATE KEY UPDATE 
              xp = xp + VALUES(xp),
              games_played = games_played + 1,
              games_won = games_won + VALUES(games_won),
              updated_at = CURRENT_TIMESTAMP
        ")->execute([$user_id, $xpEarned, $result === 'win' ? 1 : 0]);

        break;
    }
}

// --- CHECK DRAW ---
if (!$_SESSION['winner'] && !in_array('', $_SESSION['board'])) {
    $_SESSION['winner'] = 'draw';
    $xpEarned = $points_draw;

    $pdo->prepare("INSERT INTO game_results (game_id, user_id, score, result) VALUES (?, ?, ?, 'draw')")
        ->execute([$game_id, $user_id, $xpEarned]);

    $pdo->prepare("
        INSERT INTO user_stats (user_id, xp, games_played) 
        VALUES (?, ?, 1) 
        ON DUPLICATE KEY UPDATE 
          xp = xp + VALUES(xp),
          games_played = games_played + 1,
          updated_at = CURRENT_TIMESTAMP
    ")->execute([$user_id, $xpEarned]);
}

// --- RESET GAME ---
if (isset($_POST['reset'])) {
    $_SESSION['board'] = array_fill(0, 9, '');
    $_SESSION['turn'] = 'X';
    $_SESSION['winner'] = null;
}
?>
<!DOCTYPE html>
<html lang="en" class="dark">
<head>
  <meta charset="UTF-8" />
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

  <!-- Board -->
  <form method="POST" class="grid grid-cols-3 gap-2 mb-6">
    <?php foreach ($_SESSION['board'] as $i => $cell): ?>
      <button name="move" value="<?= $i ?>"
        class="w-20 h-20 flex items-center justify-center text-3xl font-bold rounded-lg shadow
              <?= $cell === 'X' ? 'bg-brand text-white' : ($cell === 'O' ? 'bg-gray-700 text-yellow-300' : 'bg-white dark:bg-gray-800') ?>"
        <?= $cell !== '' || $_SESSION['winner'] ? 'disabled' : '' ?>>
        <?= htmlspecialchars($cell) ?>
      </button>
    <?php endforeach; ?>
  </form>

  <!-- Status -->
  <div class="mb-6">
    <?php if ($_SESSION['winner'] === 'draw'): ?>
      <p class="text-lg font-semibold">😮 It’s a Draw!</p>
    <?php elseif ($_SESSION['winner']): ?>
      <p class="text-lg font-semibold">🏆 Winner: <?= $_SESSION['winner'] ?></p>
    <?php else: ?>
      <p class="text-lg">Turn: <?= $_SESSION['turn'] ?></p>
    <?php endif; ?>
  </div>

  <form method="POST">
    <button type="submit" name="reset" class="px-6 py-2 bg-purple-700 text-white rounded-lg shadow hover:bg-purple-800">
      Reset Game
    </button>
  </form>

</body>
</html>
