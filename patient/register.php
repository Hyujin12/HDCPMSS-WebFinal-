<?php
session_start();
require __DIR__ . '/../vendor/autoload.php';

use MongoDB\Client;
use Dotenv\Dotenv;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Load .env configuration
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

$mongoClient = new Client($_ENV['MONGO_URI']);
$db = $mongoClient->selectDatabase('HaliliDentalClinic');
$usersCollection = $db->selectCollection('users');

$error = '';
$success = '';

// ✅ Function: Send verification email using Gmail SMTP
function sendVerificationEmail($email, $username, $code) {
    $mail = new PHPMailer(true);
    try {
        // SMTP configuration
        $mail->isSMTP();
        $mail->Host = $_ENV['SMTP_HOST']; // smtp.gmail.com
        $mail->SMTPAuth = true;
        $mail->Username = $_ENV['SMTP_USER']; // your Gmail
        $mail->Password = $_ENV['SMTP_PASS']; // your App Password
        $mail->SMTPSecure = 'tls';
        $mail->Port = $_ENV['SMTP_PORT']; // 587

        // Sender & recipient
        $mail->setFrom($_ENV['SMTP_USER'], 'Halili Dental Clinic');
        $mail->addAddress($email);

        // Email content
        $mail->isHTML(true);
        $mail->Subject = 'Verify Your Halili Dental Clinic Account';
        $mail->Body = "
            <div style='font-family: Arial, sans-serif;'>
                <h2>Welcome to Halili Dental Clinic, $username!</h2>
                <p>Your verification code is:</p>
                <h3 style='color: #007bff;'>$code</h3>
                <p>This code will expire in 15 minutes.</p>
            </div>
        ";

        $mail->send();
        return true;
    } catch (Exception $e) {
        // Log errors to file
        file_put_contents(__DIR__ . '/../email_log.txt',
            "Email Error: " . $mail->ErrorInfo . "\n", FILE_APPEND);
        return false;
    }
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
                    $error = "Registration successful, but failed to send verification email. Please contact support.";
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

<form method="POST" class="max-w-md w-full p-8 border rounded-lg shadow-lg bg-white">
  <h2 class="text-2xl font-bold mb-6 text-center">Create Your Account</h2>

  <?php if (!empty($error)) : ?>
    <div class="mb-4 text-red-600 font-semibold"><?= htmlspecialchars($error) ?></div>
  <?php elseif (!empty($success)) : ?>
    <div class="mb-4 text-green-600 font-semibold"><?= htmlspecialchars($success) ?></div>
  <?php endif; ?>

  <label class="block mb-2 font-semibold">Username</label>
  <input type="text" name="username" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" class="w-full p-2 border rounded mb-4" required>

  <label class="block mb-2 font-semibold">Email</label>
  <input type="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" class="w-full p-2 border rounded mb-4" required>

  <label class="block mb-2 font-semibold">Mobile Number</label>
  <input type="text" name="mobileNumber" value="<?= htmlspecialchars($_POST['mobileNumber'] ?? '') ?>" class="w-full p-2 border rounded mb-4" required>

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
