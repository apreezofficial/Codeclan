<?php
session_start();
require_once "../conn.php";

if (!isset($_COOKIE['user'])) {
    header("Location: ../auth");
    exit;
}

$user = json_decode($_COOKIE['user'], true);

$stmt = $pdo->prepare("SELECT * FROM users WHERE google_id = :gid LIMIT 1");
$stmt->execute([":gid" => $user['id'] ?? '']);
$dbUser = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$dbUser) {
    setcookie("user", "", time() - 3600, "/");
    header("Location: ../auth");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en" class="transition-colors duration-300">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    if (localStorage.theme === 'dark' || 
        (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
      document.documentElement.classList.add('dark')
    }
  </script>
</head>
<body class="bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-gray-100">

<nav class="flex items-center justify-between px-6 py-3 bg-purple-600 dark:bg-purple-800 shadow-lg">
  <div class="flex items-center space-x-3">
    <img src="/assets/img/codeclanlogo3d.png" alt="Logo" class="w-10 h-10 rounded-full">
    <span class="text-xl font-bold text-white">CodeClan</span>
  </div>

  <div class="hidden md:flex space-x-6 text-white">
    <a href="#" class="hover:text-yellow-300">Leaderboard</a>
    <a href="#" class="hover:text-yellow-300">Games</a>
    <a href="#" class="hover:text-yellow-300">Chats</a>
    <a href="#" class="hover:text-yellow-300">Notifications</a>
  </div>

  <div class="flex items-center space-x-4">
    <button id="themeToggle" class="p-2 rounded-lg bg-white/20 hover:bg-white/30 dark:bg-gray-700 dark:hover:bg-gray-600">
      <svg id="themeIcon" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
          d="M12 3v1m0 16v1m8.66-13.66l-.71.71M4.05 19.95l-.71-.71M21 12h-1M4 12H3m16.95 7.95l-.71-.71M4.05 4.05l-.71.71M12 8a4 4 0 100 8 4 4 0 000-8z"/>
      </svg>
    </button>

    <div class="relative">
      <button id="profileBtn" class="flex items-center focus:outline-none">
        <img src="<?php echo htmlspecialchars($dbUser['picture']); ?>" 
             alt="Profile" class="w-10 h-10 rounded-full border-2 border-white">
      </button>
      <div id="profileMenu" class="hidden absolute right-0 mt-3 w-56 bg-white dark:bg-gray-800 rounded-lg shadow-lg py-2 z-50">
        <div class="px-4 py-2 border-b dark:border-gray-700">
          <p class="text-sm font-semibold text-gray-800 dark:text-gray-100">
            <?php echo htmlspecialchars($dbUser['name']); ?>
          </p>
          <p class="text-xs text-gray-600 dark:text-gray-400">
            <?php echo htmlspecialchars($dbUser['email']); ?>
          </p>
        </div>
        <a href="#" class="block px-4 py-2 text-sm hover:bg-gray-100 dark:hover:bg-gray-700">Profile</a>
        <a href="#" class="block px-4 py-2 text-sm hover:bg-gray-100 dark:hover:bg-gray-700">Settings</a>
        <a href="/logout.php" class="block px-4 py-2 text-sm text-red-600 hover:bg-gray-100 dark:hover:bg-gray-700">Logout</a>
      </div>
    </div>
  </div>
</nav>

<script>
  document.getElementById('themeToggle').addEventListener('click', () => {
    document.documentElement.classList.toggle('dark');
    localStorage.setItem('theme', document.documentElement.classList.contains('dark') ? 'dark' : 'light');
  });

  const profileBtn = document.getElementById('profileBtn');
  const profileMenu = document.getElementById('profileMenu');
  profileBtn.addEventListener('click', () => profileMenu.classList.toggle('hidden'));
  document.addEventListener('click', e => {
    if (!profileBtn.contains(e.target) && !profileMenu.contains(e.target)) profileMenu.classList.add('hidden');
  });
</script>

</body>
</html>
