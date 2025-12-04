<?php
// api_receive_patients.php

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
$sourceUrl = "https://example.com/patients_data.php"; // <-- Replace with real URL
// -----------------------------

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

if (!$data || !isset($data["status"]) || $data["status"] !== "success") {
    http_response_code(400);
    echo json_encode([
        "status" => "error",
        "message" => "Invalid or unexpected JSON received from source URL"
    ]);
    exit;
}

$patients = $data['data'] ?? [];

try {
    // Begin transaction
    $pdo->beginTransaction();

    // =========================
    // Insert or update patients
    // =========================
    if (is_array($patients) && count($patients) > 0) {
        $stmt = $pdo->prepare("
            INSERT INTO patients (patient_id, first_name, middle_name, last_name, email)
            VALUES (:patient_id, :first_name, :middle_name, :last_name, :email)
            ON DUPLICATE KEY UPDATE
                first_name = VALUES(first_name),
                middle_name = VALUES(middle_name),
                last_name = VALUES(last_name),
                email = VALUES(email)
        ");

        foreach ($patients as $p) {
            // Skip invalid entries
            if (!isset($p['patient_id'], $p['first_name'], $p['last_name'], $p['email'])) {
                continue;
            }

            $stmt->execute([
                ':patient_id' => $p['patient_id'],
                ':first_name' => $p['first_name'],
                ':middle_name' => $p['middle_name'] ?? null,
                ':last_name' => $p['last_name'],
                ':email' => $p['email']
            ]);
        }
    }

    // Commit transaction
    $pdo->commit();

    echo json_encode([
        "status" => "success",
        "message" => "Patients data fetched and stored successfully",
        "records_processed" => count($patients)
    ]);

} catch (PDOException $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
}
