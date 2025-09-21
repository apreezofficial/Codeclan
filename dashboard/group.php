<?php
include 'top_bar.php';
error_reporting(1);
// If creating a new group
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_group'])) {
    $name = trim($_POST['name']);
    $image = null;

    // Handle upload
    if (!empty($_FILES['image']['name'])) {
        $targetDir = "uploads/groups/";
        if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);

        $filename = time() . "_" . basename($_FILES["image"]["name"]);
        $targetFile = $targetDir . $filename;
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFile)) {
            $image = $targetFile;
        }
    }

    // Insert group
    $stmt = $pdo->prepare("INSERT INTO groups (name, image, created_by) VALUES (?, ?, ?)");
    $stmt->execute([$name, $image, $user_id]);
    $group_id = $pdo->lastInsertId();

    // Assign creator as admin in group_members
    $stmt = $pdo->prepare("INSERT INTO group_members (group_id, user_id, role) VALUES (?, ?, 'admin')");
    $stmt->execute([$group_id, $user_id]);

    header("Location: group.php?group=" . $group_id);
    exit;
}

$group_id = isset($_GET['group']) ? intval($_GET['group']) : 0;

if ($group_id) {
    // Fetch group
    $stmt = $pdo->prepare("SELECT * FROM groups WHERE id = ?");
    $stmt->execute([$group_id]);
    $group = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$group) {
        die("Group not found.");
    }

    // Send message
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
        $stmt = $pdo->prepare("INSERT INTO group_messages (group_id, sender_id, message) VALUES (?, ?, ?)");
        $stmt->execute([$group_id, $user_id, $_POST['message']]);
        header("Location: group.php?group=" . $group_id);
        exit;
    }

    // Fetch messages
    $stmt = $pdo->prepare("
        SELECT gm.*, u.name, u.picture 
        FROM group_messages gm
        JOIN users u ON gm.sender_id = u.id
        WHERE gm.group_id = ?
        ORDER BY gm.sent_at ASC
    ");
    $stmt->execute([$group_id]);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en" class="dark">
<head>
  <meta charset="UTF-8" />
  <title><?= $group_id ? htmlspecialchars($group['name']) : "Groups" ?></title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      darkMode: 'class',
      theme: {
        extend: {
          colors: { brand: "#6B21A8" }
        }
      }
    };
  </script>
</head>
<body class="bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-gray-100 flex flex-col h-screen">

<?php if (!$group_id): ?>
  <!-- Empty State -->
  <div class="flex-1 flex flex-col items-center justify-center text-center p-8">
    <img src="https://illustrations.popsy.co/gray/chat.svg" alt="No Group" class="w-48 mb-6">
    <h2 class="text-2xl font-bold mb-2">No Group Selected</h2>
    <p class="text-gray-600 dark:text-gray-400 mb-6">Start by creating a new group to chat with your friends.</p>

    <!-- Create Group Form -->
    <form method="POST" enctype="multipart/form-data" class="bg-white dark:bg-gray-800 rounded-2xl shadow p-6 w-full max-w-md space-y-4">
      <input type="text" name="name" placeholder="Group Name" required
             class="w-full px-4 py-2 rounded-lg border dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100">
      <input type="file" name="image" accept="image/*"
             class="w-full text-sm text-gray-600 dark:text-gray-400">
      <button type="submit" name="create_group"
              class="w-full bg-brand text-white py-2 rounded-lg font-semibold hover:bg-purple-700">Create Group</button>
    </form>
  </div>

<?php else: ?>
  <!-- Group Header -->
  <div class="bg-gray-800 p-4 flex items-center shadow-md">
    <a href="group.php" class="mr-3 text-gray-400">⬅️</a>
    <?php if ($group['image']): ?>
      <img src="<?= htmlspecialchars($group['image']) ?>" alt="Group" class="w-10 h-10 rounded-full mr-3">
    <?php endif; ?>
    <h2 class="text-xl font-bold"><?= htmlspecialchars($group['name']) ?></h2>
  </div>

  <!-- Messages -->
  <div class="flex-1 overflow-y-auto p-4 space-y-3">
    <?php foreach ($messages as $msg): ?>
      <div class="flex <?= $msg['sender_id'] == $user_id ? 'justify-end' : 'justify-start' ?>">
        <div class="max-w-xs px-4 py-2 rounded-2xl shadow
                    <?= $msg['sender_id'] == $user_id ? 'bg-brand text-white' : 'bg-gray-700' ?>">
          <p class="flex items-center gap-2 mb-1">
            <img src="<?= htmlspecialchars($msg['picture']) ?>" alt="Avatar" class="w-6 h-6 rounded-full">
            <span class="font-semibold"><?= htmlspecialchars($msg['name']) ?></span>
          </p>
          <p><?= htmlspecialchars($msg['message']) ?></p>
          <span class="text-xs text-gray-300 block mt-1"><?= date("H:i", strtotime($msg['sent_at'])) ?></span>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <!-- Input -->
  <form method="POST" class="p-4 bg-gray-800 flex items-center">
    <input type="text" name="message" placeholder="Type a message..."
           class="flex-1 rounded-full px-4 py-2 text-gray-900" required>
    <button type="submit" class="ml-2 bg-brand px-4 py-2 rounded-full text-white">Send</button>
  </form>
<?php endif; ?>

</body>
</html>
