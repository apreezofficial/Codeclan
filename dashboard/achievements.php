<?php
include 'top_bar.php';

$stmt = $pdo->prepare("
    SELECT a.id, a.name, a.description, a.icon, a.single,
           ua.unlocked_at,
           u.id AS owner_id,
           u.name AS owner_name,
           u.picture AS owner_picture,
           ua_global.unlocked_at AS owner_unlocked_at
    FROM achievements a
    LEFT JOIN user_achievements ua 
      ON a.id = ua.achievement_id AND ua.user_id = :uid
    LEFT JOIN user_achievements ua_global 
      ON a.single = 1 AND a.id = ua_global.achievement_id
    LEFT JOIN users u 
      ON ua_global.user_id = u.id
    ORDER BY a.id ASC
");
$stmt->execute([":uid" => $user_id]);
$achievements = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en" class="dark">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Achievements</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      darkMode: 'class',
      theme: {
        extend: {
          colors: {
            brand: "#6B21A8",
            brandLight: "#9333EA"
          },
        },
      },
    };
  </script>
</head>
<body class="bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-gray-100">

<div class="max-w-6xl mx-auto px-4 py-10">
  <!-- Header -->
  <div class="flex items-center justify-between mb-10">
    <h1 class="text-4xl font-extrabold flex items-center gap-3 text-brand dark:text-brandLight">
      <i data-lucide="trophy" class="w-8 h-8"></i> Achievements
    </h1>
  </div>

  <!-- Achievements Grid -->
  <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
    <?php foreach ($achievements as $ach): ?>
      <?php $unlocked = !empty($ach['unlocked_at']); ?>
      <div class="relative group rounded-2xl shadow-xl overflow-hidden bg-gradient-to-br from-purple-500/10 to-purple-700/10 dark:from-purple-600/20 dark:to-purple-800/20 hover:scale-[1.02] hover:shadow-2xl transition-all duration-300">
        <div class="p-6 flex flex-col items-center text-center">
          
          <!-- Badge Icon -->
          <div class="w-20 h-20 mb-4 flex items-center justify-center bg-gradient-to-tr from-brand to-brandLight rounded-full shadow-lg group-hover:scale-105 transition-transform">
            <img src="<?= htmlspecialchars($ach['icon']) ?>" alt="Badge" 
                 class="w-12 h-12 <?= $unlocked ? '' : 'grayscale opacity-70' ?>">
          </div>
          
          <!-- Title -->
          <h2 class="text-lg font-bold mb-1 text-gray-900 dark:text-white">
            <?= htmlspecialchars($ach['name']) ?>
          </h2>

          <!-- Description -->
          <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
            <?= htmlspecialchars($ach['description']) ?>
          </p>

          <!-- Status -->
          <?php if ($unlocked): ?>
            <span class="px-3 py-1 rounded-full text-xs font-semibold bg-green-500 text-white">
              🏆 Claimed by You on <?= date("M d, Y", strtotime($ach['unlocked_at'])) ?>
            </span>
          <?php elseif ($ach['single'] && $ach['owner_name']): ?>
            <div class="flex items-center gap-2 mt-3 p-2 rounded-lg bg-gray-100 dark:bg-gray-700 w-full">
              <img src="<?= htmlspecialchars($ach['owner_picture']) ?>" 
                   class="w-8 h-8 rounded-full border shadow">
              <div class="text-left text-xs">
                <p class="font-medium text-gray-800 dark:text-gray-200">
                  Claimed by <?= htmlspecialchars($ach['owner_name']) ?>
                </p>
                <p class="text-[11px] text-gray-600 dark:text-gray-400">
                  <?= date("M d, Y", strtotime($ach['owner_unlocked_at'])) ?>
                </p>
              </div>
            </div>
          <?php else: ?>
            <span class="px-3 py-1 rounded-full text-xs font-semibold bg-gray-300 dark:bg-gray-600">
              🔒 Locked
            </span>
          <?php endif; ?>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</div>

<script src="https://unpkg.com/lucide@latest"></script>
<script>lucide.createIcons();</script>
</body>
</html>
