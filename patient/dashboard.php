<?php
session_start();
if (!isset($_SESSION['user_email'])) {
    header("Location: log-in.php");
    exit;
}

$userEmail = $_SESSION['user_email'];
$userFullName = $_SESSION['user_name'];

require __DIR__ . '/../vendor/autoload.php';
use MongoDB\Client;

$mongoClient = new Client("mongodb://localhost:27017");
$db = $mongoClient->HaliliDentalClinic;
$usersCollection = $db->users;
$appointmentsCollection = $db->booked_service;

// Case-insensitive fullname search
$user = $usersCollection->findOne([
    'fullname' => new MongoDB\BSON\Regex('^' . preg_quote($userFullName) . '$', 'i')
]);

// Handle selectedTeeth (odontogram)
$selectedTeeth = [];
if ($user && isset($user['selectedTeeth'])) {
    if ($user['selectedTeeth'] instanceof MongoDB\Model\BSONArray) {
        $selectedTeeth = iterator_to_array($user['selectedTeeth']);
    } elseif (is_array($user['selectedTeeth'])) {
        $selectedTeeth = $user['selectedTeeth'];
    }
}

$today = date("Y-m-d");

// Get next appointment
$firstAppointment = $appointmentsCollection->findOne(
    ['email' => $userEmail, 'date' => ['$gte' => $today]],
    ['sort' => ['date' => 1, 'time' => 1]]
);

// Count total upcoming
$totalUpcoming = $appointmentsCollection->countDocuments([
    'email' => $userEmail,
    'date' => ['$gte' => $today]
]);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Dashboard - Halili Dental</title>
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- FullCalendar -->
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/main.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>

<style>
  /* Odontogram Styles */
  #chart-container { 
    background-color: transparent !important; 
  }
  
  .tooth { 
    fill: #ccc !important; 
    stroke: #000 !important; 
    stroke-width: 1; 
    transition: fill 0.2s; 
    cursor: default; 
  }
  
  .tooth.selected { 
    fill: #f87171 !important; 
  }

  /* Responsive Odontogram */
  .odontogram-wrapper {
    min-height: 300px;
    border: 1px solid #ddd;
    border-radius: 1rem;
    overflow: hidden;
    display: flex;
    justify-content: center;
    align-items: center;
    background: white;
  }

  .odontogram-wrapper svg {
    width: 100%;
    height: auto;
    max-height: 400px;
  }

  /* Mobile optimizations */
  @media (max-width: 768px) {
    .odontogram-wrapper {
      min-height: 250px;
    }
    
    .odontogram-wrapper svg {
      transform: scale(0.8);
      max-height: 300px;
    }
    
    /* Hide sidebar on mobile, show hamburger */
    .sidebar {
      transform: translateX(-100%);
      transition: transform 0.3s ease;
    }
    
    .sidebar.active {
      transform: translateX(0);
    }
    
    .main-content {
      margin-left: 0 !important;
    }
  }

  /* Tablet optimizations */
  @media (min-width: 769px) and (max-width: 1024px) {
    .odontogram-wrapper svg {
      transform: scale(0.9);
      max-height: 350px;
    }
    
    .main-content {
      margin-left: 16rem !important;
    }
  }

  /* Desktop */
  @media (min-width: 1025px) {
    .main-content {
      margin-left: 16rem;
    }
  }

  /* Calendar responsive adjustments */
  #patientCalendar { 
    min-height: 280px; 
  }
  
  @media (max-width: 768px) {
    #patientCalendar {
      min-height: 250px;
    }
    
    .fc-toolbar {
      flex-direction: column;
      gap: 0.5rem;
    }
    
    .fc-toolbar-chunk {
      display: flex;
      justify-content: center;
    }
  }

  .fc .fc-daygrid-event .fc-event-title { 
    white-space: nowrap; 
    overflow: hidden; 
    text-overflow: ellipsis; 
  }

  /* Profile card responsive adjustments */
  @media (max-width: 640px) {
    .profile-content {
      flex-direction: column;
      text-align: center;
    }
    
    .profile-details {
      grid-template-columns: 1fr !important;
      text-align: left;
    }
  }

  /* Card responsive behavior */
  .dashboard-card {
    background: white;
    border-radius: 1rem;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    padding: 1.5rem;
  }

  @media (max-width: 768px) {
    .dashboard-card {
      padding: 1rem;
      margin-bottom: 1rem;
    }
  }

  /* Mobile menu button */
  .mobile-menu-btn {
    display: none;
    position: fixed;
    top: 1rem;
    left: 1rem;
    z-index: 1000;
    background: #3b82f6;
    color: white;
    border: none;
    border-radius: 0.5rem;
    padding: 0.75rem;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
  }

  @media (max-width: 768px) {
    .mobile-menu-btn {
      display: block;
    }
  }

  /* Overlay for mobile menu */
  .sidebar-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    z-index: 999;
  }

  @media (max-width: 768px) {
    .sidebar-overlay.active {
      display: block;
    }
  }
