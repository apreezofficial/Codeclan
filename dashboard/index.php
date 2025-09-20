<?php
session_start();
require_once "../conn.php";

// 🔐 Auth check (same pattern as top_bar.php)
if (!isset($_COOKIE['user'])) {
    header("Location: ../auth");
    exit;
}

$user = json_decode($_COOKIE['user'], true);

$stmt = $pdo->prepare("SELECT * FROM users WHERE google_id = :gid OR pxxl_id = :gid LIMIT 1");
$stmt->execute([":gid" => $user['id'] ?? '']);
$dbUser = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$dbUser) {
    setcookie("user", "", time() - 3600, "/");
    header("Location: ../auth");
    exit;
}

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
</head>
<body class="bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-gray-100">

  <!-- 🔝 Top navigation -->
  <?php include "top_bar.php"; ?>

  <!-- 📊 Dashboard -->
  <div class="p-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

    <!-- Profile Card -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-5 flex flex-col items-center text-center">
      <img src="<?php echo htmlspecialchars($dbUser['picture']); ?>" 
           class="w-20 h-20 rounded-full border-4 border-brand" alt="Profile">
      <h2 class="mt-3 text-lg font-bold"><?php echo htmlspecialchars($dbUser['name']); ?></h2>
      <p class="text-sm text-gray-500 dark:text-gray-400"><?php echo htmlspecialchars($dbUser['email']); ?></p>
      <div class="mt-4 flex space-x-6">
        <div class="text-center">
          <p class="text-xl font-bold text-brand"><?php echo $stats['level']; ?></p>
          <span class="text-xs text-gray-500">Level</span>
        </div>
        <div class="text-center">
          <p class="text-xl font-bold text-brand"><?php echo $stats['xp']; ?></p>
          <span class="text-xs text-gray-500">XP</span>
        </div>
        <div class="text-center">
          <p class="text-xl font-bold text-brand"><?php echo $stats['games_won']; ?></p>
          <span class="text-xs text-gray-500">Wins</span>
        </div>
      </div>
    </div>

    <!-- XP Growth Chart -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-5">
      <h3 class="font-semibold mb-3 flex items-center space-x-2">
        <i data-lucide="bar-chart-3" class="w-5 h-5"></i>
        <span>XP Progress</span>
      </h3>
      <canvas id="xpChart"></canvas>
    </div>

    <!-- Games Played Chart -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-5">
      <h3 class="font-semibold mb-3 flex items-center space-x-2">
        <i data-lucide="gamepad-2" class="w-5 h-5"></i>
        <span>Games Summary</span>
      </h3>
      <canvas id="gamesChart"></canvas>
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
        data: [0, <?php echo $stats['xp']; ?>],
        borderColor: "#6C2DC7",
        backgroundColor: "rgba(108,45,199,0.2)",
        fill: true,
        tension: 0.3
      }]
    },
    options: {
      responsive: true,
      plugins: { legend: { display: false } }
    }
  });

  // 🎮 Games Chart
  const gamesChart = new Chart(document.getElementById('gamesChart'), {
    type: 'doughnut',
    data: {
      labels: ["Played", "Won"],
      datasets: [{
        data: [<?php echo $stats['games_played']; ?>, <?php echo $stats['games_won']; ?>],
        backgroundColor: ["#6C2DC7", "#9D4EDD"]
      }]
    },
    options: {
      responsive: true,
      plugins: { legend: { position: 'bottom' } }
    }
  });
</script>

</body>
</html>
