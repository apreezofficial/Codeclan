<?php
session_start();
// If user is already logged in, redirect them
if (isset($_COOKIE['user'])) {
    header("Location: dashboard/index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en" class="transition-colors duration-300">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | CodeClan</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <script>
        // 🌙 Auto system theme (no toggle)
        if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
            document.documentElement.classList.add('dark');
        }
    </script>
    <style>
        .glow-hover {
            transition: all 0.3s ease;
        }
        .glow-hover:hover {
            box-shadow: 0 0 15px rgba(236, 72, 153, 0.5);
            transform: translateY(-2px);
        }
        .disabled-btn {
            cursor: not-allowed;
            opacity: 0.6;
        }
    </style>
</head>
<body class="bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 flex items-center justify-center min-h-screen p-4">

    <div class="w-full max-w-md">
        <!-- Logo & Title -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-br from-pink-500 to-purple-600 rounded-2xl shadow-lg mb-4">
                <i data-lucide="code" class="w-8 h-8 text-white"></i>
            </div>
            <h1 class="text-3xl font-extrabold bg-clip-text text-transparent bg-gradient-to-r from-pink-500 to-purple-600">
                CodeClan
            </h1>
            <p class="text-gray-500 dark:text-gray-400 mt-2">Sign in to continue your journey</p>
        </div>

        <!-- Login Card -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-8 border border-gray-100 dark:border-gray-700">
            <h2 class="text-2xl font-bold text-center mb-6">Welcome Back 👋</h2>

            <!-- Google Login Button -->
            <a href="google.php" class="w-full flex items-center justify-center gap-3 px-5 py-3.5 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 border border-gray-300 dark:border-gray-600 rounded-xl font-medium text-gray-800 dark:text-gray-200 shadow-sm glow-hover transition-all duration-200 mb-4">
                <i data-lucide="google" class="w-5 h-5"></i>
                <span>Continue with Google</span>
            </a>

            <!-- Disabled PXXL Login Button -->
            <button disabled class="w-full flex items-center justify-center gap-3 px-5 py-3.5 bg-gray-100 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-xl font-medium text-gray-500 dark:text-gray-400 cursor-not-allowed disabled-btn">
                <i data-lucide="lock" class="w-5 h-5"></i>
                <span>Login with PXXL (Coming Soon)</span>
            </button>

            <!-- Divider -->
            <div class="flex items-center my-6">
                <div class="flex-1 border-t border-gray-300 dark:border-gray-600"></div>
                <span class="px-4 text-sm text-gray-500 dark:text-gray-400">OR</span>
                <div class="flex-1 border-t border-gray-300 dark:border-gray-600"></div>
            </div>

            <!-- Footer Note -->
            <p class="text-center text-xs text-gray-500 dark:text-gray-400">
                By continuing, you agree to our <a href="#" class="underline hover:text-pink-500">Terms</a> and <a href="#" class="underline hover:text-pink-500">Privacy Policy</a>.
            </p>
        </div>

        <!-- Branding Footer -->
        <div class="text-center mt-6">
            <p class="text-sm text-gray-500 dark:text-gray-400">
                © <?= date('Y') ?> CodeClan. Made with 💜 for coders.
            </p>
        </div>
    </div>

    <script>
        // Initialize Lucide Icons
        lucide.createIcons();
    </script>
</body>
</html>