</style>
</head>
<body class="bg-gray-100 min-h-screen">

<!-- Mobile Menu Button -->
<button class="mobile-menu-btn" id="mobileMenuBtn">
  <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
  </svg>
</button>

<!-- Sidebar Overlay -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<?php include 'sidebar.php'; ?>

<main class="main-content flex-1 p-4 sm:p-6">
  <div class="max-w-7xl mx-auto">
    <!-- Mobile: Stack vertically, Desktop: Two columns -->
    <div class="flex flex-col xl:flex-row gap-6">

      <!-- LEFT COLUMN -->
      <div class="flex-1 space-y-6">

        <!-- Profile Section -->
        <div class="dashboard-card">
          <div class="profile-content flex flex-col md:flex-row gap-6 items-start">
            <!-- Profile Image -->
            <div class="flex-shrink-0">
              <img src="<?= !empty($user['profile_image']) ? htmlspecialchars($user['profile_image']) : '/images/default-avatar.png' ?>"
                   alt="Profile Picture"
                   class="w-20 h-20 sm:w-24 sm:h-24 rounded-full object-cover border border-gray-300 shadow">
            </div>
            
            <!-- Profile Details -->
            <div class="flex-1 w-full">
              <div class="profile-details grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <!-- Left Column -->
                <div class="space-y-3">
                  <p class="text-sm sm:text-base"><span class="font-medium text-gray-700">Name:</span> <span class="text-gray-900"><?= htmlspecialchars($user['fullname'] ?? 'N/A') ?></span></p>
                  <p class="text-sm sm:text-base"><span class="font-medium text-gray-700">Age:</span> <span class="text-gray-900"><?= htmlspecialchars($user['age'] ?? 'N/A') ?></span></p>
                  <p class="text-sm sm:text-base"><span class="font-medium text-gray-700">Sex:</span> <span class="text-gray-900"><?= htmlspecialchars($user['gender'] ?? 'N/A') ?></span></p>
                </div>

                <!-- Right Column -->
                <div class="space-y-3">
                  <p class="text-sm sm:text-base"><span class="font-medium text-gray-700">Address:</span> <span class="text-gray-900"><?= htmlspecialchars($user['address'] ?? 'N/A') ?></span></p>
                  <p class="text-sm sm:text-base"><span class="font-medium text-gray-700">Email:</span> <span class="text-gray-900 break-all"><?= htmlspecialchars($user['email'] ?? 'N/A') ?></span></p>
                </div>
              </div>
              
              <!-- Edit Button -->
              <div class="flex justify-start">
                <button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors duration-200" 
                        data-bs-toggle="modal" 
                        data-bs-target="#editProfileModal">
                  Edit Profile
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Odontogram Section -->
        <div class="dashboard-card">
          <h3 class="text-lg font-semibold mb-4 text-gray-800">Dental Chart</h3>
          <div id="chart-container" class="odontogram-wrapper">
            <p class="text-center text-gray-500">Loading your odontogram...</p>
          </div>
          <div id="info" class="text-center text-gray-700 font-medium mt-2"></div>
        </div>

      </div>

      <!-- RIGHT COLUMN -->
      <div class="xl:w-96 space-y-6">

        <!-- Upcoming Appointment Card -->
        <div class="dashboard-card">
          <h3 class="text-lg font-semibold mb-4 text-gray-800">Upcoming Appointment</h3>
          <?php if ($firstAppointment): ?>
            <div class="p-4 mb-4 rounded-lg border-l-4 border-blue-500 bg-blue-50">
              <h4 class="font-medium text-gray-900 mb-2"><?= htmlspecialchars($firstAppointment['serviceName']); ?></h4>
              <div class="space-y-1 text-sm text-gray-600">
                <p class="flex items-center gap-2">
                  <span>📅</span>
                  <span><?= htmlspecialchars($firstAppointment['date']); ?> at <?= htmlspecialchars($firstAppointment['time']); ?></span>
                </p>
                <p class="flex items-center gap-2">
                  <span>📋</span>
                  <span>Status: <span class="font-medium"><?= htmlspecialchars($firstAppointment['status']); ?></span></span>
                </p>
              </div>
            </div>
            <?php if ($totalUpcoming > 1): ?>
              <a href="appointments.php" 
                 class="inline-flex items-center text-blue-600 hover:text-blue-800 text-sm font-medium transition-colors duration-200">
                View More Appointments
                <svg class="ml-1 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
              </a>
            <?php endif; ?>
          <?php else: ?>
            <div class="text-center py-8">
              <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-gray-100 flex items-center justify-center">
                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a4 4 0 118 0v4m-4 12H8m4 0h4m-4 0V9"></path>
                </svg>
              </div>
              <p class="text-gray-500 italic">No upcoming appointments.</p>
            </div>
          <?php endif; ?>
        </div>

        <!-- Calendar Card -->
        <div class="dashboard-card">
          <h3 class="text-lg font-semibold mb-4 text-gray-800">Appointments Calendar</h3>
          <div id="patientCalendar"></div>
        </div>

      </div>

    </div>
  </div>
