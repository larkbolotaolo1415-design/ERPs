<?php
// api_receive_data.php

// Show errors for debugging (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set response type
header('Content-Type: application/json');

// Include database connection
require_once __DIR__ . '/../includes/db_connect.php';

// -----------------------------
// Hardcoded URL of the other module providing JSON data
// -----------------------------
$sourceUrl = "https://example.com/module_data.php"; // <-- Replace with real URL

// Fetch data from external URL using cURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $sourceUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$response = curl_exec($ch);

if (curl_errno($ch)) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "cURL error: " . curl_error($ch)
    ]);
    curl_close($ch);
    exit;
}

$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "Failed to fetch data. HTTP status code: $httpCode"
    ]);
    exit;
}

// Decode JSON data
$data = json_decode($response, true);

if (!$data) {
    http_response_code(400);
    echo json_encode([
        "status" => "error",
        "message" => "Invalid JSON received from source URL"
    ]);
    exit;
}

$employees = $data['employees'] ?? [];
$users = $data['users'] ?? [];

try {
    // Begin transaction
    $pdo->beginTransaction();

    // =========================
    // Insert or update employee
    // =========================
    if (is_array($employees) && count($employees) > 0) {
        $stmt = $pdo->prepare("
            INSERT INTO employee (empID, fullname, department, position, email_address, profile_pic)
            VALUES (:empID, :fullname, :department, :position, :email_address, :profile_pic)
            ON DUPLICATE KEY UPDATE
                fullname = VALUES(fullname),
                department = VALUES(department),
                position = VALUES(position),
                email_address = VALUES(email_address),
                profile_pic = VALUES(profile_pic)
        ");

        foreach ($employees as $emp) {
            $stmt->execute([
                ':empID'         => $emp['empID'],
                ':fullname'      => $emp['fullname'],
                ':department'    => $emp['department'],
                ':position'      => $emp['position'],
                ':email_address' => $emp['email_address'],
                ':profile_pic'   => $emp['profile_pic'] ?? null   // <-- ADDED HERE
            ]);
        }
    }

    // =========================
    // Insert or update user
    // =========================
    if (is_array($users) && count($users) > 0) {
        $stmt = $pdo->prepare("
            INSERT INTO user (user_id, applicant_employee_id, email, password, role, fullname, status, profile_pic)
            VALUES (:user_id, :applicant_employee_id, :email, :password, :role, :fullname, :status, :profile_pic)
            ON DUPLICATE KEY UPDATE
                applicant_employee_id = VALUES(applicant_employee_id),
                password = VALUES(password),
                role = VALUES(role),
                fullname = VALUES(fullname),
                status = VALUES(status),
                profile_pic = VALUES(profile_pic)
        ");

        foreach ($users as $user) {
            $stmt->execute([
                ':user_id'               => $user['user_id'],
                ':applicant_employee_id' => $user['applicant_employee_id'],
                ':email'                 => $user['email'],
                ':password'              => $user['password'],
                ':role'                  => $user['role'],
                ':fullname'              => $user['fullname'],
                ':status'                => $user['status'],
                ':profile_pic'           => $user['profile_pic'] ?? null   // <-- ADDED HERE
            ]);
        }
    }

    // Commit transaction
    $pdo->commit();

    echo json_encode([
        "status" => "success",
        "message" => "Employee and user data fetched and stored successfully"
    ]);

} catch (PDOException $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
}
