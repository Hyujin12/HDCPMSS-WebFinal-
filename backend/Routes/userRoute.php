<?php
require __DIR__ . '/../vendor/autoload.php';
use MongoDB\Client;

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// Use MONGO_URI from .env
$MONGO_URI = $_ENV['MONGO_URI'] ?? 'mongodb://localhost:27017';
$DB_NAME = 'HaliliDentalClinic';

$mongoClient = new Client($MONGO_URI);
$db = $mongoClient->selectDatabase($DB_NAME);
$usersCollection = $db->selectCollection('users');

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$path = $_GET['action'] ?? '';

$response = ['success' => false];

switch ($method) {
    case 'GET':
        if ($path === 'profile' && isset($_GET['email'])) {
            $user = $usersCollection->findOne(
                ['email' => $_GET['email']],
                ['projection' => ['password' => 0]] // exclude password
            );
            if ($user) {
                $response = ['success' => true, 'user' => $user];
            } else {
                $response['error'] = 'User not found';
            }
        }
        break;

    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);

        if ($path === 'update' && isset($data['email'])) {
            $updateFields = [];
            if (!empty($data['username'])) $updateFields['username'] = $data['username'];
            if (!empty($data['mobileNumber'])) $updateFields['mobileNumber'] = (int)$data['mobileNumber'];

            if (!empty($updateFields)) {
                $updateResult = $usersCollection->updateOne(
                    ['email' => $data['email']],
                    ['$set' => $updateFields]
                );

                if ($updateResult->getModifiedCount() === 1) {
                    $response = ['success' => true, 'message' => 'Profile updated'];
                } else {
                    $response['error'] = 'Nothing to update or user not found';
                }
            } else {
                $response['error'] = 'No valid fields to update';
            }
        }

        if ($path === 'verify' && isset($data['email'], $data['code'])) {
            $user = $usersCollection->findOne(['email' => $data['email']]);
            if ($user && $user['verification_code'] === $data['code']) {
                $usersCollection->updateOne(
                    ['email' => $data['email']],
                    ['$set' => ['isVerified' => true], '$unset' => ['verificationCode' => "", 'codeExpires' => ""]]
                );
                $response = ['success' => true, 'message' => 'Email verified'];
            } else {
                $response['error'] = 'Invalid verification code';
            }
        }

        break;

    default:
        http_response_code(405);
        $response['error'] = 'Method not allowed';
}

echo json_encode($response);
