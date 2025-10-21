<?php
$apiKey = "YOUR_RESEND_API_KEY";
$url = "https://api.resend.com/emails";

$data = [
    "from" => "Halili Dental Clinic <no-reply@halilidentalclinic.shop>",
    "to" => ["rayzeltelenr@gmail.com"],
    "subject" => "Test Email",
    "html" => "<p>This is a test from Halili Dental Clinic.</p>"
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
curl_close($ch);

echo "HTTP: $httpCode\nResponse: $response\n";
