<?php
// Suppress error display and warnings
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');

// -----------------------------
// Validate patient_id
// -----------------------------
$patientId = $_GET['patient_id'] ?? null;

if (!$patientId) {
    echo json_encode([
        "status" => "error",
        "message" => "Missing patient_id"
    ]);
    exit;
}

// -----------------------------
// Helper function to call API
// -----------------------------
function callAPI($url) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 20);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err = curl_error($ch);
    curl_close($ch);

    if ($err) {
        return [
            "status" => "error",
            "message" => "API error: $err"
        ];
    }

    if ($httpCode < 200 || $httpCode >= 300) {
        return [
            "status" => "error",
            "message" => "API returned HTTP code $httpCode"
        ];
    }

    // Check if response is HTML (likely a PHP error)
    if (strpos(trim($response), '<') === 0 || strpos($response, '<br') !== false) {
        return [
            "status" => "error",
            "message" => "API returned HTML instead of JSON. Check remote API for errors."
        ];
    }

    $data = json_decode($response, true);
    if (!$data || json_last_error() !== JSON_ERROR_NONE) {
        return [
            "status" => "error",
            "message" => "Invalid JSON from API: " . json_last_error_msg()
        ];
    }

    return $data;
}

// -----------------------------
// 1. Get Patients
// -----------------------------
$patientsURL = "http://26.233.226.98/PMS/api/get_patients.php";
$patientsResponse = callAPI($patientsURL);

if ($patientsResponse["status"] !== "success") {
    echo json_encode([
        "status" => "error",
        "message" => "Failed to load patients"
    ]);
    exit;
}

$selectedPatient = null;
foreach ($patientsResponse["data"] as $p) {
    if ((string)$p["patient_id"] === (string)$patientId) {
        $selectedPatient = $p;
        break;
    }
}

if (!$selectedPatient) {
    echo json_encode([
        "status" => "error",
        "message" => "Patient not found"
    ]);
    exit;
}

// -----------------------------
// 2. Get Prescription Records (already contains medicine_id and quantity)
// -----------------------------
$recordsURL = "http://26.233.226.98/PMS/api/get_record_prescriptions.php?patient_id=$patientId";
$recordsResponse = callAPI($recordsURL);

$prescriptionRecords = $recordsResponse["data"] ?? [];

// -----------------------------
// Format prescription data (already has everything we need)
// -----------------------------
$finalPrescriptions = [];

foreach ($prescriptionRecords as $record) {
    // Skip if medicine_id is null or empty
    if (!isset($record["medicine_id"]) || $record["medicine_id"] === null || $record["medicine_id"] === '') {
        continue;
    }
    
    $finalPrescriptions[] = [
        "record_id"   => $record["record_id"] ?? $record["id"] ?? null,
        "medicine_id" => $record["medicine_id"],
        "quantity"    => isset($record["quantity"]) ? (int)$record["quantity"] : 1
    ];
}

// -----------------------------
// Final Response
// -----------------------------
echo json_encode([
    "status" => "success",
    "patient" => $selectedPatient,
    "prescriptions" => $finalPrescriptions
]);
