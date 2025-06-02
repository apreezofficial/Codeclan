
<style>
  /* Define variables based on system or manual theme */
  :root {
    --scrollbar-track: #f4f4f4; /* Light: Cloud White */
    --scrollbar-thumb: #1e88e5; /* Light: Electric Blue */
  }

  html.dark,
  [data-theme='dark'] {
    --scrollbar-track: #0d0d0d; /* Dark: Jet Black */
    --scrollbar-thumb: #39ff14; /* Dark: Neon Green */
  }

  html {
    scroll-behavior: smooth;
    scroll-padding-top: 4rem;
  }

  :focus-visible {
    scroll-margin-top: 4rem;
  }

  /* Scrollbar for WebKit (Chrome, Edge, Safari) */
  ::-webkit-scrollbar {
    width: 8px;
    height: 8px;
  }

  ::-webkit-scrollbar-track {
    background: var(--scrollbar-track);
  }

  ::-webkit-scrollbar-thumb {
    background-color: var(--scrollbar-thumb);
    border-radius: 10px;
    border: 2px solid var(--scrollbar-track);
    transition: background-color 0.3s ease;
  }

  ::-webkit-scrollbar-thumb:hover {
    filter: brightness(1.2);
  }

  /* Firefox Scrollbar */
  * {
    scrollbar-width: thin;
    scrollbar-color: var(--scrollbar-thumb) var(--scrollbar-track);
  }

  /* Slidebar classes */
  .slidebar {
    transition: transform 0.4s ease;
  }

  .slidebar.hidden {
    transform: translateX(-100%);
  }

  .slidebar.visible {
    transform: translateX(0);
  }
</style>
<nav class="fixed top-0 left-0 w-full z-50 backdrop-blur-lg bg-white dark:bg-black shadow-md border-b border-gray-200 dark:border-gray-800 transition-colors duration-300">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between h-16">
    <!-- Logo -->
    <div class="flex items-center">
<a href="/" class="flex items-center gap-3">
  <img 
    src="assets/img/codeclanlogo3d.png" 
    alt="CodeClan Logo" 
    class="w-10 h-10 sm:w-12 sm:h-12 object-contain drop-shadow-md transition-transform duration-300 hover:scale-105"
  />
  <span class="text-2xl font-black text-[#39FF14] tracking-wide drop-shadow-lg dark:text-[#39FF14]">CodeClan</span>
</a>
    </div>

    <!-- Desktop Nav -->
    <div class="hidden md:flex space-x-6 items-center text-sm font-semibold text-gray-800 dark:text-gray-100">
      <a href="#about" class="hover:text-[#1E88E5] transition duration-300">About</a>
      <a href="#programs" class="hover:text-[#1E88E5] transition duration-300">Programs</a>
      <a href="#community" class="hover:text-[#1E88E5] transition duration-300">Community</a>
      <a href="#events" class="hover:text-[#1E88E5] transition duration-300">Events</a>
      <a href="#join" class="bg-[#FF6D00] hover:bg-orange-600 text-white px-5 py-2 rounded-full shadow-xl transition duration-300">Join Us</a>

      <!-- Theme Toggle -->
      <button id="themeToggle" class="ml-4 text-xl text-gray-700 dark:text-gray-300">
        <i class="fas fa-moon hidden dark:inline-block animate-pulse"></i>
        <i class="fas fa-sun dark:hidden animate-spin-slow"></i>
      </button>
    </div>

    <!-- Mobile Menu Icon -->
    <div class="md:hidden flex items-center">
      <button id="menuBtn" class="text-2xl text-gray-800 dark:text-gray-200">
        <i class="fas fa-bars"></i>
      </button>
    </div>
  </div>

  <!-- Mobile Sidebar -->
  <div id="sidebar" class="fixed top-0 left-0 w-64 h-full bg-white dark:bg-[#0D0D0D] shadow-xl border-r border-gray-300 dark:border-gray-800 transform -translate-x-full transition-transform duration-300 z-50">
    <div class="flex justify-between items-center p-4 border-b border-gray-300 dark:border-gray-700">
      <span class="text-xl font-extrabold text-[#39FF14]">CodeClan</span>
      <button id="closeSidebar" class="text-xl text-gray-800 dark:text-gray-200">
        <i class="fas fa-times"></i>
      </button>
    </div>
