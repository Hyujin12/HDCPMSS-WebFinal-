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
    $requestedMinutes = ($reqHour * 60) + (int)$reqMin;
    
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
        $bookingMinutes = ($bookHour * 60) + (int)$bookMin;
        
        // Calculate the end time of the existing booking (booking time + 30 minutes)
        $bookingEndMinutes = $bookingMinutes + 30;
        
        // Check if the requested time falls within the existing booking's 30-minute window
        // Requested time must be AFTER the existing booking's end time
        if ($requestedMinutes >= $bookingMinutes && $requestedMinutes < $bookingEndMinutes) {
            $isAvailable = false;
            $conflictingTime = $bookingTime;
            break;
        }
        
        // Also check if the requested booking's 30-minute window overlaps with existing booking
        $requestedEndMinutes = $requestedMinutes + 30;
        if ($requestedMinutes < $bookingEndMinutes && $requestedEndMinutes > $bookingMinutes) {
            $isAvailable = false;
            $conflictingTime = $bookingTime;
            break;
        }
    }
    
    if (!$isAvailable) {
        // Format the conflicting time for display
        $conflictDateTime = DateTime::createFromFormat('H:i', $conflictingTime);
        $formattedTime = $conflictDateTime->format('g:i A');
        
        // Calculate the available time (30 minutes after the conflicting booking)
        $endDateTime = clone $conflictDateTime;
        $endDateTime->modify('+30 minutes');
        $formattedEndTime = $endDateTime->format('g:i A');
        
        echo json_encode([
            'available' => false,
            'message' => "Time slot unavailable. There's an appointment at {$formattedTime}. Please book at {$formattedEndTime} or later.",
            'conflictingTime' => $conflictingTime,
            'nextAvailableTime' => $endDateTime->format('H:i')
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