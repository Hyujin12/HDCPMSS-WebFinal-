<?php
session_start();
require __DIR__ . '/../vendor/autoload.php';

use MongoDB\Client;
use Dotenv\Dotenv;

// Load environment variables
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

header('Content-Type: application/json');

// Check if the user is logged in
if (!isset($_SESSION['email'])) {
    echo json_encode(['error' => 'Not logged in']);
    exit;
}

$userEmail = $_SESSION['email'];

// Connect to MongoDB
$mongoClient = new Client($_ENV['MONGO_URI']);
$db = $mongoClient->HaliliDentalClinic;
$appointmentsCollection = $db->bookedservices;

// Fetch all appointments for this user
$appointments = $appointmentsCollection->find([
    'email' => $userEmail
]);

$events = [];

foreach ($appointments as $appt) {
    if (!empty($appt['date'])) {
        // Combine date and time properly for FullCalendar
        $start = $appt['date'];
        if (!empty($appt['time'])) {
            $start .= 'T' . date('H:i:s', strtotime($appt['time']));
        }

        // Assign color based on status
        $color = match (strtolower($appt['status'] ?? 'Pending')) {
            'Accepted' => '#22c55e', // green
            'Declined' => '#ef4444', // red
            'Pending' => '#f59e0b',  // yellow
            default => '#3b82f6',    // blue
        };

        $events[] = [
            'id' => (string) $appt['_id'],
            'title' => $appt['serviceName'] ?? 'Dental Appointment',
            'start' => $start,
            'color' => $color,
            'extendedProps' => [
                'time' => $appt['time'] ?? '',
                'status' => $appt['status'] ?? 'Pending',
                'notes' => $appt['notes'] ?? 'No additional notes',
                'dentist' => $appt['dentist'] ?? 'Not assigned',
                'serviceName' => $appt['serviceName'] ?? 'N/A'
            ]
        ];
    }
}

// Output as JSON for FullCalendar
echo json_encode($events);
?>
