<?php
session_start();
include 'top_bar.php'; 

// Initialize variables
$group_id = isset($_GET['group']) ? intval($_GET['group']) : 0;
$group = null;
$messages = [];
$groups = [];
$publicGroups = [];
$allUsers = [];
$groupMembers = [];
$errorMessage = '';
$successMessage = '';

// Fetch user's groups for sidebar
$stmt = $pdo->prepare("
    SELECT g.*, COUNT(gm.id) as member_count 
    FROM groups g 
    JOIN group_members gm ON g.id = gm.group_id 
    WHERE gm.user_id = ? 
    GROUP BY g.id
");
$stmt->execute([$user_id]);
$groups = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch ALL public groups for discovery
$stmt = $pdo->prepare("
    SELECT g.*, COUNT(gm.id) as member_count, u.name as creator_name
    FROM groups g 
    JOIN group_members gm ON g.id = gm.group_id 
    JOIN users u ON g.created_by = u.id
    GROUP BY g.id
    ORDER BY member_count DESC
    LIMIT 20
");
$stmt->execute();
$publicGroups = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch all users (for invite modal)
$stmt = $pdo->prepare("SELECT id, name, picture, email FROM users WHERE id != ?");
$stmt->execute([$user_id]);
$allUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle form submissions (NO AJAX except send_message)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['action']) && $_POST['action'] === 'send_message') {
            // This is the ONLY AJAX endpoint
            header('Content-Type: application/json');
            
            if (!$group_id) throw new Exception("No group selected.");
            
            $message = trim($_POST['message'] ?? '');
            if (empty($message)) throw new Exception("Message cannot be empty.");
            
            // Verify user is a member
            $stmt = $pdo->prepare("SELECT id FROM group_members WHERE group_id = ? AND user_id = ?");
            $stmt->execute([$group_id, $user_id]);
            if (!$stmt->fetch()) {
                throw new Exception("You must join the group before sending messages.");
            }
            
            $stmt = $pdo->prepare("INSERT INTO group_messages (group_id, sender_id, message) VALUES (?, ?, ?)");
            $stmt->execute([$group_id, $user_id, $message]);
            
            // Return new message data for optimistic UI update
            $stmt = $pdo->prepare("
                SELECT gm.*, u.name, u.picture 
                FROM group_messages gm
                JOIN users u ON gm.sender_id = u.id
                WHERE gm.id = ?
            ");
            $stmt->execute([$pdo->lastInsertId()]);
            $newMessage = $stmt->fetch(PDO::FETCH_ASSOC);
            
            echo json_encode(['success' => true, 'message_data' => $newMessage]);
            exit;
        }
        
        if (isset($_POST['create_group'])) {
            $name = trim($_POST['name'] ?? '');
            if (empty($name)) {
                $errorMessage = "Group name is required.";
            } else {
                $image = null;
                if (!empty($_FILES['image']['name'])) {
                    $targetDir = "uploads/groups/";
                    if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
                    
                    $filename = time() . "_" . basename($_FILES["image"]["name"]);
                    $targetFile = $targetDir . $filename;
                    $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
                    
                    if (!in_array($imageFileType, ['jpg', 'jpeg', 'png', 'gif'])) {
                        $errorMessage = "Only JPG, JPEG, PNG & GIF files are allowed.";
                    } else if (!move_uploaded_file($_FILES["image"]["tmp_name"], $targetFile)) {
                        $errorMessage = "Error uploading image.";
                    } else {
                        $image = $targetFile;
                    }
                }
                
                if (!$errorMessage) {
                    $stmt = $pdo->prepare("INSERT INTO groups (name, image, created_by) VALUES (?, ?, ?)");
                    $stmt->execute([$name, $image, $user_id]);
                    $new_group_id = $pdo->lastInsertId();
                    
                    $stmt = $pdo->prepare("INSERT INTO group_members (group_id, user_id, role) VALUES (?, ?, 'admin')");
                    $stmt->execute([$new_group_id, $user_id]);
                    
                    $successMessage = "Group created successfully!";
                    header("Location: group.php?group=" . $new_group_id);
                    exit;
                }
            }
        }
        
        if (isset($_POST['join_group'])) {
            $target_group_id = intval($_POST['group_id'] ?? 0);
            if (!$target_group_id) {
                $errorMessage = "Invalid group.";
            } else {
                $stmt = $pdo->prepare("SELECT id FROM group_members WHERE group_id = ? AND user_id = ?");
                $stmt->execute([$target_group_id, $user_id]);
                if ($stmt->fetch()) {
                    $errorMessage = "You are already a member.";
                } else {
                    $stmt = $pdo->prepare("INSERT INTO group_members (group_id, user_id, role) VALUES (?, ?, 'member')");
                    $stmt->execute([$target_group_id, $user_id]);
                    $successMessage = "Joined group successfully!";
                    header("Location: group.php?group=" . $target_group_id);
                    exit;
                }
            }
        }
        
        if (isset($_POST['leave_group'])) {
            if (!$group_id) {
                $errorMessage = "Invalid group.";
            } else {
                $stmt = $pdo->prepare("SELECT role FROM group_members WHERE group_id = ? AND user_id = ?");
                $stmt->execute([$group_id, $user_id]);
                $member = $stmt->fetch();
                if (!$member) {
                    $errorMessage = "You are not a member of this group.";
                } else if ($member['role'] === 'admin') {
                    $stmt = $pdo->prepare("SELECT COUNT(*) as admin_count FROM group_members WHERE group_id = ? AND role = 'admin' AND user_id != ?");
                    $stmt->execute([$group_id, $user_id]);
                    $count = $stmt->fetch()['admin_count'];
                    if ($count === 0) {
                        $errorMessage = "You cannot leave. You are the only admin.";
                    } else {
                        $stmt = $pdo->prepare("DELETE FROM group_members WHERE group_id = ? AND user_id = ?");
                        $stmt->execute([$group_id, $user_id]);
                        $successMessage = "Left group successfully.";
                        header("Location: group.php");
                        exit;
                    }
                } else {
                    $stmt = $pdo->prepare("DELETE FROM group_members WHERE group_id = ? AND user_id = ?");
                    $stmt->execute([$group_id, $user_id]);
                    $successMessage = "Left group successfully.";
                    header("Location: group.php");
                    exit;
                }
            }
        }
        
        if (isset($_POST['remove_member'])) {
            if (!$group_id) {
                $errorMessage = "Invalid group.";
            } else {
                $target_user_id = intval($_POST['user_id'] ?? 0);
                if ($target_user_id == $user_id) {
                    $errorMessage = "Use 'leave_group' to leave.";
                } else {
                    $stmt = $pdo->prepare("SELECT role FROM group_members WHERE group_id = ? AND user_id = ?");
                    $stmt->execute([$group_id, $user_id]);
                    $currentUser = $stmt->fetch();
                    if (!$currentUser || $currentUser['role'] !== 'admin') {
                        $errorMessage = "Only admins can remove members.";
                    } else {
                        $stmt = $pdo->prepare("SELECT id FROM group_members WHERE group_id = ? AND user_id = ?");
                        $stmt->execute([$group_id, $target_user_id]);
                        if (!$stmt->fetch()) {
                            $errorMessage = "User is not a member of this group.";
                        } else {
                            $stmt = $pdo->prepare("DELETE FROM group_members WHERE group_id = ? AND user_id = ?");
                            $stmt->execute([$group_id, $target_user_id]);
                            $successMessage = "Member removed.";
                            header("Location: group.php?group=" . $group_id);
                            exit;
                        }
                    }
                }
            }
        }
    } catch (Exception $e) {
        $errorMessage = $e->getMessage();
    }
}

