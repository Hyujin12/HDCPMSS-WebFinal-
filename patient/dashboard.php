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
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

/* Theme Variables */
:root {
  --bg-primary: #f8f9fa;
  --bg-card: #ffffff;
  --text-primary: #212529;
  --text-secondary: #6c757d;
  --border-color: #e9ecef;
  --shadow: 0 2px 4px rgba(0,0,0,0.04);
}

[data-theme="dark"] {
  --bg-primary: #111827;
  --bg-card: #1f2937;
  --text-primary: #f9fafb;
  --text-secondary: #9ca3af;
  --border-color: #374151;
  --shadow: 0 2px 4px rgba(0,0,0,0.3);
}

body { 
  background: var(--bg-primary);
  color: var(--text-primary);
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
  overflow-x: hidden;
  transition: background-color 0.3s ease, color 0.3s ease;
}

.main-content {
  min-height: 100vh;
  padding: 1rem;
  display: flex;
  flex-direction: column;
  margin-left: 0;
  transition: margin-left 0.3s ease;
}

@media (min-width: 768px) {
  .main-content {
    margin-left: 16rem;
  }
}

.dashboard-container {
  max-width: 1400px;
  margin: 0 auto;
  width: 100%;
  flex: 1;
  display: flex;
  flex-direction: column;
}

/* Desktop Layout - Single Page, No Scroll */
@media (min-width: 992px) {
  .main-content {
    padding: 1.5rem;
    height: 100vh;
    overflow: hidden;
  }
  
  .dashboard-container {
    height: 100%;
    overflow: hidden;
  }
  
  .dashboard-grid {
    display: grid;
    grid-template-columns: 350px 1fr;
    grid-template-rows: auto 1fr;
    gap: 1.25rem;
    height: 100%;
    overflow: hidden;
  }
  
  .profile-section {
    grid-column: 1;
    grid-row: 1 / 3;
    overflow-y: auto;
  }
  
  .stats-section {
    grid-column: 2;
    grid-row: 1;
  }
  
  .calendar-section {
    grid-column: 2;
    grid-row: 2;
    min-height: 0;
  }
}

/* Tablet Layout */
@media (min-width: 768px) and (max-width: 991px) {
  .main-content {
    padding: 1.25rem;
  }
  
  .dashboard-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
  }
  
  .profile-section {
    grid-column: 1 / 3;
  }
  
  .calendar-section {
    grid-column: 1 / 3;
  }
}

/* Mobile Layout */
@media (max-width: 767px) {
  .main-content {
    padding: 0.75rem;
    padding-top: 5rem;
  }
  
  .dashboard-grid {
    display: flex;
    flex-direction: column;
    gap: 1rem;
  }
}

/* Card Styles */
.card {
  background: var(--bg-card);
  border-radius: 12px;
  border: 1px solid var(--border-color);
  box-shadow: var(--shadow);
  height: 100%;
  display: flex;
  flex-direction: column;
  transition: background-color 0.3s ease, border-color 0.3s ease;
}

.card-header {
  padding: 1rem 1.25rem;
  border-bottom: 1px solid var(--border-color);
  background: var(--bg-card);
  border-radius: 12px 12px 0 0;
  transition: background-color 0.3s ease, border-color 0.3s ease;
}

.card-title {
  font-size: 1.1rem;
  font-weight: 600;
  color: var(--text-primary);
  margin: 0;
  transition: color 0.3s ease;
}

.card-body {
  padding: 1.25rem;
  flex: 1;
  overflow-y: auto;
}

/* Profile Section */
.profile-header {
  text-align: center;
  padding-bottom: 1rem;
  border-bottom: 1px solid var(--border-color);
  margin-bottom: 1rem;
  transition: border-color 0.3s ease;
}

.profile-img {
  width: 90px;
  height: 90px;
  border-radius: 50%;
  object-fit: cover;
  border: 3px solid #1d4ed8;
  margin-bottom: 0.75rem;
}

.profile-name {
  font-size: 1.15rem;
  font-weight: 600;
  color: var(--text-primary);
  margin-bottom: 0.25rem;
  transition: color 0.3s ease;
}

.profile-email {
  font-size: 0.85rem;
  color: var(--text-secondary);
  transition: color 0.3s ease;
}

.info-row {
  display: flex;
  justify-content: space-between;
  padding: 0.6rem 0;
  border-bottom: 1px solid var(--border-color);
  transition: border-color 0.3s ease;
}

.info-row:last-child {
  border-bottom: none;
}

.info-label {
  font-size: 0.85rem;
  color: var(--text-secondary);
  font-weight: 500;
  transition: color 0.3s ease;
}

.info-value {
  font-size: 0.85rem;
  color: var(--text-primary);
  font-weight: 600;
  text-align: right;
  transition: color 0.3s ease;
}

