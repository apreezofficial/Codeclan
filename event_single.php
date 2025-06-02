<?php
// DB connection
require 'conn.php';
require 'inc/parsedown.php';

$id = $_GET['id'] ?? null;

if (!$id) {
  die("Event not found.");
}

$stmt = $conn->prepare("SELECT * FROM events WHERE id = ?");
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}

$stmt->bind_param("i", $id);
$stmt->execute();

$result = $stmt->get_result();
$event = $result->fetch_assoc();

if (!$event) {
  die("Event not found.");
}

$stmt->close();
$Parsedown = new Parsedown();
$detailsHtml = $Parsedown->text($event['details'] ?? '');

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= htmlspecialchars($event['title']) ?> | CodeClan Event</title>
  <script src="/tailwind.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
</head>
<script src="https://cdn.tailwindcss.com?plugins=typography"></script>
<script>
  tailwind.config = {
    darkMode: 'class',
    theme: {
      extend: {
        typography: {
          dark: {
            css: {
              color: '#d1d5db',
              a: { color: '#34d399', '&:hover': { color: '#6ee7b7' } },
              code: {
                color: '#f472b6',
                backgroundColor: '#1f2937',
                padding: '2px 4px',
                borderRadius: '4px',
              },
              pre: {
                color: '#f3f4f6',
                backgroundColor: '#111827',
                padding: '1rem',
                borderRadius: '8px',
              },
              strong: { color: '#fff' },
              blockquote: {
                color: '#9ca3af',
                borderLeftColor: '#34d399',
              },
            },
          },
        },
      },
    },
  }
</script>
<style>
  @tailwind base;
  @tailwind components;
  @tailwind utilities;
</style>
<body class="bg-white dark:bg-[#0B0B0B] text-gray-900 dark:text-white font-sans">
<?php include 'nav.php';?>
<div class="max-w-4xl mx-auto px-4 py-20">
  <a href="index.php#events" class="text-sm text-[#1E88E5] dark:text-[#39FF14] hover:underline mb-6 inline-block">
    ← Back to Events
  </a>

  <div class="bg-white dark:bg-[#111] shadow-lg rounded-xl overflow-hidden">
    <img src="<?= htmlspecialchars($event['image_url']) ?>" alt="<?= htmlspecialchars($event['title']) ?>" class="w-full h-72 object-cover">

    <div class="p-8">
      <h1 class="text-3xl md:text-4xl font-bold mb-4 text-[#1E88E5] dark:text-[#39FF14]">
        <?= htmlspecialchars($event['title']) ?>
      </h1>

      <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">
        📅 <?= date('F j, Y', strtotime($event['event_date'])) ?>
      </p>

      <!-- Short description -->
      <p class="text-lg text-gray-800 dark:text-gray-300 mb-8 leading-relaxed">
        <?= nl2br(htmlspecialchars($event['description'])) ?>
      </p>

      <!-- Markdown Details -->
<div class="prose dark:prose-invert max-w-none text-gray-800 dark:text-gray-300 leading-relaxed">
  <?= $detailsHtml ?>
</div>
    </div>
  </div>
</div>
<?php include 'footer.php';?>
</body>
</html>