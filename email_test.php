<?php
require __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$apiKey = $_ENV['RESEND_API_KEY'];
$url = "https://api.resend.com/emails";

$data = [
    "from" => "Halili Dental Clinic <no-reply@halilidentalclinic.shop>",
    "to" => ["renatocarasco60@gmail.com"], // 👈 Replace with your actual email
    "subject" => "✅ Test from Halili Dental Clinic",
    "html" => "
        <div style='font-family: Arial, sans-serif;'>
            <h2>Hello!</h2>
            <p>This is a <strong>test email</strong> sent using <b>Resend API</b>.</p>
            <p>If you received this, your configuration works 🎉</p>
        </div>"
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer $apiKey",
    "Content-Type: application/json"
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "HTTP Code: $httpCode\n";
echo "Response: $response\n";
echo "Error: $error\n";

// Optional: save to log file
file_put_contents(__DIR__ . '/email_test_log.txt', 
    "HTTP: $httpCode\nResponse: $response\nError: $error\n\n", FILE_APPEND);