/* Stats Cards */
.stats-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
  height: 100%;
}

@media (max-width: 991px) {
  .stats-grid {
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  }
}

.stat-card {
  background: white;
  border-radius: 12px;
  border: 1px solid var(--border-color);
  padding: 1.25rem;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  transition: background-color 0.3s ease, border-color 0.3s ease;
}

[data-theme="dark"] .stat-card {
  background: var(--bg-card);
}

.stat-card.primary {
  background: linear-gradient(135deg, #1d4ed8 0%, #1e40af 100%);
  color: white;
  border: none;
}

.stat-card.success {
  background: linear-gradient(135deg, #059669 0%, #047857 100%);
  color: white;
  border: none;
}

.stat-icon {
  font-size: 2rem;
  opacity: 0.9;
  margin-bottom: 0.5rem;
}

.stat-number {
  font-size: 2rem;
  font-weight: 700;
  line-height: 1;
  margin: 0.5rem 0;
}

.stat-label {
  font-size: 0.9rem;
  opacity: 0.9;
}

.appointment-card {
  background: var(--border-color);
  border-left: 3px solid #1d4ed8;
  padding: 1rem;
  border-radius: 8px;
  margin-bottom: 1rem;
  transition: background-color 0.3s ease;
}

.appointment-card:last-child {
  margin-bottom: 0;
}

.appointment-title {
  font-size: 1rem;
  font-weight: 600;
  color: var(--text-primary);
  margin-bottom: 0.5rem;
  transition: color 0.3s ease;
}

.appointment-detail {
  font-size: 0.85rem;
  color: var(--text-secondary);
  margin-bottom: 0.25rem;
  transition: color 0.3s ease;
}

.appointment-detail i {
  width: 16px;
  text-align: center;
  margin-right: 0.5rem;
  color: #1d4ed8;
}

/* Calendar */
.calendar-section .card-body {
  padding: 1rem;
}

#calendar {
  height: 100%;
  min-height: 400px;
}

@media (min-width: 992px) {
  #calendar {
    height: calc(100vh - 180px);
  }
}

.fc {
  height: 100% !important;
}

/* Dark mode calendar adjustments */
[data-theme="dark"] .fc {
  color: var(--text-primary);
}

[data-theme="dark"] .fc-toolbar-title {
  color: var(--text-primary);
}

[data-theme="dark"] .fc-col-header-cell {
  background-color: var(--bg-card);
  color: var(--text-primary);
}

[data-theme="dark"] .fc-daygrid-day {
  background-color: var(--bg-card);
}

[data-theme="dark"] .fc-daygrid-day-number {
  color: var(--text-primary);
}

[data-theme="dark"] .fc-day-today {
  background-color: rgba(29, 78, 216, 0.1) !important;
}

[data-theme="dark"] .fc-scrollgrid {
  border-color: var(--border-color);
}

[data-theme="dark"] .fc td,
[data-theme="dark"] .fc th {
  border-color: var(--border-color);
}

.fc-toolbar-title {
  font-size: 1.1rem !important;
  color: var(--text-primary);
}

.fc .fc-button {
  padding: 0.4rem 0.8rem;
  font-size: 0.85rem;
}

.fc .fc-button-primary {
  background-color: #1d4ed8;
  border-color: #1d4ed8;
}

.fc .fc-button-primary:hover {
  background-color: #1e40af;
  border-color: #1e40af;
}

.fc-event {
  border: none;
  border-radius: 4px;
}

/* Buttons */
.btn-edit {
  width: 100%;
  margin-top: 1rem;
  padding: 0.6rem;
  font-size: 0.9rem;
  font-weight: 600;
  background-color: #1d4ed8;
  border-color: #1d4ed8;
  color: white;
}

.btn-edit:hover {
  background-color: #1e40af;
  border-color: #1e40af;
}

/* Modal Improvements */
.modal-content {
  background-color: var(--bg-card);
  border-color: var(--border-color);
  transition: background-color 0.3s ease;
}

.modal-header {
  background: #1d4ed8;
  color: white;
  border-bottom: none;
}

.modal-title {
  font-size: 1.1rem;
  font-weight: 600;
}

.modal-body {
  background-color: var(--bg-card);
}

.modal-footer {
  background-color: var(--bg-card);
  border-top: 1px solid var(--border-color);
}

.form-label {
  font-size: 0.85rem;
  font-weight: 600;
  color: var(--text-primary);
  margin-bottom: 0.4rem;
  transition: color 0.3s ease;
}

.form-control, .form-select {
  font-size: 0.9rem;
  border-radius: 6px;
  border: 1px solid var(--border-color);
  padding: 0.6rem 0.75rem;
  background-color: var(--bg-card);
  color: var(--text-primary);
  transition: all 0.3s ease;
}

