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
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
:root {
  --primary-color: #0891b2;
  --primary-dark: #0e7490;
  --secondary-color: #06b6d4;
  --accent-color: #22d3ee;
  --success-color: #10b981;
  --warning-color: #f59e0b;
  --danger-color: #ef4444;
  --text-dark: #1e293b;
  --text-muted: #64748b;
  --bg-light: #f8fafc;
  --card-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
  --card-shadow-hover: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
}

body { 
  background: linear-gradient(135deg, #f8fafc 0%, #e0f2fe 100%);
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  min-height: 100vh;
}

.dashboard-header {
  background: white;
  padding: 1.5rem;
  margin-bottom: 2rem;
  border-radius: 1rem;
  box-shadow: var(--card-shadow);
}

.dashboard-card {
  background: white;
  border-radius: 1rem;
  box-shadow: var(--card-shadow);
  padding: 1.5rem;
  margin-bottom: 1.5rem;
  transition: all 0.3s ease;
  border: 1px solid rgba(8, 145, 178, 0.1);
  height: 100%;
}

.dashboard-card:hover {
  box-shadow: var(--card-shadow-hover);
  transform: translateY(-2px);
}

.dashboard-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 1.5rem;
}

@media (min-width: 768px) {
  .dashboard-grid { 
    grid-template-columns: repeat(2, 1fr); 
  }
}

@media (min-width: 1024px) {
  .dashboard-grid { 
    grid-template-columns: repeat(3, 1fr); 
  }
}

.profile-card {
  grid-column: 1 / -1;
}

.calendar-card {
  grid-column: 1 / -1;
}

@media (min-width: 1024px) {
  .profile-card {
    grid-column: span 2;
  }
}

.stat-card {
  background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
  color: white;
  border: none;
  padding: 2rem 1.5rem;
}

.stat-card .icon {
  font-size: 2.5rem;
  opacity: 0.9;
}

.stat-card .number {
  font-size: 2rem;
  font-weight: 700;
  margin: 0.5rem 0;
}

.stat-card .label {
  font-size: 0.95rem;
  opacity: 0.9;
  font-weight: 500;
}

.appointment-card {
  background: linear-gradient(135deg, #ffffff 0%, #f0f9ff 100%);
  border-left: 4px solid var(--primary-color);
  padding: 1.5rem;
  border-radius: 0.75rem;
  margin-bottom: 1rem;
  transition: all 0.3s ease;
}

.appointment-card:hover {
  transform: translateX(4px);
  box-shadow: var(--card-shadow);
}

.appointment-card .service-name {
  color: var(--text-dark);
  font-size: 1.1rem;
  font-weight: 600;
  margin-bottom: 0.75rem;
}

.appointment-card .appointment-info {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  color: var(--text-muted);
  font-size: 0.95rem;
  margin-bottom: 0.5rem;
}

.appointment-card .appointment-info i {
  color: var(--primary-color);
  width: 20px;
}

.status-badge {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.375rem 0.875rem;
  border-radius: 9999px;
  font-size: 0.875rem;
  font-weight: 600;
}

.status-pending {
  background-color: #fef3c7;
  color: #92400e;
}

.status-confirmed {
  background-color: #d1fae5;
  color: #065f46;
}

.status-completed {
  background-color: #dbeafe;
  color: #1e40af;
}

.profile-image-container {
  position: relative;
  width: 120px;
  height: 120px;
  margin: 0 auto;
}

.profile-image {
  width: 120px;
  height: 120px;
  border-radius: 50%;
  object-fit: cover;
  border: 4px solid var(--primary-color);
  box-shadow: var(--card-shadow);
}

.info-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 1rem;
  margin-top: 1.5rem;
}

