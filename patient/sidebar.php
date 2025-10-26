<?php
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<style>
/* Sidebar Styles */
.sidebar {
  position: fixed;
  top: 0;
  left: 0;
  height: 100vh;
  width: 280px;
  background: linear-gradient(180deg, #1e40af 0%, #1e3a8a 100%);
  color: white;
  padding: 1.5rem;
  transform: translateX(-100%);
  transition: transform 0.3s ease-in-out;
  z-index: 1000;
  overflow-y: auto;
  box-shadow: 4px 0 15px rgba(0, 0, 0, 0.2);
}

.sidebar.active {
  transform: translateX(0);
}

/* Desktop sidebar always visible */
@media (min-width: 768px) {
  .sidebar {
    transform: translateX(0);
    box-shadow: none;
  }
  
  .main-content {
    margin-left: 280px;
  }
}

/* Mobile navbar */
.mobile-navbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  background: linear-gradient(90deg, #1e40af 0%, #1e3a8a 100%);
  color: white;
  padding: 1rem 1.5rem;
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  z-index: 999;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

@media (min-width: 768px) {
  .mobile-navbar {
    display: none;
  }
}

@media (max-width: 767px) {
  .main-content {
    margin-top: 70px;
  }
}

/* Overlay */
.sidebar-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.5);
  z-index: 999;
  display: none;
  transition: opacity 0.3s ease-in-out;
}

.sidebar-overlay.active {
  display: block;
}

/* Sidebar Header */
.sidebar-header {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  margin-bottom: 2rem;
  padding-bottom: 1.5rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.2);
}

.sidebar-logo {
  height: 3.5rem;
  width: auto;
  filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.2));
}

.sidebar-title {
  font-size: 1.1rem;
  font-weight: 700;
  line-height: 1.3;
}

/* Navigation */
.sidebar-nav {
  list-style: none;
  padding: 0;
  margin: 0;
}

.sidebar-nav li {
  margin-bottom: 0.5rem;
}

.sidebar-nav a,
.sidebar-nav button {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  width: 100%;
  padding: 0.875rem 1rem;
  border-radius: 0.5rem;
  text-decoration: none;
  color: white;
  font-weight: 500;
  transition: all 0.2s ease;
  border: none;
  background: transparent;
  cursor: pointer;
  font-size: 0.95rem;
}

.sidebar-nav a:hover,
.sidebar-nav button:hover {
  background: rgba(255, 255, 255, 0.1);
  transform: translateX(4px);
}

.sidebar-nav a.active {
  background: rgba(255, 255, 255, 0.2);
  font-weight: 600;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
}

/* Icons */
.nav-icon {
  width: 20px;
  height: 20px;
  flex-shrink: 0;
}

/* Burger Button */
.burger-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 2.5rem;
  height: 2.5rem;
  border: none;
  background: rgba(255, 255, 255, 0.15);
  border-radius: 0.5rem;
  cursor: pointer;
  transition: all 0.2s ease;
}

.burger-btn:hover {
  background: rgba(255, 255, 255, 0.25);
}

.burger-btn svg {
  width: 1.5rem;
  height: 1.5rem;
  color: white;
}

/* Close button in sidebar */
.close-sidebar-btn {
  display: none;
  position: absolute;
  top: 1.5rem;
  right: 1.5rem;
  background: rgba(255, 255, 255, 0.15);
  border: none;
  border-radius: 0.5rem;
  padding: 0.5rem;
  cursor: pointer;
  transition: all 0.2s ease;
}

.close-sidebar-btn:hover {
  background: rgba(255, 255, 255, 0.25);
}

.close-sidebar-btn svg {
  width: 1.25rem;
  height: 1.25rem;
  color: white;
}

@media (max-width: 767px) {
  .close-sidebar-btn {
    display: block;
  }
}
</style>

<!-- Mobile Navbar -->
<div class="mobile-navbar">
  <div style="display: flex; align-items: center; gap: 0.75rem;">
    <img src="/images/halili-logo.png" alt="Halili Dental Logo" style="height: 2.5rem; width: auto;">
    <h1 style="font-size: 1.125rem; font-weight: 700; margin: 0;">Halili Dental</h1>
  </div>
  <button id="burgerBtn" class="burger-btn">
    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
    </svg>
  </button>
</div>

<!-- Sidebar -->
<aside id="sidebar" class="sidebar">
  <button id="closeSidebarBtn" class="close-sidebar-btn">
    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
    </svg>
  </button>

  <div class="sidebar-header">
    <img src="/images/halili-logo.png" alt="Halili Dental Logo" class="sidebar-logo">
    <h1 class="sidebar-title">Halili Dental Clinic PMSS</h1>
  </div>

  <ul class="sidebar-nav">
    <li>
      <a href="dashboard.php" class="<?= $currentPage === 'dashboard.php' ? 'active' : '' ?>">
        <svg class="nav-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
        </svg>
        Home
      </a>
    </li>
    <li>
      <a href="treatment-plan.php" class="<?= $currentPage === 'treatment-plan.php' ? 'active' : '' ?>">
        <svg class="nav-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
        </svg>
        My Treatment Plan
      </a>
    </li>
    <li>
      <a href="appointments.php" class="<?= $currentPage === 'appointments.php' ? 'active' : '' ?>">
        <svg class="nav-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
        </svg>
        My Appointments
      </a>
    </li>
    <li>
      <a href="profile.php" class="<?= $currentPage === 'profile.php' ? 'active' : '' ?>">
        <svg class="nav-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
        </svg>
        Profile
      </a>
    </li>
    <li>
      <a href="feedback.php" class="<?= $currentPage === 'feedback.php' ? 'active' : '' ?>">
        <svg class="nav-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
        </svg>
        Feedback & Surveys
      </a>
    </li>
    <li style="margin-top: 2rem; padding-top: 1rem; border-top: 1px solid rgba(255, 255, 255, 0.2);">
      <form method="POST" action="logout.php" style="margin: 0;">
        <button type="submit">
          <svg class="nav-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
          </svg>
          Logout
        </button>
      </form>
    </li>
  </ul>
</aside>

<!-- Overlay -->
<div id="overlay" class="sidebar-overlay"></div>

<!-- Script for Sidebar Toggle -->
<script>
(function() {
  const burgerBtn = document.getElementById('burgerBtn');
  const closeSidebarBtn = document.getElementById('closeSidebarBtn');
  const sidebar = document.getElementById('sidebar');
  const overlay = document.getElementById('overlay');

  function openSidebar() {
    sidebar.classList.add('active');
    overlay.classList.add('active');
    document.body.style.overflow = 'hidden';
  }

  function closeSidebar() {
    sidebar.classList.remove('active');
    overlay.classList.remove('active');
    document.body.style.overflow = '';
  }

  if (burgerBtn) {
    burgerBtn.addEventListener('click', openSidebar);
  }

  if (closeSidebarBtn) {
    closeSidebarBtn.addEventListener('click', closeSidebar);
  }

  if (overlay) {
    overlay.addEventListener('click', closeSidebar);
  }

  // Close sidebar when clicking a link on mobile
  const sidebarLinks = sidebar.querySelectorAll('a');
  sidebarLinks.forEach(link => {
    link.addEventListener('click', () => {
      if (window.innerWidth < 768) {
        closeSidebar();
      }
    });
  });

  // Handle window resize
  window.addEventListener('resize', () => {
    if (window.innerWidth >= 768) {
      closeSidebar();
    }
  });
})();
</script>