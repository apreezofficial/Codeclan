<?php
session_start();
include 'top_bar.php'; // Make sure this sets $pdo and $user_id

// Initialize variables
$group_id = isset($_GET['group']) ? intval($_GET['group']) : 0;
$group = null;
$messages = [];
$groups = [];
$errorMessage = '';
$successMessage = '';

// Fetch all user's groups for sidebar
$stmt = $pdo->prepare("
    SELECT g.*, COUNT(gm.id) as member_count 
    FROM groups g 
    JOIN group_members gm ON g.id = gm.group_id 
    WHERE gm.user_id = ? 
    GROUP BY g.id
");
$stmt->execute([$user_id]);
$groups = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle group creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_group'])) {
    $name = trim($_POST['name']);
    if (empty($name)) {
        $errorMessage = "Group name is required.";
    } else {
        $image = null;

        // Handle image upload
        if (!empty($_FILES['image']['name'])) {
            $targetDir = "uploads/groups/";
            if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);

            $filename = time() . "_" . basename($_FILES["image"]["name"]);
            $targetFile = $targetDir . $filename;
            $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

            if (in_array($imageFileType, ['jpg', 'jpeg', 'png', 'gif'])) {
                if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFile)) {
                    $image = $targetFile;
                } else {
                    $errorMessage = "Sorry, there was an error uploading your file.";
                }
            } else {
                $errorMessage = "Only JPG, JPEG, PNG & GIF files are allowed.";
            }
        }

        if (!$errorMessage) {
            try {
                // Insert group
                $stmt = $pdo->prepare("INSERT INTO groups (name, image, created_by) VALUES (?, ?, ?)");
                $stmt->execute([$name, $image, $user_id]);
                $new_group_id = $pdo->lastInsertId();

                // Add creator as admin
                $stmt = $pdo->prepare("INSERT INTO group_members (group_id, user_id, role) VALUES (?, ?, 'admin')");
                $stmt->execute([$new_group_id, $user_id]);

                $successMessage = "Group created successfully!";
                header("Location: group.php?group=" . $new_group_id);
                exit;
            } catch (Exception $e) {
                $errorMessage = "Failed to create group. Please try again.";
                error_log("Group Creation Error: " . $e->getMessage());
            }
        }
    }
}

// If viewing a specific group
if ($group_id) {
    // Fetch group info
    $stmt = $pdo->prepare("SELECT * FROM groups WHERE id = ?");
    $stmt->execute([$group_id]);
    $group = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$group) {
        die("Group not found.");
    }

    // Handle sending message
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
        $message = trim($_POST['message']);
        if (!empty($message)) {
            $stmt = $pdo->prepare("INSERT INTO group_messages (group_id, sender_id, message) VALUES (?, ?, ?)");
            $stmt->execute([$group_id, $user_id, $message]);
            // Redirect to same group to prevent resubmission
            header("Location: group.php?group=" . $group_id);
            exit;
        }
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
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $group_id ? htmlspecialchars($group['name'] ?? 'Group') : "CodeClan Groups" ?></title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <script>
    tailwind.config = {
      darkMode: 'class',
      theme: {
        extend: {
          colors: {
            brand: "#8B5CF6", // Vibrant purple
            success: "#10B981",
            danger: "#EF4444"
          },
          animation: {
            'pulse-slow': 'pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite',
          }
        }
      }
    };

    // Auto-scroll to bottom on page load
    document.addEventListener('DOMContentLoaded', function() {
      const messagesContainer = document.getElementById('messages-container');
      if (messagesContainer) {
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
      }
    });
  </script>
