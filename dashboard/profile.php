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
    <div class="flex items-center gap-4">
      <img src="<?= htmlspecialchars($user['picture']) ?>" alt="Profile"
           class="w-20 h-20 rounded-full border">
      <div>
        <h2 class="text-2xl font-bold"><?= htmlspecialchars($user['name']) ?></h2>
        <p class="text-sm text-gray-500 dark:text-gray-400"><?= htmlspecialchars($user['email']) ?></p>
      </div>
    </div>

    <?php if (!empty($message)): ?>
      <p class="mt-4 text-green-600 font-medium"><?= $message ?></p>
    <?php endif; ?>

    <form method="POST" class="mt-6 space-y-4">
      <!-- Name -->
      <div>
        <label class="block text-sm font-medium mb-1">Name</label>
        <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>"
               class="w-full px-3 py-2 border rounded-lg bg-gray-50 dark:bg-gray-700 dark:border-gray-600">
      </div>

      <!-- Email-->
      <div>
        <label class="block text-sm font-medium mb-1">Email</label>
        <input type="text" value="<?= htmlspecialchars($user['email']) ?>" disabled
               class="w-full px-3 py-2 border rounded-lg bg-gray-100 dark:bg-gray-700 dark:border-gray-600">
      </div>

      <!-- Google ID -->
      <div>
        <label class="block text-sm font-medium mb-1">Google ID</label>
        <input type="text" value="<?= htmlspecialchars($user['google_id']) ?>" disabled
               class="w-full px-3 py-2 border rounded-lg bg-gray-100 dark:bg-gray-700 dark:border-gray-600">
      </div>

      <!-- PXXL ID -->
      <div>
        <label class="block text-sm font-medium mb-1">PXXL ID</label>
        <input type="text" value="<?= htmlspecialchars($user['pxxl_id'] ?? '—') ?>" disabled
               class="w-full px-3 py-2 border rounded-lg bg-gray-100 dark:bg-gray-700 dark:border-gray-600">
      </div>

      <!-- Created At -->
      <div>
        <label class="block text-sm font-medium mb-1">Created At</label>
        <input type="text" value="<?= htmlspecialchars($user['created_at']) ?>" disabled
               class="w-full px-3 py-2 border rounded-lg bg-gray-100 dark:bg-gray-700 dark:border-gray-600">
      </div>

      <!-- Submit -->
      <div class="pt-4">
        <button type="submit"
          class="px-4 py-2 bg-purple-600 text-white rounded-lg shadow hover:bg-purple-700 flex items-center gap-2">
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