[data-theme="dark"] .form-control,
[data-theme="dark"] .form-select {
  background-color: #374151;
}

.form-control:focus, .form-select:focus {
  border-color: #1d4ed8;
  box-shadow: 0 0 0 0.2rem rgba(29, 78, 216, 0.15);
  background-color: var(--bg-card);
  color: var(--text-primary);
}

/* Scrollbar Styling */
.card-body::-webkit-scrollbar,
.profile-section::-webkit-scrollbar {
  width: 6px;
}

.card-body::-webkit-scrollbar-track,
.profile-section::-webkit-scrollbar-track {
  background: var(--border-color);
  border-radius: 10px;
}

.card-body::-webkit-scrollbar-thumb,
.profile-section::-webkit-scrollbar-thumb {
  background: var(--text-secondary);
  border-radius: 10px;
}

.card-body::-webkit-scrollbar-thumb:hover,
.profile-section::-webkit-scrollbar-thumb:hover {
  background: var(--text-primary);
}

/* Responsive adjustments */
@media (max-width: 767px) {
  .profile-img {
    width: 80px;
    height: 80px;
  }
  
  .profile-name {
    font-size: 1rem;
  }
  
  .card-header {
    padding: 0.875rem 1rem;
  }
  
  .card-body {
    padding: 1rem;
  }
  
  .stat-number {
    font-size: 1.75rem;
  }
  
  .stat-icon {
    font-size: 1.75rem;
  }
}
</style>
</head>
<body>

<?php include 'sidebar.php'; ?>

<main class="main-content">
  <div class="dashboard-container">
    <div class="dashboard-grid">

      <!-- Profile Section -->
      <div class="profile-section">
        <div class="card">
          <div class="card-header">
            <h2 class="card-title">Patient Profile</h2>
          </div>
          <div class="card-body">
            <div class="profile-header">
              <img src="<?= !empty($user['profile_image']) ? htmlspecialchars($user['profile_image']) : '/images/default-avatar.png' ?>" 
                   alt="Profile" class="profile-img">
              <div class="profile-name"><?= htmlspecialchars($user['username'] ?? 'N/A') ?></div>
              <div class="profile-email"><?= htmlspecialchars($user['email'] ?? 'N/A') ?></div>
            </div>
            
            <div class="info-row">
              <span class="info-label">Age</span>
              <span class="info-value"><?= htmlspecialchars($user['age'] ?? 'N/A') ?> years</span>
            </div>
            
            <div class="info-row">
              <span class="info-label">Gender</span>
              <span class="info-value"><?= htmlspecialchars($user['gender'] ?? 'N/A') ?></span>
            </div>
            
            <div class="info-row">
              <span class="info-label">Civil Status</span>
              <span class="info-value"><?= htmlspecialchars($user['status'] ?? 'N/A') ?></span>
            </div>
            
            <div class="info-row">
              <span class="info-label">Contact</span>
              <span class="info-value"><?= htmlspecialchars($user['contactNumber'] ?? 'N/A') ?></span>
            </div>
            
            <div class="info-row">
              <span class="info-label">Address</span>
              <span class="info-value"><?= htmlspecialchars($user['address'] ?? 'N/A') ?></span>
            </div>
            
            <div class="info-row">
              <span class="info-label">Occupation</span>
              <span class="info-value"><?= htmlspecialchars($user['occupation'] ?? 'N/A') ?></span>
            </div>
            
            <div class="info-row">
              <span class="info-label">Nationality</span>
              <span class="info-value"><?= htmlspecialchars($user['nationality'] ?? 'N/A') ?></span>
            </div>
            
            <button class="btn btn-primary btn-edit" data-bs-toggle="modal" data-bs-target="#editProfileModal">
              <i class="fas fa-edit me-2"></i>Edit Profile
            </button>
          </div>
        </div>
      </div>

      <!-- Stats Section -->
      <div class="stats-section">
        <div class="stats-grid">
          <!-- Total Appointments -->
          <div class="stat-card primary">
            <div>
              <div class="stat-icon">
                <i class="fas fa-calendar-check"></i>
              </div>
              <div class="stat-number"><?= $totalUpcoming ?></div>
              <div class="stat-label">Upcoming</div>
            </div>
          </div>

          <!-- Next Appointment -->
          <div class="stat-card success">
            <div class="card-header border-0 p-0 bg-transparent">
              <h3 class="card-title text-white" style="font-size: 0.95rem;">Next Appointment</h3>
            </div>
            <div style="margin-top: 0.75rem;">
              <?php if ($firstAppointment): ?>
                <div class="appointment-title text-white" style="font-size: 0.95rem;">
                  <?= htmlspecialchars($firstAppointment['serviceName']); ?>
                </div>
                <div class="appointment-detail text-white-50" style="font-size: 0.8rem;">
                  <i class="fas fa-calendar"></i>
                  <?= date('M d, Y', strtotime($firstAppointment['date'])); ?>
                </div>
                <div class="appointment-detail text-white-50" style="font-size: 0.8rem;">
                  <i class="fas fa-clock"></i>
                  <?= htmlspecialchars($firstAppointment['time']); ?>
                </div>
              <?php else: ?>
                <p class="text-white-50 mb-0" style="font-size: 0.85rem;">No appointments scheduled</p>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>

      <!-- Calendar Section -->
      <div class="calendar-section">
        <div class="card">
          <div class="card-header">
            <h2 class="card-title">Appointment Calendar</h2>
          </div>
          <div class="card-body">
            <div id="calendar"></div>
          </div>
        </div>
      </div>

    </div>
  </div>
