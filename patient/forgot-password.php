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

function sendResetCodeEmail($email, $username, $code)
{
    $apiKey = $_ENV['RESEND_API_KEY'];
    $url = "https://api.resend.com/emails";

    $data = [
        "from" => "Halili Dental Clinic <no-reply@halilidentalclinic.shop>",
        "to" => [$email],
        "subject" => "Password Reset Code - Halili Dental Clinic",
        "html" => "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                <h2 style='color: #667eea;'>Password Reset Request</h2>
                <p>Hello $username,</p>
                <p>You requested to reset your password for your Halili Dental Clinic account.</p>
                <p>Your password reset code is:</p>
                <div style='background-color: #f3f4f6; padding: 20px; border-radius: 8px; text-align: center; margin: 20px 0;'>
                    <h1 style='color: #667eea; font-size: 32px; margin: 0; letter-spacing: 5px;'>$code</h1>
                </div>
                <p><strong>This code will expire in 10 minutes.</strong></p>
                <p>If you did not request a password reset, please ignore this email or contact us if you have concerns.</p>
                <br>
                <p style='color: #6b7280; font-size: 14px;'>
                    Best regards,<br>
                    Halili Dental Clinic Team
                </p>
            </div>"
    ];

    $options = [
        'http' => [
            'header' => [
                "Authorization: Bearer $apiKey",
                "Content-Type: application/json"
            ],
            'method' => 'POST',
            'content' => json_encode($data),
        ]
    ];

    $context = stream_context_create($options);
    $result = @file_get_contents($url, false, $context);

    return $result !== false;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $swal = "
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid Email',
                    text: 'Please enter a valid email address.'
                });
            });
        </script>";
    } else {
        $user = $usersCollection->findOne(['email' => $email]);

        if ($user) {
            // Generate 6-digit reset code
            $resetCode = random_int(100000, 999999);
            $expires = new MongoDB\BSON\UTCDateTime(strtotime("+10 minutes") * 1000);

            // Store reset code in DB
            $usersCollection->updateOne(
                ['email' => $email],
                ['$set' => [
                    'reset_code' => $resetCode, 
                    'reset_expires' => $expires
                ]]
            );

            // Send reset code via Resend
            $username = $user['username'] ?? 'User';
            
            if (sendResetCodeEmail($email, $username, $resetCode)) {
                // Store email in session for the reset page
                $_SESSION['reset_email'] = $email;
                
                $swal = "
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        Swal.fire({
                            icon: 'success',
                            title: 'Reset Code Sent!',
                            text: 'Please check your email for the password reset code.',
                            confirmButtonColor: '#667eea',
                            confirmButtonText: 'Proceed'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.location.href = 'reset-password.php';
                            }
                        });
                    });
                </script>";
            } else {
                $swal = "
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Email Failed',
                            text: 'We could not send the reset code. Please try again later.'
                        });
                    });
                </script>";
            }
        } else {
            $swal = "
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Email Not Found',
                        text: 'No account is registered with this email address.'
                    });
                });
            </script>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Forgot Password - Halili Dental Clinic</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
      <h2 class="text-2xl font-bold gradient-text mb-2">Forgot Password?</h2>
      <p class="text-gray-600 text-sm">Enter your email to receive a reset code</p>
    </div>

    <form method="POST">
      <div class="mb-6">
        <label class="block font-semibold mb-2 text-gray-700">Email Address</label>
        <input type="email" name="email" placeholder="Enter your registered email" 
          class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 transition" required>
      </div>

      <button type="submit" 
        class="w-full gradient-bg text-white py-3 rounded-lg font-bold hover:opacity-90 transition duration-300 shadow-md">
        Send Reset Code
      </button>
    </form>

    <div class="mt-6 text-center space-y-2">
      <p class="text-gray-700">
        <a href="reset-password.php" class="text-purple-600 font-semibold hover:underline">
          Already have a reset code?
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

</body>
</html>