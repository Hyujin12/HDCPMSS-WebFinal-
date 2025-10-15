<?php
session_start();
require __DIR__ . '/../vendor/autoload.php';

use MongoDB\Client;
use MongoDB\BSON\ObjectId;
use Dotenv\Dotenv;

header('Content-Type: application/json');

$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

if (!isset($_SESSION['email'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$appointmentId = $data['appointmentId'] ?? '';

if (!$appointmentId) {
    echo json_encode(['success' => false, 'message' => 'Missing appointment ID']);
    exit;
}

try {
    $client = new Client($_ENV['MONGO_URI']);
    $db = $client->HaliliDentalClinic;
    $appointments = $db->booked_service;

    $result = $appointments->updateOne(
        ['_id' => new ObjectId($appointmentId), 'email' => $_SESSION['email']],
        ['$set' => ['status' => 'Cancelled']]
    );

    if ($result->getModifiedCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Appointment cancelled successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'No appointment updated']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
