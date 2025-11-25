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
    $appointments = $db->bookedservices;

    // First, fetch the appointment to check its status and ownership
    $appointment = $appointments->findOne([
        '_id' => new ObjectId($appointmentId),
        'email' => $_SESSION['email']
    ]);

    if (!$appointment) {
        echo json_encode(['success' => false, 'message' => 'Appointment not found or you do not have permission to cancel it']);
        exit;
    }

    // Get the current status
    $currentStatus = $appointment['status'] ?? 'Pending';

    // Check if the appointment can be cancelled
    // Only Pending appointments can be cancelled
    if (in_array($currentStatus, ['Accepted', 'Completed', 'Rejected', 'Cancelled'])) {
        $statusMessage = match($currentStatus) {
            'Accepted' => 'accepted appointments cannot be cancelled',
            'Completed' => 'completed appointments cannot be cancelled',
            'Rejected' => 'rejected appointments cannot be cancelled',
            'Cancelled' => 'this appointment is already cancelled',
            default => 'this appointment cannot be cancelled'
        };
        
        echo json_encode([
            'success' => false, 
            'message' => 'Cannot cancel: ' . $statusMessage
        ]);
        exit;
    }

    // Update the appointment status to Cancelled
    // Double-check it's still Pending before updating
    $result = $appointments->updateOne(
        [
            '_id' => new ObjectId($appointmentId),
            'email' => $_SESSION['email'],
            'status' => 'Pending' // Only update if status is still Pending
        ],
        [
            '$set' => [
                'status' => 'Cancelled',
                'cancelledAt' => new MongoDB\BSON\UTCDateTime()
            ]
        ]
    );

    if ($result->getModifiedCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Appointment cancelled successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to cancel appointment. It may have already been processed.']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}