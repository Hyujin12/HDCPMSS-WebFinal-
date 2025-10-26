<?php
session_start();
if (!isset($_SESSION['email'])) {
    header("Location: log-in.php");
    exit;
}

$userEmail = $_SESSION['email'];
$userFullName = $_SESSION['username'];

require __DIR__ . '/../vendor/autoload.php';
use MongoDB\Client;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

$mongoClient = new Client($_ENV['MONGO_URI']);
$db = $mongoClient->HaliliDentalClinic;
$usersCollection = $db->users;
$appointmentsCollection = $db->bookedservices;

$user = $usersCollection->findOne(['email' => $userEmail]);

$today = date("Y-m-d");

$firstAppointment = $appointmentsCollection->findOne(
    ['email' => $userEmail, 'date' => ['$gte' => $today]],
    ['sort' => ['date' => 1, 'time' => 1]]
);

$totalUpcoming = $appointmentsCollection->countDocuments([
    'email' => $userEmail,
    'date' => ['$gte' => $today]
]);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Dashboard - Halili Dental</title>
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>

<style>
body { 
  background-color: #f3f4f6;
  margin: 0;
  padding: 0;
}

.main-content {
  padding: 1.5rem;
  min-height: 100vh;
  margin-top: 70px;
}

@media (min-width: 640px) {
  .main-content {
    padding: 2rem;
  }
}

.dashboard-card {
  background: white;
  border-radius: 1rem;
  box-shadow: 0 4px 6px rgba(0,0,0,0.1);
  padding: 1.5rem;
  margin-bottom: 1.5rem;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.dashboard-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 12px rgba(0,0,0,0.15);
}

.dashboard-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 1.5rem;
  max-width: 1400px;
  margin: 0 auto;
}

@media (min-width: 768px) {
  .dashboard-grid { 
    grid-template-columns: repeat(2, 1fr); 
  }
}

.calendar-card {
  grid-column: span 1;
}

@media (min-width: 768px) {
  .calendar-card {
    grid-column: span 2;
  }
}

#calendar {
  width: 100%;
  height: 450px;
  margin: 0 auto;
}

@media (max-width: 767px) {
  #calendar {
    height: 350px;
  }
}

.profile-img {
  width: 100px;
  height: 100px;
  object-fit: cover;
  border-radius: 50%;
  border: 3px solid #1e40af;
  box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}

@media (max-width: 575px) {
  .profile-img {
    width: 80px;
    height: 80px;
  }
}

.appointment-highlight {
  padding: 1rem;
  background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
  border-left: 4px solid #1e40af;
  border-radius: 0.5rem;
  transition: all 0.2s ease;
}

.appointment-highlight:hover {
  transform: translateX(4px);
  box-shadow: 0 4px 8px rgba(30, 64, 175, 0.1);
}

