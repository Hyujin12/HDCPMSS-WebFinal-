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
    $termsAccepted = isset($_POST['terms_accepted']);

    if (!$termsAccepted) {
        $error = "You must accept the Terms and Conditions to register.";
    } elseif (!$username || !$email || !$password || !$confirmPassword || !$contactNumber || !$address || !$age || !$status || !$birthday || !$gender || !$nationality || !$occupation) {
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
  .gradient-bg {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  }
  .gradient-text {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
  }
  .branding-bg {
    background-image: linear-gradient(135deg, rgba(102, 126, 234, 0.85) 0%, rgba(118, 75, 162, 0.85) 100%), 
                      url('/images/halilibackground.jpg');
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
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

  .input-feedback.info {
    color: #3b82f6;
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

  .input-success {
    border-color: #10b981 !important;
    background-color: #f0fdf4 !important;
  }

  .input-error {
    border-color: #ef4444 !important;
    background-color: #fef2f2 !important;
  }

  /* Modal styles */
  .modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0, 0, 0, 0.5);
    animation: fadeIn 0.3s ease-in-out;
  }

  .modal.active {
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .modal-content {
    background-color: #fefefe;
    margin: auto;
    padding: 0;
    border-radius: 12px;
    width: 90%;
    max-width: 700px;
    max-height: 90vh;
    display: flex;
    flex-direction: column;
    animation: slideIn 0.3s ease-out;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
  }

  .modal-header {
    padding: 20px 24px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 12px 12px 0 0;
    display: flex;
    justify-content: space-between;
    align-items: center;
  }

  .modal-body {
    padding: 24px;
    overflow-y: auto;
    flex: 1;
  }

  .modal-footer {
    padding: 16px 24px;
    border-top: 1px solid #e5e7eb;
    display: flex;
    justify-content: flex-end;
  }

  .close {
    color: white;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
    transition: all 0.3s;
    line-height: 1;
  }

  .close:hover,
  .close:focus {
    transform: rotate(90deg);
  }

  @keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
  }

  @keyframes slideIn {
    from {
      transform: translateY(-50px);
      opacity: 0;
    }
    to {
      transform: translateY(0);
      opacity: 1;
    }
  }

  .terms-content h3 {
    color: #667eea;
    font-size: 1.1rem;
    font-weight: 600;
    margin-top: 1.5rem;
    margin-bottom: 0.5rem;
  }

  .terms-content h3:first-child {
    margin-top: 0;
  }

  .terms-content p, .terms-content ul {
    margin-bottom: 1rem;
    line-height: 1.6;
    color: #374151;
  }

  .terms-content ul {
    list-style-type: disc;
    padding-left: 1.5rem;
  }

  .terms-content ul li {
    margin-bottom: 0.5rem;
  }

  .checkbox-wrapper {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
    padding: 1rem;
    background-color: #f9fafb;
    border-radius: 8px;
    border: 2px solid #e5e7eb;
    transition: all 0.3s;
  }

  .checkbox-wrapper:hover {
    border-color: #667eea;
  }

  .checkbox-wrapper.error {
    border-color: #ef4444;
    background-color: #fef2f2;
    animation: shake 0.5s;
  }

  @keyframes shake {
    0%, 100% { transform: translateX(0); }
    25% { transform: translateX(-10px); }
    75% { transform: translateX(10px); }
  }

  .checkbox-wrapper input[type="checkbox"] {
    width: 20px;
    height: 20px;
    cursor: pointer;
    margin-top: 2px;
    flex-shrink: 0;
  }

  .checkbox-wrapper label {
    cursor: pointer;
    color: #374151;
    line-height: 1.5;
  }

  .checkbox-wrapper label a {
    color: #667eea;
    text-decoration: underline;
    font-weight: 600;
  }

  .checkbox-wrapper label a:hover {
    color: #764ba2;
  }
</style>
</head>
<body class="min-h-screen flex flex-col md:flex-row">

  <!-- Left Side Image / Branding -->
  <div class="hidden md:flex md:w-1/2 branding-bg items-center justify-center text-white p-10 order-1 md:order-1">
    <div class="max-w-md text-center">
      <img src="/images/newlogohalili.png" alt="Clinic Logo" class="w-24 h-24 mx-auto mb-6 drop-shadow-lg">
      <h1 class="text-4xl font-bold mb-4 drop-shadow-lg">Halili Dental Clinic</h1>
      <p class="text-lg leading-relaxed drop-shadow-lg">
        Excellence in Dental Care. Bringing you the best smiles with comfort and care.
      </p>
    </div>
  </div>

  <!-- Right Side Registration Form -->
  <div class="flex justify-center items-center w-full md:w-1/2 p-6 order-2 md:order-2">
    <form method="POST" id="registrationForm" class="w-full max-w-md bg-white shadow-2xl rounded-2xl p-6 space-y-6">

      <h2 class="text-3xl font-bold text-center text-blue-600 mb-4">Create Your Account</h2>

      <?php if (!empty($error)) : ?>
        <div class="text-red-600 bg-red-50 p-3 rounded-lg font-semibold text-center"><?= htmlspecialchars($error) ?></div>
      <?php elseif (!empty($success)) : ?>
        <div class="text-green-600 bg-green-50 p-3 rounded-lg font-semibold text-center"><?= htmlspecialchars($success) ?></div>
      <?php endif; ?>

      <!-- Grid layout for compact fitting -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

        <div>
          <label class="block text-sm font-semibold mb-1">
            <i class="fas fa-user text-blue-600 mr-2"></i>Full Name
          </label>
          <input type="text" 
                 name="username" 
                 id="username"
                 value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" 
                 class="w-full border p-2 rounded focus:ring-2 focus:ring-blue-400" 
                 required>
          <div id="usernameCheck" class="input-feedback" style="display: none;"></div>
        </div>

        <div>
          <label class="block text-sm font-semibold mb-1">
            <i class="fas fa-envelope text-blue-600 mr-2"></i>Email
          </label>
          <input type="email" 
                 name="email" 
                 id="email"
                 value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" 
                 class="w-full border p-2 rounded focus:ring-2 focus:ring-blue-400" 
                 required>
          <div id="emailCheck" class="input-feedback" style="display: none;"></div>
        </div>

        <div>
          <label class="block text-sm font-semibold mb-1">
            <i class="fas fa-phone text-blue-600 mr-2"></i>Contact Number
          </label>
          <input type="text" name="contact_number" value="<?= htmlspecialchars($_POST['contact_number'] ?? '') ?>" class="w-full border p-2 rounded focus:ring-2 focus:ring-blue-400" required>
        </div>

        <div>
          <label class="block text-sm font-semibold mb-1">
            <i class="fas fa-map-marker-alt text-blue-600 mr-2"></i>Address
          </label>
          <input type="text" name="address" value="<?= htmlspecialchars($_POST['address'] ?? '') ?>" class="w-full border p-2 rounded focus:ring-2 focus:ring-blue-400" required>
        </div>

        <div>
          <label class="block text-sm font-semibold mb-1">
            <i class="fas fa-calendar text-blue-600 mr-2"></i>Birthday
          </label>
          <input type="date" 
                 name="birthday" 
                 id="birthday"
                 value="<?= htmlspecialchars($_POST['birthday'] ?? '') ?>" 
                 class="w-full border p-2 rounded focus:ring-2 focus:ring-blue-400" 
                 required>
        </div>

        <div>
          <label class="block text-sm font-semibold mb-1">
            <i class="fas fa-hashtag text-blue-600 mr-2"></i>Age
          </label>
          <input type="number" 
                 name="age" 
                 id="age"
                 value="<?= htmlspecialchars($_POST['age'] ?? '') ?>" 
                 class="w-full border p-2 rounded focus:ring-2 focus:ring-blue-400 bg-gray-100"
                 readonly
                 required>
        </div>

        <div>
          <label class="block text-sm font-semibold mb-1">
            <i class="fas fa-heart text-blue-600 mr-2"></i>Status
          </label>
          <select name="status" class="w-full border p-2 rounded focus:ring-2 focus:ring-blue-400" required>
            <option value="">Select</option>
            <?php
            $statuses = ['Single', 'Married', 'Separated', 'Widowed', 'Divorced', 'Complicated'];
            foreach ($statuses as $s) {
              $selected = (($_POST['status'] ?? '') === $s) ? 'selected' : '';
              echo "<option value='$s' $selected>$s</option>";
            }
            ?>
          </select>
        </div>

        <div>
          <label class="block text-sm font-semibold mb-1">
            <i class="fas fa-venus-mars text-blue-600 mr-2"></i>Gender
          </label>
          <select name="gender" class="w-full border p-2 rounded focus:ring-2 focus:ring-blue-400" required>
            <option value="">Select</option>
            <option value="Male" <?= (($_POST['gender'] ?? '') === 'Male') ? 'selected' : '' ?>>Male</option>
            <option value="Female" <?= (($_POST['gender'] ?? '') === 'Female') ? 'selected' : '' ?>>Female</option>
            <option value="Other" <?= (($_POST['gender'] ?? '') === 'Other') ? 'selected' : '' ?>>Other</option>
          </select>
        </div>

        <div>
          <label class="block text-sm font-semibold mb-1">
            <i class="fas fa-flag text-blue-600 mr-2"></i>Nationality
          </label>
          <select name="nationality" class="w-full border p-2 rounded focus:ring-2 focus:ring-blue-400" required>
            <option value="">Select</option>
            <option value="Filipino" <?= (($_POST['nationality'] ?? '') === 'Filipino') ? 'selected' : '' ?>>Filipino</option>
            <option value="Foreign National" <?= (($_POST['nationality'] ?? '') === 'Foreign National') ? 'selected' : '' ?>>Foreign National</option>
          </select>
        </div>

        <div>
          <label class="block text-sm font-semibold mb-1">
            <i class="fas fa-briefcase text-blue-600 mr-2"></i>Occupation
          </label>
          <input type="text" name="occupation" value="<?= htmlspecialchars($_POST['occupation'] ?? '') ?>" class="w-full border p-2 rounded focus:ring-2 focus:ring-blue-400" required>
        </div>

        <div>
          <label class="block text-sm font-semibold mb-1">
            <i class="fas fa-lock text-blue-600 mr-2"></i>Password
          </label>
          <div class="relative">
            <input type="password" 
                  name="password" 
                  id="password"
                  class="w-full border p-2 pr-10 rounded focus:ring-2 focus:ring-blue-400" 
                  required>
            <button type="button" 
                    onclick="togglePassword('password', 'togglePasswordIcon')"
                    class="absolute right-2 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700 focus:outline-none">
              <i id="togglePasswordIcon" class="fas fa-eye"></i>
            </button>
          </div>
          <div id="passwordCheck" class="input-feedback" style="display: none;"></div>
        </div>

        <div>
          <label class="block text-sm font-semibold mb-1">
            <i class="fas fa-check-circle text-blue-600 mr-2"></i>Confirm Password
          </label>
          <div class="relative">
            <input type="password" 
                  name="confirm_password" 
                  id="confirm_password"
                  class="w-full border p-2 pr-10 rounded focus:ring-2 focus:ring-blue-400" 
                  required>
            <button type="button" 
                    onclick="togglePassword('confirm_password', 'toggleConfirmPasswordIcon')"
                    class="absolute right-2 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700 focus:outline-none">
              <i id="toggleConfirmPasswordIcon" class="fas fa-eye"></i>
            </button>
          </div>
          <div id="confirmPasswordCheck" class="input-feedback" style="display: none;"></div>
        </div>

      </div>

      <!-- Terms and Conditions Checkbox -->
      <div class="checkbox-wrapper" id="termsCheckboxWrapper">
        <input type="checkbox" name="terms_accepted" id="terms_accepted" value="1">
        <label for="terms_accepted">
          I agree to the <a href="#" id="openTermsModal">Terms and Conditions</a> and Privacy Policy
        </label>
      </div>
      <div id="termsError" class="input-feedback error" style="display: none;">
        <i class="fas fa-exclamation-circle"></i>
        <span>You must accept the Terms and Conditions to register</span>
      </div>

      <button type="submit" class="w-full bg-blue-600 text-white py-3 rounded-lg font-bold hover:bg-blue-700 transition" id="submitBtn">
        Register
      </button>

      <p class="text-center text-gray-700">
        Already have an account?
        <a href="log-in.php" class="text-blue-600 hover:underline font-semibold">Log in</a>
      </p>

    </form>
  </div>

  <!-- Terms and Conditions Modal -->
  <div id="termsModal" class="modal">
    <div class="modal-content">
      <div class="modal-header">
        <h2 class="text-2xl font-bold">Terms and Conditions</h2>
        <span class="close" id="closeModal">&times;</span>
      </div>
      <div class="modal-body">
        <div class="terms-content">
          <h3>1. Acceptance of Terms</h3>
          <p>By creating an account with Halili Dental Clinic, you agree to be bound by these Terms and Conditions. If you do not agree to these terms, please do not register or use our services.</p>

          <h3>2. User Account</h3>
          <p>You are responsible for:</p>
          <ul>
            <li>Maintaining the confidentiality of your account credentials</li>
            <li>All activities that occur under your account</li>
            <li>Providing accurate and complete information during registration</li>
            <li>Updating your information to keep it current and accurate</li>
          </ul>

          <h3>3. Privacy and Data Protection</h3>
          <p>We collect and process your personal information in accordance with applicable data privacy laws. Your information will be used for:</p>
          <ul>
            <li>Providing dental services and appointment management</li>
            <li>Communication regarding your appointments and treatment</li>
            <li>Maintaining your medical records</li>
            <li>Improving our services</li>
          </ul>

          <h3>4. Appointment Policy</h3>
          <ul>
            <li>Appointments must be cancelled at least 24 hours in advance</li>
            <li>Late cancellations or no-shows may incur charges</li>
            <li>We reserve the right to cancel appointments due to emergencies or unforeseen circumstances</li>
          </ul>

          <h3>5. Medical Information</h3>
          <p>You agree to provide accurate medical history and health information. Failure to disclose relevant medical information may affect your treatment and our ability to provide appropriate care.</p>

          <h3>6. Payment Terms</h3>
          <ul>
            <li>Payment is due at the time of service unless other arrangements have been made</li>
            <li>We accept various payment methods as displayed at our clinic</li>
            <li>Outstanding balances may be subject to collection procedures</li>
          </ul>

          <h3>7. Limitation of Liability</h3>
          <p>Halili Dental Clinic shall not be liable for any indirect, incidental, special, or consequential damages arising from the use of our services or website.</p>

          <h3>8. Changes to Terms</h3>
          <p>We reserve the right to modify these terms at any time. Continued use of our services after changes constitutes acceptance of the modified terms.</p>

          <h3>9. Contact Information</h3>
          <p>For questions about these Terms and Conditions, please contact us at our clinic or through the contact information provided on our website.</p>

          <p class="mt-6 text-sm text-gray-600">Last Updated: December 2024</p>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" id="acceptTermsBtn" class="bg-blue-600 text-white px-6 py-2 rounded-lg font-semibold hover:bg-blue-700 transition">
          Accept & Close
        </button>
      </div>
    </div>
  </div>

<script>
  let usernameCheckTimeout;
  let emailCheckTimeout;
  let isUsernameAvailable = false;
  let isEmailAvailable = false;
  let isPasswordValid = false;
  let doPasswordsMatch = false;

  // Modal functionality
  const termsModal = document.getElementById('termsModal');
  const openTermsModalLink = document.getElementById('openTermsModal');
  const closeModalBtn = document.getElementById('closeModal');
  const acceptTermsBtn = document.getElementById('acceptTermsBtn');
  const termsCheckbox = document.getElementById('terms_accepted');
  const termsCheckboxWrapper = document.getElementById('termsCheckboxWrapper');
  const termsError = document.getElementById('termsError');

  openTermsModalLink.addEventListener('click', function(e) {
    e.preventDefault();
    termsModal.classList.add('active');
    document.body.style.overflow = 'hidden';
  });

  closeModalBtn.addEventListener('click', function() {
    termsModal.classList.remove('active');
    document.body.style.overflow = 'auto';
  });

  acceptTermsBtn.addEventListener('click', function() {
    termsCheckbox.checked = true;
    termsCheckboxWrapper.classList.remove('error');
    termsError.style.display = 'none';
    termsModal.classList.remove('active');
    document.body.style.overflow = 'auto';
  });

  // Close modal when clicking outside
  window.addEventListener('click', function(e) {
    if (e.target === termsModal) {
      termsModal.classList.remove('active');
      document.body.style.overflow = 'auto';
    }
  });

  // Username availability check
  document.getElementById('username').addEventListener('input', function() {
    const username = this.value.trim();
    const feedback = document.getElementById('usernameCheck');
    const input = this;

    if (username.length < 3) {
      feedback.style.display = 'none';
      input.classList.remove('input-error', 'input-success');
      isUsernameAvailable = false;
      updateSubmitButton();
      return;
    }

    clearTimeout(usernameCheckTimeout);
    
    feedback.style.display = 'flex';
    feedback.className = 'input-feedback checking';
    feedback.innerHTML = '<div class="spinner"></div><span>Checking availability...</span>';
    input.classList.remove('input-error', 'input-success');

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
          input.classList.add('input-success');
          input.classList.remove('input-error');
          isUsernameAvailable = true;
        } else {
          feedback.className = 'input-feedback error';
          feedback.innerHTML = '<i class="fas fa-times-circle"></i><span>Username already taken</span>';
          input.classList.add('input-error');
          input.classList.remove('input-success');
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

  // Email availability check
  document.getElementById('email').addEventListener('input', function() {
    const email = this.value.trim();
    const feedback = document.getElementById('emailCheck');
    const input = this;

    // Basic email validation regex
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    if (!email) {
      feedback.style.display = 'none';
      input.classList.remove('input-error', 'input-success');
      isEmailAvailable = false;
      updateSubmitButton();
      return;
    }

    if (!emailRegex.test(email)) {
      feedback.style.display = 'flex';
      feedback.className = 'input-feedback error';
      feedback.innerHTML = '<i class="fas fa-times-circle"></i><span>Invalid email format</span>';
      input.classList.add('input-error');
      input.classList.remove('input-success');
      isEmailAvailable = false;
      updateSubmitButton();
      return;
    }

    clearTimeout(emailCheckTimeout);
    
    feedback.style.display = 'flex';
    feedback.className = 'input-feedback checking';
    feedback.innerHTML = '<div class="spinner"></div><span>Checking availability...</span>';
    input.classList.remove('input-error', 'input-success');

    emailCheckTimeout = setTimeout(async () => {
      try {
        const response = await fetch('check-email.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ email: email })
        });

        const data = await response.json();

        if (data.available) {
          feedback.className = 'input-feedback success';
          feedback.innerHTML = '<i class="fas fa-check-circle"></i><span>Email available!</span>';
          input.classList.add('input-success');
          input.classList.remove('input-error');
          isEmailAvailable = true;
        } else {
          feedback.className = 'input-feedback error';
          feedback.innerHTML = '<i class="fas fa-times-circle"></i><span>Email already registered</span>';
          input.classList.add('input-error');
          input.classList.remove('input-success');
          isEmailAvailable = false;
        }
      } catch (error) {
        feedback.className = 'input-feedback error';
        feedback.innerHTML = '<i class="fas fa-exclamation-circle"></i><span>Error checking email</span>';
        isEmailAvailable = false;
      }
      
      updateSubmitButton();
    }, 500);
  });

  // Password validation
  document.getElementById('password').addEventListener('input', function() {
    const password = this.value;
    const feedback = document.getElementById('passwordCheck');
    const input = this;

    if (!password) {
      feedback.style.display = 'none';
      input.classList.remove('input-error', 'input-success');
      isPasswordValid = false;
      updateSubmitButton();
      return;
    }

    feedback.style.display = 'flex';

    // Password requirements
    const hasMinLength = password.length >= 6;
    const hasUpperCase = /[A-Z]/.test(password);
    const hasLowerCase = /[a-z]/.test(password);
    const hasNumber = /[0-9]/.test(password);
    const hasSpecialChar = /[!@#$%^&*(),.?":{}|<>]/.test(password);

    // Show strength indicator
    const strength = [hasMinLength, hasUpperCase, hasLowerCase, hasNumber, hasSpecialChar].filter(Boolean).length;

    if (password.length < 6) {
      feedback.className = 'input-feedback error';
      feedback.innerHTML = '<i class="fas fa-times-circle"></i><span>Password must be at least 6 characters</span>';
      input.classList.add('input-error');
      input.classList.remove('input-success');
      isPasswordValid = false;
    } else if (strength < 3) {
      feedback.className = 'input-feedback info';
      feedback.innerHTML = '<i class="fas fa-info-circle"></i><span>Weak password. Add uppercase, numbers, or symbols</span>';
      input.classList.remove('input-error', 'input-success');
      isPasswordValid = true;
    } else if (strength < 4) {
      feedback.className = 'input-feedback success';
      feedback.innerHTML = '<i class="fas fa-check-circle"></i><span>Medium strength password</span>';
      input.classList.add('input-success');
      input.classList.remove('input-error');
      isPasswordValid = true;
    } else {
      feedback.className = 'input-feedback success';
      feedback.innerHTML = '<i class="fas fa-check-circle"></i><span>Strong password!</span>';
      input.classList.add('input-success');
      input.classList.remove('input-error');
      isPasswordValid = true;
    }

    updateSubmitButton();
    checkPasswordMatch();
  });

  // Confirm password validation
  document.getElementById('confirm_password').addEventListener('input', checkPasswordMatch);

  function checkPasswordMatch() {
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    const feedback = document.getElementById('confirmPasswordCheck');
    const input = document.getElementById('confirm_password');

    if (!confirmPassword) {
      feedback.style.display = 'none';
      input.classList.remove('input-error', 'input-success');
      doPasswordsMatch = false;
      updateSubmitButton();
      return;
    }

    feedback.style.display = 'flex';

    if (password === confirmPassword) {
      feedback.className = 'input-feedback success';
      feedback.innerHTML = '<i class="fas fa-check-circle"></i><span>Passwords match!</span>';
      input.classList.add('input-success');
      input.classList.remove('input-error');
      doPasswordsMatch = true;
    } else {
      feedback.className = 'input-feedback error';
      feedback.innerHTML = '<i class="fas fa-times-circle"></i><span>Passwords do not match</span>';
      input.classList.add('input-error');
      input.classList.remove('input-success');
      doPasswordsMatch = false;
    }

    updateSubmitButton();
  }

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

  // Update submit button state
  function updateSubmitButton() {
    const submitBtn = document.getElementById('submitBtn');
    const username = document.getElementById('username').value.trim();
    const email = document.getElementById('email').value.trim();
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    
    let shouldDisable = false;
    
    if (username.length >= 3 && !isUsernameAvailable) {
      shouldDisable = true;
    }
    
    if (email && !isEmailAvailable) {
      shouldDisable = true;
    }
    
    if (password && !isPasswordValid) {
      shouldDisable = true;
    }
    
    if (confirmPassword && !doPasswordsMatch) {
      shouldDisable = true;
    }
    
    if (shouldDisable) {
      submitBtn.disabled = true;
      submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
    } else {
      submitBtn.disabled = false;
      submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
    }
  }

  // Form validation on submit
  document.getElementById('registrationForm').addEventListener('submit', function(e) {
    const username = document.getElementById('username').value.trim();
    const email = document.getElementById('email').value.trim();
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    const termsAccepted = document.getElementById('terms_accepted').checked;
    
    // Check terms and conditions first
    if (!termsAccepted) {
      e.preventDefault();
      termsCheckboxWrapper.classList.add('error');
      termsError.style.display = 'flex';
      termsCheckboxWrapper.scrollIntoView({ behavior: 'smooth', block: 'center' });
      
      // Remove error styling after 3 seconds
      setTimeout(() => {
        if (!document.getElementById('terms_accepted').checked) {
          termsCheckboxWrapper.classList.remove('error');
        }
      }, 3000);
      
      return false;
    }
    
    if (username.length >= 3 && !isUsernameAvailable) {
      e.preventDefault();
      alert('Please choose an available username before submitting.');
      return false;
    }
    
    if (email && !isEmailAvailable) {
      e.preventDefault();
      alert('Please use a different email address.');
      return false;
    }
    
    if (password && !isPasswordValid) {
      e.preventDefault();
      alert('Please enter a valid password (at least 6 characters).');
      return false;
    }
    
    if (password !== confirmPassword) {
      e.preventDefault();
      alert('Passwords do not match. Please check and try again.');
      return false;
    }
  });

  // Remove error styling when checkbox is checked
  termsCheckbox.addEventListener('change', function() {
    if (this.checked) {
      termsCheckboxWrapper.classList.remove('error');
      termsError.style.display = 'none';
    }
  });

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