// If viewing a specific group
if ($group_id) {
    $stmt = $pdo->prepare("SELECT g.*, u.name as creator_name FROM groups g JOIN users u ON g.created_by = u.id WHERE g.id = ?");
    $stmt->execute([$group_id]);
    $group = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$group) {
        die("Group not found.");
    }

    $stmt = $pdo->prepare("
        SELECT gm.*, u.name, u.picture, u.email 
        FROM group_members gm
        JOIN users u ON gm.user_id = u.id
        WHERE gm.group_id = ?
        ORDER BY gm.role DESC, u.name ASC
    ");
    $stmt->execute([$group_id]);
    $groupMembers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch last 50 messages
    $stmt = $pdo->prepare("
        SELECT gm.*, u.name, u.picture 
        FROM group_messages gm
        JOIN users u ON gm.sender_id = u.id
        WHERE gm.group_id = ?
        ORDER BY gm.sent_at DESC
        LIMIT 50
    ");
    $stmt->execute([$group_id]);
    $messages = array_reverse($stmt->fetchAll(PDO::FETCH_ASSOC));
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
            brand: "#8B5CF6",
            success: "#10B981",
            danger: "#EF4444",
            warning: "#F59E0B",
            accent: "#F97316"
          }
        }
      }
    };
  </script>
  <style>
    /* Mobile-First Optimizations */
    .mobile-message {
      max-width: 85% !important;
    }
    .mobile-full-height {
      height: calc(100vh - 8rem); /* Account for header and input */
    }
    @media (min-width: 768px) {
      .mobile-full-height {
        height: auto;
      }
    }
  </style>
