<?php
$insertResult = $usersCollection->insertOne([
    'username' => $username,                     // added
    'mobileNumber' => (int)$mobileNumber,       // added
    'email' => $email,
    'password' => $hashed_password,
    'is_verified' => false,
    'verification_code' => (string)$verification_code,
    'code_expires' => new MongoDB\BSON\UTCDateTime(strtotime("+1 hour") * 1000), // optional expiry
    'created_at' => new MongoDB\BSON\UTCDateTime()
]);
?>