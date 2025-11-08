<?php
session_start();
header('Content-Type: application/json');

require __DIR__ . '/../vendor/autoload.php';
use MongoDB\Client;

try {
    // Get JSON input
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    if (!$data) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid JSON data']);
        exit;
    }

    // Validate required fields
    $requiredFields = ['serviceName', 'username', 'email', 'contactNumber', 'description', 'date', 'time'];
    foreach ($requiredFields as $field) {
        if (empty($data[$field])) {
            http_response_code(400);
            echo json_encode(['error' => "Missing required field: $field"]);
            exit;
        }
    }

    // Connect to MongoDB
    $mongoClient = new Client($_ENV['MONGO_URI']);
    $db = $mongoClient->HaliliDentalClinic;
    $bookedService = $db->bookedservices;

    // Validate date and time
    $appointmentDate = $data['date'];
    $appointmentTime = $data['time'];
    $appointmentDateTime = new DateTime("$appointmentDate $appointmentTime");
    $now = new DateTime();

    if ($appointmentDateTime < $now) {
        http_response_code(400);
        echo json_encode(['error' => 'Cannot book appointment in the past']);
        exit;
    }

    // Check for existing appointment at the same date and time
    $existingAppointment = $bookedService->findOne([
        'date' => $appointmentDate,
        'time' => $appointmentTime,
        'status' => ['$ne' => 'Cancelled']
    ]);

    if ($existingAppointment) {
        http_response_code(409);
        echo json_encode(['error' => 'This time slot is already booked. Please choose another time.']);
        exit;
    }

    // Prepare document for insertion
    $bookingDocument = [
        'serviceName' => $data['serviceName'],
        'fullname' => $data['username'],
        'email' => $data['email'],
        'phone' => $data['contactNumber'],
        'contactNumber' => $data['contactNumber'], // Keep both for compatibility
        'description' => $data['description'],
        'date' => $appointmentDate,
        'time' => $appointmentTime,
        'status' => 'Pending',
        'createdAt' => new MongoDB\BSON\UTCDateTime(),
        'updatedAt' => new MongoDB\BSON\UTCDateTime()
    ];

    // Add medical fields (default to 'N/A' if not provided)
    $bookingDocument['medicalHistory'] = !empty($data['medicalHistory']) 
        ? trim($data['medicalHistory']) 
        : 'N/A';
    
    $bookingDocument['allergies'] = !empty($data['allergies']) 
        ? trim($data['allergies']) 
        : 'N/A';

    // Insert the booking
    $result = $bookedService->insertOne($bookingDocument);

    if ($result->getInsertedCount() === 1) {
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Booking confirmed successfully',
            'bookingId' => (string)$result->getInsertedId()
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to create booking']);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}
exit;
?>