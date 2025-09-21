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
$user_id = $dbUser['id']; 
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
  <title>CodeClan Dashboard</title>
  <!-- Tailwind CSS -->
  <script src="https://cdn.tailwindcss.com"></script>
  <!-- Lucide Icons via CDN -->
  <script src="https://unpkg.com/lucide@latest"></script>
  <script>
    // Initialize Lucide Icons
    document.addEventListener("DOMContentLoaded", function() {
      lucide.createIcons();
    });

    // Dark Mode Toggle
    if (localStorage.theme === 'dark' || 
        (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
      document.documentElement.classList.add('dark');
    }
  </script>
  <style>
    .fade-in {
      animation: fadeIn 0.3s ease-in-out;
    }
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(-10px); }
      to { opacity: 1; transform: translateY(0); }
    }
  </style>
</head>
<body class="bg-gray-50 dark:bg-gray-950 text-gray-900 dark:text-gray-100 min-h-screen">

  <!-- Mobile Menu Overlay -->
  <div id="mobileMenuOverlay" class="fixed inset-0 bg-black bg-opacity-50 z-40 hidden md:hidden"></div>

  <!-- Navbar -->
  <nav class="sticky top-0 z-50 flex items-center justify-between px-4 sm:px-6 py-3 bg-gradient-to-r from-purple-600 to-indigo-600 dark:from-purple-800 dark:to-indigo-900 shadow-lg">
    <a href="index.php" class="flex items-center space-x-3">
      <img src="../assets/img/codeclanlogo3d.png" alt="CodeClan Logo" class="w-10 h-10 rounded-full shadow-md">
      <span class="hidden sm:block text-xl font-extrabold text-white tracking-tight">CodeClan</span>
    </a>

    <!-- Desktop Nav (Hidden on Mobile) -->
    <div class="hidden md:flex space-x-6 text-white font-medium">
      <a href="leaderboard.php" class="flex items-center space-x-2 hover:text-yellow-300 transition group">
        <i data-lucide="trophy" class="h-5 w-5 group-hover:scale-110 transition"></i>
        <span>Leaderboard</span>
      </a>
      <a href="games.php" class="flex items-center space-x-2 hover:text-yellow-300 transition group">
        <i data-lucide="gamepad-2" class="h-5 w-5 group-hover:scale-110 transition"></i>
        <span>Games</span>
      </a>
      <a href="chats.php" class="flex items-center space-x-2 hover:text-yellow-300 transition group">
        <i data-lucide="message-circle" class="h-5 w-5 group-hover:scale-110 transition"></i>
        <span>Chats</span>
      </a>
    </div>

    <!-- Mobile Hamburger Button -->
    <button id="mobileMenuButton" class="md:hidden p-2 rounded-lg text-white hover:bg-white/20 transition">
      <i data-lucide="menu" class="h-6 w-6"></i>
    </button>

    <!-- Right Side Icons (Profile + Theme) -->
    <div class="hidden md:flex items-center space-x-4">
      <!-- Theme Toggle -->
      <button id="themeToggle" class="p-2.5 rounded-lg bg-white/20 hover:bg-white/30 dark:bg-gray-700 dark:hover:bg-gray-600 transition-all duration-200">
        <i id="themeIcon" data-lucide="sun" class="h-5 w-5 text-white"></i>
      </button>

      <!-- Profile Dropdown -->
      <div class="relative">
        <button id="profileBtn" class="flex items-center focus:outline-none group">
          <img src="<?php echo htmlspecialchars($dbUser['picture']); ?>" 
               alt="Profile" class="w-10 h-10 rounded-full border-2 border-white shadow-sm group-hover:scale-105 transition">
        </button>
        <div id="profileMenu" class="hidden absolute right-0 mt-3 w-56 bg-white dark:bg-gray-800 rounded-xl shadow-xl py-2 z-50 border border-gray-200 dark:border-gray-700 fade-in">
          <div class="px-4 py-3 border-b dark:border-gray-700">
            <p class="text-sm font-semibold text-gray-800 dark:text-gray-100">
              <?php echo htmlspecialchars($dbUser['name']); ?>
            </p>
            <p class="text-xs text-gray-600 dark:text-gray-400 truncate">
              <?php echo htmlspecialchars($dbUser['email']); ?>
            </p>
          </div>
          <a href="profile.php" class="flex items-center px-4 py-2.5 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 transition">
            <i data-lucide="user" class="mr-2 h-4 w-4"></i>
            Profile
          </a>
          <a href="./settings.php" class="flex items-center px-4 disabled py-2.5 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 transition">
            <i data-lucide="settings" class="mr-2 h-4 w-4"></i>
            Settings
          </a>
          <a href="./logout.php" class="flex items-center px-4 py-2.5 text-sm text-red-600 hover:bg-gray-100 dark:hover:bg-gray-700 transition">
            <i data-lucide="log-out" class="mr-2 h-4 w-4"></i>
            Logout
          </a>
        </div>
      </div>
    </div>
  </nav>

  <!-- Mobile Sliding Menu -->
  <div id="mobileMenu" class="fixed top-0 left-0 w-64 h-full bg-white dark:bg-gray-900 shadow-2xl transform -translate-x-full transition-transform duration-300 ease-in-out z-50 md:hidden flex flex-col">
    <div class="flex items-center justify-between p-4 border-b dark:border-gray-800">
      <span class="text-xl font-bold text-purple-600 dark:text-purple-400">CodeClan</span>
      <button id="closeMobileMenu" class="p-2 rounded-lg text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800">
        <i data-lucide="x" class="h-6 w-6"></i>
      </button>
    </div>
    <nav class="flex-1 p-4 space-y-2">
      <a href="leaderboard.php" class="flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-700 dark:text-gray-200 hover:bg-purple-50 dark:hover:bg-gray-800 transition group">
        <i data-lucide="trophy" class="h-5 w-5 text-yellow-500 group-hover:scale-110 transition"></i>
        <span class="font-medium">Leaderboard</span>
      </a>
      <a href="games.php" class="flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-700 dark:text-gray-200 hover:bg-purple-50 dark:hover:bg-gray-800 transition group">
        <i data-lucide="gamepad-2" class="h-5 w-5 text-green-500 group-hover:scale-110 transition"></i>
        <span class="font-medium">Games</span>
      </a>
      <a href="chats.php" class="flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-700 dark:text-gray-200 hover:bg-purple-50 dark:hover:bg-gray-800 transition group">
        <i data-lucide="message-circle" class="h-5 w-5 text-blue-500 group-hover:scale-110 transition"></i>
        <span class="font-medium">Chats</span>
      </a>
    </nav>
    <div class="p-4 border-t dark:border-gray-800">
      <button id="mobileThemeToggle" class="flex items-center w-full px-4 py-2.5 rounded-lg text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 transition">
        <i id="mobileThemeIcon" data-lucide="sun" class="mr-3 h-5 w-5"></i>
        <span>Toggle Theme</span>
      </button>
    </div>
  </div>

  <script>
    // Initialize Lucide Icons (again for dynamic content)
    lucide.createIcons();

    // Theme Toggle Function
    const toggleDarkMode = () => {
      const isDark = document.documentElement.classList.toggle('dark');
      localStorage.setItem('theme', isDark ? 'dark' : 'light');
      updateThemeIcon(isDark);
    };

    // Update Theme Icon (Sun/Moon)
    const updateThemeIcon = (isDark) => {
      const icons = document.querySelectorAll('[data-lucide="sun"], [data-lucide="moon"]');
      icons.forEach(icon => {
        if (isDark) {
          icon.setAttribute('data-lucide', 'moon');
        } else {
          icon.setAttribute('data-lucide', 'sun');
        }
      });
      lucide.createIcons(); // Re-render icons
    };

    // Set initial theme icon
    const isDarkMode = document.documentElement.classList.contains('dark');
    updateThemeIcon(isDarkMode);

    // Event Listeners for Theme Toggle
    document.getElementById('themeToggle').addEventListener('click', toggleDarkMode);
    document.getElementById('mobileThemeToggle').addEventListener('click', toggleDarkMode);

    // Profile Dropdown Toggle
    const profileBtn = document.getElementById('profileBtn');
    const profileMenu = document.getElementById('profileMenu');
    
    const toggleProfileMenu = () => profileMenu.classList.toggle('hidden');

    profileBtn.addEventListener('click', toggleProfileMenu);

    // Close profile menu when clicking outside
    document.addEventListener('click', e => {
      if (!profileBtn.contains(e.target) && !profileMenu.contains(e.target)) {
        profileMenu.classList.add('hidden');
      }
    });

    // Mobile Menu Toggle
    const mobileMenuButton = document.getElementById('mobileMenuButton');
    const mobileMenu = document.getElementById('mobileMenu');
    const closeMobileMenu = document.getElementById('closeMobileMenu');
    const mobileMenuOverlay = document.getElementById('mobileMenuOverlay');

    const openMobileMenu = () => {
      mobileMenu.classList.remove('-translate-x-full');
      mobileMenuOverlay.classList.remove('hidden');
      document.body.style.overflow = 'hidden'; // Prevent background scrolling
    };

    const closeMobileMenuFunc = () => {
      mobileMenu.classList.add('-translate-x-full');
      mobileMenuOverlay.classList.add('hidden');
      document.body.style.overflow = ''; // Restore scrolling
    };

    mobileMenuButton.addEventListener('click', openMobileMenu);
    closeMobileMenu.addEventListener('click', closeMobileMenuFunc);
    mobileMenuOverlay.addEventListener('click', closeMobileMenuFunc);

    // Close mobile menu on Escape key
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && !mobileMenu.classList.contains('-translate-x-full')) {
        closeMobileMenuFunc();
      }
    });
  </script>
    <!-- Ad Modal -->
