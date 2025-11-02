<?php
require __DIR__ . '/../vendor/autoload.php';

use MongoDB\Client;
use Dotenv\Dotenv;

// Load environment variables
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// Set JSON header
header('Content-Type: application/json');

// MongoDB connection
try {
    $mongoClient = new Client($_ENV['MONGO_URI']);
    $db = $mongoClient->selectDatabase('HaliliDentalClinic');
    $usersCollection = $db->selectCollection('users');

    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    $email = trim($input['email'] ?? '');

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['available' => false, 'message' => 'Invalid email format']);
        exit;
    }

    // Check if email exists (case-insensitive)
    $existingEmail = $usersCollection->findOne(['email' => strtolower($email)]);

    if ($existingEmail) {
        echo json_encode(['available' => false, 'message' => 'Email already registered']);
    } else {
        echo json_encode(['available' => true, 'message' => 'Email available']);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['available' => false, 'message' => 'Server error', 'error' => $e->getMessage()]);
}
?>