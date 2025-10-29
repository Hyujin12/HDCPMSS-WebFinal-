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
        $existingUser = $usersCollection->findOne(['email' => strtolower($email)]);
        if ($existingUser) {
            $error = "Email already registered.";
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Register - Halili Dental Clinic</title>
<script src="https://cdn.tailwindcss.com"></script>
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
<body class="min-h-screen flex flex-col md:flex-row">

  <!-- Left Side Image / Branding -->
  <div class="hidden md:flex md:w-1/2 gradient-bg items-center justify-center text-white p-10">
    <div class="max-w-md text-center">
      <img src="/images/logodental.png" alt="Clinic Logo" class="w-24 h-24 mx-auto mb-6 drop-shadow-lg">
      <h1 class="text-4xl font-bold mb-4">Halili's Dental Clinic</h1>
      <p class="text-lg leading-relaxed">
        Creating beautiful smiles through modern dental care.
      </p>
    </div>
  </div>

  <!-- Right Side Registration Form -->
  <!-- Right Section (Form) -->
    <div class="flex justify-center items-center w-full md:w-1/2 p-6">
      <form method="POST" class="w-full max-w-md bg-white shadow-2xl rounded-2xl p-6 space-y-6">

        <h2 class="text-3xl font-bold text-center text-blue-600 mb-4">Create Your Account</h2>

        <?php if (!empty($error)) : ?>
          <div class="text-red-600 bg-red-50 p-3 rounded-lg font-semibold text-center"><?= htmlspecialchars($error) ?></div>
        <?php elseif (!empty($success)) : ?>
          <div class="text-green-600 bg-green-50 p-3 rounded-lg font-semibold text-center"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <!-- Grid layout for compact fitting -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

          <div>
            <label class="block text-sm font-semibold mb-1">Full Name</label>
            <input type="text" name="username" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" class="w-full border p-2 rounded focus:ring-2 focus:ring-blue-400" required>
          </div>

          <div>
            <label class="block text-sm font-semibold mb-1">Email</label>
            <input type="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" class="w-full border p-2 rounded focus:ring-2 focus:ring-blue-400" required>
          </div>

          <div>
            <label class="block text-sm font-semibold mb-1">Contact Number</label>
            <input type="text" name="contact_number" value="<?= htmlspecialchars($_POST['contact_number'] ?? '') ?>" class="w-full border p-2 rounded focus:ring-2 focus:ring-blue-400" required>
          </div>

          <div>
            <label class="block text-sm font-semibold mb-1">Address</label>
            <input type="text" name="address" value="<?= htmlspecialchars($_POST['address'] ?? '') ?>" class="w-full border p-2 rounded focus:ring-2 focus:ring-blue-400" required>
          </div>

          <div>
            <label class="block text-sm font-semibold mb-1">Age</label>
            <input type="number" name="age" value="<?= htmlspecialchars($_POST['age'] ?? '') ?>" class="w-full border p-2 rounded focus:ring-2 focus:ring-blue-400" required>
          </div>

          <div>
            <label class="block text-sm font-semibold mb-1">Birthday</label>
            <input type="date" name="birthday" value="<?= htmlspecialchars($_POST['birthday'] ?? '') ?>" class="w-full border p-2 rounded focus:ring-2 focus:ring-blue-400" required>
          </div>

          <div>
            <label class="block text-sm font-semibold mb-1">Status</label>
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
            <label class="block text-sm font-semibold mb-1">Gender</label>
            <select name="gender" class="w-full border p-2 rounded focus:ring-2 focus:ring-blue-400" required>
              <option value="">Select</option>
              <option value="Male" <?= (($_POST['gender'] ?? '') === 'Male') ? 'selected' : '' ?>>Male</option>
              <option value="Female" <?= (($_POST['gender'] ?? '') === 'Female') ? 'selected' : '' ?>>Female</option>
              <option value="Other" <?= (($_POST['gender'] ?? '') === 'Other') ? 'selected' : '' ?>>Other</option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-semibold mb-1">Nationality</label>
            <select name="nationality" class="w-full border p-2 rounded focus:ring-2 focus:ring-blue-400" required>
              <option value="">Select</option>
              <option value="Filipino" <?= (($_POST['nationality'] ?? '') === 'Filipino') ? 'selected' : '' ?>>Filipino</option>
              <option value="Foreign National" <?= (($_POST['nationality'] ?? '') === 'Foreign National') ? 'selected' : '' ?>>Foreign National</option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-semibold mb-1">Occupation</label>
            <input type="text" name="occupation" value="<?= htmlspecialchars($_POST['occupation'] ?? '') ?>" class="w-full border p-2 rounded focus:ring-2 focus:ring-blue-400" required>
          </div>

          <div>
            <label class="block text-sm font-semibold mb-1">Password</label>
            <input type="password" name="password" class="w-full border p-2 rounded focus:ring-2 focus:ring-blue-400" required>
          </div>

          <div>
            <label class="block text-sm font-semibold mb-1">Confirm Password</label>
            <input type="password" name="confirm_password" class="w-full border p-2 rounded focus:ring-2 focus:ring-blue-400" required>
          </div>
        </div>

        <button type="submit" class="w-full bg-blue-600 text-white py-3 rounded-lg font-bold hover:bg-blue-700 transition">
          Register
        </button>

        <p class="text-center text-gray-700">
          Already have an account?
          <a href="log-in.php" class="text-blue-600 hover:underline font-semibold">Log in</a>
        </p>

      </form>
    </div>
  </div>

</body>
</html>