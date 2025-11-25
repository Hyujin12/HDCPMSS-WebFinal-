<?php
session_start();
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['email'])) {
    http_response_code(401);
    echo json_encode([
        'available' => false,
        'error' => 'Unauthorized'
    ]);
    exit;
}

require __DIR__ . '/../vendor/autoload.php';
use MongoDB\Client;

try {
    // Get the request data
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['date']) || !isset($input['time'])) {
        http_response_code(400);
        echo json_encode([
            'available' => false,
            'error' => 'Missing date or time'
        ]);
        exit;
    }
    
    $requestedDate = $input['date'];
    $requestedTime = $input['time'];
    
    // Connect to MongoDB
    $mongoClient = new Client($_ENV['MONGO_URI']);
    $db = $mongoClient->HaliliDentalClinic;
    $bookingsCollection = $db->bookings;
    
    // Check if there's already an accepted booking at this date and time
    $existingBooking = $bookingsCollection->findOne([
        'date' => $requestedDate,
        'time' => $requestedTime,
        'status' => 'accepted' // Only check for accepted bookings
    ]);
    
    if ($existingBooking) {
        echo json_encode([
            'available' => false,
            'message' => 'This time slot is already booked'
        ]);
    } else {
        echo json_encode([
            'available' => true,
            'message' => 'Time slot is available'
        ]);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'available' => false,
        'error' => 'Server error: ' . $e->getMessage()
    ]);
}
?>