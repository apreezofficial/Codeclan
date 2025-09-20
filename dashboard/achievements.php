<?php
include 'top_bar.php';
$stmt = $pdo->prepare("
    SELECT a.id, a.name, a.description, a.icon, a.single,
           ua.unlocked_at,
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
            brand: "#6B21A8", // purple vibe
          },
        },
      },
    };
  </script>
</head>
<body class="bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-gray-100">

<div class="max-w-5xl mx-auto px-4 py-8">
  <!-- Header -->
  <div class="flex items-center justify-between mb-8">
    <h1 class="text-3xl font-bold flex items-center gap-2">
      🏅 Achievements
    </h1>
  </div>

  <!-- Achievements Grid -->
  <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
    <?php foreach ($achievements as $ach): ?>
      <?php $unlocked = !empty($ach['unlocked_at']); ?>
      <div class="bg-white dark:bg-gray-800 rounded-2xl shadow p-6 flex flex-col items-center text-center 
                  <?= $unlocked ? '' : 'opacity-50' ?>">
        
        <!-- Badge Icon -->
        <img src="<?= htmlspecialchars($ach['icon']) ?>" alt="Badge" 
             class="w-16 h-16 mb-4 <?= $unlocked ? '' : 'grayscale' ?>">
        
        <!-- Title -->
        <h2 class="text-lg font-semibold mb-1">
          <?= htmlspecialchars($ach['name']) ?>
        </h2>

        <!-- Description -->
        <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">
          <?= htmlspecialchars($ach['description']) ?>
        </p>

        <!-- Unlock Status -->
        <?php if ($unlocked): ?>
          <span class="px-3 py-1 bg-brand text-white rounded-full text-xs font-medium">
            ✅ Unlocked on <?= date("M d, Y", strtotime($ach['unlocked_at'])) ?>
          </span>
        <?php elseif ($ach['single'] && $ach['owner_name']): ?>
          <div class="flex items-center gap-2 mt-2 text-xs text-gray-600 dark:text-gray-400">
            <img src="<?= htmlspecialchars($ach['owner_picture']) ?>" 
                 class="w-6 h-6 rounded-full border">
            <div class="text-left">
              <p>Claimed by <strong><?= htmlspecialchars($ach['owner_name']) ?></strong></p>
              <p class="text-[11px]">on <?= date("M d, Y", strtotime($ach['owner_unlocked_at'])) ?></p>
            </div>
          </div>
        <?php else: ?>
          <span class="px-3 py-1 bg-gray-300 dark:bg-gray-700 rounded-full text-xs font-medium">
            🔒 Locked
          </span>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
  </div>
</div>

</body>
</html>