</main>

<!-- Appointment Detail Modal -->
<div class="modal fade" id="appointmentModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Appointment Details</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="info-row">
          <span class="info-label">Service</span>
          <span class="info-value" id="modalService"></span>
        </div>
        <div class="info-row">
          <span class="info-label">Date</span>
          <span class="info-value" id="modalDate"></span>
        </div>
        <div class="info-row">
          <span class="info-label">Time</span>
          <span class="info-value" id="modalTime"></span>
        </div>
        <div class="info-row">
          <span class="info-label">Status</span>
          <span class="info-value" id="modalStatus"></span>
        </div>
        <div class="info-row">
          <span class="info-label">Notes</span>
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
      <div class="modal-header">
        <h5 class="modal-title">Edit Profile</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="row g-3">
          <input type="hidden" name="id" value="<?= (string)$user->_id ?>">
          
          <div class="col-md-6">
            <label class="form-label">Full Name</label>
            <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($user->username ?? '') ?>" required>
          </div>
          
          <div class="col-md-6">
            <label class="form-label">Date of Birth</label>
            <input type="date" name="birthday" class="form-control" value="<?= htmlspecialchars($user->birthday ?? '') ?>" required>
          </div>
          
          <div class="col-md-6">
            <label class="form-label">Gender</label>
            <select class="form-select" name="gender" required>
              <option value="">Select Gender</option>
              <option value="Male" <?= ($user->gender ?? '') == "Male" ? "selected" : "" ?>>Male</option>
              <option value="Female" <?= ($user->gender ?? '') == "Female" ? "selected" : "" ?>>Female</option>
            </select>
          </div>
          
          <div class="col-md-6">
            <label class="form-label">Contact Number</label>
            <input type="text" name="contactNumber" class="form-control" value="<?= htmlspecialchars($user->contactNumber ?? '') ?>" required>
          </div>
          
          <div class="col-12">
            <label class="form-label">Address</label>
            <input type="text" name="address" class="form-control" value="<?= htmlspecialchars($user->address ?? '') ?>" required>
          </div>
          
          <div class="col-md-6">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user->email ?? '') ?>" required readonly>
          </div>
          
          <div class="col-md-6">
            <label class="form-label">Civil Status</label>
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
            <label class="form-label">Occupation</label>
            <input type="text" name="occupation" class="form-control" value="<?= htmlspecialchars($user->occupation ?? '') ?>" required>
          </div>
          
          <div class="col-md-6">
            <label class="form-label">Nationality</label>
            <select name="nationality" class="form-select" required>
              <option value="">Select Nationality</option>
              <option value="Filipino" <?= (($user['nationality'] ?? '') === 'Filipino') ? 'selected' : '' ?>>Filipino</option>
              <option value="Foreign National" <?= (($user['nationality'] ?? '') === 'Foreign National') ? 'selected' : '' ?>>Foreign National</option>
            </select>
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
// Initialize theme from sidebar
const currentTheme = '<?= isset($_SESSION['theme']) ? $_SESSION['theme'] : 'light' ?>';
document.documentElement.setAttribute('data-theme', currentTheme);

// Calendar
document.addEventListener('DOMContentLoaded', function() {
  var calendarEl = document.getElementById('calendar');
  var appointmentModal = new bootstrap.Modal(document.getElementById('appointmentModal'));

  var calendar = new FullCalendar.Calendar(calendarEl, {
    initialView: 'dayGridMonth',
    headerToolbar: {
      left: 'prev,next today',
      center: 'title',
      right: ''
    },
    themeSystem: 'bootstrap5',
    events: 'get_user_appointments.php',
    eventColor: '#1d4ed8',
    eventTextColor: '#fff',
    height: '100%',
    contentHeight: 'auto',
    selectable: false,
    editable: false,
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
    }
  });

  calendar.render();
});
</script>

</body>
</html>