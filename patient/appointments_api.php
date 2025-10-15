<?php
session_start();
header('Content-Type: application/json');

require __DIR__ . '/../vendor/autoload.php';
use MongoDB\Client;
$mongoClient = new Client($_ENV['MONGO_URI']);
$db = $mongoClient->HaliliDentalClinic;
$bookedService = $db->bookedservices;

$userEmail = $_SESSION['email'] ?? '';
if (!$userEmail) {
    echo json_encode([]);
    exit;
}

$query = ['email' => $userEmail];
if (isset($_GET['date'])) {
    $query['date'] = $_GET['date'];
}

// Fetch appointments
$cursor = $bookedService->find($query, [
    'sort' => ['date' => 1, 'time' => 1]
]);

$appointments = [];
foreach ($cursor as $appt) {
    $appointments[] = [
        '_id' => (string)$appt['_id'],
        'serviceName' => $appt['serviceName'] ?? '',
        'fullname' => $appt['fullname'] ?? '',
        'email' => $appt['email'] ?? '',
        'phone' => $appt['phone'] ?? '',
        'date' => $appt['date'] ?? '',
        'time' => $appt['time'] ?? '',
        'status' => $appt['status'] ?? 'pending',
        'description' => $appt['description'] ?? ''
    ];
}

echo json_encode($appointments);
exit;