<div id="adModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center">
  <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl p-6 max-w-sm w-full mx-4 fade-in">
    <h2 class="text-xl font-bold text-purple-600 dark:text-purple-400 mb-3">Visit ProForms!</h2>
    <p class="text-gray-600 dark:text-gray-300 mb-4">Boost your productivity with ProForms. Try it now and get instant form submissions on your web Forms!!!</p>
    <button onclick="closeAd()" class="w-full px-4 py-2 bg-purple-600 dark:bg-purple-700 text-white rounded-lg hover:bg-purple-700 dark:hover:bg-purple-800 transition">Close</button>
  </div>
</div>

<script>
function manageAdDisplay() {
  let visitCount = parseInt(localStorage.getItem('visitCount')) || 0;
  
  // Increment visit count
  visitCount++;
  localStorage.setItem('visitCount', visitCount);

  const shouldShowAd = visitCount === 5 || visitCount === 100 || (visitCount >= 200 && visitCount % 200 === 0);

  if (shouldShowAd) {
    const adModal = document.getElementById('adModal');
    adModal.classList.remove('hidden');
  }
}

// Close ad function
function closeAd() {
  const adModal = document.getElementById('adModal');
  adModal.classList.add('hidden');
}

// Run on page load
document.addEventListener('DOMContentLoaded', () => {
  manageAdDisplay();
  
});
</script>
</body>
</html>