</head>
<body class="bg-gradient-to-br from-gray-900 via-purple-900 to-violet-900 text-gray-100 flex flex-col min-h-screen">

<!-- Header -->
<header class="bg-gray-800/50 backdrop-blur-sm p-3 flex items-center justify-between shadow-lg">
  <div class="flex items-center space-x-3">
    <!-- SIDEBAR TOGGLE BUTTON (Mobile Only) -->
    <button id="toggle-sidebar" class="p-2 rounded-full hover:bg-gray-700 transition md:hidden">
      <i class="fas fa-bars text-xl text-gray-300"></i>
    </button>
    <i class="fas fa-users text-2xl text-brand"></i>
    <h1 class="text-lg font-bold truncate"><?= $group_id ? htmlspecialchars($group['name']) : "Groups" ?></h1>
  </div>
  <div class="flex items-center space-x-2">
    <?php if ($group_id): ?>
      <form method="POST" class="inline">
        <input type="hidden" name="group_id" value="<?= $group_id ?>">
        <?php 
        $isMember = false;
        foreach ($groupMembers as $member) {
            if ($member['user_id'] == $user_id) {
                $isMember = true;
                break;
            }
        }
        ?>
        <?php if ($isMember): ?>
          <button type="submit" name="leave_group" class="px-3 py-1 bg-gray-700 hover:bg-gray-600 text-white text-xs rounded transition">
            <i class="fas fa-sign-out-alt mr-1"></i> Leave Group
          </button>
        <?php else: ?>
          <button type="submit" name="join_group" class="px-3 py-1 bg-brand hover:bg-purple-600 text-white text-xs rounded transition">
            <i class="fas fa-user-plus mr-1"></i> Join Group
          </button>
        <?php endif; ?>
      </form>
    <?php endif; ?>
    <button onclick="toggleTheme()" class="p-2 rounded-full hover:bg-gray-700 transition">
      <i id="theme-icon" class="fas fa-moon text-yellow-300"></i>
    </button>
  </div>
</header>

  <div class="flex flex-1 overflow-hidden">
    <!-- Groups Sidebar (Collapsible on Mobile) -->
