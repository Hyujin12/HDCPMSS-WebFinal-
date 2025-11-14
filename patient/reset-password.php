<?php
session_start();
require __DIR__ . '/../vendor/autoload.php';

use MongoDB\Client;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

$mongoClient = new Client($_ENV['MONGO_URI']);
$db = $mongoClient->selectDatabase('HaliliDentalClinic');
$usersCollection = $db->selectCollection('users');

$swal = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $resetCode = trim($_POST['reset_code']);
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];

    // Validate inputs
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } elseif (strlen($newPassword) < 8) {
        $error = "Password must be at least 8 characters long.";
    } elseif ($newPassword !== $confirmPassword) {
        $error = "Passwords do not match.";
    } else {
        $user = $usersCollection->findOne(['email' => $email]);

        if (!$user) {
            $error = "No user found with that email.";
        } elseif (empty($user['reset_code']) || empty($user['reset_expires'])) {
            $error = "No reset code found. Please request a new one.";
        } else {
            $currentTime = new MongoDB\BSON\UTCDateTime();
            
            // Check if code has expired
            if ($currentTime > $user['reset_expires']) {
                $error = "Reset code has expired. Please request a new one.";
            } elseif ($user['reset_code'] != $resetCode) {
                $error = "Invalid reset code.";
            } else {
                // Reset code is valid, update password
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                
                $usersCollection->updateOne(
                    ['email' => $email],
                    [
                        '$set' => ['password' => $hashedPassword],
                        '$unset' => ['reset_code' => '', 'reset_expires' => '']
                    ]
                );

                // Clear session
                unset($_SESSION['reset_email']);

                $swal = "
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        Swal.fire({
                            icon: 'success',
                            title: 'Password Reset Successful!',
                            text: 'You can now log in with your new password.',
                            confirmButtonColor: '#667eea',
                            confirmButtonText: 'Go to Login'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.location.href = 'log-in.php';
                            }
                        });
                    });
                </script>";
            }
        }
    }
}

// Pre-fill email if coming from forgot-password page
$prefillEmail = $_SESSION['reset_email'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Reset Password - Halili Dental Clinic</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    .gradient-bg {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
    .gradient-text {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
    }
  </style>
</head>
<body class="min-h-screen bg-gray-50 flex items-center justify-center p-4">
  
  <div class="bg-white p-8 rounded-2xl shadow-2xl w-full max-w-md">
    <div class="text-center mb-6">
      <img src="/images/newlogohalili.png" alt="Halili Logo" class="w-20 h-20 mx-auto mb-4">
      <h2 class="text-2xl font-bold gradient-text mb-2">Reset Password</h2>
      <p class="text-gray-600 text-sm">Enter your reset code and new password</p>
    </div>

    <?php if (!empty($error)) : ?>
      <div class="mb-4 bg-red-100 text-red-700 font-medium p-3 rounded-md text-center">
        <?= htmlspecialchars($error) ?>
      </div>
    <?php endif; ?>

    <form method="POST">
      <div class="mb-4">
        <label class="block font-semibold mb-2 text-gray-700">Email</label>
        <input type="email" name="email" value="<?= htmlspecialchars($prefillEmail) ?>" 
          placeholder="Enter your email" 
          class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 transition" required>
      </div>

      <div class="mb-4">
        <label class="block font-semibold mb-2 text-gray-700">Reset Code</label>
        <input type="text" name="reset_code" placeholder="Enter 6-digit code" 
          pattern="[0-9]{6}" maxlength="6"
          class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 transition text-center text-2xl tracking-widest font-bold" required>
      </div>

      <div class="mb-4">
        <label class="block font-semibold mb-2 text-gray-700">New Password</label>
        <div class="relative">
          <input type="password" 
                 name="new_password" 
                 id="new_password"
                 placeholder="Minimum 8 characters" 
                 class="w-full p-3 pr-12 border rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 transition" required>
          <button type="button" 
                  onclick="togglePassword('new_password', 'toggleNewPasswordIcon')"
                  class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700 focus:outline-none">
            <i id="toggleNewPasswordIcon" class="fas fa-eye"></i>
          </button>
        </div>
      </div>

      <div class="mb-6">
        <label class="block font-semibold mb-2 text-gray-700">Confirm Password</label>
        <div class="relative">
          <input type="password" 
                 name="confirm_password" 
                 id="confirm_password"
                 placeholder="Re-enter new password" 
                 class="w-full p-3 pr-12 border rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 transition" required>
          <button type="button" 
                  onclick="togglePassword('confirm_password', 'toggleConfirmPasswordIcon')"
                  class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700 focus:outline-none">
            <i id="toggleConfirmPasswordIcon" class="fas fa-eye"></i>
          </button>
        </div>
      </div>

      <button type="submit" 
        class="w-full gradient-bg text-white py-3 rounded-lg font-bold hover:opacity-90 transition duration-300 shadow-md">
        Reset Password
      </button>
    </form>

    <div class="mt-6 text-center space-y-2">
      <p class="text-gray-700">
        <a href="forgot-password.php" class="text-purple-600 font-semibold hover:underline">
          Request New Code
        </a>
      </p>
      <p class="text-gray-700">
        <a href="log-in.php" class="text-purple-600 font-semibold hover:underline">
          Back to Login
        </a>
      </p>
    </div>
  </div>

  <?= $swal ?>

  <script>
  function togglePassword(inputId, iconId) {
    const input = document.getElementById(inputId);
    const icon = document.getElementById(iconId);
    
    if (input.type === 'password') {
      input.type = 'text';
      icon.classList.remove('fa-eye');
      icon.classList.add('fa-eye-slash');
    } else {
      input.type = 'password';
      icon.classList.remove('fa-eye-slash');
      icon.classList.add('fa-eye');
    }
  }
  </script>

</body>
</html>