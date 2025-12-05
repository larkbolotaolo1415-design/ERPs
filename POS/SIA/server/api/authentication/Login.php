<?php
// No need for local DB since login is API-based now.

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo 'invalid_request';
    exit;
}

$email = trim($_POST['email'] ?? '');
$password = trim($_POST['password'] ?? '');

if ($email === '' || $password === '') {
    echo 'empty';
    exit;
}

// External API endpoint
$apiUrl = "http://26.137.144.53/HR-EMPLOYEE-MANAGEMENT/API/get_users.php?email=" 
            . urlencode($email) 
            . "&password=" 
            . urlencode($password);

$curl = curl_init();

curl_setopt_array($curl, [
    CURLOPT_URL => $apiUrl,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 10,
]);

$response = curl_exec($curl);
$curlError = curl_error($curl);
curl_close($curl);

// If CURL fails / API unreachable
if ($response === false) {
    echo json_encode([
        "status" => "error",
        "message" => "server_unreachable",
        "details" => $curlError
    ]);
    exit;
}

// Decode the API JSON
$data = json_decode($response, true);

// Error if invalid JSON
if ($data === null) {
    echo json_encode([
        "status" => "error",
        "message" => "invalid_api_response"
    ]);
    exit;
}

// If the API returns success → pass through JSON to frontend
if (isset($data['status']) && $data['status'] === 'success') {
    echo json_encode($data);
    exit;
}

// If API returns fail or incorrect password
echo json_encode($data);
exit;
?>
