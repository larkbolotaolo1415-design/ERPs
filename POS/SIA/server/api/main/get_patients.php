<?php
header('Content-Type: application/json');

// PMS API URL
$apiUrl = "http://26.233.226.98/PMS/api/get_patients.php";

// Initialize cURL
$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10); // optional: timeout
curl_setopt($ch, CURLOPT_TIMEOUT, 30); // optional: response timeout

$response = curl_exec($ch);
$err = curl_error($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// Check for cURL errors
if ($err) {
    echo json_encode([
        "status" => "error",
        "message" => "Failed to fetch patients: $err"
    ]);
    exit;
}

// Check HTTP status code
if ($httpCode < 200 || $httpCode >= 300) {
    echo json_encode([
        "status" => "error",
        "message" => "PMS API returned HTTP code $httpCode"
    ]);
    exit;
}

// Decode the API response to ensure valid JSON
$data = json_decode($response, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode([
        "status" => "error",
        "message" => "Invalid JSON received from PMS API"
    ]);
    exit;
}

// Return the JSON as-is
echo json_encode($data);
