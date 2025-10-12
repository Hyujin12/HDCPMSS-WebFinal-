<?php
session_start();
require __DIR__ . '/../vendor/autoload.php';

use MongoDB\Client;
use Dotenv\Dotenv;

// Load .env
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

$mongoClient = new Client($_ENV['MONGO_URI']);
$db = $mongoClient->selectDatabase('HaliliDentalClinic');
$usersCollection = $db->selectCollection('users');

$error = '';
$success = '';

// Send email via Resend
function sendVerificationEmail($email, $username, $code) {
    $apiKey = $_ENV['RESEND_API_KEY'];
    $url = "https://api.resend.com/emails";

    $data = [
        "from" => "no-reply@halilidentalclinic.com",
        "to" => [$email],
        "subject" => "Your Halili Dental Clinic Verification Code",
        "html" => "<p>Hi " . htmlspecialchars($username) . ",</p>
                   <p>Your verification code is: <strong>$code</strong></p>
                   <p>Please enter this code on the verification page to activate your account.</p>"
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
    curl_close($ch);

    return $httpCode >= 200 && $httpCode < 300;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $mobileNumber = trim($_POST['mobileNumber'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    // Validation
    if (!$username || !$email || !$mobileNumber || !$password || !$confirmPassword) {
        $error = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email address.";
    } elseif (!preg_match('/^\d+$/', $mobileNumber)) {
        $error = "Mobile number must be digits only.";
    } elseif ($password !== $confirmPassword) {
        $error = "Passwords do not match.";
    } else {
        $existingUser = $usersCollection->findOne(['email' => $email]);
        if ($existingUser) {
            $error = "Email already registered.";
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $verificationCode = random_int(100000, 999999);

            $insertResult = $usersCollection->insertOne([
                'username' => $username,
                'email' => $email,
                'mobileNumber' => (int)$mobileNumber,
                'password' => $hashedPassword,
                'isVerified' => false,
                'verificationCode' => (string)$verificationCode,
                'codeExpires' => new MongoDB\BSON\UTCDateTime((time() + 900) * 1000) // 15 min
            ]);

            if ($insertResult->getInsertedCount() === 1) {
                if (sendVerificationEmail($email, $username, $verificationCode)) {
                    header("Location: verify-code.php?email=" . urlencode($email));
                    exit;
                } else {
                    $error = "Failed to send verification email.";
                }
            } else {
                $error = "Registration failed. Try again.";
            }
        }
    }
}
?>

<!-- HTML form -->
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Register - Halili Dental Clinic</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="flex items-center justify-center min-h-screen bg-white">

<form method="POST" class="max-w-md w-full p-8 border rounded-lg shadow-lg">
  <h2 class="text-2xl font-bold mb-6 text-center">Register</h2>

  <?php if (!empty($error)) : ?>
    <div class="mb-4 text-red-600 font-semibold"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <label class="block mb-2 font-semibold">Username</label>
  <input type="text" name="username" class="w-full p-2 border rounded mb-4" required>

  <label class="block mb-2 font-semibold">Email</label>
  <input type="email" name="email" class="w-full p-2 border rounded mb-4" required>

  <label class="block mb-2 font-semibold">Mobile Number</label>
  <input type="text" name="mobileNumber" class="w-full p-2 border rounded mb-4" required>

  <label class="block mb-2 font-semibold">Password</label>
  <input type="password" name="password" class="w-full p-2 border rounded mb-4" required>

  <label class="block mb-2 font-semibold">Confirm Password</label>
  <input type="password" name="confirm_password" class="w-full p-2 border rounded mb-6" required>

  <button type="submit" class="w-full bg-blue-600 text-white py-3 rounded font-bold hover:bg-blue-700 transition">
    Register
  </button>

  <p class="mt-6 text-center text-gray-700">
    Already have an account? <a href="log-in.php" class="text-blue-600 hover:underline">Log in</a>
  </p>
</form>

</body>
</html>