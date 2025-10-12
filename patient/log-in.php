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

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$email || !$password) {
        $error = "Please enter both email and password.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } else {
        $user = $usersCollection->findOne(['email' => $email]);

        if (!$user) {
            $error = "No user found with that email.";
        } elseif (!password_verify($password, $user['password'])) {
            $error = "Incorrect password.";
        } elseif (!$user['isVerified']) {
            $error = "Please verify your email first.";
        } else {
            $_SESSION['user_email'] = $email;
            $_SESSION['username'] = $user['username'];
            $success = "Login successful! Redirecting...";

            echo "<script>
                    setTimeout(() => { window.location.href = 'dashboard.php'; }, 1500);
                  </script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Log In - Halili Dental Clinic</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="flex items-center justify-center min-h-screen bg-white">

<form method="POST" class="max-w-md w-full p-8 border rounded-lg shadow-lg">
  <h2 class="text-2xl font-bold mb-6 text-center">Log In</h2>

  <?php if (!empty($error)) : ?>
    <div class="mb-4 text-red-600 font-semibold"><?= htmlspecialchars($error) ?></div>
  <?php elseif (!empty($success)) : ?>
    <div class="mb-4 text-green-600 font-semibold"><?= htmlspecialchars($success) ?></div>
  <?php endif; ?>

  <label class="block mb-2 font-semibold">Email</label>
  <input type="email" name="email" class="w-full p-2 border rounded mb-4" required>

  <label class="block mb-2 font-semibold">Password</label>
  <input type="password" name="password" class="w-full p-2 border rounded mb-6" required>

  <button type="submit" class="w-full bg-blue-600 text-white py-3 rounded font-bold hover:bg-blue-700 transition">
    Log In
  </button>

  <p class="mt-6 text-center text-gray-700">
    Don't have an account? <a href="register.php" class="text-blue-600 hover:underline">Register</a>
  </p>
</form>

</body>
</html>