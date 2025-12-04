<?php
/**
 * Diagnostic Script for Doctor File Display Issue
 * Run this script to check if files are being assigned correctly
 * 
 * Usage: Access via browser when logged in as a doctor
 * Or add ?debug=1 to dashboard URL to see errors
 */

require_once __DIR__ . '/includes/db_connect.php';

// Check if running from command line or web
$isCLI = php_sapi_name() === 'cli';

if (!$isCLI && !isset($_SESSION['user_id'])) {
    die('Please log in first. Or run from command line: php diagnose_doctor_files.php [doctor_id]');
}

$doctorId = $isCLI ? (isset($argv[1]) ? (int)$argv[1] : 2) : (int)$_SESSION['user_id'];

echo "<!DOCTYPE html><html><head><title>Doctor File Diagnostic</title>";
echo "<style>body{font-family:monospace;padding:20px;background:#f5f5f5;} ";
echo "table{border-collapse:collapse;width:100%;margin:10px 0;background:white;} ";
echo "th,td{border:1px solid #ddd;padding:8px;text-align:left;} ";
echo "th{background:#2563EB;color:white;} ";
echo ".success{color:green;} .error{color:red;} .warning{color:orange;}</style>";
echo "</head><body>";
echo "<h1>🔍 Doctor File Assignment Diagnostic</h1>";
echo "<p>Checking doctor ID: <strong>$doctorId</strong></p>";

// 1. Check if doctor exists
echo "<h2>1. Doctor Information</h2>";
try {
    $stmt = $pdo->prepare("SELECT id, name, email, role, user_type_id FROM users WHERE id = ?");
    $stmt->execute([$doctorId]);
    $doctor = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($doctor) {
        echo "<table><tr><th>Field</th><th>Value</th></tr>";
        foreach ($doctor as $key => $value) {
            echo "<tr><td><strong>$key</strong></td><td>" . htmlspecialchars($value ?? 'NULL') . "</td></tr>";
        }
        echo "</table>";
        if ($doctor['role'] !== 'doctor' && (int)($doctor['user_type_id'] ?? 0) !== 2) {
            echo "<p class='error'>⚠️ WARNING: User is not identified as a doctor (role: {$doctor['role']}, user_type_id: {$doctor['user_type_id']})</p>";
        }
    } else {
        echo "<p class='error'>❌ ERROR: Doctor with ID $doctorId not found!</p>";
    }
} catch (Exception $e) {
    echo "<p class='error'>❌ ERROR: " . htmlspecialchars($e->getMessage()) . "</p>";
}

