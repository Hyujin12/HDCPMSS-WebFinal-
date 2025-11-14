<?php
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<style>
/* Sidebar Styles */
.sidebar {
  width: 16rem;
  background: linear-gradient(180deg, #1e40af 0%, #1e3a8a 100%);
  color: white;
  padding: 0;
  position: fixed;
  height: 100vh;
  top: 0;
  left: 0;
  transform: translateX(-100%);
  transition: transform 0.3s ease;
  z-index: 1000;
  box-shadow: 4px 0 12px rgba(0, 0, 0, 0.1);
  display: flex;
  flex-direction: column;
}

@media (min-width: 768px) {
  .sidebar {
    transform: translateX(0);
  }
}

.sidebar.open {
  transform: translateX(0);
}

/* Sidebar Header */
.sidebar-header {
  padding: 1.5rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  background: rgba(0, 0, 0, 0.1);
}

.sidebar-logo {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.sidebar-logo img {
  height: 3rem;
  width: auto;
}

.sidebar-title {
  font-size: 1rem;
  font-weight: 700;
  line-height: 1.3;
  color: white;
}

/* User Profile Section */
.sidebar-user {
  padding: 1.25rem;
  background: rgba(0, 0, 0, 0.15);
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  display: flex;
  align-items: center;
  gap: 0.875rem;
}

.user-avatar {
  width: 45px;
  height: 45px;
  border-radius: 50%;
  background: white;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #1e40af;
  font-weight: 700;
  font-size: 1.1rem;
  flex-shrink: 0;
  border: 2px solid rgba(255, 255, 255, 0.3);
}

.user-info {
  flex: 1;
  min-width: 0;
}

.user-name {
  font-weight: 600;
  font-size: 0.95rem;
  margin: 0;
  color: white;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.user-role {
  font-size: 0.75rem;
  color: rgba(255, 255, 255, 0.7);
  margin: 0;
}

/* Navigation Menu */
.sidebar-nav {
  flex: 1;
  overflow-y: auto;
  padding: 1rem 0;
  /* Add padding at bottom on mobile to prevent content being hidden by browser tabs */
  padding-bottom: 6rem;
}

@media (min-width: 768px) {
  .sidebar-nav {
    padding-bottom: 1rem;
  }
}

.nav-section-title {
  font-size: 0.7rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  color: rgba(255, 255, 255, 0.5);
  padding: 1rem 1.5rem 0.5rem;
  margin-top: 0.5rem;
}

.nav-section-title:first-child {
  margin-top: 0;
}

.sidebar-menu {
  list-style: none;
  margin: 0;
  padding: 0;
}

.sidebar-menu li {
  margin: 0.25rem 0.75rem;
}

.sidebar-link {
  display: flex;
  align-items: center;
  gap: 0.875rem;
  padding: 0.75rem 1rem;
  border-radius: 10px;
  color: rgba(255, 255, 255, 0.9);
  text-decoration: none;
  transition: all 0.2s ease;
  font-weight: 500;
  font-size: 0.95rem;
}

.sidebar-link:hover {
  background: rgba(255, 255, 255, 0.1);
  color: white;
  transform: translateX(4px);
}

.sidebar-link.active {
  background: rgba(255, 255, 255, 0.15);
  color: white;
  font-weight: 600;
}

.sidebar-link i {
  width: 20px;
  text-align: center;
  font-size: 1.1rem;
}

.sidebar-link .badge {
  margin-left: auto;
  background: rgba(239, 68, 68, 0.9);
  color: white;
  padding: 0.2rem 0.5rem;
  border-radius: 12px;
  font-size: 0.7rem;
  font-weight: 700;
}

/* Logout Button */
.sidebar-footer {
  padding: 1rem;
  border-top: 1px solid rgba(255, 255, 255, 0.1);
  background: rgba(0, 0, 0, 0.1);
  /* Elevate footer on mobile to avoid browser tabs */
  margin-bottom: 5rem;
}

@media (min-width: 768px) {
  .sidebar-footer {
    margin-bottom: 0;
  }
}

.btn-logout {
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  padding: 0.875rem;
  background: rgba(220, 38, 38, 0.9);
  border: none;
  border-radius: 10px;
  color: white;
  font-weight: 600;
  font-size: 0.95rem;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-logout:hover {
  background: rgba(185, 28, 28, 1);
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(220, 38, 38, 0.3);
}

/* Mobile Top Bar */
.mobile-topbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1rem;
  background: linear-gradient(135deg, #1e40af 0%, #1e3a8a 100%);
  color: white;
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  z-index: 999;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

@media (min-width: 768px) {
  .mobile-topbar {
    display: none;
  }
}

.mobile-brand {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.mobile-brand img {
  height: 2.5rem;
  width: auto;
}

.mobile-brand-text {
  font-size: 1rem;
  font-weight: 700;
}

.burger-btn {
  background: none;
  border: none;
  color: white;
  cursor: pointer;
  padding: 0.5rem;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 8px;
  transition: background 0.2s ease;
}

.burger-btn:hover {
  background: rgba(255, 255, 255, 0.1);
}

.burger-icon {
  width: 1.75rem;
  height: 1.75rem;
}

/* Overlay */
.sidebar-overlay {
  display: none;
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.6);
  z-index: 999;
  backdrop-filter: blur(2px);
}

.sidebar-overlay.show {
  display: block;
}

@media (min-width: 768px) {
  .sidebar-overlay {
    display: none !important;
  }
}

/* Scrollbar */
.sidebar-nav::-webkit-scrollbar {
  width: 6px;
}

.sidebar-nav::-webkit-scrollbar-track {
  background: rgba(255, 255, 255, 0.05);
}

.sidebar-nav::-webkit-scrollbar-thumb {
  background: rgba(255, 255, 255, 0.2);
  border-radius: 10px;
}

.sidebar-nav::-webkit-scrollbar-thumb:hover {
  background: rgba(255, 255, 255, 0.3);
}
</style>

<!-- Mobile Top Bar -->
<div class="mobile-topbar">
  <div class="mobile-brand">
    <img src="../images/newlogohalili.png" alt="Halili Dental Logo">
    <span class="mobile-brand-text">Halili Dental</span>
  </div>
  <button id="burgerBtn" class="burger-btn">
    <svg class="burger-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
    </svg>
  </button>
</div>

<!-- Sidebar -->
<aside id="sidebar" class="sidebar">
  <!-- Sidebar Header -->
  <div class="sidebar-header">
    <div class="sidebar-logo">
      <img src="/images/newlogohalili.png" alt="Halili Dental Logo">
      <span class="sidebar-title">Halili Dental<br>Clinic PMSS</span>
    </div>
  </div>

  <!-- User Profile Section -->
  <div class="sidebar-user">
    <div class="user-avatar">
      <?php
      // Get first letter of username for avatar
      $username = $_SESSION['username'] ?? 'User';
      echo strtoupper(substr($username, 0, 1));
      ?>
    </div>
    <div class="user-info">
      <p class="user-name"><?= htmlspecialchars($username) ?></p>
      <p class="user-role">Patient</p>
    </div>
  </div>

  <!-- Navigation Menu -->
  <nav class="sidebar-nav">
    <p class="nav-section-title">Main Menu</p>
    <ul class="sidebar-menu">
      <li>
        <a href="dashboard.php" class="sidebar-link <?= $currentPage === 'dashboard.php' ? 'active' : '' ?>">
          <i class="fas fa-home"></i>
          <span>Home</span>
        </a>
      </li>
      <li>
        <a href="treatment-plan.php" class="sidebar-link <?= $currentPage === 'treatment-plan.php' ? 'active' : '' ?>">
          <i class="fas fa-tooth"></i>
          <span>My Treatment Plan</span>
        </a>
      </li>
      <li>
        <a href="appointments.php" class="sidebar-link <?= $currentPage === 'appointments.php' ? 'active' : '' ?>">
          <i class="fas fa-calendar-check"></i>
          <span>My Appointments</span>
        </a>
      </li>
      <li>
        <a href="profile.php" class="sidebar-link <?= $currentPage === 'profile.php' ? 'active' : '' ?>">
          <i class="fas fa-user"></i>
          <span>Profile</span>
        </a>
      </li>
    </ul>
  </nav>

  <!-- Sidebar Footer -->
  <div class="sidebar-footer">
    <button type="button" class="btn-logout" onclick="confirmLogout()">
      <i class="fas fa-sign-out-alt"></i>
      <span>Logout</span>
    </button>
  </div>
</aside>

<!-- Overlay -->
<div id="overlay" class="sidebar-overlay"></div>

<!-- Hidden Logout Form -->
<form id="logoutForm" method="POST" action="logout.php" style="display: none;"></form>

<!-- Scripts -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
  const burgerBtn = document.getElementById('burgerBtn');
  const sidebar = document.getElementById('sidebar');
  const overlay = document.getElementById('overlay');

  function openSidebar() {
    sidebar.classList.add('open');
    overlay.classList.add('show');
    document.body.style.overflow = 'hidden';
  }

  function closeSidebar() {
    sidebar.classList.remove('open');
    overlay.classList.remove('show');
    document.body.style.overflow = '';
  }

  burgerBtn.addEventListener('click', () => {
    if (sidebar.classList.contains('open')) {
      closeSidebar();
    } else {
      openSidebar();
    }
  });

  overlay.addEventListener('click', closeSidebar);

  // Close sidebar when clicking a link on mobile
  if (window.innerWidth < 768) {
    document.querySelectorAll('.sidebar-link').forEach(link => {
      link.addEventListener('click', closeSidebar);
    });
  }

  // SweetAlert logout confirmation
  function confirmLogout() {
    Swal.fire({
      title: 'Are you sure?',
      text: 'You will be logged out from your account.',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#2563eb',
      cancelButtonColor: '#d33',
      confirmButtonText: 'Yes, logout',
      cancelButtonText: 'Cancel',
      background: '#f9fafb',
      customClass: {
        popup: 'rounded-xl shadow-lg',
        confirmButton: 'px-4 py-2 font-semibold',
        cancelButton: 'px-4 py-2 font-semibold'
      }
    }).then((result) => {
      if (result.isConfirmed) {
        Swal.fire({
          title: 'Logging out...',
          text: 'Please wait a moment.',
          icon: 'info',
          showConfirmButton: false,
          timer: 1200,
          didClose: () => {
            document.getElementById('logoutForm').submit();
          }
        });
      }
    });
  }
</script>