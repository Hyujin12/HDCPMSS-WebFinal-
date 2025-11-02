<?php
require __DIR__ . '/../vendor/autoload.php';

use MongoDB\Client;
use Dotenv\Dotenv;

// Load environment variables
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// MongoDB connection
$mongoClient = new Client($_ENV['MONGO_URI']);
$db = $mongoClient->selectDatabase('HaliliDentalClinic');
$usersCollection = $db->selectCollection('users');

header('Content-Type: application/json');
    $input = json_decode(file_get_contents('php://input'), true);
    $email = trim($input['email'] ?? '');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['username']) || empty(trim($input['username']))) {
    echo json_encode(['available' => false, 'error' => 'Username is required']);
    exit;
}

$username = trim($input['username']);

// Validate username length
if (strlen($username) < 3) {
    echo json_encode(['available' => false, 'error' => 'Username must be at least 3 characters']);
    exit;
}

try {
    // Check if username exists (case-insensitive)
    $existingUser = $usersCollection->findOne([
        'username' => new MongoDB\BSON\Regex('^' . preg_quote($username) . '$', 'i')
    ]);

    if ($existingUser) {
        echo json_encode([
            'available' => false,
            'message' => 'Username is already taken'
        ]);
    } else {
        echo json_encode([
            'available' => true,
            'message' => 'Username is available'
        ]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'available' => false,
        'error' => 'Server error checking username'
    ]);
}