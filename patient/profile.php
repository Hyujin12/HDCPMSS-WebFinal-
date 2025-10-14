<?php
session_start();

// Redirect if not logged in
if (!isset($_SESSION['user_email'])) {
    header("Location: log-in.php");
    exit;
}

require __DIR__ . '/../vendor/autoload.php';
use MongoDB\Client;

// MongoDB
$mongo = new Client("mongodb://localhost:27017");
$db = $mongo->HaliliDentalClinic;
$users = $db->users;

// Find user
$userEmail = $_SESSION['user_email'];
$user = $users->findOne(['email' => $userEmail]);

if (!$user) {
    die("User not found.");
}

// SweetAlert flag
$updateSuccess = $_SESSION['update_success'] ?? null;
unset($_SESSION['update_success']);
?>

<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Patient Profile</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  

  <style>
      body {
          display: flex;
          min-height: 100vh;
          background: linear-gradient(to right, #dbeafe, #f8fafc);
      }
      .card-custom {
          border-radius: 20px;
          background: #fff;
      }
      .profile-header {
          font-size: 1.8rem;
          font-weight: bold;
          color: #2563eb;
      }
  </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>

    <div class="content container py-5">
        <div class="card card-custom shadow p-5 mx-auto" style="max-width: 750px;">
            <div class="text-center mb-4">
                <img src="<?= !empty($user->profile_image) 
                              ? htmlspecialchars($user->profile_image) 
                              : 'https://cdn-icons-png.flaticon.com/512/847/847969.png' ?>" 
                     class="rounded-circle shadow" 
                     width="120" height="120" 
                     style="object-fit:cover;" 
                     alt="Profile">
                <h3 class="profile-header mt-3"><?= htmlspecialchars($user->fullname ?? 'Patient') ?></h3>
                <p class="text-muted"><?= htmlspecialchars($user->email ?? '') ?></p>
            </div>

            <hr>

            <div class="row g-3">
                <div class="col-md-6"><strong>Date of Birth:</strong><br><?= htmlspecialchars($user->dob ?? '') ?></div>
                <div class="col-md-6"><strong>Age:</strong><br><?= htmlspecialchars($user->age ?? '') ?></div>
                <div class="col-md-6"><strong>Gender:</strong><br><?= htmlspecialchars($user->gender ?? '') ?></div>
                <div class="col-md-6"><strong>Civil Status:</strong><br><?= htmlspecialchars($user->civil_status ?? '') ?></div>
                <div class="col-md-12"><strong>Address:</strong><br><?= htmlspecialchars($user->address ?? '') ?></div>
                <div class="col-md-6"><strong>Phone:</strong><br><?= htmlspecialchars($user->phone ?? '') ?></div>
                <div class="col-md-6"><strong>Occupation:</strong><br><?= htmlspecialchars($user->occupation ?? '') ?></div>
                <div class="col-md-6"><strong>Nationality:</strong><br><?= htmlspecialchars($user->nationality ?? '') ?></div>
            </div>

            <div class="text-center mt-5">
                <button class="btn btn-primary px-4 py-2 rounded-pill shadow" 
                        data-bs-toggle="modal" data-bs-target="#editProfileModal">
                    ✏️ Edit Profile
                </button>
            </div>
        </div>
    </div>

    <!-- Edit Profile Modal -->
    <div class="modal fade" id="editProfileModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <form method="POST" action="update_profile.php" enctype="multipart/form-data" class="modal-content">
          <div class="modal-header bg-primary text-white">
            <h5 class="modal-title">Edit Personal Information</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body row g-3 p-4">
              <input type="hidden" name="id" value="<?= $user->_id ?>">

              <div class="col-md-6">
                  <label class="form-label">Full Name</label>
                  <input type="text" class="form-control" name="fullname" value="<?= htmlspecialchars($user->fullname ?? '') ?>" required>
              </div>
              <div class="col-md-6">
             <label class="form-label">Date of Birth</label>
                 <input type="date" class="form-control" id="dobInput" name="dob" 
           value="<?= htmlspecialchars($user->dob ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Age</label>
                    <input type="number" class="form-control" id="ageInput" name="age" 
                     value="<?= htmlspecialchars($user->age ?? '') ?>" readonly>
                </div>

              <div class="col-md-4">
                  <label class="form-label">Gender</label>
                  <select class="form-control" name="gender">
                      <option value="">Select</option>
                      <option value="Male" <?= ($user->gender ?? '') == "Male" ? "selected" : "" ?>>Male</option>
                      <option value="Female" <?= ($user->gender ?? '') == "Female" ? "selected" : "" ?>>Female</option>
                  </select>
              </div>
              <div class="col-md-4">
                  <label class="form-label">Civil Status</label>
                  <select class="form-control" name="civil_status">
                      <option value="">Select</option>
                      <option value="Single" <?= ($user->civil_status ?? '') == "Single" ? "selected" : "" ?>>Single</option>
                      <option value="Married" <?= ($user->civil_status ?? '') == "Married" ? "selected" : "" ?>>Married</option>
                      <option value="Widowed" <?= ($user->civil_status ?? '') == "Widowed" ? "selected" : "" ?>>Widowed</option>
                      <option value="Divorced" <?= ($user->civil_status ?? '') == "Divorced" ? "selected" : "" ?>>Divorced</option>
                  </select>
              </div>
              <div class="col-md-12">
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
              <div class="col-md-12">
                  <label class="form-label">Profile Image</label>
                  <input type="file" class="form-control" name="profile_image" accept="image/*">
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

    <?php if ($updateSuccess): ?>
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Profile Updated!',
            text: 'Your profile information has been successfully updated.',
            confirmButtonColor: '#2563eb'
        });
        document.addEventListener("DOMContentLoaded", function() {
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

  // Update Age when DOB changes
  dobInput.addEventListener("input", function() {
    ageInput.value = calculateAge(dobInput.value);
  });

  // Auto-fill if DOB already has value
  if (dobInput.value) {
    ageInput.value = calculateAge(dobInput.value);
  }
});
    </script>
    <?php endif; ?>
</body>
</html>
