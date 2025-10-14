<?php
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!-- Mobile Navbar -->
<div class="bg-blue-700 text-white flex items-center justify-between p-4 md:hidden">
  <h1 class="text-lg font-bold flex items-center gap-2">
    <img src="../images/halili-logo.png" alt="Halili Dental Logo" class="h-10 w-auto">
    Halili Dental Clinic
  </h1>
  <!-- Burger Button -->
  <button id="burgerBtn" class="focus:outline-none">
    <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
    </svg>
  </button>
</div>

<!-- Sidebar -->
<aside id="sidebar" class="w-64 bg-blue-700 text-white p-4 fixed h-full top-0 left-0 transform -translate-x-full md:translate-x-0 transition-transform duration-300 ease-in-out z-50">
  <h1 class="text-xl font-bold mb-4 flex items-center gap-2">
    <img src="../images/halili logo.png" alt="Halili Dental Logo" class="h-14 w-auto">
    Halili Dental Clinic PMSS
  </h1>
  <ul class="space-y-4">
    <li>
      <a href="dashboard.php" class="block px-3 py-2 rounded <?= $currentPage === 'dashboard.php' ? 'bg-blue-800' : 'hover:bg-blue-600' ?>">Home</a>
    </li>
    <li>
      <a href="treatment-plan.php" class="block px-3 py-2 rounded <?= $currentPage === 'treatment-plan.php' ? 'bg-blue-800' : 'hover:bg-blue-600' ?>">My Treatment Plan</a>
    </li>
    <li>
      <a href="appointments.php" class="block px-3 py-2 rounded <?= $currentPage === 'appointments.php' ? 'bg-blue-800' : 'hover:bg-blue-600' ?>">My Appointments</a>
    </li>
    <li>
      <a href="profile.php" class="block px-3 py-2 rounded <?= $currentPage === 'profile.php' ? 'bg-blue-800' : 'hover:bg-blue-600' ?>">Profile</a>
    </li>
    <li>
      <a href="feedback.php" class="block px-3 py-2 rounded <?= $currentPage === 'feedback.php' ? 'bg-blue-800' : 'hover:bg-blue-600' ?>">Feedback & Surveys</a>
    </li>
    <li>
      <form method="POST" action="logout.php">
        <button type="submit" class="w-full text-left px-3 py-2 rounded hover:bg-blue-600">Logout</button>
      </form>
    </li>
  </ul>
</aside>

<!-- Overlay (for mobile when sidebar is open) -->
<div id="overlay" class="fixed inset-0 bg-black bg-opacity-50 hidden md:hidden"></div>

<!-- Script for Burger Toggle -->
<script>
  const burgerBtn = document.getElementById('burgerBtn');
  const sidebar = document.getElementById('sidebar');
  const overlay = document.getElementById('overlay');

  burgerBtn.addEventListener('click', () => {
    sidebar.classList.toggle('-translate-x-full');
    overlay.classList.toggle('hidden');
  });

  overlay.addEventListener('click', () => {
    sidebar.classList.add('-translate-x-full');
    overlay.classList.add('hidden');
  });
</script>