<nav
  class="flex flex-col gap-6 p-6 text-lg font-semibold
    text-gray-900 dark:text-gray-100
    bg-white dark:bg-[#0D0D0D]"
>
  <a href="#about" class="hover:text-[#1E88E5] focus:text-[#1E88E5] transition-colors duration-300 outline-none">About</a>
  <a href="#programs" class="hover:text-[#1E88E5] focus:text-[#1E88E5] transition-colors duration-300 outline-none">Programs</a>
  <a href="#community" class="hover:text-[#1E88E5] focus:text-[#1E88E5] transition-colors duration-300 outline-none">Community</a>
  <a href="#events" class="hover:text-[#1E88E5] focus:text-[#1E88E5] transition-colors duration-300 outline-none">Events</a>
  <a href="#join"
     class="bg-[#FF6D00] hover:bg-orange-600 focus:bg-orange-600
            text-white px-6 py-3 text-center rounded-full shadow-md
            transition duration-300 outline-none"
  >
    Join Us
  </a>

  <!-- Theme Toggle Mobile -->
  <button
    id="themeToggleMobile"
    class="mt-8 flex items-center justify-center gap-3 text-xl
           text-gray-800 dark:text-gray-200
           px-4 py-3 rounded-lg
           bg-gray-100 dark:bg-[#1A1A1A]
           hover:bg-gray-200 dark:hover:bg-[#333333]
           transition-colors duration-300
           outline-none focus:ring-2 focus:ring-[#39FF14]"
    aria-label="Toggle theme"
  >
    <i class="fas fa-moon hidden dark:inline-block animate-pulse"></i>
    <i class="fas fa-sun dark:hidden animate-spin-slow"></i>
    <span class="select-none">Toggle Theme</span>
  </button>
</nav>
  </div>

  <!-- Overlay -->
  <div id="overlay" class="fixed inset-0 bg-black/50 hidden z-40"></div>
</nav>
<script>
  const menuBtn = document.getElementById('menuBtn');
  const sidebar = document.getElementById('sidebar');
  const overlay = document.getElementById('overlay');
  const closeSidebar = document.getElementById('closeSidebar');
  const sidebarLinks = sidebar.querySelectorAll('a');

  const openSidebar = () => {
    sidebar.classList.remove('-translate-x-full');
    sidebar.classList.add('translate-x-0');
    overlay.classList.remove('hidden');
    overlay.classList.add('bg-black/50', 'backdrop-blur-sm');
  };

  const closeSidebarFn = () => {
    sidebar.classList.add('-translate-x-full');
    sidebar.classList.remove('translate-x-0');
    overlay.classList.add('hidden');
    overlay.classList.remove('bg-black/50', 'backdrop-blur-sm');
  };

  menuBtn.addEventListener('click', openSidebar);
  closeSidebar.addEventListener('click', closeSidebarFn);
  overlay.addEventListener('click', closeSidebarFn);

  // Close sidebar when any link inside it is clicked
  sidebarLinks.forEach(link => {
    link.addEventListener('click', closeSidebarFn);
  });

  const themeToggle = document.getElementById('themeToggle');
  const themeToggleMobile = document.getElementById('themeToggleMobile');

  // Initial theme setup
  const savedTheme = localStorage.getItem('theme');

  if (savedTheme === 'dark') {
    document.documentElement.classList.add('dark');
  } else if (savedTheme === 'light') {
    document.documentElement.classList.remove('dark');
  } else {
    // No preference saved — use system preference
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    document.documentElement.classList.toggle('dark', prefersDark);
    localStorage.setItem('theme', prefersDark ? 'dark' : 'light');
  }

//forst timer uses system theme 
  if (!savedTheme) {
    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
      const prefersDark = e.matches;
      document.documentElement.classList.toggle('dark', prefersDark);
      localStorage.setItem('theme', prefersDark ? 'dark' : 'light');
    });
  }

  // User toggle theme manually
  function toggleTheme() {
    const isDark = document.documentElement.classList.toggle('dark');
    localStorage.setItem('theme', isDark ? 'dark' : 'light');
  }

  themeToggle?.addEventListener('click', toggleTheme);
  themeToggleMobile?.addEventListener('click', toggleTheme);
</script>