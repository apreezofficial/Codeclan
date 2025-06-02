<?php include 'conn.php';?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>CodeClan - The home of titans</title>
  <script src="/tailwind.js"></script>
<!-- Tailwind CSS -->
<script>
  tailwind.config = { darkMode: 'class' }
</script>

<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <style>
  :root {
  /* Light Theme Scrollbar Colors */
  --scrollbar-track: #f4f4f4;    /* Cloud White background */
  --scrollbar-thumb: #1e88e5;    /* Electric Blue accent */
}

[data-theme='dark'] {
  /* Dark Theme Scrollbar Colors */
  --scrollbar-track: #0d0d0d;    /* Jet Black background */
  --scrollbar-thumb: #39ff14;    /* Neon Green accent */
}

html {
  scroll-behavior: smooth;
  scroll-padding-top: 4rem; 
}

/* Keyboard focus nice scroll offset */
:focus-visible {
  scroll-margin-top: 4rem;
}

/* Scrollbar Styling for WebKit Browsers */
::-webkit-scrollbar {
  width: 8px;
  height: 8px;
}

::-webkit-scrollbar-track {
  background: var(--scrollbar-track);
}

::-webkit-scrollbar-thumb {
  background-color: var(--scrollbar-thumb);
  border-radius: 10px;
  border: 2px solid var(--scrollbar-track);
  transition: background-color 0.3s ease;
}

::-webkit-scrollbar-thumb:hover {
  background-color: var(--scrollbar-thumb);
  filter: brightness(1.2);
}

/* Firefox Scrollbar Styling */
* {
  scrollbar-width: thin;
  scrollbar-color: var(--scrollbar-thumb) var(--scrollbar-track);
}
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
<?php include 'nav.php';
include 'hero.php';
include 'about.php';
include 'idolo.php';
include 'programs.php';
include 'community.php';
include 'why_join_us.php';
include 'events.php';
?>
</html>