</main>

<!-- Appointment Details Modal -->
<div class="modal fade" id="appointmentModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title">Appointment Details</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="row g-3">
          <div class="col-md-6">
            <p><strong>Service Name:</strong></p>
            <p class="text-muted" id="modalServiceName"></p>
          </div>
          <div class="col-md-6">
            <p><strong>Full Name:</strong></p>
            <p class="text-muted" id="modalFullName"></p>
          </div>
          <div class="col-md-6">
            <p><strong>Email:</strong></p>
            <p class="text-muted" id="modalEmail"></p>
          </div>
          <div class="col-md-6">
            <p><strong>Phone:</strong></p>
            <p class="text-muted" id="modalPhone"></p>
          </div>
          <div class="col-md-6">
            <p><strong>Date & Time:</strong></p>
            <p class="text-muted" id="modalDateTime"></p>
          </div>
          <div class="col-md-6">
            <p><strong>Status:</strong></p>
            <p class="text-muted" id="modalStatus"></p>
          </div>
          <div class="col-12">
            <p><strong>Description:</strong></p>
            <p class="text-muted" id="modalDescription"></p>
          </div>
          <div class="col-12">
            <p><strong>ID:</strong></p>
            <p class="text-muted small" id="modalId"></p>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- Edit Profile Modal -->
