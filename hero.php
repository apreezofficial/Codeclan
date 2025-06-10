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
        class="inline-block bg-[#8A2BA1] hover:bg-[#8A2BA1]/80 transition rounded-full px-8 py-3 font-semibold shadow-md text-white"
      >
        Get Started
      </a>
      <a
        href="#about"
        class="text-[#8A2BA1] hover:underline font-medium cursor-pointer dark:text-[#8A2BA1]"
      >
        Learn More
      </a>
    </div>
  </div>

  <!-- Right Content Placeholder -->
  <div
    class="flex-1 hidden md:flex justify-center items-center
           bg-gray-100 rounded-2xl border-4 border-[#8A2BA1]
           dark:bg-[#121212] dark:border-[#8A2BA1]
           w-full max-w-lg h-96"
  ><div id="simpleSlider" class="relative w-full h-96 overflow-hidden rounded-2xl shadow-xl bg-gray-100">
  <!-- Slides Container -->
  <div class="slides-container flex h-full transition-transform duration-500 ease-in-out">
    <!-- Slide 1 -->
    <div class="slide w-full flex-shrink-0">
      <img src="/assets/img/ccmcap.png" class="w-full h-full object-cover rounded-2xl" />
    </div>
    <!-- Slide 2 -->
    <div class="slide w-full flex-shrink-0">
      <img src="/assets/img/ccmidcard.png" class="w-full h-full object-cover rounded-2xl" />
    </div>
    <!-- Slide 3 -->
    <div class="slide w-full flex-shrink-0">
      <img src="/assets/img/ccmiphone.png" class="w-full h-full object-cover rounded-2xl" />
    </div>
  </div>

  <!-- Navigation Arrows -->
  <button class="nav-arrow absolute left-2 top-1/2 -translate-y-1/2 bg-black/30 text-white p-2 rounded-full hover:bg-black/50 transition">
    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
    </svg>
  </button>
  <button class="nav-arrow absolute right-2 top-1/2 -translate-y-1/2 bg-black/30 text-white p-2 rounded-full hover:bg-black/50 transition">
    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
    </svg>
  </button>

  <!-- Indicators -->
  <div class="absolute bottom-4 left-1/2 -translate-x-1/2 flex space-x-2">
    <span class="indicator-dot w-2 h-2 rounded-full bg-white/80 cursor-pointer"></span>
    <span class="indicator-dot w-2 h-2 rounded-full bg-white/50 cursor-pointer"></span>
    <span class="indicator-dot w-2 h-2 rounded-full bg-white/50 cursor-pointer"></span>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    const slider = document.getElementById('simpleSlider');
    const slidesContainer = slider.querySelector('.slides-container');
    const slides = slider.querySelectorAll('.slide');
    const prevBtn = slider.querySelectorAll('.nav-arrow')[0];
    const nextBtn = slider.querySelectorAll('.nav-arrow')[1];
    const dots = slider.querySelectorAll('.indicator-dot');
    
    let currentIndex = 0;
    let autoSlideInterval;

    // Initialize slider
    function initSlider() {
      updateSlider();
      startAutoSlide();
      
      // Event listeners
      prevBtn.addEventListener('click', goToPrevSlide);
      nextBtn.addEventListener('click', goToNextSlide);
      
      dots.forEach((dot, index) => {
        dot.addEventListener('click', () => goToSlide(index));
      });
      
      // Touch events
      let touchStartX = 0;
      let touchEndX = 0;
      
      slidesContainer.addEventListener('touchstart', e => {
        touchStartX = e.changedTouches[0].clientX;
        clearInterval(autoSlideInterval);
      }, {passive: true});
      
      slidesContainer.addEventListener('touchend', e => {
        touchEndX = e.changedTouches[0].clientX;
        handleSwipe();
        startAutoSlide();
      }, {passive: true});
    }

    function handleSwipe() {
      if (touchEndX < touchStartX - 50) goToNextSlide();
      if (touchEndX > touchStartX + 50) goToPrevSlide();
    }

    function startAutoSlide() {
      autoSlideInterval = setInterval(goToNextSlide, 5000);
    }

    function updateSlider() {
      slidesContainer.style.transform = `translateX(-${currentIndex * 100}%)`;
      
      // Update dots
      dots.forEach((dot, index) => {
        dot.classList.toggle('bg-white/80', index === currentIndex);
        dot.classList.toggle('bg-white/50', index !== currentIndex);
      });
    }

    function goToSlide(index) {
      currentIndex = index;
      updateSlider();
      resetAutoSlide();
    }

    function goToPrevSlide() {
      currentIndex = (currentIndex - 1 + slides.length) % slides.length;
      updateSlider();
      resetAutoSlide();
    }

    function goToNextSlide() {
      currentIndex = (currentIndex + 1) % slides.length;
      updateSlider();
      resetAutoSlide();
    }

    function resetAutoSlide() {
      clearInterval(autoSlideInterval);
      startAutoSlide();
    }

    // Initialize the slider
    initSlider();
  });
</script>
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