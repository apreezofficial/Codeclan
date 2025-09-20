<?php
include 'top_bar.php';

// Get receiver from URL
$receiver_id = isset($_GET['user']) ? intval($_GET['user']) : 0;

// Send message
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['message'])) {
    $stmt = $pdo->prepare("INSERT INTO chats (sender_id, receiver_id, message) VALUES (?, ?, ?)");
    $stmt->execute([$user_id, $receiver_id, $_POST['message']]);
    header("Location: chat.php?user=" . $receiver_id);
    exit;
}

// Fetch messages
$stmt = $pdo->prepare("
    SELECT c.*, u.username, u.avatar 
    FROM chats c 
    JOIN users u ON c.sender_id = u.id
    WHERE (c.sender_id = ? AND c.receiver_id = ?)
       OR (c.sender_id = ? AND c.receiver_id = ?)
    ORDER BY c.sent_at ASC
");
$stmt->execute([$user_id, $receiver_id, $receiver_id, $user_id]);
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en" class="dark">
<head>
  <meta charset="UTF-8" />
  <title>Chat</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-900 text-gray-100 flex flex-col h-screen">

<!-- Header -->
<div class="bg-gray-800 p-4 flex items-center shadow-md">
  <a href="users.php" class="mr-3 text-gray-400">⬅️</a>
  <h2 class="text-xl font-bold">Chat with User <?= $receiver_id ?></h2>
</div>

<!-- Messages -->
<div class="flex-1 overflow-y-auto p-4 space-y-3">
  <?php foreach ($messages as $msg): ?>
    <div class="flex <?= $msg['sender_id'] == $user_id ? 'justify-end' : 'justify-start' ?>">
      <div class="max-w-xs px-4 py-2 rounded-2xl shadow 
                  <?= $msg['sender_id'] == $user_id ? 'bg-green-600 text-white' : 'bg-gray-700' ?>">
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
  <button type="submit" class="ml-2 bg-green-600 px-4 py-2 rounded-full">Send</button>
</form>

</body>
</html>
