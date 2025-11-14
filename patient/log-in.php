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
        } elseif (empty($user['isVerified']) || !$user['isVerified']) {
            $error = "Please verify your email first.";
        } else {
            $_SESSION['email'] = $email;
            $_SESSION['username'] = $user['username'];
            header("Location: dashboard.php");
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Log In - Halili Dental Clinic</title>
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
  .branding-bg {
    background-image: linear-gradient(135deg, rgba(102, 126, 234, 0.85) 0%, rgba(118, 75, 162, 0.85) 100%), 
                      url('./images/halilibackground.jpg');
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
  }
</style>
</head>
<body class="min-h-screen flex flex-col md:flex-row">

  <!-- Left Side Login Form -->
  <div class="flex-1 flex items-center justify-center bg-gray-50 p-8 order-2 md:order-1">
    <form method="POST" class="w-full max-w-md bg-white shadow-2xl rounded-2xl p-8">
      <div class="text-center mb-6">
        <img src="/images/newlogohalili.png" alt="Halili Logo" class="w-20 h-20 mx-auto mb-4">
        <h2 class="text-2xl font-bold gradient-text mb-2">Welcome Back</h2>
        <p class="text-gray-600 text-sm">Please log in to access your dashboard</p>
      </div>

      <?php if (!empty($error)) : ?>
        <div class="mb-4 bg-red-100 text-red-700 font-medium p-3 rounded-md text-center">
          <?= htmlspecialchars($error) ?>
        </div>
      <?php endif; ?>

      <div class="mb-4">
        <label class="block font-semibold mb-2 text-gray-700">Email</label>
        <input type="email" name="email" required
          class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 transition">
      </div>

      <div class="mb-6">
        <label class="block font-semibold mb-2 text-gray-700">Password</label>
        <input type="password" name="password" required
          class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 transition">
      </div>

      <button type="submit"
        class="w-full gradient-bg text-white py-3 rounded-lg font-bold hover:opacity-90 transition duration-300 shadow-md">
        Log In
      </button>

      <p class="mt-6 text-center text-gray-700">
        Don't have an account? 
        <a href="register.php" class="text-purple-600 font-semibold hover:underline">Register</a>
      </p>
    </form>
  </div>

  <!-- Right Side Image / Branding -->
  <div class="hidden md:flex md:w-1/2 branding-bg items-center justify-center text-white p-10 order-1 md:order-2">
    <div class="max-w-md text-center">
      <img src="/images/newlogohalili.png" alt="Clinic Logo" class="w-24 h-24 mx-auto mb-6 drop-shadow-lg">
      <h1 class="text-4xl font-bold mb-4 drop-shadow-lg">Halili's Dental Clinic</h1>
      <p class="text-lg leading-relaxed drop-shadow-lg">
        Excellence in Dental Care. Bringing you the best smiles with comfort and care.
      </p>
    </div>
  </div>

</body>
</html>