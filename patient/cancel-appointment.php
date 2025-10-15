<?php
session_start();
header('Content-Type: application/json');

require __DIR__ . '/../vendor/autoload.php';
use MongoDB\Client;
use MongoDB\BSON\ObjectId;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["success" => false, "message" => "Invalid request."]);
    exit;
}

$input = json_decode(file_get_contents("php://input"), true);
$id = $input['id'] ?? null;
$userEmail = $_SESSION['user_email'] ?? '';

if (!$id || !$userEmail) {
    echo json_encode(["success" => false, "message" => "Missing appointment ID or not logged in."]);
    exit;
}

try {
    $mongo = new Client("mongodb://localhost:27017");
    $db = $mongo->HaliliDentalClinic;
    $bookedService = $db->booked_service;

    // Ensure only the owner can cancel their appointment
    $result = $bookedService->updateOne(
        ['_id' => new ObjectId($id), 'email' => $userEmail],
        ['$set' => ['status' => 'cancelled']]
    );

    if ($result->getModifiedCount() > 0) {
        echo json_encode(["success" => true, "message" => "Your appointment has been cancelled."]);
    } else {
        echo json_encode(["success" => false, "message" => "Appointment not found or already cancelled."]);
    }
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => "Error: " . $e->getMessage()]);
}
exit;
