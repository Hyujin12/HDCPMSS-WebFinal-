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

// Load environment variables
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

$mongoClient = new Client($_ENV['MONGO_URI']);
$db = $mongoClient->HaliliDentalClinic;
$usersCollection = $db->users;
$appointmentsCollection = $db->booked_service;

// Case-insensitive fullname search
$user = $usersCollection->findOne([
    'fullname' => new MongoDB\BSON\Regex('^' . preg_quote($userFullName ?? '', '/') . '$', 'i')
]);

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

<style>
body { background-color: #f3f4f6; }
.dashboard-card {
  background: white;
  border-radius: 1rem;
  box-shadow: 0 4px 6px rgba(0,0,0,0.1);
  padding: 1.5rem;
  margin-bottom: 1.5rem;
}
</style>
</head>
<body>

<?php include 'sidebar.php'; ?>

<main class="main-content p-4 sm:p-6">
  <div class="max-w-7xl mx-auto">

    <!-- Profile Section -->
    <div class="dashboard-card">
      <div class="d-flex flex-column flex-md-row gap-4 align-items-start">
        <div class="flex-shrink-0">
          <img src="<?= !empty($user['profile_image']) ? htmlspecialchars($user['profile_image']) : '/images/default-avatar.png' ?>"
               alt="Profile Picture"
               class="rounded-circle border shadow" style="width:100px; height:100px; object-fit:cover;">
        </div>
        <div class="flex-grow-1">
          <div class="row">
            <div class="col-md-6">
              <p><strong>Name:</strong> <?= htmlspecialchars($user['fullname'] ?? 'N/A') ?></p>
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
          <p>Status: <strong><?= htmlspecialchars($firstAppointment['status']); ?></strong></p>
        </div>
        <?php if ($totalUpcoming > 1): ?>
          <a href="appointments.php" class="d-block mt-2 text-primary">View More Appointments →</a>
        <?php endif; ?>
      <?php else: ?>
        <p class="text-muted fst-italic">No upcoming appointments.</p>
      <?php endif; ?>
    </div>

  </div>
</main>

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
          <input type="hidden" name="id" value="<?= $user->_id ?? '' ?>">
          <div class="col-md-6">
            <label class="form-label">Full Name</label>
            <input type="text" name="fullname" class="form-control" value="<?= htmlspecialchars($user->fullname ?? '') ?>" required>
          </div>
          <div class="col-md-6">
            <label class="form-label">Date of Birth</label>
            <input type="date" name="dob" class="form-control" value="<?= htmlspecialchars($user->dob ?? '') ?>">
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
            <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($user->phone ?? '') ?>">
          </div>
          <div class="col-md-6">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user->email ?? '') ?>" required>
          </div>
          <div class="col-md-6">
      <label class="form-label">Civil Status</label>
     <input type="text" name="civil_status" class="form-control" value="<?= htmlspecialchars($user->civil_status ?? '') ?>">
    </div>
      <div class="col-md-6">
       <label class="form-label">Occupation</label>
  <input type="text" name="occupation" class="form-control" value="<?= htmlspecialchars($user->occupation ?? '') ?>">
</div>
<div class="col-md-6">
  <label class="form-label">Nationality</label>
  <input type="text" name="nationality" class="form-control" value="<?= htmlspecialchars($user->nationality ?? '') ?>">
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
</body>
</html>
