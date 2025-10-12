<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use Dotenv\Dotenv;
use MongoDB\Client;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();

try {
    $client = new Client($_ENV['MONGO_URI']);
    $database = $client->selectDatabase('hdcpmss');
} catch (Exception $e) {
    die("MongoDB connection failed: " . $e->getMessage());
}
?>