</head>
<body class="bg-gradient-to-br from-gray-900 via-purple-900 to-violet-900 text-gray-100 flex flex-col h-screen">

  <!-- Header -->
  <header class="bg-gray-800/50 backdrop-blur-sm p-4 flex items-center justify-between shadow-lg">
    <div class="flex items-center space-x-3">
      <i class="fas fa-users text-2xl text-brand"></i>
      <h1 class="text-xl font-bold"><?= $group_id ? htmlspecialchars($group['name']) : "Your Groups" ?></h1>
    </div>
    <div class="flex items-center space-x-2">
      <?php if ($group_id): ?>
        <a href="group.php" class="p-2 rounded-full hover:bg-gray-700 transition">
          <i class="fas fa-list text-gray-300"></i>
        </a>
      <?php endif; ?>
      <button onclick="toggleTheme()" class="p-2 rounded-full hover:bg-gray-700 transition">
        <i id="theme-icon" class="fas fa-moon text-yellow-300"></i>
      </button>
    </div>
  </header>

  <div class="flex flex-1 overflow-hidden">
    <!-- Groups Sidebar (Visible on desktop, collapsible on mobile) -->
    <aside class="w-80 bg-gray-800/30 backdrop-blur-sm border-r border-gray-700/50 hidden md:block">
      <div class="p-4">
        <h2 class="text-lg font-semibold mb-4 flex items-center">
          <i class="fas fa-comments mr-2"></i> Your Groups
        </h2>
        
        <!-- Create Group Button -->
        <button onclick="document.getElementById('create-group-modal').classList.remove('hidden')" 
                class="w-full bg-brand hover:bg-purple-600 text-white py-2 px-4 rounded-lg font-medium mb-6 transition flex items-center justify-center">
          <i class="fas fa-plus mr-2"></i> Create New Group
        </button>

        <!-- Groups List -->
        <div class="space-y-2 max-h-96 overflow-y-auto">
          <?php if (empty($groups)): ?>
            <p class="text-gray-400 text-center py-8">You haven't joined any groups yet.</p>
          <?php else: ?>
            <?php foreach ($groups as $g): ?>
              <a href="group.php?group=<?= $g['id'] ?>" 
                 class="block p-3 rounded-lg hover:bg-gray-700/50 transition <?= $g['id'] == $group_id ? 'bg-brand/20 border-l-4 border-brand' : '' ?>">
                <div class="flex items-center">
                  <?php if ($g['image']): ?>
                    <img src="<?= htmlspecialchars($g['image']) ?>" alt="<?= htmlspecialchars($g['name']) ?>" class="w-10 h-10 rounded-full mr-3 object-cover">
                  <?php else: ?>
                    <div class="w-10 h-10 rounded-full bg-gradient-to-r from-purple-500 to-pink-500 flex items-center justify-center mr-3">
                      <span class="font-bold"><?= strtoupper(substr($g['name'], 0, 1)) ?></span>
                    </div>
                  <?php endif; ?>
                  <div class="flex-1 min-w-0">
                    <p class="font-medium truncate"><?= htmlspecialchars($g['name']) ?></p>
                    <p class="text-xs text-gray-400"><?= $g['member_count'] ?> members</p>
                  </div>
                </div>
              </a>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 flex flex-col">
      <?php if (!$group_id): ?>
        <!-- Empty State - No Group Selected -->
        <div class="flex-1 flex flex-col items-center justify-center p-8 text-center">
          <div class="relative mb-8">
            <img src="https://illustrations.popsy.co/gray/chat.svg" alt="No Group" class="w-64 mx-auto drop-shadow-2xl">
            <div class="absolute -top-4 -right-4 w-16 h-16 bg-brand rounded-full flex items-center justify-center animate-pulse-slow">
              <i class="fas fa-comments text-white text-xl"></i>
            </div>
          </div>
          <h2 class="text-3xl font-bold mb-4 bg-gradient-to-r from-brand to-purple-400 bg-clip-text text-transparent">Welcome to CodeClan Groups</h2>
          <p class="text-gray-300 mb-8 max-w-md">Connect with your coding buddies, share knowledge, and collaborate in real-time. Start by creating your first group!</p>
          
          <button onclick="document.getElementById('create-group-modal').classList.remove('hidden')" 
                  class="bg-brand hover:bg-purple-600 text-white py-3 px-8 rounded-full font-semibold text-lg transition transform hover:scale-105 shadow-lg">
            <i class="fas fa-plus mr-2"></i> Create Your First Group
          </button>
        </div>
      <?php else: ?>
        <!-- Group Chat Interface -->
        <!-- Messages Area -->
        <div id="messages-container" class="flex-1 p-4 space-y-4 overflow-y-auto">
          <?php if (empty($messages)): ?>
            <div class="text-center py-12 text-gray-400">
              <i class="fas fa-comment-slash text-4xl mb-4"></i>
              <p class="text-lg">No messages yet. Be the first to say hello! 👋</p>
            </div>
          <?php else: ?>
            <?php foreach ($messages as $msg): ?>
              <div class="flex <?= $msg['sender_id'] == $user_id ? 'justify-end' : 'justify-start' ?> group">
                <div class="max-w-xs lg:max-w-md px-4 py-3 rounded-2xl shadow-md transition-all duration-300 transform hover:scale-105
                            <?= $msg['sender_id'] == $user_id ? 'bg-brand text-white' : 'bg-gray-700/80 backdrop-blur-sm' ?>">
                  <div class="flex items-center gap-2 mb-1">
                    <img src="<?= htmlspecialchars($msg['picture'] ?? '/assets/img/default-avatar.png') ?>" 
                         alt="Avatar" class="w-8 h-8 rounded-full border-2 <?= $msg['sender_id'] == $user_id ? 'border-white' : 'border-gray-500' ?> object-cover">
                    <span class="font-semibold text-sm"><?= htmlspecialchars($msg['name']) ?></span>
                  </div>
                  <p class="text-sm leading-relaxed"><?= htmlspecialchars($msg['message']) ?></p>
                  <span class="text-xs text-gray-300 block mt-1"><?= date("H:i", strtotime($msg['sent_at'])) ?></span>
                </div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>

        <!-- Message Input -->
        <form method="POST" class="p-4 bg-gray-800/50 backdrop-blur-sm border-t border-gray-700/50">
          <div class="flex items-center space-x-2">
            <input type="text" name="message" placeholder="Type a message..." 
                   class="flex-1 bg-gray-700/50 border border-gray-600 rounded-full px-4 py-3 text-gray-100 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-brand transition" required>
            <button type="submit" 
                    class="bg-brand hover:bg-purple-600 text-white p-3 rounded-full transition transform hover:scale-110 shadow-lg">
              <i class="fas fa-paper-plane"></i>
            </button>
          </div>
        </form>
      <?php endif; ?>
    </main>
  </div>

  <!-- Create Group Modal -->
  <div id="create-group-modal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 hidden">
    <div class="bg-gray-800 rounded-2xl p-6 w-full max-w-md mx-4 shadow-2xl">
      <div class="flex justify-between items-center mb-4">
        <h3 class="text-xl font-bold">Create New Group</h3>
        <button onclick="document.getElementById('create-group-modal').classList.add('hidden')" 
                class="text-gray-400 hover:text-white">
          <i class="fas fa-times"></i>
        </button>
      </div>

      <?php if ($errorMessage): ?>
        <div class="mb-4 p-3 bg-danger/20 text-danger rounded-lg text-sm">
          <i class="fas fa-exclamation-circle mr-2"></i><?= htmlspecialchars($errorMessage) ?>
        </div>
      <?php endif; ?>

      <?php if ($successMessage): ?>
        <div class="mb-4 p-3 bg-success/20 text-success rounded-lg text-sm">
          <i class="fas fa-check-circle mr-2"></i><?= htmlspecialchars($successMessage) ?>
        </div>
      <?php endif; ?>

      <form method="POST" enctype="multipart/form-data" class="space-y-4">
        <div>
          <label class="block text-sm font-medium mb-1">Group Name</label>
          <input type="text" name="name" placeholder="e.g., Python Masters" required
                 class="w-full px-4 py-2 rounded-lg bg-gray-700 border border-gray-600 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-brand">
        </div>
        <div>
          <label class="block text-sm font-medium mb-1">Group Image (Optional)</label>
          <input type="file" name="image" accept="image/*"
                 class="w-full text-sm text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-brand file:text-white hover:file:bg-purple-600">
          <p class="text-xs text-gray-500 mt-1">JPG, PNG, GIF up to 5MB</p>
        </div>
        <div class="flex space-x-3 pt-2">
          <button type="button" 
                  onclick="document.getElementById('create-group-modal').classList.add('hidden')"
                  class="flex-1 py-2 px-4 bg-gray-700 hover:bg-gray-600 text-white rounded-lg transition">
            Cancel
          </button>
          <button type="submit" name="create_group"
                  class="flex-1 py-2 px-4 bg-brand hover:bg-purple-600 text-white rounded-lg font-semibold transition transform hover:scale-105">
            Create Group
          </button>
        </div>
      </form>
    </div>
  </div>

  <script>
    // Toggle dark/light theme
    function toggleTheme() {
      document.documentElement.classList.toggle('dark');
      const isDark = document.documentElement.classList.contains('dark');
      localStorage.setItem('theme', isDark ? 'dark' : 'light');
      document.getElementById('theme-icon').className = isDark ? 'fas fa-sun text-yellow-300' : 'fas fa-moon text-gray-300';
    }

    // Set initial theme
    if (localStorage.getItem('theme') === 'light' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: light)').matches)) {
      document.documentElement.classList.remove('dark');
      document.getElementById('theme-icon').className = 'fas fa-moon text-gray-300';
    } else {
      document.getElementById('theme-icon').className = 'fas fa-sun text-yellow-300';
    }
  </script>
</body>
</html>