.section-title {
  font-weight: 700;
  margin-bottom: 1rem;
  color: #1f2937;
  font-size: 1.25rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.section-title::before {
  content: '';
  width: 4px;
  height: 24px;
  background: linear-gradient(180deg, #1e40af 0%, #1e3a8a 100%);
  border-radius: 2px;
}
</style>
</head>
<body>

<?php include 'sidebar.php'; ?>

<main class="main-content">
  <div class="dashboard-grid">

    <!-- Profile Card -->
    <div class="dashboard-card">
      <h3 class="section-title">👤 Profile Overview</h3>
      <div class="d-flex flex-column flex-md-row gap-4 align-items-start">
        <img src="<?= !empty($user['profile_image']) ? htmlspecialchars($user['profile_image']) : '/images/default-avatar.png' ?>" 
             alt="Profile Picture" class="profile-img">
        <div class="flex-grow-1">
          <div class="row g-3">
            <div class="col-md-6">
              <p class="mb-2"><strong>Name:</strong> <?= htmlspecialchars($user['username'] ?? 'N/A') ?></p>
              <p class="mb-2"><strong>Age:</strong> <?= htmlspecialchars($user['age'] ?? 'N/A') ?></p>
              <p class="mb-2"><strong>Sex:</strong> <?= htmlspecialchars($user['gender'] ?? 'N/A') ?></p>
            </div>
            <div class="col-md-6">
              <p class="mb-2"><strong>Address:</strong> <?= htmlspecialchars($user['address'] ?? 'N/A') ?></p>
              <p class="mb-2"><strong>Email:</strong> <?= htmlspecialchars($user['email'] ?? 'N/A') ?></p>
            </div>
          </div>
          <button class="btn btn-primary btn-sm mt-3" data-bs-toggle="modal" data-bs-target="#editProfileModal">
            <svg style="width: 16px; height: 16px; display: inline-block; margin-right: 4px;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
            </svg>
            Edit Profile
          </button>
        </div>
      </div>
    </div>

    <!-- Upcoming Appointment Card -->
    <div class="dashboard-card">
      <h3 class="section-title">📅 Upcoming Appointment</h3>
      <?php if ($firstAppointment): ?>
        <div class="appointment-highlight">
          <h5 class="mb-2 fw-bold text-primary"><?= htmlspecialchars($firstAppointment['serviceName']); ?></h5>
          <p class="mb-1">
            <svg style="width: 16px; height: 16px; display: inline-block;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            <?= htmlspecialchars($firstAppointment['date']); ?> at <?= htmlspecialchars($firstAppointment['time']); ?>
          </p>
          <p class="mb-0">
            <span class="badge bg-warning text-dark">
              <?= htmlspecialchars($firstAppointment['status'] ?? 'Pending'); ?>
            </span>
          </p>
        </div>
        <?php if ($totalUpcoming > 1): ?>
          <a href="appointments.php" class="d-block mt-3 text-primary fw-semibold text-decoration-none">
            View All Appointments (<?= $totalUpcoming ?>) →
          </a>
        <?php endif; ?>
      <?php else: ?>
        <div class="text-center py-4">
          <svg style="width: 64px; height: 64px; margin: 0 auto; color: #d1d5db;" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
          </svg>
          <p class="text-muted mt-3 mb-0">No upcoming appointments.</p>
        </div>
      <?php endif; ?>
    </div>

    <!-- Appointment Calendar -->
    <div class="dashboard-card calendar-card">
      <h3 class="section-title">📆 Appointment Calendar</h3>
      <div id="calendar"></div>
    </div>

  </div>
</main>

<!-- Appointment Detail Modal -->
<div class="modal fade" id="appointmentModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title">📋 Appointment Details</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p><strong>Service:</strong> <span id="modalService"></span></p>
        <p><strong>Date:</strong> <span id="modalDate"></span></p>
        <p><strong>Time:</strong> <span id="modalTime"></span></p>
        <p><strong>Status:</strong> <span id="modalStatus"></span></p>
        <p class="mb-0"><strong>Notes:</strong> <span id="modalNotes"></span></p>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- Edit Profile Modal -->
<div class="modal fade" id="editProfileModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <form method="POST" action="update_profile.php" enctype="multipart/form-data" class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title">✏️ Edit Profile</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="row g-3">
          <input type="hidden" name="id" value="<?= (string)$user->_id ?>">
          <div class="col-md-6">
            <label class="form-label fw-semibold">Full Name</label>
            <input type="text" name="fullname" class="form-control" value="<?= htmlspecialchars($user->username ?? '') ?>" required>
          </div>
          <div class="col-md-6">
            <label class="form-label fw-semibold">Date of Birth</label>
            <input type="date" name="dob" class="form-control" value="<?= htmlspecialchars($user->birthday ?? '') ?>">
          </div>
          <div class="col-md-6">
            <label class="form-label fw-semibold">Gender</label>
            <select class="form-control" name="gender">
              <option value="">Select</option>
              <option value="Male" <?= ($user->gender ?? '') == "Male" ? "selected" : "" ?>>Male</option>
              <option value="Female" <?= ($user->gender ?? '') == "Female" ? "selected" : "" ?>>Female</option>
            </select>
          </div>
          <div class="col-12">
            <label class="form-label fw-semibold">Address</label>
            <input type="text" name="address" class="form-control" value="<?= htmlspecialchars($user->address ?? '') ?>">
          </div>
          <div class="col-md-6">
            <label class="form-label fw-semibold">Phone</label>
            <input type="text" name="contactNumber" class="form-control" value="<?= htmlspecialchars($user->contactNumber ?? '') ?>">
          </div>
          <div class="col-md-6">
            <label class="form-label fw-semibold">Email</label>
            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user->email ?? '') ?>" required>
          </div>
          <div class="col-md-6">
            <label class="form-label fw-semibold">Civil Status</label>
            <input type="text" name="civil_status" class="form-control" value="<?= htmlspecialchars($user->status ?? '') ?>">
          </div>
          <div class="col-md-6">
            <label class="form-label fw-semibold">Occupation</label>
            <input type="text" name="occupation" class="form-control" value="<?= htmlspecialchars($user->occupation ?? '') ?>">
          </div>
          <div class="col-md-6">
            <label class="form-label fw-semibold">Nationality</label>
            <input type="text" name="nationality" class="form-control" value="<?= htmlspecialchars($user->nationality ?? '') ?>">
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-success">💾 Save Changes</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
      </div>
    </form>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
  var calendarEl = document.getElementById('calendar');
  var appointmentModal = new bootstrap.Modal(document.getElementById('appointmentModal'));

  var calendar = new FullCalendar.Calendar(calendarEl, {
    initialView: 'dayGridMonth',
    themeSystem: 'bootstrap5',
    headerToolbar: {
      left: 'prev,next today',
      center: 'title',
      right: 'dayGridMonth,timeGridWeek'
    },
    events: 'get_user_appointments.php',
    eventColor: '#1e40af',
    eventTextColor: '#fff',
    eventClick: function(info) {
      const data = info.event.extendedProps;

      document.getElementById('modalService').innerText = info.event.title;
      document.getElementById('modalDate').innerText = info.event.start.toISOString().slice(0,10);
      document.getElementById('modalTime').innerText = data.time || 'N/A';
      document.getElementById('modalStatus').innerText = data.status || 'Pending';
      document.getElementById('modalNotes').innerText = data.notes || 'No additional notes';

      appointmentModal.show();
    },
    eventDidMount: function(info) {
      // Add tooltip on hover
      info.el.setAttribute('title', info.event.title);
    }
  });

  calendar.render();

  // Make calendar responsive
  window.addEventListener('resize', function() {
    calendar.updateSize();
  });
});
</script>

</body>
</html>