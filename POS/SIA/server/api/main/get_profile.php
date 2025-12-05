<?php
session_start();
header("Content-Type: application/json");

// Only GET allowed
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(["status" => "invalid_request"]);
    exit;
}

// Check session for logged-in user
$email = $_SESSION['email'] ?? '';
$password = $_SESSION['password'] ?? '';

if ($email === '' || $password === '') {
    echo json_encode(["status" => "not_logged_in"]);
    exit;
}

// Remote API URL
$remote_url = "http://26.137.144.53/HR-EMPLOYEE-MANAGEMENT/API/get_users.php?email=" . urlencode($email) . "&password=" . urlencode($password);

// Call remote API
$curl = curl_init($remote_url);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_TIMEOUT, 10);
$response = curl_exec($curl);
curl_close($curl);

$data = json_decode($response, true);

if (!$data || !isset($data['status']) || $data['status'] !== 'success' || !isset($data['user'])) {
    echo json_encode(["status" => "not_found"]);
    exit;
}

$user = $data['user'];

// Map API data to profile structure
$profile = [
    "employee_id"      => $user["applicant_employee_id"] ?? "",
    "fullname"         => $user["fullname"] ?? "",
    "email"            => $user["email"] ?? "",
    "role"             => $user["role"] ?? "",
    "sub_role"         => $user["sub_role"] ?? "",
    "status"           => $user["status"] ?? "",
    "created_at"       => $user["created_at"] ?? "",
    "profile_pic"      => $user["profile_pic"] ?? null,

    // Optional placeholders
    "contact_number"       => $user["contact_number"] ?? "",
    "emergency_contact"    => $user["emergency_contact"] ?? "",
    "birthdate"            => $user["birthdate"] ?? "",
    "gender"               => $user["gender"] ?? "",
    "address"              => $user["home_address"] ?? "",
    "pagibig"              => $user["pagibig"] ?? "",
    "philhealth"           => $user["philhealth"] ?? "",
    "sss"                  => $user["sss"] ?? "",
    "tin"                  => $user["tin"] ?? ""
];

echo json_encode([
    "status" => "success",
    "profile" => $profile
]);
