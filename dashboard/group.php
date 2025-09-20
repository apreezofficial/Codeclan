<?php
include 'top_bar.php';

$group_id = isset($_GET['group']) ? intval($_GET['group']) : 0;

// Send group message
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['message'])) {
    $stmt = $pdo->prepare("INSERT INTO group_messages (group_id, sender_id, message) VALUES (?, ?, ?)");
    $stmt->execute([$group_id, $user_id, $_POST['message']]);
    header("Location: group.php?group=" . $group_id);
    exit;
}

// Fetch group info
$stmt = $pdo->prepare("SELECT * FROM groups WHERE id = ?");
$stmt->execute([$group_id]);
$group = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch messages
$stmt = $pdo->prepare("
    SELECT gm.*, u.username, u.avatar 
    FROM group_messages gm
    JOIN users u ON gm.sender_id = u.id
    WHERE gm.group_id = ?
    ORDER BY gm.sent_at ASC
");
$stmt->execute([$group_id]);
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en" class="dark">
<head>
  <meta charset="UTF-8" />
  <title><?= htmlspecialchars($group['name']) ?> - Group</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-900 text-gray-100 flex flex-col h-screen">

<!-- Header -->
<div class="bg-gray-800 p-4 flex items-center shadow-md">
  <a href="groups_list.php" class="mr-3 text-gray-400">⬅️</a>
  <h2 class="text-xl font-bold"><?= htmlspecialchars($group['name']) ?></h2>
</div>

<!-- Messages -->
<div class="flex-1 overflow-y-auto p-4 space-y-3">
  <?php foreach ($messages as $msg): ?>
    <div class="flex <?= $msg['sender_id'] == $user_id ? 'justify-end' : 'justify-start' ?>">
      <div class="max-w-xs px-4 py-2 rounded-2xl shadow 
                  <?= $msg['sender_id'] == $user_id ? 'bg-blue-600 text-white' : 'bg-gray-700' ?>">
        <p class="font-semibold"><?= htmlspecialchars($msg['username']) ?></p>
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
  <button type="submit" class="ml-2 bg-blue-600 px-4 py-2 rounded-full">Send</button>
</form>

</body>
</html>