<aside id="sidebar" class="w-64 bg-gray-800/30 backdrop-blur-sm border-r border-gray-700/50 flex-shrink-0 transition-transform duration-300 ease-in-out -translate-x-full md:translate-x-0 md:block fixed md:static inset-y-0 left-0 z-20 md:z-auto"> 
    <div class="p-4 flex flex-col h-full">
        <div class="flex justify-between items-center mb-4">
          <h2 class="text-lg font-semibold flex items-center">
            <i class="fas fa-comments mr-2"></i> Your Groups
          </h2>
          <!-- Create Group Button -->
          <button onclick="document.getElementById('create-group-modal').classList.remove('hidden')" class="text-brand hover:text-purple-300 transition">
            <i class="fas fa-plus"></i>
          </button>
        </div>
        
        <!-- Groups List -->
        <div class="space-y-2 overflow-y-auto flex-grow">
          <?php if (empty($groups)): ?>
            <p class="text-gray-400 text-center py-4 text-sm">No groups yet.</p>
          <?php else: ?>
            <?php foreach ($groups as $g): ?>
              <a href="group.php?group=<?= $g['id'] ?>" 
                 class="block p-3 rounded-lg hover:bg-gray-700/50 transition <?= $g['id'] == $group_id ? 'bg-brand/20 border-l-4 border-brand' : '' ?>">
                <div class="flex items-center">
                  <?php if ($g['image']): ?>
                    <img src="<?= htmlspecialchars($g['image']) ?>" alt="<?= htmlspecialchars($g['name']) ?>" class="w-10 h-10 rounded-full mr-3 object-cover">
                  <?php else: ?>
                    <div class="w-10 h-10 rounded-full bg-gradient-to-r from-purple-500 to-pink-500 flex items-center justify-center mr-3">
                      <span class="font-bold text-sm"><?= strtoupper(substr($g['name'], 0, 1)) ?></span>
                    </div>
                  <?php endif; ?>
                  <div class="flex-1 min-w-0">
                    <p class="font-medium text-sm truncate"><?= htmlspecialchars($g['name']) ?></p>
                    <p class="text-xs text-gray-400"><?= $g['member_count'] ?> members</p>
                  </div>
                </div>
              </a>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>

        <!-- Discover Public Groups -->
        <div class="mt-6 pt-4 border-t border-gray-700">
          <h3 class="text-sm font-semibold mb-3 flex items-center text-accent">
            <i class="fas fa-compass mr-2"></i> Discover Groups
          </h3>
          <div class="space-y-2 max-h-48 overflow-y-auto">
            <?php foreach (array_slice($publicGroups, 0, 5) as $pg): ?>
              <div class="flex items-center justify-between p-2 rounded hover:bg-gray-700/30 transition group">
                <div class="flex items-center space-x-2">
                  <?php if ($pg['image']): ?>
                    <img src="<?= htmlspecialchars($pg['image']) ?>" alt="<?= htmlspecialchars($pg['name']) ?>" class="w-8 h-8 rounded-full">
                  <?php else: ?>
                    <div class="w-8 h-8 rounded-full bg-gradient-to-r from-blue-500 to-cyan-500 flex items-center justify-center">
                      <span class="text-xs font-bold"><?= strtoupper(substr($pg['name'], 0, 1)) ?></span>
                    </div>
                  <?php endif; ?>
                  <div>
                    <p class="text-xs font-medium truncate max-w-[120px]"><?= htmlspecialchars($pg['name']) ?></p>
                    <p class="text-[10px] text-gray-400"><?= $pg['member_count'] ?> members</p>
                  </div>
                </div>
                <form method="POST" class="inline">
                  <input type="hidden" name="group_id" value="<?= $pg['id'] ?>">
                  <button type="submit" name="join_group" class="opacity-0 group-hover:opacity-100 transition-opacity bg-accent hover:bg-orange-500 text-white text-xs py-1 px-2 rounded">
                    Join
                  </button>
                </form>
              </div>
            <?php endforeach; ?>
          </div>
          <a href="#public-groups" class="block text-center text-xs text-accent hover:underline mt-2">View All →</a>
        </div>
      </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 flex flex-col">
      <?php if (!$group_id): ?>
        <!-- Public Groups Discovery Page -->
        <div id="public-groups" class="flex-1 p-4 overflow-y-auto">
          <div class="max-w-4xl mx-auto">
            <div class="text-center mb-6">
              <h1 class="text-2xl font-bold mb-2">Discover Communities</h1>
              <p class="text-gray-300">Join groups that match your interests.</p>
            </div>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
              <?php foreach ($publicGroups as $pg): ?>
                <div class="bg-gray-800/50 backdrop-blur-sm rounded-xl p-4 hover:bg-gray-800/70 transition-all duration-300 transform hover:-translate-y-1 shadow-lg">
                  <div class="flex items-center mb-3">
                    <?php if ($pg['image']): ?>
                      <img src="<?= htmlspecialchars($pg['image']) ?>" alt="<?= htmlspecialchars($pg['name']) ?>" class="w-14 h-14 rounded-xl object-cover">
                    <?php else: ?>
                      <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center">
                        <span class="text-xl font-bold text-white"><?= strtoupper(substr($pg['name'], 0, 1)) ?></span>
                      </div>
                    <?php endif; ?>
                    <div class="ml-3">
                      <h3 class="font-bold"><?= htmlspecialchars($pg['name']) ?></h3>
                      <p class="text-sm text-gray-400">by <?= htmlspecialchars($pg['creator_name']) ?></p>
                    </div>
                  </div>
                  <div class="flex items-center justify-between mt-4">
                    <span class="text-sm text-gray-400"><i class="fas fa-users mr-1"></i> <?= $pg['member_count'] ?> members</span>
                    <form method="POST" class="inline">
                      <input type="hidden" name="group_id" value="<?= $pg['id'] ?>">
                      <button type="submit" name="join_group" class="bg-brand hover:bg-purple-600 text-white py-1.5 px-3 rounded-lg font-medium transition text-sm">
                        Join Group
                      </button>
                    </form>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
      <?php else: ?>
        <!-- Group Chat Interface -->
        <div class="bg-gray-800/50 backdrop-blur-sm p-3 border-b border-gray-700/50 flex items-center justify-between">
          <div class="flex items-center space-x-3">
            <?php if ($group['image']): ?>
              <img src="<?= htmlspecialchars($group['image']) ?>" alt="<?= htmlspecialchars($group['name']) ?>" class="w-12 h-12 rounded-full object-cover">
            <?php else: ?>
              <div class="w-12 h-12 rounded-full bg-gradient-to-r from-purple-500 to-pink-500 flex items-center justify-center">
                <span class="font-bold text-lg"><?= strtoupper(substr($group['name'], 0, 1)) ?></span>
              </div>
            <?php endif; ?>
            <div>
              <h2 class="font-bold"><?= htmlspecialchars($group['name']) ?></h2>
              <p class="text-xs text-gray-400">by <?= htmlspecialchars($group['creator_name']) ?></p>
            </div>
          </div>
          <div class="flex space-x-2">
            <?php if ($isMember): ?>
              <a href="group.php?group=<?= $group_id ?>" class="px-3 py-1.5 bg-green-600 hover:bg-green-700 text-white text-xs rounded transition flex items-center">
                <i class="fas fa-eye mr-1"></i> View Group
              </a>
            <?php endif; ?>
          </div>
        </div>

        <!-- Messages Area -->
        <div id="messages-container" class="flex-1 p-3 space-y-4 overflow-y-auto mobile-full-height">
          <?php if (empty($messages)): ?>
            <div class="text-center py-12 text-gray-400">
              <i class="fas fa-comment-slash text-4xl mb-4"></i>
              <p class="text-lg">No messages yet. Be the first to say hello! 👋</p>
              <?php if (!$isMember): ?>
                <p class="text-sm mt-2">Join the group to start chatting.</p>
              <?php endif; ?>
            </div>
          <?php else: ?>
            <?php foreach ($messages as $msg): ?>
              <div class="flex <?= $msg['sender_id'] == $user_id ? 'justify-end' : 'justify-start' ?>">
                <div class="mobile-message max-w-xs sm:max-w-sm md:max-w-md px-4 py-3 rounded-2xl shadow-md
                            <?= $msg['sender_id'] == $user_id ? 'bg-brand text-white' : 'bg-gray-700/80 backdrop-blur-sm text-gray-100' ?>">
                  <div class="flex items-center gap-2 mb-1">
                    <img src="<?= htmlspecialchars($msg['picture'] ?? '/assets/img/default-avatar.png') ?>" 
                         alt="Avatar" class="w-8 h-8 rounded-full border-2 <?= $msg['sender_id'] == $user_id ? 'border-white' : 'border-gray-500' ?> object-cover">
                    <span class="font-semibold text-sm"><?= htmlspecialchars($msg['name']) ?></span>
                  </div>
                  <p class="text-sm leading-relaxed break-words"><?= htmlspecialchars($msg['message']) ?></p>
                  <span class="text-xs text-gray-300 block mt-1"><?= date("g:i A", strtotime($msg['sent_at'])) ?></span>
                </div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>

        <!-- Message Input -->
        <form id="message-form" class="p-3 bg-gray-800/50 backdrop-blur-sm border-t border-gray-700/50">
          <div class="flex items-center space-x-2">
            <input type="text" id="message-input" placeholder="<?= $isMember ? 'Type a message...' : 'Join group to chat...' ?>" 
                   class="flex-1 bg-gray-700/50 border border-gray-600 rounded-full px-4 py-3 text-gray-100 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-brand transition text-sm" 
                   <?= $isMember ? 'required' : 'disabled' ?>>
            <button type="submit" id="send-btn" 
                    class="bg-brand hover:bg-purple-600 text-white p-3 rounded-full transition transform hover:scale-110 shadow-lg <?= $isMember ? '' : 'opacity-50 cursor-not-allowed' ?>" 
                    <?= $isMember ? '' : 'disabled' ?>>
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
        <button onclick="document.getElementById('create-group-modal').classList.add('hidden')" class="text-gray-400 hover:text-white">
          <i class="fas fa-times"></i>
        </button>
      </div>
      
      <?php if ($errorMessage): ?>
        <div class="mb-4 p-3 bg-red-900/50 text-red-300 rounded-lg text-sm">
          <i class="fas fa-exclamation-circle mr-2"></i><?= htmlspecialchars($errorMessage) ?>
        </div>
      <?php endif; ?>
      
      <?php if ($successMessage): ?>
        <div class="mb-4 p-3 bg-green-900/50 text-green-300 rounded-lg text-sm">
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
    // Theme Toggle
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
    }

    // Auto-scroll to bottom
    function scrollToBottom() {
        const container = document.getElementById('messages-container');
        if (container) {
            container.scrollTop = container.scrollHeight;
        }
    }
    scrollToBottom();

    // Send Message with AJAX (ONLY AJAX in the entire app)
    document.getElementById('message-form')?.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const messageInput = document.getElementById('message-input');
        const message = messageInput.value.trim();
        if (!message) return;
        
        const formData = new FormData();
        formData.append('action', 'send_message');
        formData.append('message', message);
        
        try {
            const response = await fetch('group.php?group=<?= $group_id ?>', {
                method: 'POST',
                body: formData
            });
            const result = await response.json();
            
            if (result.success) {
                // Append new message to UI
                const container = document.getElementById('messages-container');
                const isOwnMessage = result.message_data.sender_id == <?= $user_id ?>;
                
                const messageDiv = document.createElement('div');
                messageDiv.className = `flex ${isOwnMessage ? 'justify-end' : 'justify-start'}`;
                
                messageDiv.innerHTML = `
                    <div class="mobile-message max-w-xs sm:max-w-sm md:max-w-md px-4 py-3 rounded-2xl shadow-md
                                ${isOwnMessage ? 'bg-brand text-white' : 'bg-gray-700/80 backdrop-blur-sm text-gray-100'}">
                      <div class="flex items-center gap-2 mb-1">
                        <img src="${result.message_data.picture || '/assets/img/default-avatar.png'}" 
                             alt="Avatar" class="w-8 h-8 rounded-full border-2 ${isOwnMessage ? 'border-white' : 'border-gray-500'} object-cover">
                        <span class="font-semibold text-sm">${result.message_data.name}</span>
                      </div>
                      <p class="text-sm leading-relaxed break-words">${result.message_data.message}</p>
                      <span class="text-xs text-gray-300 block mt-1">${new Date().toLocaleTimeString([], {hour: 'numeric', minute:'2-digit'})}</span>
                    </div>
                `;
                
                container.appendChild(messageDiv);
                messageInput.value = '';
                scrollToBottom();
            } else {
                alert('Error: ' + result.message);
            }
        } catch (error) {
            alert('Failed to send message.');
        }
    });
  </script>
</body>
</html>
