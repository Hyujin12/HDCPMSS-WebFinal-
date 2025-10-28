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
$email = $_GET['email'] ?? '';

// ✅ Check if account already verified (auto check on page load)
if (!empty($email)) {
    $existingUser = $usersCollection->findOne(['email' => $email]);
    if ($existingUser && !empty($existingUser['isVerified']) && $existingUser['isVerified'] === true) {
        $success = "Your account is already verified! You can now log in.";
    }
}

// ✅ Function to resend verification code
function resendVerificationCode($usersCollection, $email)
{
    $user = $usersCollection->findOne(['email' => $email]);
    if (!$user) return false;

    $newCode = random_int(100000, 999999);

    $updateResult = $usersCollection->updateOne(
        ['email' => $email],
        ['$set' => [
            'verificationCode' => (string)$newCode,
            'codeExpires' => new MongoDB\BSON\UTCDateTime((time() + 900) * 1000)
        ]]
    );

    if ($updateResult->getModifiedCount() === 1) {
        $apiKey = $_ENV['RESEND_API_KEY'];
        $url = "https://api.resend.com/emails";
        $data = [
            "from" => "Halili Dental Clinic <no-reply@halilidentalclinic.shop>",
            "to" => [$email],
            "subject" => "Your Halili Dental Clinic Verification Code",
            "html" => "<p>Hi " . htmlspecialchars($user['username']) . ",</p>
                       <p>Your new verification code is: <strong>$newCode</strong></p>"
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

        file_put_contents(__DIR__ . '/../resend_log.txt',
            "Email: $email\nHTTP: $httpCode\nResponse: $response\nError: $error\n\n",
            FILE_APPEND
        );

        return $httpCode >= 200 && $httpCode < 300;
    }
    return false;
}

// ✅ Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = trim($_POST['code'] ?? '');
    $email = trim($_POST['email'] ?? '');

    if (!$code || !$email) {
        $error = "Please enter the verification code.";
    } else {
        $user = $usersCollection->findOne(['email' => $email]);

        if (!$user) {
            $error = "User not found.";
        } elseif ($user['isVerified']) {
            $success = "Your account is already verified! You can now log in.";
        } elseif ($user['verificationCode'] !== $code) {
            $error = "Invalid verification code.";
        } else {
            $now = new MongoDB\BSON\UTCDateTime();
            if (isset($user['codeExpires']) && $user['codeExpires'] < $now) {
                $error = "Verification code expired. Please resend.";
            } else {
                $usersCollection->updateOne(
                    ['email' => $email],
                    ['$set' => ['isVerified' => true], '$unset' => ['verificationCode' => "", 'codeExpires' => ""]]
                );
                $success = "Email verified successfully!";
            }
        }
    }
}

// ✅ Handle resend request
if (isset($_GET['resend']) && $email) {
    if (resendVerificationCode($usersCollection, $email)) {
        $success = "A new verification code has been sent to your email.";
    } else {
        $error = "Failed to resend code. Try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Verify Email - Halili's Dental Clinic</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-white text-gray-900 flex items-center justify-center min-h-screen">

<form method="POST" class="max-w-md w-full p-8 border rounded-lg shadow-lg">
    <h2 class="text-2xl font-bold mb-6 text-center">Verify Your Email</h2>

    <input type="hidden" name="email" value="<?= htmlspecialchars($email) ?>">

    <label class="block mb-2 font-semibold" for="code">Verification Code</label>
    <input type="text" name="code" id="code" class="w-full p-2 border rounded mb-6" required>

    <button type="submit" class="w-full bg-blue-600 text-white py-3 rounded font-bold hover:bg-blue-700 transition">
        Verify
    </button>

    <p class="mt-6 text-center text-gray-700">
        Didn't receive a code? 
        <a href="?email=<?= urlencode($email) ?>&resend=1" class="text-blue-600 hover:underline">Resend Code</a>
    </p>
</form>

<!-- ✅ SweetAlert Notifications -->
<?php if (!empty($error)) : ?>
<script>
Swal.fire({ icon: 'error', title: 'Error', text: '<?= htmlspecialchars($error) ?>' });
</script>
<?php endif; ?>

<?php if (!empty($success)) : ?>
<script>
Swal.fire({
    icon: 'success',
    title: 'Success',
    text: '<?= htmlspecialchars($success) ?>',
    confirmButtonText: 'OK'
}).then(() => {
    // ✅ If already verified, redirect to login
    <?php if (str_contains($success, 'already verified')): ?>
        window.location.href = 'log-in.php';
    <?php elseif (str_contains($success, 'Email verified successfully')): ?>
        window.location.href = 'log-in.php';
    <?php else: ?>
        window.location.href = 'verify-code.php?email=<?= urlencode($email) ?>';
    <?php endif; ?>
});
</script>
<?php endif; ?>

</body>
</html>
