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
body { background-color: #f3f4f6; }
.dashboard-card {
  background: white;
  border-radius: 1rem;
  box-shadow: 0 4px 6px rgba(0,0,0,0.1);
  padding: 1.5rem;
  margin-bottom: 1.5rem;
}
.dashboard-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 1.5rem;
}
@media (min-width: 768px) {
  .dashboard-grid { grid-template-columns: repeat(2, 1fr); }
}
#calendar {
  width: 100%;
  height: 450px;
  margin: 0 auto;
}
</style>
</head>
<body>

<?php include 'sidebar.php'; ?>

<main class="main-content p-4 sm:p-6">
  <div class="max-w-7xl mx-auto dashboard-grid">

    <!-- Profile -->
    <div class="dashboard-card">
      <div class="d-flex flex-column flex-md-row gap-4 align-items-start">
        <img src="<?= !empty($user['profile_image']) ? htmlspecialchars($user['profile_image']) : '/images/default-avatar.png' ?>" 
             alt="Profile Picture" class="rounded-circle border shadow" style="width:100px; height:100px; object-fit:cover;">
        <div class="flex-grow-1">
          <div class="row">
            <div class="col-md-6">
              <p><strong>Name:</strong> <?= htmlspecialchars($user['username'] ?? 'N/A') ?></p>
              <p><strong>Age:</strong> <?= htmlspecialchars($user['age'] ?? 'N/A') ?></p>
              <p><strong>Sex:</strong> <?= htmlspecialchars($user['gender'] ?? 'N/A') ?></p>
            </div>
            <div class="col-md-6">
              <p><strong>Address:</strong> <?= htmlspecialchars($user['address'] ?? 'N/A') ?></p>
              <p><strong>Email:</strong> <?= htmlspecialchars($user['email'] ?? 'N/A') ?></p>
            </div>
          </div>
          <a href="#" class="btn btn-primary btn-sm mt-2" data-bs-toggle="modal" data-bs-target="#editProfileModal">Edit Profile</a>
        </div>
      </div>
    </div>

    <!-- Upcoming Appointment -->
    <div class="dashboard-card">
      <h3 class="fw-bold mb-3">Upcoming Appointment</h3>
      <?php if ($firstAppointment): ?>
        <div class="p-3 bg-light border-start border-primary border-4 rounded">
          <h5 class="mb-1"><?= htmlspecialchars($firstAppointment['serviceName']); ?></h5>
          <p class="mb-1">📅 <?= htmlspecialchars($firstAppointment['date']); ?> at <?= htmlspecialchars($firstAppointment['time']); ?></p>
          <p>Status: <strong><?= htmlspecialchars($firstAppointment['status'] ?? 'Pending'); ?></strong></p>
        </div>
        <?php if ($totalUpcoming > 1): ?>
          <a href="appointments.php" class="d-block mt-2 text-primary">View More Appointments →</a>
        <?php endif; ?>
      <?php else: ?>
        <p class="text-muted fst-italic">No upcoming appointments.</p>
      <?php endif; ?>
    </div>

    <!-- Appointment Calendar -->
    <div class="dashboard-card" style="grid-column: span 2;">
      <h3 class="fw-bold mb-3">Appointment Calendar</h3>
      <div id="calendar"></div>
    </div>

  </div>
</main>

<!-- Appointment Detail Modal -->
<div class="modal fade" id="appointmentModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title">Appointment Details</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p><strong>Service:</strong> <span id="modalService"></span></p>
        <p><strong>Date:</strong> <span id="modalDate"></span></p>
        <p><strong>Time:</strong> <span id="modalTime"></span></p>
        <p><strong>Status:</strong> <span id="modalStatus"></span></p>
        <p><strong>Notes:</strong> <span id="modalNotes"></span></p>
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
        <h5 class="modal-title">Edit Profile</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="row g-3">
          <input type="hidden" name="id" value="<?= (string)$user->_id ?>">
          <div class="col-md-6">
            <label class="form-label">Full Name</label>
            <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($user->username ?? '') ?>">
          </div>
          <div class="col-md-6">
            <label class="form-label">Date of Birth</label>
           <input type="date" name="birthday" class="form-control" value="<?= htmlspecialchars($user->birthday ?? '') ?>">
          </div>
          <div class="col-md-6">
            <label class="form-label">Gender</label>
            <select class="form-control" name="gender">
              <option value="">Select</option>
              <option value="Male" <?= ($user->gender ?? '') == "Male" ? "selected" : "" ?>>Male</option>
              <option value="Female" <?= ($user->gender ?? '') == "Female" ? "selected" : "" ?>>Female</option>
            </select>
          </div>
          <div class="col-12">
            <label class="form-label">Address</label>
            <input type="text" name="address" class="form-control" value="<?= htmlspecialchars($user->address ?? '') ?>">
          </div>
          <div class="col-md-6">
            <label class="form-label">Phone</label>
            <input type="text" name="contactNumber" class="form-control" value="<?= htmlspecialchars($user->contactNumber ?? '') ?>">
          </div>
          <div class="col-md-6">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user->email ?? '') ?>" required readonly>
          </div>
          <div class="col-md-6">
          <label class="form-label">Civil Status</label>
            <select name="status" class="form-control" required>
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
            <input type="text" name="occupation" class="form-control" value="<?= htmlspecialchars($user->occupation ?? '') ?>">
          </div>
          <div class="col-md-6">
            <label class="form-label">Nationality</label>
             <select name="nationality" class="form-control" required>
          <option value="">Select Nationality</option>
          <option value="Filipino" <?= (($user['nationality'] ?? '') === 'Filipino') ? 'selected' : '' ?>>Filipino</option>
          <option value="Foreign National" <?= (($user['nationality'] ?? '') === 'Foreign National') ? 'selected' : '' ?>>Foreign National</option>
        </select>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-success">Save</button>
        <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
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
    events: 'get_user_appointments.php',
    eventColor: '#0d6efd',
    eventTextColor: '#fff',
    eventClick: function(info) {
      const data = info.event.extendedProps;

      document.getElementById('modalService').innerText = info.event.title;
      document.getElementById('modalDate').innerText = info.event.start.toISOString().slice(0,10);
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
