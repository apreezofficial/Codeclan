<!DOCTYPE html><html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>CodeClan Navbar</title>
  <script src="/tailwind.js"></script>
<!-- Tailwind CSS -->
<script>
  tailwind.config = { darkMode: 'class' }
</script>

<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <style>
    .slidebar {
      transition: transform 0.4s ease;
    }
    .slidebar.hidden {
      transform: translateX(-100%);
    }
    .slidebar.visible {
      transform: translateX(0);
    }
  </style>
</head>
<?php include 'nav.php';?>
<?php include 'hero.php';?>
</html>