<div class="modal fade" id="editProfileModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
    <form method="POST" action="update_profile.php" enctype="multipart/form-data" class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title">Edit Personal Information</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="row g-3">
          <input type="hidden" name="id" value="<?= $user->_id ?? '' ?>">

          <div class="col-md-6">
            <label class="form-label">Full Name</label>
            <input type="text" class="form-control" name="fullname" value="<?= htmlspecialchars($user->fullname ?? '') ?>" required>
          </div>
          <div class="col-md-6">
            <label class="form-label">Date of Birth</label>
            <input type="date" class="form-control" id="dobInput" name="dob" value="<?= htmlspecialchars($user->dob ?? '') ?>">
          </div>
          <div class="col-md-6">
            <label class="form-label">Age</label>
            <input type="number" class="form-control" id="ageInput" name="age" value="<?= htmlspecialchars($user->age ?? '') ?>" readonly>
          </div>
          <div class="col-md-6">
            <label class="form-label">Gender</label>
            <select class="form-control" name="gender">
              <option value="">Select</option>
              <option value="Male" <?= ($user->gender ?? '') == "Male" ? "selected" : "" ?>>Male</option>
              <option value="Female" <?= ($user->gender ?? '') == "Female" ? "selected" : "" ?>>Female</option>
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label">Civil Status</label>
            <select class="form-control" name="civil_status">
              <option value="">Select</option>
              <option value="Single" <?= ($user->civil_status ?? '') == "Single" ? "selected" : "" ?>>Single</option>
              <option value="Married" <?= ($user->civil_status ?? '') == "Married" ? "selected" : "" ?>>Married</option>
              <option value="Widowed" <?= ($user->civil_status ?? '') == "Widowed" ? "selected" : "" ?>>Widowed</option>
              <option value="Divorced" <?= ($user->civil_status ?? '') == "Divorced" ? "selected" : "" ?>>Divorced</option>
            </select>
          </div>
          <div class="col-12">
            <label class="form-label">Address</label>
            <input type="text" class="form-control" name="address" value="<?= htmlspecialchars($user->address ?? '') ?>">
          </div>
          <div class="col-md-6">
            <label class="form-label">Phone Number</label>
            <input type="text" class="form-control" name="phone" value="<?= htmlspecialchars($user->phone ?? '') ?>">
          </div>
          <div class="col-md-6">
            <label class="form-label">Email Address</label>
            <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($user->email ?? '') ?>" required>
          </div>
          <div class="col-md-6">
            <label class="form-label">Occupation</label>
            <input type="text" class="form-control" name="occupation" value="<?= htmlspecialchars($user->occupation ?? '') ?>">
          </div>
          <div class="col-md-6">
            <label class="form-label">Nationality</label>
            <input type="text" class="form-control" name="nationality" value="<?= htmlspecialchars($user->nationality ?? '') ?>">
          </div>
          <div class="col-12">
            <label class="form-label">Profile Image</label>
            <input type="file" class="form-control" name="profile_image" accept="image/*">
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-success">Save Changes</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
      </div>
    </form>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
  // Mobile menu functionality
  const mobileMenuBtn = document.getElementById('mobileMenuBtn');
  const sidebar = document.querySelector('.sidebar');
  const sidebarOverlay = document.getElementById('sidebarOverlay');

  if (mobileMenuBtn && sidebar && sidebarOverlay) {
    mobileMenuBtn.addEventListener('click', function() {
      sidebar.classList.add('active');
      sidebarOverlay.classList.add('active');
    });

    sidebarOverlay.addEventListener('click', function() {
      sidebar.classList.remove('active');
      sidebarOverlay.classList.remove('active');
    });
  }

  // FullCalendar with responsive options
  const calendarEl = document.getElementById('patientCalendar');
  if (!calendarEl) return;

  const isMobile = window.innerWidth < 768;
  
  const calendar = new FullCalendar.Calendar(calendarEl, {
    initialView: isMobile ? 'dayGridMonth' : 'dayGridMonth',
    height: isMobile ? 280 : 320,
    headerToolbar: {
      left: 'prev,next',
      center: 'title',
      right: isMobile ? 'today' : 'today'
    },
    dayMaxEvents: isMobile ? 2 : 3,
    eventDisplay: 'block',

    // Load events from API
    events: async function(fetchInfo, successCallback, failureCallback) {
      try {
        const res = await fetch('/patient/appointments_api.php');
        if (!res.ok) throw new Error('Failed to load appointments');
        const data = await res.json();
        successCallback(data.map(appt => ({
          id: appt._id,
          title: (appt.serviceName || 'Appointment') + (isMobile ? '' : " — " + (appt.time || '')),
          start: (appt.date || '') + (appt.time ? 'T' + appt.time : ''),
          color: appt.status === "accepted" ? "#22c55e" :
                 appt.status === "declined" ? "#ef4444" : "#f59e0b",
          textColor: "#ffffff",
          extendedProps: { ...appt }
        })));
      } catch (err) { 
        console.error(err); 
        failureCallback(err); 
      }
    },

    // Click an existing event
    eventClick: function(info) {
      const p = info.event.extendedProps;
      document.getElementById('modalServiceName').textContent = p.serviceName || 'N/A';
      document.getElementById('modalFullName').textContent = p.fullname || 'N/A';
      document.getElementById('modalEmail').textContent = p.email || 'N/A';
      document.getElementById('modalPhone').textContent = p.phone || 'N/A';
      document.getElementById('modalDateTime').textContent = `${p.date} ${p.time || ''}`;
      document.getElementById('modalStatus').textContent = p.status || 'N/A';
      document.getElementById('modalDescription').textContent = p.description || 'N/A';
      document.getElementById('modalId').textContent = p._id || 'N/A';

      const modalEl = document.getElementById('appointmentModal');
      const modal = new bootstrap.Modal(modalEl);
      modal.show();
    },

    // Click a date cell
    dateClick: async function(info) {
      const clickedDate = info.dateStr;
      try {
        const res = await fetch(`/patient/appointments_api.php?date=${clickedDate}`);
        if (!res.ok) throw new Error('Failed to fetch appointments');
        const appointments = await res.json();
        if (appointments.length === 0) {
          Swal.fire({ 
            icon: 'info', 
            title: 'No Appointments', 
            text: `You have no appointments on ${clickedDate}`,
            confirmButtonColor: '#3b82f6'
          });
          return;
        }
        // Show first appointment
        const appt = appointments[0];
        document.getElementById('modalServiceName').textContent = appt.serviceName || 'N/A';
        document.getElementById('modalFullName').textContent = appt.fullname || 'N/A';
        document.getElementById('modalEmail').textContent = appt.email || 'N/A';
        document.getElementById('modalPhone').textContent = appt.phone || 'N/A';
        document.getElementById('modalDateTime').textContent = `${appt.date} ${appt.time || ''}`;
        document.getElementById('modalStatus').textContent = appt.status || 'N/A';
        document.getElementById('modalDescription').textContent = appt.description || 'N/A';
        document.getElementById('modalId').textContent = appt._id || 'N/A';
        const modalEl = document.getElementById('appointmentModal');
        const modal = new bootstrap.Modal(modalEl);
        modal.show();
      } catch(err) { 
        console.error(err); 
        Swal.fire({
          icon:'error', 
          title:'Error', 
          text:'Could not load appointments',
          confirmButtonColor: '#3b82f6'
        }); 
      }
    }
  });

  calendar.render();

  // Responsive calendar adjustment on window resize
  window.addEventListener('resize', function() {
    const newIsMobile = window.innerWidth < 768;
    if (newIsMobile !== isMobile) {
      calendar.setOption('height', newIsMobile ? 280 : 320);
      calendar.setOption('dayMaxEvents', newIsMobile ? 2 : 3);
    }
  });

  // Load Odontogram SVG
  let selectedTeeth = <?= json_encode($selectedTeeth) ?>;
  async function loadSVG() {
    const container = document.getElementById("chart-container");
    container.innerHTML = "<p class='text-center text-gray-500'>Loading SVG...</p>";
    try {
      const res = await fetch("../dental_arches.svg");
      let svgText = await res.text();
      svgText = svgText.replace(/fill="[^"]*"/g, "");
      container.innerHTML = svgText;
      const svg = container.querySelector("svg");
      if (!svg) throw new Error("SVG not found");
      svg.removeAttribute("width"); 
      svg.removeAttribute("height");
      svg.setAttribute("viewBox", "0 0 3027 4736");
      const allPathIds = Array.from(svg.querySelectorAll("path")).map(p => {
        if (!p.id) p.id = "path" + Math.random().toString(36).slice(2, 8);
        p.classList.add("tooth");
        return p.id.trim();
      });
      selectedTeeth = selectedTeeth.filter(id => allPathIds.includes(id.trim()));
      selectedTeeth.forEach(id => {
        const el = document.getElementById(id);
        if (el) el.classList.add("selected");
      });
      document.getElementById("info").textContent = "";
    } catch (err) {
      container.innerHTML = `<p class="text-red-500 text-center">Failed to load SVG.</p>`;
    }
  }
  loadSVG();

  // DOB and Age calculation
  const dobInput = document.getElementById("dobInput");
  const ageInput = document.getElementById("ageInput");

  function calculateAge(dob) {
    if (!dob) return "";
    const birthDate = new Date(dob);
    const today = new Date();
    let age = today.getFullYear() - birthDate.getFullYear();
    const monthDiff = today.getMonth() - birthDate.getMonth();
    if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
      age--;
    }
    return age;
  }

  if (dobInput && ageInput) {
    // Recalculate when DOB changes
    dobInput.addEventListener("input", function() {
      ageInput.value = calculateAge(dobInput.value);
    });

    // Run once on load if DOB already exists
    if (dobInput.value) {
      ageInput.value = calculateAge(dobInput.value);
    }
  }
});
</script>
</body>
</html>