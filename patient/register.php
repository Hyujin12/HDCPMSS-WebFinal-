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
    $mobileNumber = trim($_POST['mobileNumber'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    // New fields
    $contactNumber = trim($_POST['contact_number'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $age = (int)($_POST['age'] ?? 0);
    $status = trim($_POST['status'] ?? '');
    $birthday = trim($_POST['birthday'] ?? '');
    $gender = trim($_POST['gender'] ?? '');
    $nationality = trim($_POST['nationality'] ?? '');
    $occupation = trim($_POST['occupation'] ?? '');

    // === Validation ===
    if (!$username || !$email || !$mobileNumber || !$password || !$confirmPassword || !$contactNumber || !$address || !$age || !$status || !$birthday || !$gender || !$nationality || !$occupation) {
        $error = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email address.";
    } elseif (!preg_match('/^[0-9]{10,15}$/', $mobileNumber)) {
        $error = "Mobile number must contain only digits (10–15 digits).";
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
                'mobileNumber' => $mobileNumber,
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
  <title>Register - Halili Dental Clinic</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="flex items-center justify-center min-h-screen bg-white">

<form method="POST" class="max-w-2xl w-full p-8 border rounded-lg shadow-lg bg-white space-y-6">
  <h2 class="text-2xl font-bold mb-4 text-center">Create Your Account</h2>

  <?php if (!empty($error)) : ?>
    <div class="text-red-600 font-semibold"><?= htmlspecialchars($error) ?></div>
  <?php elseif (!empty($success)) : ?>
    <div class="text-green-600 font-semibold"><?= htmlspecialchars($success) ?></div>
  <?php endif; ?>

  <!-- Section 1: Basic Info -->
  <fieldset class="border p-4 rounded space-y-4">
    <legend class="font-semibold text-gray-700">Basic Info</legend>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div>
        <label class="block mb-1 font-semibold">Username</label>
        <input type="text" name="username" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" class="w-full p-2 border rounded" required>
      </div>
      <div>
        <label class="block mb-1 font-semibold">Email</label>
        <input type="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" class="w-full p-2 border rounded" required>
      </div>
      <div>
        <label class="block mb-1 font-semibold">Mobile Number</label>
        <input type="text" name="mobileNumber" value="<?= htmlspecialchars($_POST['mobileNumber'] ?? '') ?>" class="w-full p-2 border rounded" required>
      </div>
      <div>
        <label class="block mb-1 font-semibold">Contact Number</label>
        <input type="text" name="contact_number" value="<?= htmlspecialchars($_POST['contact_number'] ?? '') ?>" class="w-full p-2 border rounded" required>
      </div>
    </div>
  </fieldset>

  <!-- Section 2: Personal Info -->
  <fieldset class="border p-4 rounded space-y-4">
    <legend class="font-semibold text-gray-700">Personal Info</legend>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div>
        <label class="block mb-1 font-semibold">Address</label>
        <input type="text" name="address" value="<?= htmlspecialchars($_POST['address'] ?? '') ?>" class="w-full p-2 border rounded" required>
      </div>
      <div>
        <label class="block mb-1 font-semibold">Age</label>
        <input type="number" name="age" value="<?= htmlspecialchars($_POST['age'] ?? '') ?>" class="w-full p-2 border rounded" required>
      </div>
      <div>
        <label class="block mb-1 font-semibold">Status</label>
        <input type="text" name="status" value="<?= htmlspecialchars($_POST['status'] ?? '') ?>" class="w-full p-2 border rounded" required>
      </div>
      <div>
        <label class="block mb-1 font-semibold">Birthday</label>
        <input type="date" name="birthday" value="<?= htmlspecialchars($_POST['birthday'] ?? '') ?>" class="w-full p-2 border rounded" required>
      </div>
      <div>
        <label class="block mb-1 font-semibold">Gender</label>
        <select name="gender" class="w-full p-2 border rounded" required>
          <option value="">Select Gender</option>
          <option value="Male" <?= (($_POST['gender'] ?? '') === 'Male') ? 'selected' : '' ?>>Male</option>
          <option value="Female" <?= (($_POST['gender'] ?? '') === 'Female') ? 'selected' : '' ?>>Female</option>
          <option value="Other" <?= (($_POST['gender'] ?? '') === 'Other') ? 'selected' : '' ?>>Other</option>
        </select>
      </div>
      <div>
        <label class="block mb-1 font-semibold">Nationality</label>
        <input type="text" name="nationality" value="<?= htmlspecialchars($_POST['nationality'] ?? '') ?>" class="w-full p-2 border rounded" required>
      </div>
      <div>
        <label class="block mb-1 font-semibold">Occupation</label>
        <input type="text" name="occupation" value="<?= htmlspecialchars($_POST['occupation'] ?? '') ?>" class="w-full p-2 border rounded" required>
      </div>
    </div>
  </fieldset>

  <!-- Section 3: Password -->
  <fieldset class="border p-4 rounded space-y-4">
    <legend class="font-semibold text-gray-700">Account Security</legend>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div>
        <label class="block mb-1 font-semibold">Password</label>
        <input type="password" name="password" class="w-full p-2 border rounded" required>
      </div>
      <div>
        <label class="block mb-1 font-semibold">Confirm Password</label>
        <input type="password" name="confirm_password" class="w-full p-2 border rounded" required>
      </div>
    </div>
  </fieldset>

  <button type="submit" class="w-full bg-blue-600 text-white py-3 rounded font-bold hover:bg-blue-700 transition">
    Register
  </button>

  <p class="mt-4 text-center text-gray-700">
    Already have an account? <a href="log-in.php" class="text-blue-600 hover:underline">Log in</a>
  </p>
</form>


</body>
</html>
