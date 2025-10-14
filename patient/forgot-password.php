<?php
require 'config.php';
require __DIR__ . '/../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;

$swal = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $user = $usersCollection->findOne(['email' => $email]);
//jsut a comment
    if ($user) {
        // Generate 6-digit reset code
        $resetCode = random_int(100000, 999999);
        $expires = new MongoDB\BSON\UTCDateTime(strtotime("+10 minutes") * 1000);

        // Store reset code in DB
        $usersCollection->updateOne(
            ['email' => $email],
            ['$set' => ['reset_code' => $resetCode, 'reset_expires' => $expires]]
        );

        // Send reset code via email
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = "smtp.gmail.com";
        $mail->SMTPAuth = true;
        $mail->Username = "eugenedianito@gmail.com";  // your Gmail
        $mail->Password = "ldoo bdat hahl ybdb";      // app password
        $mail->SMTPSecure = "tls";
        $mail->Port = 587;

        $mail->setFrom("eugenedianito@gmail.com", "Dental Clinic");
        $mail->addAddress($email);
        $mail->Subject = "Password Reset Code";
        $mail->Body = "Your password reset code is: $resetCode\n\nThis code will expire in 10 minutes.";

        if ($mail->send()) {
            // Swal alert + redirect
            $swal = "
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        icon: 'success',
                        title: 'Reset Password Code Sent!',
                        text: 'Please check your email for the reset code.',
                        confirmButtonColor: '#3085d6',
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
                        text: 'We could not send the reset code. Try again later.'
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
                    text: 'No account is registered with this email.'
                });
            });
        </script>";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Forgot Password</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">
<div class="bg-white p-8 rounded-lg shadow-md w-full max-w-md">
  <h2 class="text-2xl font-bold mb-6 text-center">Forgot Password</h2>
  <form method="POST">
    <input type="email" name="email" placeholder="Enter your email" class="w-full p-2 border rounded mb-6" required>
    <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded">Send Reset Code</button>
  </form>
  <p class="mt-4 text-center">
    <a href="reset-password.php" class="text-blue-600">Already have a reset code?</a>
  </p>
</div>

<?= $swal ?>

</body>
</html>
