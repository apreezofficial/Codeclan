<?php
// Footer.php
?>

<footer class="bg-gray-100 dark:bg-[#111] text-gray-700 dark:text-gray-300 py-12">
  <div class="max-w-7xl mx-auto px-6 md:px-12 flex flex-col md:flex-row justify-between items-center space-y-6 md:space-y-0">

    <!-- Logo + Copyright -->
    <div class="text-center md:text-left">
      <a href="/" class="text-2xl font-extrabold text-[#39FF14] drop-shadow-lg tracking-wide mb-2 inline-block">
        CodeClan
      </a>
      <p class="text-sm">
        &copy; <?= date('Y') ?> CodeClan. All rights reserved.
      </p>
    </div>

    <!-- Quick Links -->
    <nav class="flex space-x-6 text-sm font-medium">
      <a href="/#community" class="hover:text-[#1E88E5] dark:hover:text-[#39FF14] transition">Community</a>
      <a href="/#events" class="hover:text-[#1E88E5] dark:hover:text-[#39FF14] transition">Events</a>
      <a href="/#about" class="hover:text-[#1E88E5] dark:hover:text-[#39FF14] transition">About</a>
      <a href="/#contact" class="hover:text-[#1E88E5] dark:hover:text-[#39FF14] transition">Contact</a>
    </nav>

    <!-- Social Icons -->
    <div class="flex space-x-6 text-xl">
      <a href="https://x.com/codeclan" target="_blank" aria-label="Twitter" class="hover:text-[#1DA1F2] dark:hover:text-[#39FF14] transition">
        <i class="fab fa-twitter"></i>
      </a>
      <a href="https://github.com/codeclan" target="_blank" aria-label="GitHub" class="hover:text-gray-900 dark:hover:text-[#39FF14] transition">
        <i class="fab fa-github"></i>
      </a>
      <a href="https://linkedin.com/in/codeclan" target="_blank" aria-label="LinkedIn" class="hover:text-[#0A66C2] dark:hover:text-[#39FF14] transition">
        <i class="fab fa-linkedin"></i>
      </a>
      <a href="https://chat.whatsapp.com/JPzFKZqQs86Glwe2FnnUGB" target="_blank" aria-label="WhatsApp" class="hover:text-[#25D366] dark:hover:text-[#39FF14] transition">
        <i class="fab fa-whatsapp"></i>
      </a>
    </div>

  </div>
<div class="max-w-7xl mx-auto px-6 md:px-12 mt-12 text-center">
  <form id="subscribeForm" class="flex flex-col sm:flex-row justify-center gap-4 max-w-md mx-auto" novalidate>
    <input 
      type="email" 
      name="email" 
      id="emailInput"
      placeholder="Your email address" 
      required 
      class="w-full sm:flex-1 rounded-md border border-gray-300 dark:border-gray-700 px-4 py-3 text-gray-900 dark:bg-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#39FF14]"
      autocomplete="email"
    />
    <button 
      type="submit" 
      class="bg-[#39FF14] text-black font-semibold rounded-md px-6 py-3 hover:bg-[#2bcf11] transition"
    >
      Subscribe
    </button>
  </form>
  <p id="subscribeMessage" class="mt-3 text-xs text-gray-500 dark:text-gray-400 min-h-[1.5rem]"></p>
</div>
  </footer>
  <script src="all.ts"></script>