<?php
  include 'conn.php';
  $limit = basename($_SERVER['PHP_SELF']) === 'index.php' ? 3 : 1000;
  $today = date('Y-m-d');
  $sql = "SELECT * FROM events ORDER BY event_date DESC LIMIT $limit";
  $result = mysqli_query($conn, $sql);
?>
<section id="events" class="py-24 px-6 md:px-12 bg-white dark:bg-[#0B0B0B] text-gray-900 dark:text-white">
  <div class="max-w-7xl mx-auto">
    <h2 class="text-4xl md:text-5xl font-bold text-center mb-8">CodeClan <span class="text-[#8A2BA1] dark:text-[#8A2BA1]">Events</span></h2>

    <!-- Filters -->
    <div class="flex justify-center gap-4 mb-10">
      <button class="filter-btn active-filter" data-filter="all">All</button>
      <button class="filter-btn" data-filter="upcoming">Upcoming</button>
      <button class="filter-btn" data-filter="past">Past</button>
    </div>

    <!-- Events Grid -->
    <div id="eventsContainer" class="grid md:grid-cols-3 gap-8 transition-all duration-300">
      <?php while($event = mysqli_fetch_assoc($result)): ?>
        <?php
          $isUpcoming = $event['event_date'] >= $today ? 'upcoming' : 'past';
        ?>
<a href="event_single.php?id=<?= $event['id'] ?>" class="block group event-card <?= $isUpcoming ?> bg-white dark:bg-[#111] rounded-2xl shadow-xl overflow-hidden border border-gray-200 dark:border-[#222] hover:scale-[1.02] hover:shadow-2xl transition-all duration-300">
  <div class="relative">
    <img src="<?= htmlspecialchars($event['image_url']) ?>" alt="<?= htmlspecialchars($event['title']) ?>" class="w-full h-48 object-cover transition-transform duration-300 group-hover:scale-105" />

    <!-- Tag Label -->
    <span class="absolute top-3 right-3 bg-[#8A2BA1] text-white text-xs px-3 py-1 rounded-full font-semibold shadow-md uppercase">
      <?= ucfirst($isUpcoming) ?>
    </span>
  </div>

  <div class="p-5">
    <h3 class="text-2xl font-bold text-[#8A2BA1] group-hover:underline mb-2">
      <?= htmlspecialchars($event['title']) ?>
    </h3>
    <p class="text-sm text-gray-700 dark:text-gray-300 mb-4 line-clamp-3">
      <?= htmlspecialchars($event['description']) ?>
    </p>
    <div class="flex items-center justify-between text-sm text-gray-600 dark:text-gray-400">
      <span>📅 <?= date('F j, Y', strtotime($event['event_date'])) ?></span>
      <span class="text-xs bg-gray-100 dark:bg-[#1c1c1c] px-2 py-1 rounded-md">
        Tap to view →
      </span>
    </div>
  </div>
</a>
      <?php endwhile; ?>
    </div>

    <!-- View All Button -->
    <?php if (basename($_SERVER['PHP_SELF']) === 'index.php'): ?>
      <div class="text-center mt-10">
        <a href="events.php" class="inline-block bg-black dark:bg-[#1c1c1c] text-white px-6 py-3 rounded-full font-semibold hover:bg-[#6A2BA1] transition">View All Events >></a>
      </div>
    <?php endif; ?>
  </div>
</section>
<style>
  .filter-btn {
  padding: 0.5rem 1rem;
  border-radius: 9999px;
  border: 1px solid #ccc;
  background-color: transparent;
  transition: all 0.3s ease;
}

.filter-btn:hover {
  background-color: #8A2BA1;
}

.active-filter {
  background-color: #6A2BA1;
  color: white;
  border-color: #6A2BA1;
}

.event-card {
  opacity: 1;
  transition: opacity 0.3s ease;
}
a{
  text-decoration: none;
}
</style>
