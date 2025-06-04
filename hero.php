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
<div id="madSlideShow" class="relative w-full h-96 overflow-hidden rounded-2xl shadow-xl">
  <div class="slides flex transition-transform duration-700 ease-in-out h-full">
    <!-- Slide 1 -->
    <div class="slide w-full flex-shrink-0 flex items-center justify-center relative glass-slide active">
      <img src="/assets/img/ccmcap.png" class="object-cover w-full h-full rounded-2xl" />
      <div class="overlay absolute inset-0 bg-black/40 backdrop-blur-md rounded-2xl"></div>
    </div>
    <!-- Slide 2 -->
    <div class="slide w-full flex-shrink-0 flex items-center justify-center relative glass-slide">
      <img src="/assets/img/ccmidcard.png" class="object-cover w-full h-full rounded-2xl" />
      <div class="overlay absolute inset-0 bg-black/40 backdrop-blur-md rounded-2xl"></div>
    </div>
    <!-- Slide 3 -->
    <div class="slide w-full flex-shrink-0 flex items-center justify-center relative glass-slide">
      <img src="/assets/img/ccmiphone.png" class="object-cover w-full h-full rounded-2xl" />
      <div class="overlay absolute inset-0 bg-black/40 backdrop-blur-md rounded-2xl"></div>
    </div>
  </div>
</div>
  </div>
</section>
  <style>
  .glass-slide {
    position: relative;
    overflow: hidden;
  }

  .glass-slide.active .overlay {
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(1px) saturate(180%);
    border: 1px solid rgba(255, 255, 255, 0.2);
    transition: all 0.5s ease;
  }

  .glass-slide:not(.active) .overlay {
    background: rgba(0, 0, 0, 0.6);
    backdrop-filter: blur(4px);
  }

  html.dark .glass-slide.active .overlay {
    background: rgba(57, 255, 20, 0.08);
    border: 1px solid rgba(57, 255, 20, 0.3);
  }
</style>
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