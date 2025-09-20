<?php
include 'top_bar.php';

// Fetch leaderboard stats
$stmt = $pdo->query("
    SELECT u.id, u.name, us.level, us.xp, us.games_played, us.games_won, us.rank_position
    FROM users u
    JOIN user_stats us ON u.id = us.user_id
    ORDER BY us.rank_position ASC, us.xp DESC
");
$players = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en" class="dark">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Leaderboard</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      darkMode: 'class',
      theme: {
        extend: {
          colors: {
            brand: "#FDC500",
          },
        },
      },
    };
  </script>
</head>
<body class="bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-gray-100">

<div class="max-w-6xl mx-auto px-4 py-8">
  <!-- Header -->
  <div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold">🏆 Leaderboard</h1>
    <div class="flex space-x-2">
      <button id="cardViewBtn" class="px-3 py-1 rounded-lg bg-brand text-white">Cards</button>
      <button id="tableViewBtn" class="px-3 py-1 rounded-lg bg-gray-300 dark:bg-gray-700">Table</button>
    </div>
  </div>

  <!-- Card Grid View (default) -->
  <div id="cardView" class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
    <?php foreach ($players as $player): ?>
      <div class="bg-white dark:bg-gray-800 rounded-2xl shadow p-6 flex flex-col items-center">
        <div class="w-16 h-16 rounded-full bg-brand text-white flex items-center justify-center text-xl font-bold mb-4">
          <?= strtoupper(substr($player['name'], 0, 2)) ?>
        </div>
        <h2 class="text-lg font-semibold mb-2"><?= htmlspecialchars($player['name']) ?></h2>
        <p class="text-sm text-gray-600 dark:text-gray-400">Level <?= $player['level'] ?> • <?= $player['xp'] ?> XP</p>
        <div class="flex justify-between w-full mt-4 text-sm">
          <span>Games: <?= $player['games_played'] ?></span>
          <span>Wins: <?= $player['games_won'] ?></span>
        </div>
        <div class="mt-3 font-bold text-brand">Rank #<?= $player['rank_position'] ?></div>
      </div>
    <?php endforeach; ?>
  </div>

  <!-- Table View -->
  <div id="tableView" class="hidden overflow-x-auto">
    <table class="w-full text-left border-collapse mt-6">
      <thead>
        <tr class="bg-gray-200 dark:bg-gray-700">
          <th class="px-4 py-2">Rank</th>
          <th class="px-4 py-2">Name</th>
          <th class="px-4 py-2">Level</th>
          <th class="px-4 py-2">XP</th>
          <th class="px-4 py-2">Games</th>
          <th class="px-4 py-2">Wins</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($players as $player): ?>
        <tr class="border-b border-gray-300 dark:border-gray-600">
          <td class="px-4 py-2 font-bold text-brand">#<?= $player['rank_position'] ?></td>
          <td class="px-4 py-2"><?= htmlspecialchars($player['name']) ?></td>
          <td class="px-4 py-2"><?= $player['level'] ?></td>
          <td class="px-4 py-2"><?= $player['xp'] ?></td>
          <td class="px-4 py-2"><?= $player['games_played'] ?></td>
          <td class="px-4 py-2"><?= $player['games_won'] ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<script>
  const cardViewBtn = document.getElementById("cardViewBtn");
  const tableViewBtn = document.getElementById("tableViewBtn");
  const cardView = document.getElementById("cardView");
  const tableView = document.getElementById("tableView");

  cardViewBtn.addEventListener("click", () => {
    cardView.classList.remove("hidden");
    tableView.classList.add("hidden");
    cardViewBtn.classList.add("bg-brand", "text-white");
    tableViewBtn.classList.remove("bg-brand", "text-white");
    tableViewBtn.classList.add("bg-gray-300", "dark:bg-gray-700");
  });

  tableViewBtn.addEventListener("click", () => {
    tableView.classList.remove("hidden");
    cardView.classList.add("hidden");
    tableViewBtn.classList.add("bg-brand", "text-white");
    cardViewBtn.classList.remove("bg-brand", "text-white");
    cardViewBtn.classList.add("bg-gray-300", "dark:bg-gray-700");
  });
</script>

</body>
</html>
