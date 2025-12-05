<?php
header("Content-Type: application/json");

// Suppress error display
error_reporting(0);
ini_set('display_errors', 0);

// Only GET allowed
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(["status" => "invalid_request"]);
    exit;
}

// Get employee_id from GET parameter
$employeeId = $_GET['employee_id'] ?? '';

if ($employeeId === '') {
    echo json_encode(["status" => "not_logged_in", "message" => "Missing employee_id"]);
    exit;
}

// Remote API URL - fetch all employees
$remote_url = "http://26.137.144.53/HR-EMPLOYEE-MANAGEMENT/API/employees.php";

// Call remote API
$curl = curl_init($remote_url);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_TIMEOUT, 10);
curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 5);
$response = curl_exec($curl);
$httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
$curlError = curl_error($curl);
curl_close($curl);

// Check for cURL errors
if ($curlError) {
    echo json_encode([
        "status" => "error",
        "message" => "API connection error: " . $curlError
    ]);
    exit;
}

// Check HTTP status code
if ($httpCode < 200 || $httpCode >= 300) {
    echo json_encode([
        "status" => "error",
        "message" => "API returned HTTP code $httpCode"
    ]);
    exit;
}

// Check if response is HTML (likely a PHP error)
if (strpos(trim($response), '<') === 0 || strpos($response, '<br') !== false) {
    echo json_encode([
        "status" => "error",
        "message" => "API returned HTML instead of JSON. Check remote API for errors."
    ]);
    exit;
}

$data = json_decode($response, true);

if (!$data || json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode([
        "status" => "error",
        "message" => "Invalid JSON from API: " . json_last_error_msg()
    ]);
    exit;
}

// Check if API returned success
if (!isset($data['status']) || $data['status'] !== 'success' || !isset($data['employees'])) {
    echo json_encode(["status" => "not_found"]);
    exit;
}

// Find employee by employee_id (empID)
$employee = null;
$employeeIdTrimmed = trim($employeeId);

// Debug: Log the employee_id we're looking for
error_log("Looking for employee_id: " . $employeeIdTrimmed);
error_log("Total employees in response: " . count($data['employees']));

foreach ($data['employees'] as $emp) {
    $empId = trim($emp['empID'] ?? '');
    // Try exact match first
    if ($empId === $employeeIdTrimmed) {
        $employee = $emp;
        break;
    }
    // Try case-insensitive match
    if (strcasecmp($empId, $employeeIdTrimmed) === 0) {
        $employee = $emp;
        break;
    }
}

if (!$employee) {
    // Return more detailed error for debugging
    $sampleEmpIds = [];
    $sampleEmails = [];
    foreach (array_slice($data['employees'], 0, 5) as $emp) {
        $sampleEmpIds[] = $emp['empID'] ?? 'N/A';
        $sampleEmails[] = $emp['email_address'] ?? 'N/A';
    }
    echo json_encode([
        "status" => "not_found",
        "message" => "Employee not found with the provided employee_id",
        "debug" => [
            "searched_for" => $employeeIdTrimmed,
            "sample_emp_ids" => $sampleEmpIds,
            "sample_emails" => $sampleEmails,
            "total_employees" => count($data['employees'])
        ]
    ]);
    exit;
}

// Construct profile picture URL if available
$profilePicUrl = null;
if (!empty($employee['profile_pic'])) {
    // If it's already a full URL, use it; otherwise construct the path
    if (strpos($employee['profile_pic'], 'http') === 0) {
        $profilePicUrl = $employee['profile_pic'];
    } else {
        // Construct URL to HR system's profile pictures
        $profilePicUrl = "http://26.137.144.53/HR-EMPLOYEE-MANAGEMENT/uploads/" . $employee['profile_pic'];
    }
}

// Map API data to profile structure
$profile = [
    "employee_id"      => $employee["empID"] ?? "",
    "fullname"         => $employee["fullname"] ?? "",
    "email"            => $employee["email_address"] ?? "",
    "role"             => $employee["department"] ?? "",
    "sub_role"         => $employee["position"] ?? "",
    "status"           => $employee["type_name"] ?? "",
    "created_at"       => $employee["hired_at"] ?? "",
    "profile_pic"      => $profilePicUrl,

    // Personal information
    "contact_number"       => $employee["contact_number"] ?? "",
    "emergency_contact"    => $employee["emergency_contact"] ?? "",
    "birthdate"            => $employee["date_of_birth"] ?? "",
    "gender"               => $employee["gender"] ?? "",
    "address"              => trim($employee["home_address"] ?? ""),
    
    // Government IDs
    "pagibig"              => $employee["pagibig_number"] ?? "",
    "philhealth"           => $employee["phil_health_number"] ?? "",
    "sss"                  => $employee["SSS_number"] ?? "",
    "tin"                  => $employee["TIN_number"] ?? ""
];

echo json_encode([
    "status" => "success",
    "profile" => $profile
]);
