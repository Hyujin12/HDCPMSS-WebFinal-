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
    
    // Convert requested time to minutes for comparison
    list($reqHour, $reqMin) = explode(':', $requestedTime);
    $requestedMinutes = ($reqHour * 60) + $reqMin;
    
    // Find all accepted bookings on the same date
    $existingBookings = $bookingsCollection->find([
        'date' => $requestedDate,
        'status' => 'accepted'
    ]);
    
    $isAvailable = true;
    $conflictingTime = null;
    
    foreach ($existingBookings as $booking) {
        $bookingTime = $booking['time'];
        list($bookHour, $bookMin) = explode(':', $bookingTime);
        $bookingMinutes = ($bookHour * 60) + $bookMin;
        
        // Check if the requested time is within 30 minutes of an existing booking
        // This prevents bookings from requestedTime to requestedTime + 30 minutes
        $timeDifference = abs($requestedMinutes - $bookingMinutes);
        
        if ($timeDifference < 30) {
            $isAvailable = false;
            $conflictingTime = $bookingTime;
            break;
        }
    }
    
    if (!$isAvailable) {
        // Format the conflicting time for display
        $conflictDateTime = DateTime::createFromFormat('H:i', $conflictingTime);
        $formattedTime = $conflictDateTime->format('g:i A');
        
        // Calculate the end time (30 minutes after)
        $endDateTime = clone $conflictDateTime;
        $endDateTime->modify('+30 minutes');
        $formattedEndTime = $endDateTime->format('g:i A');
        
        echo json_encode([
            'available' => false,
            'message' => "This time slot conflicts with an existing appointment at {$formattedTime}. Please select a time after {$formattedEndTime}."
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