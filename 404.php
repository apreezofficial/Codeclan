<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>404 - Page Not Found</title>
  <script src="https://cdn.tailwindcss.com?plugins=typography"></script>
  <script>
    tailwind.config = {
      darkMode: 'class',
      theme: {
        extend: {
          colors: {
            neon: '#39FF14',
          },
        },
      },
    };
  </script>

<script>
  function updateTheme() {
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    const root = document.documentElement;
    if (prefersDark) {
      root.classList.add('dark');
    } else {
      root.classList.remove('dark');
    }
  }

  // Initial check
  updateTheme();

  setInterval(updateTheme, 1000);
</script>
</head>
<body class="bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-200 min-h-screen flex flex-col justify-center items-center px-6">
  <div class="text-center">
    <h1 class="text-8xl font-bold text-neon mb-4">404</h1>
    <h2 class="text-2xl md:text-3xl font-semibold mb-4">Page Not Found</h2>
    <p class="text-lg text-gray-600 dark:text-gray-400 mb-8">Oops! The page you're looking for doesn't exist or has been moved.</p>
    <a href="/" class="inline-block px-6 py-3 bg-neon text-black rounded-md text-sm font-semibold hover:bg-[#2bcf11] transition">
      ← Back to Home
    </a>
  </div>
</body>
</html>