// 2. Check patient_file_access records
echo "<h2>2. File Access Records for This Doctor</h2>";
try {
    $stmt = $pdo->prepare("
        SELECT pfa.id, pfa.file_id, pfa.doctor_id, pfa.granted_date,
               pf.original_filename, pf.patient_id, u.name AS patient_name
        FROM patient_file_access pfa
        INNER JOIN patient_files pf ON pf.id = pfa.file_id
        INNER JOIN users u ON u.id = pf.patient_id
        WHERE pfa.doctor_id = ?
        ORDER BY pfa.granted_date DESC
    ");
    $stmt->execute([$doctorId]);
    $accessRecords = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($accessRecords)) {
        echo "<p class='warning'>⚠️ WARNING: No file access records found for this doctor.</p>";
        echo "<p>This means either:</p><ul>";
        echo "<li>No files have been assigned to this doctor yet</li>";
        echo "<li>The patient_file_access table is empty</li>";
        echo "<li>There's an issue with the upload process</li>";
        echo "</ul>";
    } else {
        echo "<p class='success'>✅ Found <strong>" . count($accessRecords) . "</strong> file access record(s)</p>";
        echo "<table><tr><th>Access ID</th><th>File ID</th><th>File Name</th><th>Patient</th><th>Granted Date</th></tr>";
        foreach ($accessRecords as $record) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($record['id']) . "</td>";
            echo "<td>" . htmlspecialchars($record['file_id']) . "</td>";
            echo "<td>" . htmlspecialchars($record['original_filename']) . "</td>";
            echo "<td>" . htmlspecialchars($record['patient_name']) . " (ID: " . htmlspecialchars($record['patient_id']) . ")</td>";
            echo "<td>" . htmlspecialchars($record['granted_date']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
} catch (Exception $e) {
    echo "<p class='error'>❌ ERROR: " . htmlspecialchars($e->getMessage()) . "</p>";
}

// 3. Test the exact query used in dashboard
echo "<h2>3. Test Dashboard Query (Patients with Files)</h2>";
try {
    $stmt = $pdo->prepare("
        SELECT DISTINCT 
            u.id, 
            u.name, 
            u.email,
            MIN(pfa.granted_date) AS assigned_date
        FROM users u
        INNER JOIN patient_files pf ON pf.patient_id = u.id
        INNER JOIN patient_file_access pfa ON pfa.file_id = pf.id
        WHERE pfa.doctor_id = ? AND u.role = 'patient'
        GROUP BY u.id, u.name, u.email
        ORDER BY u.name
    ");
    $stmt->execute([$doctorId]);
    $patients = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($patients)) {
        echo "<p class='warning'>⚠️ No patients found using dashboard query.</p>";
        echo "<p>This query is what the dashboard uses. If it returns empty, the dashboard will show 'No patients assigned yet'.</p>";
    } else {
        echo "<p class='success'>✅ Found <strong>" . count($patients) . "</strong> patient(s):</p>";
        echo "<table><tr><th>Patient ID</th><th>Name</th><th>Email</th><th>Assigned Date</th></tr>";
        foreach ($patients as $patient) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($patient['id']) . "</td>";
            echo "<td>" . htmlspecialchars($patient['name']) . "</td>";
            echo "<td>" . htmlspecialchars($patient['email']) . "</td>";
            echo "<td>" . htmlspecialchars($patient['assigned_date']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
} catch (Exception $e) {
    echo "<p class='error'>❌ ERROR: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p>This is the error that's preventing files from showing on the dashboard.</p>";
}

// 4. Check all patient files
echo "<h2>4. All Patient Files (with access info)</h2>";
try {
    $stmt = $pdo->query("
        SELECT pf.id, pf.original_filename, pf.patient_id, pf.upload_date,
               u.name AS patient_name,
               GROUP_CONCAT(DISTINCT u2.name ORDER BY u2.name SEPARATOR ', ') AS assigned_doctors
        FROM patient_files pf
        INNER JOIN users u ON u.id = pf.patient_id
        LEFT JOIN patient_file_access pfa ON pfa.file_id = pf.id
        LEFT JOIN users u2 ON u2.id = pfa.doctor_id
        GROUP BY pf.id, pf.original_filename, pf.patient_id, pf.upload_date, u.name
        ORDER BY pf.upload_date DESC
        LIMIT 20
    ");
    $allFiles = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($allFiles)) {
        echo "<p>No patient files found in database.</p>";
    } else {
        echo "<p>Showing last 20 files:</p>";
        echo "<table><tr><th>File ID</th><th>File Name</th><th>Patient</th><th>Upload Date</th><th>Assigned Doctors</th></tr>";
        foreach ($allFiles as $file) {
            $doctors = $file['assigned_doctors'] ?: '<span class="warning">None</span>';
            echo "<tr>";
            echo "<td>" . htmlspecialchars($file['id']) . "</td>";
            echo "<td>" . htmlspecialchars($file['original_filename']) . "</td>";
            echo "<td>" . htmlspecialchars($file['patient_name']) . " (ID: " . htmlspecialchars($file['patient_id']) . ")</td>";
            echo "<td>" . htmlspecialchars($file['upload_date']) . "</td>";
            echo "<td>$doctors</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
} catch (Exception $e) {
    echo "<p class='error'>❌ ERROR: " . htmlspecialchars($e->getMessage()) . "</p>";
}

// 5. Database structure check
echo "<h2>5. Database Structure Check</h2>";
try {
    $tables = ['patient_files', 'patient_file_access', 'users'];
    foreach ($tables as $table) {
        $stmt = $pdo->query("SHOW COLUMNS FROM `$table`");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "<h3>Table: <code>$table</code></h3>";
        echo "<table><tr><th>Column</th><th>Type</th><th>Null</th><th>Key</th></tr>";
        foreach ($columns as $col) {
            echo "<tr>";
            echo "<td><strong>" . htmlspecialchars($col['Field']) . "</strong></td>";
            echo "<td>" . htmlspecialchars($col['Type']) . "</td>";
            echo "<td>" . htmlspecialchars($col['Null']) . "</td>";
            echo "<td>" . htmlspecialchars($col['Key']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
} catch (Exception $e) {
    echo "<p class='error'>❌ ERROR: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "<hr><p><strong>Next Steps:</strong></p>";
echo "<ul>";
echo "<li>If no file access records exist, have a patient upload a file and assign it to this doctor</li>";
echo "<li>Check PHP error logs for any upload errors</li>";
echo "<li>Verify the upload form is sending doctor_ids correctly</li>";
echo "<li>Verify database structure using document_management_system.sql (master SQL file)</li>";
echo "</ul>";

echo "</body></html>";
?>


