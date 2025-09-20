<?php
include 'top_bar.php';

// Handle update form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_name = trim($_POST['name']);
    if ($new_name) {
        $update = $pdo->prepare("UPDATE users SET name = ? WHERE id = ?");
        $update->execute([$new_name, $user_id]);
        $user['name'] = $new_name; // update locally
        $message = "Profile updated successfully!";
    }
}

// Detect login method
$login_method = "Email";
if (!empty($user['google_id'])) {
    $login_method = "Google";
} elseif (!empty($user['pxxl_id'])) {
    $login_method = "PXXL";
}
?>

<!DOCTYPE html>
<html lang="en" class="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Profile - Dashboard</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-gray-100">

<div class="max-w-3xl mx-auto p-6">
  <div class="bg-white dark:bg-gray-800 shadow rounded-2xl p-6">
    
    <!-- Header -->
    <div class="flex items-center gap-4">
      <img src="<?= htmlspecialchars($user['picture']) ?>" alt="Profile"
           class="w-20 h-20 rounded-full border shadow-sm">
      <div>
        <h2 class="text-2xl font-bold"><?= htmlspecialchars($user['name']) ?></h2>
        <p class="text-sm text-gray-500 dark:text-gray-400"><?= htmlspecialchars($user['email']) ?></p>
        <span class="inline-flex items-center mt-1 px-2 py-1 rounded-full text-xs font-medium
              <?= $login_method === 'Google' ? 'bg-red-100 text-red-600 dark:bg-red-900 dark:text-red-200' :
                 ($login_method === 'PXXL' ? 'bg-blue-100 text-blue-600 dark:bg-blue-900 dark:text-blue-200' :
                 'bg-green-100 text-green-600 dark:bg-green-900 dark:text-green-200') ?>">
          <i data-lucide="<?= $login_method === 'Google' ? 'mail' : ($login_method === 'PXXL' ? 'cpu' : 'user') ?>" class="w-3 h-3 mr-1"></i>
          <?= $login_method ?> Login
        </span>
      </div>
    </div>

    <!-- Message -->
    <?php if (!empty($message)): ?>
      <div class="mt-4 p-3 rounded-lg bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-200 flex items-center gap-2">
        <i data-lucide="check-circle" class="w-4 h-4"></i>
        <?= $message ?>
      </div>
    <?php endif; ?>

    <!-- Editable form -->
    <form method="POST" class="mt-6 space-y-4">
      <!-- Name (editable) -->
      <div>
        <label class="block text-sm font-medium mb-1">Name</label>
        <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>"
               class="w-full px-3 py-2 border rounded-lg bg-gray-50 dark:bg-gray-700 dark:border-gray-600 focus:ring-2 focus:ring-purple-500">
      </div>

      <!-- Info section -->
      <div class="grid sm:grid-cols-2 gap-4">
        <div class="bg-gray-50 dark:bg-gray-700 p-3 rounded-lg">
          <p class="text-xs text-gray-500">Email</p>
          <p class="font-medium"><?= htmlspecialchars($user['email']) ?></p>
        </div>
        <div class="bg-gray-50 dark:bg-gray-700 p-3 rounded-lg">
          <p class="text-xs text-gray-500">Joined</p>
          <p class="font-medium"><?= date("M d, Y", strtotime($user['created_at'])) ?></p>
        </div>
      </div>

      <!-- Submit -->
      <div class="pt-4">
        <button type="submit"
          class="px-4 py-2 bg-purple-600 text-white rounded-lg shadow hover:bg-purple-700 flex items-center gap-2 transition">
          <i data-lucide="save"></i> Save Changes
        </button>
      </div>
    </form>
  </div>
</div>

<script>
  lucide.createIcons();
</script>
</body>
</html>
