
  document.getElementById('subscribeForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const emailInput = document.getElementById('emailInput');
    const message = document.getElementById('subscribeMessage');
    const email = emailInput.value.trim();

    if (!email) {
      message.textContent = 'Please enter your email address.';
      message.className = 'mt-3 text-xs text-red-500 dark:text-red-400';
      return;
    }

    message.textContent = 'Submitting...';
    message.className = 'mt-3 text-xs text-gray-500 dark:text-gray-400';

    try {
      const response = await fetch('/ajax/subscribe.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({ email })
      });

      const result = await response.json();

      if (result.status === 'success') {
        message.textContent = result.message;
        message.className = 'mt-3 text-xs text-green-600 dark:text-green-400';
        emailInput.value = '';
      } else {
        message.textContent = result.message;
        message.className = 'mt-3 text-xs text-red-500 dark:text-red-400';
      }
    } catch (error) {
      message.textContent = 'An error occurred. Please try again.';
      message.className = 'mt-3 text-xs text-red-500 dark:text-red-400';
    }
  });
  
document.addEventListener('DOMContentLoaded', () => {
  const filterButtons = document.querySelectorAll('.filter-btn');
  const eventCards = document.querySelectorAll('.event-card');

  function filterEvents(type) {
    eventCards.forEach(card => {
      if (type === 'all' || card.classList.contains(type)) {
        card.style.display = 'block';
        setTimeout(() => card.style.opacity = 1, 100);
      } else {
        card.style.opacity = 0;
        setTimeout(() => card.style.display = 'none', 200);
      }
    });

    filterButtons.forEach(btn => btn.classList.remove('active-filter'));
    document.querySelector(`[data-filter="${type}"]`).classList.add('active-filter');
  }

  filterButtons.forEach(btn => {
    btn.addEventListener('click', () => {
      const type = btn.dataset.filter;
      filterEvents(type);
    });
  });

  // Initial filter
  filterEvents('all');
});
  