<?php
session_start();
require __DIR__ . '/../vendor/autoload.php';

use MongoDB\Client;
use Dotenv\Dotenv;

// Load environment variables
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// MongoDB connection
$mongoClient = new Client($_ENV['MONGO_URI']);
$db = $mongoClient->selectDatabase('HaliliDentalClinic');
$usersCollection = $db->selectCollection('users');

$error = '';
$success = '';

// ✅ Function: Send verification email using Resend API
function sendVerificationEmail($email, $username, $code)
{
    $apiKey = $_ENV['RESEND_API_KEY'];
    $url = "https://api.resend.com/emails";

    $data = [
        "from" => "Halili Dental Clinic <no-reply@halilidentalclinic.shop>",
        "to" => [$email],
        "subject" => "Verify Your Halili Dental Clinic Account",
        "html" => "
            <div style='font-family: Arial, sans-serif;'>
                <h2>Welcome to Halili Dental Clinic, $username!</h2>
                <p>Your verification code is:</p>
                <h3 style='color: #007bff;'>$code</h3>
                <p>This code will expire in 15 minutes.</p>
                <br>
                <p>If you did not create this account, please ignore this email.</p>
            </div>"
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer $apiKey",
        "Content-Type: application/json"
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    file_put_contents(__DIR__ . '/../email_log.txt',
        "==== " . date('Y-m-d H:i:s') . " ====\n" .
        "To: $email\nHTTP: $httpCode\nResponse: $response\nError: $error\n\n",
        FILE_APPEND
    );

    return $httpCode >= 200 && $httpCode < 300;
}

// ✅ Registration handler
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $contactNumber = trim($_POST['contact_number'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $age = (int)($_POST['age'] ?? 0);
    $status = trim($_POST['status'] ?? '');
    $birthday = trim($_POST['birthday'] ?? '');
    $gender = trim($_POST['gender'] ?? '');
    $nationality = trim($_POST['nationality'] ?? '');
    $occupation = trim($_POST['occupation'] ?? '');

    if (!$username || !$email || !$password || !$confirmPassword || !$contactNumber || !$address || !$age || !$status || !$birthday || !$gender || !$nationality || !$occupation) {
        $error = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email address.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long.";
    } elseif ($password !== $confirmPassword) {
        $error = "Passwords do not match.";
    } else {
        // Check for existing email
        $existingEmail = $usersCollection->findOne(['email' => strtolower($email)]);
        if ($existingEmail) {
            $error = "Email already registered.";
        } else {
            // Check for existing username (case-insensitive)
            $existingUsername = $usersCollection->findOne([
                'username' => new MongoDB\BSON\Regex('^' . preg_quote($username) . '$', 'i')
            ]);
            
            if ($existingUsername) {
                $error = "Username already taken. Please choose another.";
            } else {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $verificationCode = random_int(100000, 999999);

                $newUser = [
                    'username' => $username,
                    'email' => strtolower($email),
                    'contactNumber' => $contactNumber,
                    'address' => $address,
                    'age' => $age,
                    'status' => $status,
                    'birthday' => $birthday,
                    'gender' => $gender,
                    'nationality' => $nationality,
                    'occupation' => $occupation,
                    'password' => $hashedPassword,
                    'isVerified' => false,
                    'verificationCode' => (string)$verificationCode,
                    'codeExpires' => new MongoDB\BSON\UTCDateTime((time() + 900) * 1000),
                    'createdAt' => new MongoDB\BSON\UTCDateTime()
                ];

                $insertResult = $usersCollection->insertOne($newUser);

                if ($insertResult->getInsertedCount() === 1) {
                    if (sendVerificationEmail($email, $username, $verificationCode)) {
                        header("Location: verify-code.php?email=" . urlencode($email));
                        exit;
                    } else {
                        $error = "✅ Account created, but email failed to send. Please contact support.";
                    }
                } else {
                    $error = "Registration failed. Please try again.";
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Register - Halili Dental Clinic</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
  * {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
  }

  body {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    min-height: 100vh;
  }

  .gradient-text {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
  }

  .branding-bg {
    background-image: linear-gradient(135deg, rgba(102, 126, 234, 0.95) 0%, rgba(118, 75, 162, 0.95) 100%),
                      url('/images/dental-background.jpg');
    background-size: cover;
    background-position: center;
  }

  .form-container {
    background: white;
    border-radius: 24px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    max-width: 900px;
    margin: 2rem auto;
    overflow: hidden;
  }

  .form-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 2rem;
    text-align: center;
    color: white;
  }

  .form-body {
    padding: 2rem;
  }

  .input-group {
    position: relative;
    margin-bottom: 1.5rem;
  }

  .input-label {
    display: block;
    font-weight: 600;
    color: #374151;
    margin-bottom: 0.5rem;
    font-size: 0.9rem;
  }

  .input-label i {
    color: #667eea;
    margin-right: 0.5rem;
    width: 20px;
  }

  .input-field {
    width: 100%;
    padding: 0.875rem 1rem;
    border: 2px solid #e5e7eb;
    border-radius: 12px;
    font-size: 0.95rem;
    transition: all 0.3s ease;
    background: #f9fafb;
  }

  .input-field:focus {
    outline: none;
    border-color: #667eea;
    background: white;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
  }

  .input-field.error {
    border-color: #ef4444;
    background: #fef2f2;
  }

  .input-field.success {
    border-color: #10b981;
    background: #f0fdf4;
  }

  .input-feedback {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-top: 0.5rem;
    font-size: 0.85rem;
  }

  .input-feedback.error {
    color: #ef4444;
  }

  .input-feedback.success {
    color: #10b981;
  }

  .input-feedback.checking {
    color: #6b7280;
  }

  .spinner {
    border: 2px solid #e5e7eb;
    border-top: 2px solid #667eea;
    border-radius: 50%;
    width: 16px;
    height: 16px;
    animation: spin 0.8s linear infinite;
  }

  @keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
  }

  .btn-submit {
    width: 100%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 1rem;
    border: none;
    border-radius: 12px;
    font-weight: 600;
    font-size: 1rem;
    cursor: pointer;
    transition: all 0.3s ease;
    margin-top: 1rem;
  }

  .btn-submit:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
  }

  .btn-submit:disabled {
    opacity: 0.6;
    cursor: not-allowed;
  }

  .password-strength {
    height: 4px;
    background: #e5e7eb;
    border-radius: 2px;
    margin-top: 0.5rem;
    overflow: hidden;
  }

  .password-strength-bar {
    height: 100%;
    transition: all 0.3s ease;
    border-radius: 2px;
  }

  .strength-weak { width: 33%; background: #ef4444; }
  .strength-medium { width: 66%; background: #f59e0b; }
  .strength-strong { width: 100%; background: #10b981; }

  .alert {
    padding: 1rem;
    border-radius: 12px;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    font-weight: 500;
  }

  .alert-error {
    background: #fef2f2;
    color: #dc2626;
    border: 1px solid #fecaca;
  }

  .alert-success {
    background: #f0fdf4;
    color: #16a34a;
    border: 1px solid #bbf7d0;
  }

  @media (max-width: 768px) {
    .form-container {
      margin: 1rem;
      border-radius: 16px;
    }

    .form-body {
      padding: 1.5rem;
    }
  }
</style>
</head>
<body>

<div class="form-container">
  <div class="form-header">
    <img src="/images/logodental.png" alt="Clinic Logo" class="w-20 h-20 mx-auto mb-3 drop-shadow-lg">
    <h1 class="text-3xl font-bold mb-2">Create Your Account</h1>
    <p class="opacity-90">Join Halili Dental Clinic for quality dental care</p>
  </div>

  <div class="form-body">
    <?php if (!empty($error)) : ?>
      <div class="alert alert-error">
        <i class="fas fa-exclamation-circle"></i>
        <span><?= htmlspecialchars($error) ?></span>
      </div>
    <?php elseif (!empty($success)) : ?>
      <div class="alert alert-success">
        <i class="fas fa-check-circle"></i>
        <span><?= htmlspecialchars($success) ?></span>
      </div>
    <?php endif; ?>

    <form method="POST" id="registrationForm">
      <!-- Personal Information Section -->
      <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
        <i class="fas fa-user text-purple-600"></i>
        Personal Information
      </h3>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="input-group">
          <label class="input-label">
            <i class="fas fa-user"></i>Full Name
          </label>
          <input type="text" 
                 name="username" 
                 id="username"
                 value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" 
                 class="input-field" 
                 required>
          <div id="usernameCheck" class="input-feedback" style="display: none;"></div>
        </div>

        <div class="input-group">
          <label class="input-label">
            <i class="fas fa-envelope"></i>Email Address
          </label>
          <input type="email" 
                 name="email" 
                 value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" 
                 class="input-field" 
                 required>
        </div>

        <div class="input-group">
          <label class="input-label">
            <i class="fas fa-phone"></i>Contact Number
          </label>
          <input type="text" 
                 name="contact_number" 
                 value="<?= htmlspecialchars($_POST['contact_number'] ?? '') ?>" 
                 class="input-field"
                 placeholder="0912 345 6789" 
                 required>
        </div>

        <div class="input-group">
          <label class="input-label">
            <i class="fas fa-calendar"></i>Birthday
          </label>
          <input type="date" 
                 name="birthday" 
                 id="birthday"
                 value="<?= htmlspecialchars($_POST['birthday'] ?? '') ?>" 
                 class="input-field" 
                 required>
        </div>

        <div class="input-group">
          <label class="input-label">
            <i class="fas fa-hashtag"></i>Age
          </label>
          <input type="number" 
                 name="age" 
                 id="age"
                 value="<?= htmlspecialchars($_POST['age'] ?? '') ?>" 
                 class="input-field"
                 min="1"
                 max="120"
                 readonly 
                 required>
        </div>

        <div class="input-group">
          <label class="input-label">
            <i class="fas fa-venus-mars"></i>Gender
          </label>
          <select name="gender" class="input-field" required>
            <option value="">Select Gender</option>
            <option value="Male" <?= (($_POST['gender'] ?? '') === 'Male') ? 'selected' : '' ?>>Male</option>
            <option value="Female" <?= (($_POST['gender'] ?? '') === 'Female') ? 'selected' : '' ?>>Female</option>
            <option value="Other" <?= (($_POST['gender'] ?? '') === 'Other') ? 'selected' : '' ?>>Other</option>
          </select>
        </div>

        <div class="input-group">
          <label class="input-label">
            <i class="fas fa-heart"></i>Civil Status
          </label>
          <select name="status" class="input-field" required>
            <option value="">Select Status</option>
            <?php
            $statuses = ['Single', 'Married', 'Separated', 'Widowed', 'Divorced'];
            foreach ($statuses as $s) {
              $selected = (($_POST['status'] ?? '') === $s) ? 'selected' : '';
              echo "<option value='$s' $selected>$s</option>";
            }
            ?>
          </select>
        </div>

        <div class="input-group">
          <label class="input-label">
            <i class="fas fa-flag"></i>Nationality
          </label>
          <select name="nationality" class="input-field" required>
            <option value="">Select Nationality</option>
            <option value="Filipino" <?= (($_POST['nationality'] ?? '') === 'Filipino') ? 'selected' : '' ?>>Filipino</option>
            <option value="Foreign National" <?= (($_POST['nationality'] ?? '') === 'Foreign National') ? 'selected' : '' ?>>Foreign National</option>
          </select>
        </div>

        <div class="input-group">
          <label class="input-label">
            <i class="fas fa-briefcase"></i>Occupation
          </label>
          <input type="text" 
                 name="occupation" 
                 value="<?= htmlspecialchars($_POST['occupation'] ?? '') ?>" 
                 class="input-field"
                 placeholder="e.g., Teacher, Engineer" 
                 required>
        </div>

        <div class="input-group md:col-span-2">
          <label class="input-label">
            <i class="fas fa-map-marker-alt"></i>Address
          </label>
          <input type="text" 
                 name="address" 
                 value="<?= htmlspecialchars($_POST['address'] ?? '') ?>" 
                 class="input-field"
                 placeholder="Complete address" 
                 required>
        </div>
      </div>

      <!-- Security Section -->
      <h3 class="text-lg font-bold text-gray-800 mt-6 mb-4 flex items-center gap-2">
        <i class="fas fa-lock text-purple-600"></i>
        Account Security
      </h3>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="input-group">
          <label class="input-label">
            <i class="fas fa-key"></i>Password
          </label>
          <input type="password" 
                 name="password" 
                 id="password"
                 class="input-field" 
                 required>
          <div class="password-strength">
            <div id="passwordStrengthBar" class="password-strength-bar"></div>
          </div>
          <div id="passwordStrengthText" class="input-feedback" style="margin-top: 0.25rem;"></div>
        </div>

        <div class="input-group">
          <label class="input-label">
            <i class="fas fa-check-circle"></i>Confirm Password
          </label>
          <input type="password" 
                 name="confirm_password" 
                 id="confirmPassword"
                 class="input-field" 
                 required>
          <div id="confirmPasswordFeedback" class="input-feedback" style="display: none;"></div>
        </div>
      </div>

      <button type="submit" class="btn-submit" id="submitBtn">
        <i class="fas fa-user-plus"></i>
        Create Account
      </button>

      <p class="text-center text-gray-600 mt-4">
        Already have an account?
        <a href="log-in.php" class="text-purple-600 hover:underline font-semibold">Log in</a>
      </p>
    </form>
  </div>
</div>

<script>
  let usernameCheckTimeout;
  let isUsernameAvailable = false;

  // Username availability check
  document.getElementById('username').addEventListener('input', function() {
    const username = this.value.trim();
    const feedback = document.getElementById('usernameCheck');
    const input = this;

    if (username.length < 3) {
      feedback.style.display = 'none';
      input.classList.remove('error', 'success');
      isUsernameAvailable = false;
      updateSubmitButton();
      return;
    }

    clearTimeout(usernameCheckTimeout);
    
    feedback.style.display = 'flex';
    feedback.className = 'input-feedback checking';
    feedback.innerHTML = '<div class="spinner"></div><span>Checking availability...</span>';
    input.classList.remove('error', 'success');

    usernameCheckTimeout = setTimeout(async () => {
      try {
        const response = await fetch('check-username.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ username: username })
        });

        const data = await response.json();

        if (data.available) {
          feedback.className = 'input-feedback success';
          feedback.innerHTML = '<i class="fas fa-check-circle"></i><span>Username available!</span>';
          input.classList.add('success');
          input.classList.remove('error');
          isUsernameAvailable = true;
        } else {
          feedback.className = 'input-feedback error';
          feedback.innerHTML = '<i class="fas fa-times-circle"></i><span>Username already taken</span>';
          input.classList.add('error');
          input.classList.remove('success');
          isUsernameAvailable = false;
        }
      } catch (error) {
        feedback.className = 'input-feedback error';
        feedback.innerHTML = '<i class="fas fa-exclamation-circle"></i><span>Error checking username</span>';
        isUsernameAvailable = false;
      }
      
      updateSubmitButton();
    }, 500);
  });

  // Auto-calculate age from birthday
  document.getElementById('birthday').addEventListener('change', function() {
    const birthday = new Date(this.value);
    const today = new Date();
    let age = today.getFullYear() - birthday.getFullYear();
    const monthDiff = today.getMonth() - birthday.getMonth();
    
    if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthday.getDate())) {
      age--;
    }
    
    document.getElementById('age').value = age > 0 ? age : '';
  });

  // Password strength checker
  document.getElementById('password').addEventListener('input', function() {
    const password = this.value;
    const strengthBar = document.getElementById('passwordStrengthBar');
    const strengthText = document.getElementById('passwordStrengthText');
    
    let strength = 0;
    if (password.length >= 6) strength++;
    if (password.length >= 10) strength++;
    if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
    if (/\d/.test(password)) strength++;
    if (/[^a-zA-Z\d]/.test(password)) strength++;

    strengthBar.className = 'password-strength-bar';
    strengthText.style.display = 'flex';
    
    if (strength <= 2) {
      strengthBar.classList.add('strength-weak');
      strengthText.className = 'input-feedback error';
      strengthText.innerHTML = '<i class="fas fa-exclamation-triangle"></i><span>Weak password</span>';
    } else if (strength <= 3) {
      strengthBar.classList.add('strength-medium');
      strengthText.className = 'input-feedback checking';
      strengthText.innerHTML = '<i class="fas fa-info-circle"></i><span>Medium password</span>';
    } else {
      strengthBar.classList.add('strength-strong');
      strengthText.className = 'input-feedback success';
      strengthText.innerHTML = '<i class="fas fa-check-circle"></i><span>Strong password</span>';
    }
    
    if (password.length === 0) {
      strengthText.style.display = 'none';
      strengthBar.className = 'password-strength-bar';
    }
  });

  // Confirm password validation
  document.getElementById('confirmPassword').addEventListener('input', function() {
    const password = document.getElementById('password').value;
    const confirmPassword = this.value;
    const feedback = document.getElementById('confirmPasswordFeedback');
    
    if (confirmPassword.length === 0) {
      feedback.style.display = 'none';
      this.classList.remove('error', 'success');
      return;
    }
    
    feedback.style.display = 'flex';
    
    if (password === confirmPassword) {
      feedback.className = 'input-feedback success';
      feedback.innerHTML = '<i class="fas fa-check-circle"></i><span>Passwords match</span>';
      this.classList.add('success');
      this.classList.remove('error');
    } else {
      feedback.className = 'input-feedback error';
      feedback.innerHTML = '<i class="fas fa-times-circle"></i><span>Passwords do not match</span>';
      this.classList.add('error');
      this.classList.remove('success');
    }
  });

  // Update submit button state
  function updateSubmitButton() {
    const submitBtn = document.getElementById('submitBtn');
    const username = document.getElementById('username').value.trim();
    
    if (username.length >= 3 && !isUsernameAvailable) {
      submitBtn.disabled = true;
    } else {
      submitBtn.disabled = false;
    }
  }

  // Form validation on submit
  document.getElementById('registrationForm').addEventListener('submit', function(e) {
    const username = document.getElementById('username').value.trim();
    
    if (username.length >= 3 && !isUsernameAvailable) {
      e.preventDefault();
      alert('Please choose an available username before submitting.');
      return false;
    }
  });
</script>

</body>
</html>