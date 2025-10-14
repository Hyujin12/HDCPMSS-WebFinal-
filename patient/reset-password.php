<?php
require 'config.php';
require __DIR__ . '/../vendor/autoload.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $code = trim($_POST['code']);
    $newPassword = trim($_POST['new_password']);

    // Strong password validation (server-side)
    $passwordRegex = "/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/";
    if (!preg_match($passwordRegex, $newPassword)) {
        $error = "Password must be at least 8 characters, include uppercase, lowercase, number, and special character.";
    } else {
        $user = $usersCollection->findOne(['email' => $email]);

        if ($user && isset($user['reset_code']) && isset($user['reset_expires'])) {
            $expires = $user['reset_expires']->toDateTime();

            if ($user['reset_code'] == $code && $expires > new DateTime()) {
                // Hash new password
                $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);

                // Update password and remove reset fields
                $usersCollection->updateOne(
                    ['email' => $email],
                    [
                        '$set' => ['password' => $hashedPassword],
                        '$unset' => ['reset_code' => "", 'reset_expires' => ""]
                    ]
                );

                $success = "Password updated successfully!";
            } else {
                $error = "Invalid or expired reset code.";
            }
        } else {
            $error = "No reset request found for this email.";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Reset Password</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script>
    // Client-side password validation feedback
    function validatePassword() {
      const password = document.getElementById("new_password").value;
      const message = document.getElementById("passwordMessage");
      const regex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/;

      if (!regex.test(password)) {
        message.textContent = "❌ Weak password. Must contain 8+ chars, uppercase, lowercase, number, and special char.";
        message.className = "text-red-600 text-sm mt-1";
      } else {
        message.textContent = "✅ Strong password!";
        message.className = "text-green-600 text-sm mt-1";
      }
    }
  </script>
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">

<div class="bg-white p-8 rounded-lg shadow-md w-full max-w-md">
  <h2 class="text-2xl font-bold mb-6 text-center">Reset Password</h2>

  <form method="POST">
    <input type="email" name="email" placeholder="Enter your email"
           class="w-full p-2 border rounded mb-4" required>
    <input type="text" name="code" placeholder="Enter reset code"
           class="w-full p-2 border rounded mb-4" required>
    <input type="password" name="new_password" id="new_password" placeholder="Enter new password"
           class="w-full p-2 border rounded mb-2" onkeyup="validatePassword()" required>
    <p id="passwordMessage"></p>
    <button type="submit"
            class="w-full bg-green-600 text-white py-2 mt-4 rounded hover:bg-green-700 transition">
      Change Password
    </button>
  </form>
</div>

<?php if (!empty($error)) : ?>
<script>
  Swal.fire({
    icon: 'error',
    title: 'Oops...',
    text: '<?= htmlspecialchars($error) ?>'
  });
</script>
<?php elseif (!empty($success)) : ?>
<script>
  Swal.fire({
    icon: 'success',
    title: 'Success!',
    text: '<?= htmlspecialchars($success) ?>',
    confirmButtonText: 'Go to Login'
  }).then(() => {
    window.location.href = "log-in.php"; // redirect after success
  });
</script>
<?php endif; ?>

</body>
</html>
