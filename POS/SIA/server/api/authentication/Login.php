<?php

// Allow CORS (optional but recommended if accessed externally)
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

session_start();
header("Content-Type: application/json");

// Only POST allowed
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["status" => "invalid_request"]);
    exit;
}

$email = trim($_POST['email'] ?? '');
$password = trim($_POST['password'] ?? '');

if ($email === '' || $password === '') {
    echo json_encode(["status" => "empty"]);
    exit;
}

// Remote API URL
$api_url = "http://26.137.144.53/HR-EMPLOYEE-MANAGEMENT/API/get_users.php?email=" . urlencode($email) . "&password=" . urlencode($password);

// Call remote API
$curl = curl_init($api_url);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_TIMEOUT, 10);
$response = curl_exec($curl);
$http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
curl_close($curl);

// Handle API errors
if ($response === false || $http_code !== 200) {
    echo json_encode(["status" => "server_error"]);
    exit;
}

$data = json_decode($response, true);

if (!is_array($data) || !isset($data['status']) || $data['status'] !== 'success' || !isset($data['user'])) {
    echo json_encode(["status" => "wrong_credentials"]);
    exit;
}

$user = $data['user'];

// Store email/password in PHP session for secure profile fetching
$_SESSION['email'] = $email;
$_SESSION['password'] = $password;

// Return simplified user info to frontend
echo json_encode([
    "status" => "success",
    "user" => [
        "id" => $user['applicant_employee_id'],
        "firstname" => explode(' ', $user['fullname'])[0] ?? '',
        "lastname" => explode(' ', $user['fullname'])[1] ?? '',
        "email" => $user['email'],
        "role" => $user['role'],
        "sub_role" => $user['sub_role'],
        "emp_id" => $user['applicant_employee_id'],
        "profile_pic" => $user['profile_pic'] ?? null
    ]
]);
