<?php
session_start();

if (!isset($_SESSION['email'])) {
    header("Location: log-in.php");
    exit;
}

require __DIR__ . '/../vendor/autoload.php';
use MongoDB\Client;
use MongoDB\BSON\ObjectId;
use Dotenv\Dotenv;

// Load .env
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

$mongoClient = new Client($_ENV['MONGO_URI']);
$db = $mongoClient->selectDatabase('HaliliDentalClinic');
$users = $db->selectCollection('users');

// --- Collect POST data ---
$id          = $_POST['id'] ?? '';
$fullname    = $_POST['fullname'] ?? '';
$dobInput    = $_POST['birthday'] ?? '';
$gender      = $_POST['gender'] ?? '';
$civilStatus = $_POST['status'] ?? '';
$address     = $_POST['address'] ?? '';
$phone       = $_POST['contactNumber'] ?? '';
$email       = $_POST['email'] ?? '';
$occupation  = $_POST['occupation'] ?? '';
$nationality = $_POST['nationality'] ?? '';

// ✅ Check for valid ObjectId before proceeding
if (empty($id) || !preg_match('/^[a-f\d]{24}$/i', $id)) {
    $_SESSION['update_error'] = "Invalid or missing user ID. Please try again.";
    header("Location: profile.php");
    exit;
}

// --- Recalculate Age ---
if (!empty($dobInput)) {
    $dobObj = new DateTime($dobInput);
    $today = new DateTime();
    $age = $today->diff($dobObj)->y;
    $dob = $dobInput;
} else {
    $age = null;
    $dob = null;
}

// --- Handle file upload ---
$profileImagePath = null;
if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == UPLOAD_ERR_OK) {
    $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
    $maxSize = 2 * 1024 * 1024; // 2MB

    if (in_array($_FILES['profile_image']['type'], $allowedTypes) && $_FILES['profile_image']['size'] <= $maxSize) {
        $uploadsDir = __DIR__ . "/uploads/";
        if (!file_exists($uploadsDir)) mkdir($uploadsDir, 0777, true);

        $ext = pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION);
        $filename = uniqid("profile_") . "." . $ext;
        $targetFile = $uploadsDir . $filename;

        if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $targetFile)) {
            $profileImagePath = "uploads/" . $filename;
        }
    }
}

// --- Prepare update fields ---
$updateFields = [
    'username'     => $fullname,
    'birthday'      => $dob,
    'age'          => $age,
    'gender'       => $gender,
    'status' => $civilStatus,
    'address'      => $address,
    'phone'        => $phone,
    'email'        => $email,
    'occupation'   => $occupation,
    'nationality'  => $nationality
];

if ($profileImagePath) {
    $updateFields['profile_image'] = $profileImagePath;
}

// --- Update MongoDB with error handling ---
try {
    $result = $users->updateOne(
        ['_id' => new ObjectId($id)],
        ['$set' => $updateFields]
    );

    if ($result->getModifiedCount() > 0) {
        $_SESSION['update_success'] = true;
    } else {
        $_SESSION['update_error'] = "No changes were made to your profile.";
    }
} catch (Exception $e) {
    $_SESSION['update_error'] = "Error updating profile: " . $e->getMessage();
}

// --- Redirect back to profile ---
header("Location: profile.php");
exit;
?>
