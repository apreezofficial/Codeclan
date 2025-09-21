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
  <title>🎮 Games</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      darkMode: 'class',
      theme: {
        extend: {
          colors: {
            brand: "#6B21A8",
            'brand-light': '#8B5CF6',
          },
        },
      },
    };
  </script>
  <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="bg-gradient-to-br from-purple-900 via-gray-900 to-indigo-900 dark:bg-gray-900 text-gray-100 min-h-screen">

<div class="max-w-6xl mx-auto px-4 py-10">
  <!-- Header -->
  <div class="flex items-center justify-between mb-10">
    <h1 class="text-4xl font-extrabold bg-clip-text text-transparent bg-gradient-to-r from-purple-400 to-pink-400">
      🎮 Game Library
    </h1>
  </div>

  <!-- Games Grid -->
  <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
    <?php foreach ($games as $game): ?>
      <?php 
        $lastResult = $userResults[$game['id']][0] ?? null;
        $gameUrl = $game['id'] . '.php';
        $isPlayable = file_exists($_SERVER['DOCUMENT_ROOT'] . $gameUrl);
      ?>
      <div class="group relative bg-white dark:bg-gray-800 rounded-2xl shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1 overflow-hidden flex flex-col">
        
        <!-- Badge Overlay -->
        <?php if (!$isPlayable): ?>
          <div class="absolute top-0 right-0 bg-gradient-to-r from-red-500 to-pink-500 text-white text-xs font-bold px-3 py-1 rounded-bl-lg z-10">
            🚧 In Prod
          </div>
        <?php endif; ?>

        <!-- Content -->
        <div class="p-6 flex flex-col h-full">
          <!-- Title + Category -->
          <div class="flex items-start justify-between mb-4">
            <h2 class="text-xl font-bold text-gray-800 dark:text-white group-hover:text-brand dark:group-hover:text-purple-300 transition-colors">
              <?= htmlspecialchars($game['title']) ?>
            </h2>
            <?php if ($game['category']): ?>
              <span class="px-3 py-1 text-xs font-medium rounded-full bg-purple-100 dark:bg-purple-700/50 text-purple-700 dark:text-purple-300 whitespace-nowrap">
                <?= htmlspecialchars($game['category']) ?>
              </span>
            <?php endif; ?>
          </div>

          <!-- Description -->
          <p class="text-sm text-gray-600 dark:text-gray-300 mb-6 flex-grow leading-relaxed">
            <?= htmlspecialchars($game['description']) ?>
          </p>

          <!-- User Result -->
          <div class="mb-6">
            <?php if ($lastResult): ?>
              <div class="flex items-center gap-2 text-sm mb-1">
                <?php if ($lastResult['result'] === 'win'): ?>
                  <i data-lucide="trophy" class="text-yellow-500 w-4 h-4"></i>
                  <span class="text-green-600 dark:text-green-400 font-medium">
                    Won (<?= $lastResult['score'] ?> XP)
                  </span>
                <?php elseif ($lastResult['result'] === 'lose'): ?>
                  <i data-lucide="x-circle" class="text-red-500 w-4 h-4"></i>
                  <span class="text-red-600 dark:text-red-400 font-medium">
                    Lost (<?= $lastResult['score'] ?> XP)
                  </span>
                <?php else: ?>
                  <i data-lucide="minus-circle" class="text-gray-400 w-4 h-4"></i>
                  <span class="text-gray-600 dark:text-gray-300 font-medium">
                    Draw (<?= $lastResult['score'] ?> XP)
                  </span>
                <?php endif; ?>
              </div>
              <p class="text-xs text-gray-500 dark:text-gray-400">
                Last played: <?= date("M d, Y", strtotime($lastResult['played_at'])) ?>
              </p>
            <?php else: ?>
              <span class="text-xs text-gray-500 dark:text-gray-400 flex items-center gap-1">
                <i data-lucide="clock" class="w-3 h-3"></i>
                Not played yet
              </span>
            <?php endif; ?>
          </div>

          <!-- Play Button -->
          <a 
            href="<?= $isPlayable ? $gameUrl : '#' ?>"
            class="w-full py-3 px-4 text-center font-semibold rounded-xl transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500
                   <?= $isPlayable 
                      ? 'bg-gradient-to-r from-brand to-purple-700 hover:from-brand-light hover:to-purple-600 text-white shadow-md hover:shadow-lg cursor-pointer' 
                      : 'bg-gray-200 dark:bg-gray-700 text-gray-500 dark:text-gray-400 cursor-not-allowed opacity-70' ?>"
            <?= $isPlayable ? '' : 'aria-disabled="true" tabindex="-1"' ?>
          >
            <?= $isPlayable ? '▶️ Play Now' : '🚧 In Production' ?>
          </a>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</div>

<script>
  // Initialize Lucide Icons
  lucide.createIcons();

  // Optional: Disable click on "In Production" cards
  document.querySelectorAll('a[aria-disabled="true"]').forEach(link => {
    link.addEventListener('click', (e) => {
      e.preventDefault();
      alert('This game is still in production. Check back soon! 🛠️');
    });
  });
</script>

</body>
</html>