@media (min-width: 640px) {
  .info-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

.info-item {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.info-label {
  color: var(--text-muted);
  font-size: 0.875rem;
  font-weight: 500;
  text-transform: uppercase;
  letter-spacing: 0.025em;
}

.info-value {
  color: var(--text-dark);
  font-size: 1rem;
  font-weight: 600;
}

#calendar {
  width: 100%;
  min-height: 450px;
  margin: 0 auto;
}

.fc .fc-button-primary {
  background-color: var(--primary-color) !important;
  border-color: var(--primary-color) !important;
}

.fc .fc-button-primary:hover {
  background-color: var(--primary-dark) !important;
}

.fc .fc-event {
  border-radius: 0.375rem;
  border: none;
  padding: 2px 4px;
}

.btn-primary {
  background-color: var(--primary-color);
  border-color: var(--primary-color);
  transition: all 0.3s ease;
}

.btn-primary:hover {
  background-color: var(--primary-dark);
  border-color: var(--primary-dark);
  transform: translateY(-1px);
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.btn-success {
  background-color: var(--success-color);
  border-color: var(--success-color);
}

.btn-outline-primary {
  color: var(--primary-color);
  border-color: var(--primary-color);
}

.btn-outline-primary:hover {
  background-color: var(--primary-color);
  border-color: var(--primary-color);
  color: white;
}

.card-title {
  color: var(--text-dark);
  font-size: 1.25rem;
  font-weight: 700;
  margin-bottom: 1.5rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.card-title i {
  color: var(--primary-color);
}

.empty-state {
  text-align: center;
  padding: 3rem 1rem;
  color: var(--text-muted);
}

.empty-state i {
  font-size: 3rem;
  color: var(--primary-color);
  opacity: 0.3;
  margin-bottom: 1rem;
}

.welcome-text {
  font-size: 1.75rem;
  font-weight: 700;
  color: var(--text-dark);
  margin-bottom: 0.5rem;
}

.welcome-subtext {
  color: var(--text-muted);
  font-size: 1rem;
}

.modal-header {
  background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
  border-bottom: none;
}

.form-label {
  color: var(--text-dark);
  font-weight: 600;
  margin-bottom: 0.5rem;
  font-size: 0.875rem;
}

.form-control, .form-select {
  border: 2px solid #e2e8f0;
  border-radius: 0.5rem;
  padding: 0.625rem 0.875rem;
  transition: all 0.3s ease;
}

.form-control:focus, .form-select:focus {
  border-color: var(--primary-color);
  box-shadow: 0 0 0 3px rgba(8, 145, 178, 0.1);
}

@media (max-width: 767px) {
  .dashboard-header {
    padding: 1rem;
  }
  
  .welcome-text {
    font-size: 1.5rem;
  }
  
  .profile-image-container, .profile-image {
    width: 100px;
    height: 100px;
  }
  
  .stat-card {
    padding: 1.5rem 1rem;
  }
  
  .stat-card .icon {
    font-size: 2rem;
  }
  
  .stat-card .number {
    font-size: 1.5rem;
  }
}

.quick-action-btn {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.625rem 1.25rem;
  font-weight: 600;
  transition: all 0.3s ease;
}

.quick-action-btn:hover {
  transform: translateY(-2px);
}
</style>
</head>
<body>

<?php include 'sidebar.php'; ?>

<main class="main-content p-3 p-sm-4 p-lg-6">
  <div class="max-w-7xl mx-auto">
    
    <!-- Welcome Header -->
    <div class="dashboard-header">
      <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-3">
        <div>
          <h1 class="welcome-text mb-1">Welcome back, <?= htmlspecialchars(explode(' ', $userFullName)[0]) ?>! 👋</h1>
          <p class="welcome-subtext mb-0">Here's what's happening with your dental care today</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
          <a href="book-appointment.php" class="btn btn-primary quick-action-btn">
            <i class="fas fa-calendar-plus"></i> Book Appointment
          </a>
        </div>
      </div>
    </div>

    <div class="dashboard-grid">

      <!-- Profile Card -->
      <div class="dashboard-card profile-card">
        <h3 class="card-title">
          <i class="fas fa-user-circle"></i> Patient Profile
        </h3>
        <div class="row g-4">
          <div class="col-12 col-sm-auto text-center">
            <div class="profile-image-container">
              <img src="<?= !empty($user['profile_image']) ? htmlspecialchars($user['profile_image']) : '/images/default-avatar.png' ?>" 
                   alt="Profile Picture" class="profile-image">
            </div>
          </div>
          <div class="col">
            <div class="info-grid">
              <div class="info-item">
                <span class="info-label">Full Name</span>
                <span class="info-value"><?= htmlspecialchars($user['username'] ?? 'N/A') ?></span>
              </div>
              <div class="info-item">
                <span class="info-label">Age</span>
                <span class="info-value"><?= htmlspecialchars($user['age'] ?? 'N/A') ?> years</span>
              </div>
              <div class="info-item">
                <span class="info-label">Gender</span>
                <span class="info-value"><?= htmlspecialchars($user['gender'] ?? 'N/A') ?></span>
              </div>
              <div class="info-item">
                <span class="info-label">Civil Status</span>
                <span class="info-value"><?= htmlspecialchars($user['status'] ?? 'N/A') ?></span>
              </div>
              <div class="info-item">
                <span class="info-label">Email Address</span>
                <span class="info-value"><?= htmlspecialchars($user['email'] ?? 'N/A') ?></span>
              </div>
              <div class="info-item">
                <span class="info-label">Contact Number</span>
                <span class="info-value"><?= htmlspecialchars($user['contactNumber'] ?? 'N/A') ?></span>
              </div>
              <div class="info-item">
                <span class="info-label">Address</span>
                <span class="info-value"><?= htmlspecialchars($user['address'] ?? 'N/A') ?></span>
              </div>
              <div class="info-item">
                <span class="info-label">Occupation</span>
                <span class="info-value"><?= htmlspecialchars($user['occupation'] ?? 'N/A') ?></span>
              </div>
            </div>
            <div class="mt-4">
              <button class="btn btn-outline-primary quick-action-btn" data-bs-toggle="modal" data-bs-target="#editProfileModal">
                <i class="fas fa-edit"></i> Edit Profile
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Stats Card - Total Appointments -->
      <div class="dashboard-card stat-card">
        <div class="text-center">
          <div class="icon">
            <i class="fas fa-calendar-check"></i>
          </div>
          <div class="number"><?= $totalUpcoming ?></div>
          <div class="label">Upcoming Appointments</div>
        </div>
      </div>

      <!-- Next Appointment Card -->
      <div class="dashboard-card">
        <h3 class="card-title">
          <i class="fas fa-clock"></i> Next Appointment
        </h3>
        <?php if ($firstAppointment): ?>
          <div class="appointment-card">
            <div class="service-name"><?= htmlspecialchars($firstAppointment['serviceName']); ?></div>
            <div class="appointment-info">
              <i class="fas fa-calendar-day"></i>
              <span><?= date('F d, Y', strtotime($firstAppointment['date'])); ?></span>
            </div>
            <div class="appointment-info">
              <i class="fas fa-clock"></i>
              <span><?= htmlspecialchars($firstAppointment['time']); ?></span>
            </div>
            <div class="mt-3">
              <span class="status-badge status-<?= strtolower($firstAppointment['status'] ?? 'pending'); ?>">
                <i class="fas fa-circle" style="font-size: 0.5rem;"></i>
                <?= htmlspecialchars($firstAppointment['status'] ?? 'Pending'); ?>
              </span>
            </div>
          </div>
          <?php if ($totalUpcoming > 1): ?>
            <a href="appointments.php" class="btn btn-outline-primary btn-sm w-100 mt-3">
              View All Appointments <i class="fas fa-arrow-right ms-1"></i>
            </a>
          <?php endif; ?>
        <?php else: ?>
          <div class="empty-state">
            <i class="fas fa-calendar-times"></i>
            <p class="mb-0 mt-2">No upcoming appointments scheduled</p>
            <a href="book-appointment.php" class="btn btn-primary btn-sm mt-3">
              <i class="fas fa-plus"></i> Schedule Now
            </a>
          </div>
        <?php endif; ?>
      </div>

      <!-- Appointment Calendar -->
      <div class="dashboard-card calendar-card">
        <h3 class="card-title">
          <i class="fas fa-calendar-alt"></i> Appointment Calendar
        </h3>
        <div id="calendar"></div>
      </div>

    </div>
  </div>
</main>

<!-- Appointment Detail Modal -->
<div class="modal fade" id="appointmentModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header text-white">
        <h5 class="modal-title"><i class="fas fa-info-circle me-2"></i>Appointment Details</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-4">
        <div class="info-item mb-3">
          <span class="info-label"><i class="fas fa-tooth me-2"></i>Service</span>
          <span class="info-value" id="modalService"></span>
        </div>
        <div class="info-item mb-3">
          <span class="info-label"><i class="fas fa-calendar-day me-2"></i>Date</span>
          <span class="info-value" id="modalDate"></span>
        </div>
        <div class="info-item mb-3">
          <span class="info-label"><i class="fas fa-clock me-2"></i>Time</span>
          <span class="info-value" id="modalTime"></span>
        </div>
        <div class="info-item mb-3">
          <span class="info-label"><i class="fas fa-info-circle me-2"></i>Status</span>
          <span class="info-value" id="modalStatus"></span>
        </div>
        <div class="info-item">
          <span class="info-label"><i class="fas fa-sticky-note me-2"></i>Notes</span>
          <span class="info-value" id="modalNotes"></span>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- Edit Profile Modal -->
<div class="modal fade" id="editProfileModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
    <form method="POST" action="update_profile.php" enctype="multipart/form-data" class="modal-content">
      <div class="modal-header text-white">
        <h5 class="modal-title"><i class="fas fa-user-edit me-2"></i>Edit Profile</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-4">
        <div class="row g-3">
          <input type="hidden" name="id" value="<?= (string)$user->_id ?>">
          
          <div class="col-md-6">
            <label class="form-label"><i class="fas fa-user me-2"></i>Full Name</label>
            <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($user->username ?? '') ?>" required>
          </div>
          
          <div class="col-md-6">
            <label class="form-label"><i class="fas fa-birthday-cake me-2"></i>Date of Birth</label>
            <input type="date" name="birthday" class="form-control" value="<?= htmlspecialchars($user->birthday ?? '') ?>" required>
          </div>
          
          <div class="col-md-6">
            <label class="form-label"><i class="fas fa-venus-mars me-2"></i>Gender</label>
            <select class="form-select" name="gender" required>
              <option value="">Select Gender</option>
              <option value="Male" <?= ($user->gender ?? '') == "Male" ? "selected" : "" ?>>Male</option>
              <option value="Female" <?= ($user->gender ?? '') == "Female" ? "selected" : "" ?>>Female</option>
            </select>
          </div>
          
          <div class="col-md-6">
            <label class="form-label"><i class="fas fa-phone me-2"></i>Contact Number</label>
            <input type="text" name="contactNumber" class="form-control" value="<?= htmlspecialchars($user->contactNumber ?? '') ?>" required>
          </div>
          
          <div class="col-12">
            <label class="form-label"><i class="fas fa-map-marker-alt me-2"></i>Address</label>
            <input type="text" name="address" class="form-control" value="<?= htmlspecialchars($user->address ?? '') ?>" required>
          </div>
          
          <div class="col-md-6">
            <label class="form-label"><i class="fas fa-envelope me-2"></i>Email</label>
            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user->email ?? '') ?>" required readonly>
          </div>
          
          <div class="col-md-6">
            <label class="form-label"><i class="fas fa-heart me-2"></i>Civil Status</label>
            <select name="status" class="form-select" required>
              <option value="">Select Status</option>
              <option value="Single" <?= ($user['status'] ?? '') === 'Single' ? 'selected' : '' ?>>Single</option>
              <option value="Married" <?= ($user['status'] ?? '') === 'Married' ? 'selected' : '' ?>>Married</option>
              <option value="Separated" <?= ($user['status'] ?? '') === 'Separated' ? 'selected' : '' ?>>Separated</option>
              <option value="Widowed" <?= ($user['status'] ?? '') === 'Widowed' ? 'selected' : '' ?>>Widowed</option>
              <option value="Divorced" <?= ($user['status'] ?? '') === 'Divorced' ? 'selected' : '' ?>>Divorced</option>
              <option value="Complicated" <?= ($user['status'] ?? '') === 'Complicated' ? 'selected' : '' ?>>Complicated</option>
            </select>
          </div>

          <div class="col-md-6">
            <label class="form-label"><i class="fas fa-briefcase me-2"></i>Occupation</label>
            <input type="text" name="occupation" class="form-control" value="<?= htmlspecialchars($user->occupation ?? '') ?>" required>
          </div>
          
          <div class="col-md-6">
            <label class="form-label"><i class="fas fa-flag me-2"></i>Nationality</label>
            <select name="nationality" class="form-select" required>
              <option value="">Select Nationality</option>
              <option value="Filipino" <?= (($user['nationality'] ?? '') === 'Filipino') ? 'selected' : '' ?>>Filipino</option>
              <option value="Foreign National" <?= (($user['nationality'] ?? '') === 'Foreign National') ? 'selected' : '' ?>>Foreign National</option>
            </select>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-success">
          <i class="fas fa-save me-2"></i>Save Changes
        </button>
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
    initialView: window.innerWidth < 768 ? 'listMonth' : 'dayGridMonth',
    headerToolbar: {
      left: 'prev,next today',
      center: 'title',
      right: window.innerWidth < 768 ? 'listMonth' : 'dayGridMonth,listMonth'
    },
    themeSystem: 'bootstrap5',
    events: 'get_user_appointments.php',
    eventColor: '#0891b2',
    eventTextColor: '#fff',
    height: 'auto',
    eventClick: function(info) {
      const data = info.event.extendedProps;
      const eventDate = info.event.start;
      const formattedDate = eventDate.toLocaleDateString('en-US', { 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric' 
      });

      document.getElementById('modalService').innerText = info.event.title;
      document.getElementById('modalDate').innerText = formattedDate;
      document.getElementById('modalTime').innerText = data.time || 'N/A';
      document.getElementById('modalStatus').innerText = data.status || 'Pending';
      document.getElementById('modalNotes').innerText = data.notes || 'No additional notes';

      appointmentModal.show();
    },
    windowResize: function(view) {
      if (window.innerWidth < 768) {
        calendar.changeView('listMonth');
      } else {
        calendar.changeView('dayGridMonth');
      }
    }
  });

  calendar.render();
});
</script>

</body>
</html>