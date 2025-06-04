<section
  id="hero"
  class="min-h-screen flex flex-col md:flex-row justify-center items-center max-w-7xl mx-auto px-6 md:px-12 py-20
         bg-gradient-to-b from-white to-gray-100 text-gray-900
         dark:from-[#0D0D0D] dark:to-black dark:text-white"
>
  <!-- Left Content -->
  <div class="flex-1 flex flex-col justify-center items-start md:pr-12 space-y-6 max-w-xl">
    <h1
      class="text-5xl md:text-6xl font-extrabold leading-tight tracking-wide"
    >
      Build. Code. Create.
    </h1>

    <p class="text-lg md:text-xl text-gray-700 dark:text-gray-300">
      Join a community of passionate developers building amazing projects and
      pushing tech forward.
    </p>

    <div class="flex gap-6 items-center">
      <a
        href="#join"
        class="inline-block bg-[#39FF14] hover:bg-[#39FF70] transition rounded-full px-8 py-3 font-semibold shadow-md text-white"
      >
        Get Started
      </a>
      <a
        href="#about"
        class="text-[#6A2BA1] hover:underline font-medium cursor-pointer"
      >
        Learn More
      </a>
    </div>
  </div>

  <!-- Right Content Placeholder -->
  <div
    class="flex-1 hidden md:flex justify-center items-center
           bg-gray-100 rounded-2xl border-2 border-[#1E88E5]
           dark:bg-[#121212] dark:border-[#39FF14]
           w-full max-w-lg h-96"
  >
    <span class="text-gray-500 dark:text-green-400 italic">
      Right side content here (slider, images, etc.).Stil on it though
    </span>
  </div>
</section>

<style>
  /* Simple fade-in animation for left content */
  #hero > div:first-child > * {
    opacity: 0;
    transform: translateY(20px);
    animation: fadeUp 0.7s ease forwards;
  }

  #hero > div:first-child > *:nth-child(1) {
    animation-delay: 0.2s;
  }

  #hero > div:first-child > *:nth-child(2) {
    animation-delay: 0.5s;
  }

  #hero > div:first-child > *:nth-child(3) {
    animation-delay: 0.8s;
  }

  @keyframes fadeUp {
    to {
      opacity: 1;
      transform: translateY(0);
    }
  }
</style>