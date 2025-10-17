<?php
session_start();
require __DIR__ . '/../vendor/autoload.php';

use MongoDB\Client;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

header('Content-Type: application/json');

if (!isset($_SESSION['email'])) {
    echo json_encode([]);
    exit;
}

$userEmail = $_SESSION['email'];
$mongoClient = new Client($_ENV['MONGO_URI']);
$db = $mongoClient->HaliliDentalClinic;

$appointments = $db->bookedservices;

$cursor = $appointments->find(['email' => $userEmail]);

$events = [];

foreach ($cursor as $appt) {
    if (!empty($appt['date'])) {
        $events[] = [
            'id' => (string)$appt['_id'],
            'title' => $appt['serviceName'] ?? 'Appointment',
            'start' => $appt['date'],
            'time' => $appt['time'] ?? '',
            'description' => $appt['status'] ?? 'Pending',
            'extendedProps' => [
                'time' => $appt['time'] ?? '',
                'status' => $appt['status'] ?? 'Pending'
            ]
        ];
    }
}

echo json_encode($events);
