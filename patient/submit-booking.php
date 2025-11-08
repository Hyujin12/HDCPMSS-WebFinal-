<?php
session_start(); // Start the session

require __DIR__ . '/../vendor/autoload.php';
use MongoDB\Client;

header('Content-Type: text/plain');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo 'Method Not Allowed';
    exit;
}

// Ensure user is logged in
if (empty($_SESSION['email']) && empty($_SESSION['email'])) {
    http_response_code(401);
    echo 'Unauthorized: Please log in first.';
    exit;
}

$inputJSON = file_get_contents('php://input');
$data = json_decode($inputJSON, true);

if (!$data) {
    http_response_code(400);
    echo 'Invalid JSON data';
    exit;
}

// Required fields for booking
$requiredFields = ['serviceName', 'contactNumber', 'description', 'date', 'time'];
foreach ($requiredFields as $field) {
    if (empty($data[$field])) {
        http_response_code(400);
        echo "Missing or empty field: $field";
        exit;
    }
}

try {
    $mongoClient = new Client($_ENV['MONGO_URI']);
    $db = $mongoClient->selectDatabase('HaliliDentalClinic');
    $usersCollection = $db->selectCollection('users');

    $bookedServiceCollection = $db->selectCollection('bookedservices');

    // Get email from session
    $email = $_SESSION['email'] ?? $_SESSION['email'] ?? '';

    // Always get the latest fullname & email from DB
    $user = $usersCollection->findOne(['email' => $email]);

    if (!$user) {
        http_response_code(404);
        echo 'User not found';
        exit;
    }

    $username = $user['username'] ?? '';
    $email = $user['email'] ?? '';

    $bookingDocument = [
        'serviceName' => $data['serviceName'],
        'username'    => $username,
        'email'       => $email,
        'contactNumber'  => $data['contactNumber'],
        'description' => $data['description'],
        'date'        => $data['date'],
        'time'        => $data['time'],
        'medicalHistory' => !empty($data['medicalHistory']) ? trim($data['medicalHistory']) : 'N/A',
        'allergies' => !empty($data['allergies']) ? trim($data['allergies']) : 'N/A',
        'createdAt'   => new MongoDB\BSON\UTCDateTime(),
    ];

    $result = $bookedServiceCollection->insertOne($bookingDocument);

    if ($result->getInsertedCount() === 1) {
        echo 'Booking saved successfully';
    } else {
        http_response_code(500);
        echo 'Failed to save booking';
    }
} catch (Exception $e) {
    http_response_code(500);
    echo 'Error: ' . $e->getMessage();
}
?>