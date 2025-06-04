<?php 
include 'conn.php';?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="google-site-verification" content="mpfvExLjgj2MPsncOhhRIMSpfFm-zw9WJNEGflQNONU" />

  <!-- Title & Description -->
  <title>CodeClan</title>
  <meta name="title" content="Codeclan- Home of titans" />
  <meta name="description" content="Loremisplur" />

  <!-- SEO Keywords -->
  <meta name="keywords" content="codeclan, ..." />
  <meta name="author" content="Precious Adedokun" />
  <meta name="robots" content="index, follow" />
  <link rel="canonical" href="https://demo.clan.preciousadedokun.com.ng" />
  
  <!-- Open Graph / Facebook -->
  <meta property="og:type" content="website" />
  <meta property="og:url" content="https://demo.clan.preciousadedokun.com.ng/assets/img/codeclanlogo3d.png" />
  <meta property="og:title" content="code clan....." />
  <meta property="og:description" content="code clan..." />
  <meta property="og:image" content="https://demo.clan.preciousadedokun.com.ng/assets/img/codeclanlogo3d.png" />

  <!-- Twitter -->
  <meta name="twitter:card" content="summary_large_image" />
  <meta name="twitter:url" content="https://demo.clan.preciousadedokun.com.ng" />
  <meta name="twitter:title" content="Code clan" />
  <meta name="twitter:description" content="code clan....." />
  <meta name="twitter:image" content="https://demo.clan.preciousadedokun.com.ng/assets/img/codeclanlogo3d.png" />

  <!-- Theme & Favicon -->
  <meta name="theme-color" content="#39FF14">
  <link rel="icon" type="image/png" href="https://demo.clan.preciousadedokun.com.ng/assets/img/codeclanlogo3d.png" />

  <script src="/tailwind.js"></script>
<!-- Tailwind CSS -->
<script>
  tailwind.config = { darkMode: 'class' }
</script>
<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
</head>
<body class="dark:bg-black">
<?php 
include 'nav.php';
include 'hero.php';
include 'about.php';
include 'idolo.php';
include 'programs.php';
include 'community.php';
include 'why.php';
include 'event.php';
include 'footer.php';
?>
</body>
</html>