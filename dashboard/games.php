<?php
include 'top_bar.php'; 

$stmt = $pdo->prepare("
    SELECT g.id, g.title, g.description, g.created_at,
           c.name AS category
    FROM games g
    LEFT JOIN game_categories c ON g.category_id = c.id
    ORDER BY g.created_at DESC
");
$stmt->execute();
$games = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch last results for current user
$resultStmt = $pdo->prepare("
    SELECT gr.game_id, gr.score, gr.result, gr.played_at
    FROM game_results gr
    WHERE gr.user_id = ?
    ORDER BY gr.played_at DESC
");
$resultStmt->execute([$user_id]);
$userResults = $resultStmt->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en" class="dark">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Games</title>
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
  <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-gray-100">

<div class="max-w-6xl mx-auto px-4 py-8">
  <!-- Header -->
  <div class="flex items-center justify-between mb-8">
    <h1 class="text-3xl font-bold flex items-center gap-2">
      🎮 Games
    </h1>
  </div>

  <!-- Games Grid -->
  <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
    <?php foreach ($games as $game): ?>
      <?php 
        $lastResult = $userResults[$game['id']][0] ?? null;
      ?>
      <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-md hover:shadow-xl transition-shadow p-6 flex flex-col">
        
        <!-- Title + Category -->
        <div class="flex items-center justify-between mb-3">
          <h2 class="text-xl font-semibold text-brand dark:text-purple-300">
            <?= htmlspecialchars($game['title']) ?>
          </h2>
          <?php if ($game['category']): ?>
            <span class="px-2 py-1 text-xs rounded-full bg-purple-100 dark:bg-purple-700 text-purple-700 dark:text-purple-200">
              <?= htmlspecialchars($game['category']) ?>
            </span>
          <?php endif; ?>
        </div>

        <!-- Description -->
        <p class="text-sm text-gray-600 dark:text-gray-400 flex-grow">
          <?= htmlspecialchars($game['description']) ?>
        </p>

        <!-- User Result -->
        <div class="mt-4">
          <?php if ($lastResult): ?>
            <div class="flex items-center gap-2 text-sm">
              <?php if ($lastResult['result'] === 'win'): ?>
                <i data-lucide="trophy" class="text-yellow-500"></i>
                <span class="text-green-600 dark:text-green-400 font-medium">
                  Won (Score: <?= $lastResult['score'] ?>)
                </span>
              <?php elseif ($lastResult['result'] === 'lose'): ?>
                <i data-lucide="x-circle" class="text-red-500"></i>
                <span class="text-red-600 dark:text-red-400 font-medium">
                  Lost (Score: <?= $lastResult['score'] ?>)
                </span>
              <?php else: ?>
                <i data-lucide="minus-circle" class="text-gray-400"></i>
                <span class="text-gray-600 dark:text-gray-300 font-medium">
                  Draw (Score: <?= $lastResult['score'] ?>)
                </span>
              <?php endif; ?>
            </div>
            <p class="text-xs text-gray-500 dark:text-gray-400">
              Last played: <?= date("M d, Y", strtotime($lastResult['played_at'])) ?>
            </p>
          <?php else: ?>
            <span class="text-xs text-gray-500 dark:text-gray-400">
              ⏳ Not played yet
            </span>
          <?php endif; ?>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</div>

<script>
  lucide.createIcons();
</script>

</body>
</html>
