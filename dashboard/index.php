<?php
session_start();
include "top_bar.php";
error_reporting(1);

// 📊 Load user stats
$stmt = $pdo->prepare("SELECT * FROM user_stats WHERE user_id = :uid LIMIT 1");
$stmt->execute([":uid" => $dbUser['id']]);
$stats = $stmt->fetch(PDO::FETCH_ASSOC) ?: [
    "xp" => 0,
    "level" => 1,
    "games_played" => 0,
    "games_won" => 0,
    "rank_position" => null
];
?>
<!DOCTYPE html>
<html lang="en" class="transition-colors duration-300">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard | CodeClan</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="https://unpkg.com/lucide@latest"></script>
  <script>
    // 🌙 Dark mode persistence
    if (localStorage.theme === 'dark' || 
        (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
      document.documentElement.classList.add('dark')
    }
  </script>
  <style>
    .card {
      @apply bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-5 transition hover:shadow-2xl hover:-translate-y-1;
    }
  </style>
</head>
<body class="bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-gray-100">

<div class="max-w-7xl mx-auto p-6">

  <!-- 👤 Profile Section -->
  <div class="card flex flex-col items-center text-center mb-8">
    <img src="<?= htmlspecialchars($dbUser['picture']); ?>" 
         class="w-24 h-24 rounded-full border-4 border-purple-600 shadow-lg" alt="Profile">
    <h2 class="mt-4 text-2xl font-bold"><?= htmlspecialchars($dbUser['name']); ?></h2>
    <p class="text-sm text-gray-500 dark:text-gray-400"><?= htmlspecialchars($dbUser['email']); ?></p>
    
    <!-- Stats Badges -->
    <div class="mt-6 grid grid-cols-3 gap-6 w-full">
      <div class="text-center">
        <p class="text-2xl font-bold text-purple-600"><?= $stats['level']; ?></p>
        <span class="text-xs text-gray-500">Level</span>
      </div>
      <div class="text-center">
        <p class="text-2xl font-bold text-purple-600"><?= $stats['xp']; ?></p>
        <span class="text-xs text-gray-500">XP</span>
      </div>
      <div class="text-center">
        <p class="text-2xl font-bold text-purple-600"><?= $stats['games_won']; ?></p>
        <span class="text-xs text-gray-500">Wins</span>
      </div>
    </div>
  </div>

  <!-- 🚀 Quick Actions -->
  <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mb-8">
    <a href="games.php" class="card flex flex-col items-center text-purple-600">
      <i data-lucide="gamepad-2" class="w-8 h-8 mb-2"></i>
      <span>Play Game</span>
    </a>
    <a href="leaderboard.php" class="card flex flex-col items-center text-purple-600">
      <i data-lucide="trophy" class="w-8 h-8 mb-2"></i>
      <span>Leaderboard</span>
    </a>
    <a href="chats.php" class="card flex flex-col items-center text-purple-600">
      <i data-lucide="messages-square" class="w-8 h-8 mb-2"></i>
      <span>Chats</span>
    </a>
    <a href="achievements.php" class="card flex flex-col items-center text-purple-600">
      <i data-lucide="medal" class="w-8 h-8 mb-2"></i>
      <span>Achievements</span>
    </a>
  </div>

  <!-- 📊 Analytics Section -->
  <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
    
    <!-- XP Growth -->
    <div class="card">
      <h3 class="font-semibold mb-3 flex items-center space-x-2">
        <i data-lucide="bar-chart-3" class="w-5 h-5"></i>
        <span>XP Progress</span>
      </h3>
      <canvas id="xpChart"></canvas>
    </div>

    <!-- Games Summary -->
    <div class="card">
      <h3 class="font-semibold mb-3 flex items-center space-x-2">
        <i data-lucide="pie-chart" class="w-5 h-5"></i>
        <span>Games Summary</span>
      </h3>
      <?php if ($stats['games_played'] > 0): ?>
        <canvas id="gamesChart"></canvas>
      <?php else: ?>
        <div class="flex flex-col items-center text-center p-6">
          <img src="assets/no-games.svg" class="w-32 mb-4" alt="No games">
          <p class="mb-3 text-gray-500">You haven’t played any games yet.</p>
          <a href="games.php" 
             class="px-5 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition">
            🎮 Play your first game
          </a>
        </div>
      <?php endif; ?>
    </div>

  </div>

</div>

<script>
  lucide.createIcons();

  // 📈 XP Chart
  const xpChart = new Chart(document.getElementById('xpChart'), {
    type: 'line',
    data: {
      labels: ["Start", "Now"],
      datasets: [{
        label: "XP",
        data: [0, <?= $stats['xp']; ?>],
        borderColor: "#6C2DC7",
        backgroundColor: "rgba(108,45,199,0.2)",
        fill: true,
        tension: 0.4,
        pointBackgroundColor: "#6C2DC7"
      }]
    },
    options: {
      responsive: true,
      plugins: { legend: { display: false } },
      scales: { y: { beginAtZero: true } }
    }
  });

  // 🎮 Games Chart
  <?php if ($stats['games_played'] > 0): ?>
  const gamesChart = new Chart(document.getElementById('gamesChart'), {
    type: 'doughnut',
    data: {
      labels: ["Played", "Won"],
      datasets: [{
        data: [<?= $stats['games_played']; ?>, <?= $stats['games_won']; ?>],
        backgroundColor: ["#6C2DC7", "#9D4EDD"]
      }]
    },
    options: {
      responsive: true,
      plugins: { legend: { position: 'bottom' } }
    }
  });
  <?php endif; ?>
</script>

</body>
</html>
