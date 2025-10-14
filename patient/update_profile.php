<?php
session_start();

if (!isset($_SESSION['email'])) {
    header("Location: log-in.php");
    exit;
}

require __DIR__ . '/../vendor/autoload.php';
use MongoDB\Client;
use Dotenv\Dotenv;

// Load .env
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

$mongo = new Client($_ENV['MONGO_URI']);
$db = $mongoClient->selectDatabase('HaliliDentalClinic');
$usersCollection = $db->selectCollection('users');


$id          = $_POST['id'];
$fullname    = $_POST['fullname'];
$dobInput    = $_POST['dob'];
$gender      = $_POST['gender'];
$civilStatus = $_POST['civil_status'];
$address     = $_POST['address'];
$phone       = $_POST['phone'];
$email       = $_POST['email'];
$occupation  = $_POST['occupation'];
$nationality = $_POST['nationality'];

// --- Recalculate Age from DOB (server-side, tamper-proof) ---
if (!empty($dobInput)) {
    $dobObj = new DateTime($dobInput);
    $today = new DateTime();
    $age = $today->diff($dobObj)->y; 
    $dob = $dobInput; // save DOB as string in DB
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
        if (!file_exists($uploadsDir)) {
            mkdir($uploadsDir, 0777, true);
        }

        $ext = pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION);
        $filename = uniqid("profile_") . "." . $ext;
        $targetFile = $uploadsDir . $filename;

        if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $targetFile)) {
            $profileImagePath = "uploads/" . $filename; // relative path
        }
    }
}

// --- Prepare update fields ---
$updateFields = [
    'fullname'     => $fullname,
    'dob'          => $dob,
    'age'          => $age,
    'gender'       => $gender,
    'civil_status' => $civilStatus,
    'address'      => $address,
    'phone'        => $phone,
    'email'        => $email,
    'occupation'   => $occupation,
    'nationality'  => $nationality
];

if ($profileImagePath) {
    $updateFields['profile_image'] = $profileImagePath;
}

// --- Update MongoDB ---
$users->updateOne(
    ['_id' => new ObjectId($id)],
    ['$set' => $updateFields]
);

// Update session if email changed
$_SESSION['email'] = $email;
$_SESSION['update_success'] = true;

header("Location: profile.php");
exit;
