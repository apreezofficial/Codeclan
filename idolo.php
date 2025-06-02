<section
  id="gurus"
  class="relative py-20 px-6 md:px-12 bg-white text-gray-900 dark:bg-[#0D0D0D] dark:text-white overflow-hidden"
>
  <div class="text-center mb-12">
    <h2 class="text-4xl md:text-5xl font-bold mb-4">We’ve Trained Gurus</h2>
    <p class="text-lg text-gray-600 dark:text-gray-300 max-w-2xl mx-auto">
      From junior devs to top senior engineers, our alumni are shaping the tech world.
    </p>
  </div>  <div class="relative">
    <!-- Gradient Edges -->
    <div class="absolute top-0 left-0 h-full w-20 bg-gradient-to-r from-white via-white/50 to-transparent dark:from-[#0D0D0D] dark:via-[#0D0D0D]/50 z-10"></div>
    <div class="absolute top-0 right-0 h-full w-20 bg-gradient-to-l from-white via-white/50 to-transparent dark:from-[#0D0D0D] dark:via-[#0D0D0D]/50 z-10"></div><!-- Logo Slider -->

<div class="relative overflow-hidden">
  <div class="flex items-center gap-10 animate-slide-infinite hover:pause-animation px-4 py-6">
    <?php
      $sql = "SELECT name, image_url FROM devs";
      $result = $conn->query($sql);

      if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
          $name = htmlspecialchars($row['name']);
          $image = htmlspecialchars($row['image_url']);
          echo <<<HTML
            <img 
              src="$image" 
              alt="$name" 
              title="$name"
              class="w-20 h-20 object-cover rounded-full hover:scale-110 transition-transform duration-300"
            />
          HTML;
        }
      } else {
        echo "<p class='text-gray-500 dark:text-gray-400'>No developers found.</p>";
      }
    ?>
  </div>

  <!-- Edge Fade (Optional) -->
  <div class="absolute top-0 left-0 w-12 h-full bg-gradient-to-r from-white via-white/60 dark:from-[#0d0d0d] dark:via-[#0d0d0d]/60 pointer-events-none"></div>
  <div class="absolute top-0 right-0 w-12 h-full bg-gradient-to-l from-white via-white/60 dark:from-[#0d0d0d] dark:via-[#0d0d0d]/60 pointer-events-none"></div>
</div>

  </div>
</section><style>
  @keyframes slide-infinite {
    0% {
      transform: translateX(0);
    }
    100% {
      transform: translateX(-50%);
    }
  }

  .animate-slide-infinite {
    animation: slide-infinite 30s linear infinite;
    width: max-content;
  }

  .hover\:pause-animation:hover {
    animation-play-state: paused;
  }
</style>