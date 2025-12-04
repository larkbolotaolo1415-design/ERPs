<?php
// Required context: $pdo, $userId, $userName, $userTypeId

$typeMap = [1 => 'Admin', 2 => 'Doctor', 3 => 'Nurse', 4 => 'Staff', 5 => 'Patient'];
$roleName = $typeMap[$userTypeId] ?? ($_SESSION['user_role'] ?? 'User');
$isPatient = ($userTypeId === 5);

function fetchPermittedFiles(PDO $pdo, int $userId, int $userTypeId): array {
    $sql = "
        SELECT f.id, f.name, f.original_filename, f.mime_type, f.size, f.upload_date,
               COALESCE(MAX(CASE WHEN fp.can_download=1 THEN 1 ELSE 0 END),0) AS can_download
        FROM files f
        JOIN users ua ON ua.id = f.uploader_id AND ua.role = 'admin'
        JOIN file_permissions fp ON fp.file_id = f.id
        WHERE fp.user_type_id = :ut OR fp.user_id = :uid
        GROUP BY f.id, f.name, f.original_filename, f.mime_type, f.size, f.upload_date
        ORDER BY f.upload_date DESC
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':ut' => $userTypeId, ':uid' => $userId]);
    return $stmt->fetchAll();
}

$files = [];
try {
    $files = fetchPermittedFiles($pdo, $userId, $userTypeId);
} catch (Exception $e) {
    // TIDY: Silent failure for file fetching
}

$manageDocs = [];
try {
    $stmt = $pdo->query('SELECT id, filename, mime_type, file_size, uploaded_by, upload_date FROM documents ORDER BY upload_date DESC');
    $manageDocs = $stmt->fetchAll();
} catch (Exception $e) {
    // TIDY: Silent failure for documents query
}

$patientFiles = [];
$doctors = [];
if ($isPatient) {
    try {
        // Fetch patient files with assigned doctors
        $stmt = $pdo->prepare("
            SELECT pf.id, pf.original_filename, pf.mime_type, pf.file_size, pf.upload_date,
                   COALESCE(GROUP_CONCAT(DISTINCT u.name ORDER BY u.name SEPARATOR ', '), 'Not assigned') AS assigned_doctors
            FROM patient_files pf
            LEFT JOIN patient_file_access pfa ON pfa.file_id = pf.id
            LEFT JOIN users u ON u.id = pfa.doctor_id
            WHERE pf.patient_id = ?
            GROUP BY pf.id, pf.original_filename, pf.mime_type, pf.file_size, pf.upload_date
            ORDER BY pf.upload_date DESC
        ");
        $stmt->execute([$userId]);
        $patientFiles = $stmt->fetchAll();
    } catch (Exception $e) {
        // TIDY: Silent failure for patient files query
    }
    try {
        $stmt = $pdo->query("SELECT id, name, email FROM users WHERE role = 'doctor' ORDER BY name");
        $doctors = $stmt->fetchAll();
    } catch (Exception $e) {
        // TIDY: Silent failure for doctors query
    }
}

// Doctor side data
$isDoctor = ($userTypeId === 2);
$doctorPatients = [];
$doctorPatientFiles = [];
$recentPatientFiles = [];
$doctorQueryError = null;
if ($isDoctor) {
    try {
        // Get all patients who have files assigned to this doctor
        // Only select columns that definitely exist in users table
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
        $stmt->execute([$userId]);
        $doctorPatients = $stmt->fetchAll();
        
        // Get files for each patient
        foreach ($doctorPatients as &$patient) {
            $stmt = $pdo->prepare("
                SELECT pf.id, pf.original_filename, pf.mime_type, pf.file_size, pf.upload_date
                FROM patient_files pf
                INNER JOIN patient_file_access pfa ON pfa.file_id = pf.id
                WHERE pf.patient_id = ? AND pfa.doctor_id = ?
                ORDER BY pf.upload_date DESC
            ");
            $stmt->execute([$patient['id'], $userId]);
            $patient['files'] = $stmt->fetchAll();
        }
        unset($patient);
        
        // Get recently uploaded files by patients assigned to this doctor
        $stmt = $pdo->prepare("
            SELECT pf.id, pf.original_filename, pf.mime_type, pf.file_size, pf.upload_date,
                   u.name AS patient_name, u.email AS patient_email
            FROM patient_files pf
            INNER JOIN patient_file_access pfa ON pfa.file_id = pf.id
            INNER JOIN users u ON u.id = pf.patient_id
            WHERE pfa.doctor_id = ?
            ORDER BY pf.upload_date DESC
            LIMIT 10
        ");
        $stmt->execute([$userId]);
        $recentPatientFiles = $stmt->fetchAll();
    } catch (Exception $e) {
        // Log error for debugging
        $doctorQueryError = $e->getMessage();
        error_log("Doctor dashboard query error: " . $doctorQueryError);
        // Set empty arrays to prevent undefined variable errors
        $doctorPatients = [];
        $recentPatientFiles = [];
    }
}

$showClinicalWidget = in_array($userTypeId, [2, 3], true); // Doctor/Nurse
$showOpsWidget = in_array($userTypeId, [4], true); // Staff
$showPatientWidget = in_array($userTypeId, [5], true); // Patient

//PAGINATION FOR TEMPLATES AND DOCUMENTS
// FILE TEMPLATE PAGINATION
$template_limit = 3;
$template_page = isset($_GET['tpl_page']) ? (int)$_GET['tpl_page'] : 1;
$template_page = max($template_page, 1);

$template_offset = ($template_page - 1) * $template_limit;

$total_templates = count($files);
$template_pages = max(1, ceil($total_templates / $template_limit));

$files_paginated = array_slice($files, $template_offset, $template_limit);

// MANAGE DOCUMENTS PAGINATION
$doc_limit = 3;
$doc_page = isset($_GET['doc_page']) ? (int)$_GET['doc_page'] : 1;
$doc_page = max($doc_page, 1);

$doc_offset = ($doc_page - 1) * $doc_limit;

$total_docs = count($manageDocs);
$doc_pages = max(1, ceil($total_docs / $doc_limit));

$docs_paginated = array_slice($manageDocs, $doc_offset, $doc_limit);



?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo htmlspecialchars($roleName); ?> Dashboard</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
<link href='https://fonts.googleapis.com/css?family=Poppins:600,700|Roboto:400,500&display=swap' rel='stylesheet' />
<link rel="stylesheet" href="assets/css/root_colors_fonts.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>

/* UI Improvement: Better body and container styling */
body { 
    background-color: var(--white); 
    font-family: var(--font-body);
}
.container {
    padding-left: 1.5rem;
    padding-right: 1.5rem;
}
.navbar-head {
    background-color: var(--deep-navy);
    box-shadow: 0 4px 6px var(--shadow-light);
    padding: 0.5rem 1rem;
}
.navbar-brand {
    font-family: var(--font-headings);
    font-weight: 700;
    color: var(--white) !important;
    font-size: 1.25rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}
.navbar-brand i {
    font-size: 1.3rem;
}
.navbar-head .btn {
    color: var(--white);
    border: none;
}
.avatar-circle {
    width: 32px;
    height: 32px;
    background-color: var(--trust-blue);
    border-radius: 50%;
    color: var(--white);
    font-weight: 600;
    font-size: 0.9rem;
    display: flex;
    justify-content: center;
    align-items: center;
    user-select: none;
}
.welcome-card { 
    background: var(--trust-blue);
    color: var(--white);
    border-radius: 8px;
    box-shadow: 0 8px 16px var(--shadow-light);
    margin-bottom: 1.75rem;
    display: flex;
    flex-direction: column; 
}
.welcome-card h2 {
    font-family: var(--font-headings);
    font-weight: 700;
    font-size: 1.5rem;
    margin-bottom: 0.3rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    }
.welcome-card h2 i {
    font-size: 1.5rem;
    }
.welcome-card p {
    font-weight: 400;
    font-size: 1rem;
    opacity: 0.85;
    margin: 0;
    }
/* UI Improvement: Enhanced card styling with better spacing */
.card { 
    border: none; 
    border-radius: 12px; 
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    transition: box-shadow 0.3s ease, transform 0.2s ease;
    margin-bottom: 1.5rem;
}
.card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.12);
    transform: translateY(-2px);
}
/* UI Improvement: Better card header styling with padding */
.card-header { 
    font-family: var(--font-headings);
    font-weight: 600;
    font-size: 1.1rem;
    padding: 1rem 1.5rem;
    margin-bottom: 0;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: var(--dark-gray);
    background-color: var(--white);
    border-bottom: 2px solid var(--light-gray);
}
.card-header i {
    font-size: 1.25rem;
    color: var(--trust-blue);
}
.sidebar { 
    position: sticky; 
    top: 1rem; }
.list-group-item { 
    border: none; 
    margin-bottom: 
    6px; border-radius: 10px !important; 
}
.btn-primary { 
    background: var(--trust-blue); 
    border: none; 
    border-radius: 10px; 
    padding: 10px 20px; font-weight: 500; transition: all 0.3s ease; }
.dropdown-toggle {
    background-color: var(--deep-navy);
    border-radius: 6px;
    height: 40px;
    border: none;
    padding: 0 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-weight: 500;
}
.dropdown-menu {
    border-radius: 12px;
    box-shadow: 0 4px 6px var(--shadow-light);
    min-width: 220px;
    padding: 0.5rem 0.5rem;
}

.dropdown-item {
    font-weight: 500;
    font-size: 0.9rem;
    color: var(--dark-gray);
    display: flex;
    align-items: center;
    gap: 0.5rem;
    border-radius: 8px;
    padding: 0.5rem 1.5rem;
    transition: background-color 0.15s ease-in-out;
}
/* UI Improvement: Removed file-manage flex, using Bootstrap grid instead */

.dropdown-item:hover, .dropdown-item:focus {
    background-color: var(--sky-blue);
    color: var(--dark-gray);
}

.dropdown-item.text-danger {
    color: var(--red) !important;
}

.btn-success { 
    background: var(--green-teal); 
    border: none; 
    border-radius: 0px 12px 12px 0px;
    font-weight: 400;
    color: white;
}
.btn-primary { 
    background: var(--trust-blue); 
    border: none; 
    border-radius: 6px;
    font-weight: 400;
    color: white;
}
.btn-danger { 
    background: var(--red); 
    border: none; 
    border-radius: 0px 8px 8px 0px;
    font-weight: 400;
    color: white;
}
.btn-group {
    height: 40px;
    align-items: center;
}
.btn-group .btn-sm {
    font-size: 0.85rem;
    padding: 0.35rem 0.50rem;
    border-radius: 0;
    min-width: 40px;
}
.btn-group > .btn:first-child {
    border-radius: 8px 0 0 8px;
}
.btn-group > .btn:last-child {
    border-radius: 0 8px 8px 0;
}
.btn-group i {
    margin-right: 0;
}

/* UI Improvement: Enhanced table styling for better readability */
.table { 
    border-radius: 8px; 
    overflow: hidden; 
    background: white;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
    margin-bottom: 0;
}
.table thead {
    background-color: var(--light-gray);
    color: var(--dark-gray);
    font-weight: 600;
    font-family: var(--font-body);
}
.table thead th {
    padding: 1rem 1.25rem;
    border-bottom: 2px solid var(--light-gray-2);
    font-size: 0.9rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.table td { 
    border: none; 
    padding: 1rem 1.25rem; 
    vertical-align: middle; 
    color: var(--dark-gray);
    border-bottom: 1px solid var(--light-gray);
}
.table tbody tr:hover {
    background-color: var(--light-gray);
    transition: background-color 0.2s ease;
}
.table tr:nth-child(even) { 
    background-color: rgba(37, 99, 235, 0.03); 
}
/* UI Improvement: Better fixed table container with improved scrolling */
.fixed-table {
    max-height: 400px;
    overflow-y: auto;
    overflow-x: auto;
    border-radius: 8px;
    border: 1px solid var(--light-gray-2);
}

.fixed-table table {
    margin: 0;
    width: 100%;
}

.fixed-table thead th {
    position: sticky;
    top: 0;
    background: var(--light-gray);
    z-index: 5;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}


/* UI Improvement: Enhanced pagination styling */
.pagination-box {
    width: 100%;
    margin-top: 1.5rem;
    padding-top: 1rem;
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.pagination-box a,
.pagination-box span {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0.5rem 0.75rem;
    min-width: 2.5rem;
    border: 1px solid var(--light-gray-2);
    border-radius: 6px;
    background: var(--white);
    text-decoration: none;
    color: var(--dark-gray);
    font-weight: 500;
    transition: all 0.2s ease;
}

.pagination-box a:hover {
    background: var(--sky-blue);
    border-color: var(--trust-blue);
    color: var(--dark-gray);
    transform: translateY(-1px);
}

.pagination-box .active {
    background: var(--trust-blue);
    color: var(--white);
    border-color: var(--trust-blue);
}


/* Doctor side styles */
.profile-card {
    border: none;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    margin-bottom: 1rem;
}
.profile-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}
.profile-card-header {
    background: linear-gradient(135deg, var(--trust-blue) 0%, var(--sky-blue) 100%);
    color: white;
    padding: 1rem;
    border-radius: 12px 12px 0 0;
    cursor: pointer;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
/* UI Improvement: Better profile card body padding */
.profile-card-body {
    padding: 1.5rem;
    display: none;
}
.profile-card-body.show {
    display: block;
}
.patient-info-row {
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    padding: 0.5rem 0;
    border-bottom: 1px solid var(--light-gray);
}
.patient-info-row:last-child {
    border-bottom: none;
}
.patient-info-label {
    font-weight: 600;
    color: var(--dark-gray);
}
.patient-info-value {
    color: var(--dark-text);
}
.file-dropdown-item {
    padding: 0.75rem;
    border-bottom: 1px solid var(--light-gray);
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.file-dropdown-item:last-child {
    border-bottom: none;
}
/* UI Improvement: Enhanced badge styling */
.badge {
    padding: 0.35rem 0.65rem;
    border-radius: 6px;
    font-size: 0.75rem;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
}
/* UI Improvement: Better form styling */
.form-label {
    font-weight: 600;
    color: var(--dark-gray);
    margin-bottom: 0.5rem;
}
.form-control, .form-select {
    border-radius: 8px;
    border: 1px solid var(--light-gray-2);
    padding: 0.625rem 1rem;
    transition: all 0.2s ease;
}
.form-control:focus, .form-select:focus {
    border-color: var(--trust-blue);
    box-shadow: 0 0 0 0.2rem rgba(37, 99, 235, 0.15);
    outline: none;
}
.form-text {
    font-size: 0.875rem;
    color: var(--dark-gray);
    opacity: 0.7;
}
.form-text .btn{
    align-items: center;
}
</style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-head">
    <div class="container px-4v">
        <a class="navbar-brand" href="#"><img src="hospitallogo.png" style="width:50px; height: 50px"> Blue Pulse</a>

        <div class="navbar-nav ms-auto">
            <div class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" style="color: white;" href="#" role="button" data-bs-toggle="dropdown">
                    <i class="fas fa-user-circle me-2"></i><?php echo htmlspecialchars($userName); ?>
                </a>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown" id="userDropdownMenu">
                <li><a class="dropdown-item" href="#"><i class="fas fa-user-circle text-primary"></i> Profile</a></li>
                <li><a class="dropdown-item" href="#"><i class="fas fa-cog text-secondary"></i> Settings</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item text-danger" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
            </div>
        </div>
    </div>
</nav>

<!-- UI Improvement: Better container structure with consistent spacing -->
<div class="container mt-4 mb-5">
    <div class="row" id="welcome">
        <div class="col-12">
            <div class="welcome-card">
                <div class="card-body p-4">
                    <h2><i class="fas fa-wave-square"></i><?php echo htmlspecialchars($roleName); ?> Dashboard</h2>
                    <p class="mb-0">Welcome back, <?php echo htmlspecialchars($userName); ?>! Here's your workspace.</p>
                </div>
            </div>
        </div>
    </div>

<!-- UI Improvement: Better grid structure for file management cards -->
<div class="row g-4">
    <!-- File Templates Card -->
    <div class="col-md-6 col-lg-6">
        <div class="card h-100" id="files">
            <div class="card-header"><i class="fas fa-folder-open me-2"></i>File Templates</div>
            <div class="card-body">
<?php if (empty($files)): ?>
    <div class="text-muted">No available templates.</div>
<?php else: ?>
    <div class="fixed-table">
    <table class="table table-sm align-middle">
        <thead><tr><th>Name</th><th style="width:120px">Actions</th></tr></thead>
        <tbody>
            <?php foreach ($files_paginated as $f): ?>
            <tr>
                <td><?php echo htmlspecialchars($f['name']); ?></td>
                <td>
                    <div class="btn-group">
                        <a class="btn btn-sm btn-success" target="_blank" href="api/files.php?id=<?php echo (int)$f['id']; ?>"><i class="fas fa-eye"></i></a>
                        <?php if ((int)$f['can_download'] === 1): ?>
                        <a class="btn btn-sm btn-primary" href="api/files.php?id=<?php echo (int)$f['id']; ?>&download=1"><i class="fas fa-download"></i></a>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<div class="pagination-box">
<?php if ($template_page > 1): ?>
    <a href="?tpl_page=<?= $template_page-1 ?>">Prev</a>
<?php endif; ?>

<?php for ($i=1; $i<=$template_pages; $i++): ?>
    <a class="<?= $i==$template_page?'active':'' ?>" href="?tpl_page=<?= $i ?>"><?= $i ?></a>
<?php endfor; ?>

<?php if ($template_page < $template_pages): ?>
    <a href="?tpl_page=<?= $template_page+1 ?>">Next</a>
<?php endif; ?>
</div>


            </div>
        <?php endif; ?>
        <div class="form-text mt-3">Files shown per assigned permissions.</div>
        <button class="btn btn-primary mt-3" onclick="window.location.href='view_all_templates.php'">View All Templates</button>
        </div>
        </div>
    </div>

    <!-- Manage Documents Card -->
    <div class="col-md-6 col-lg-6">
        <div class="card h-100" id="manage-docs">
            <div class="card-header"><i class="fas fa-file-alt me-2"></i>Manage Documents</div>
            <div class="card-body">
<?php if (empty($manageDocs)): ?>
    <div class="text-muted">No documents available.</div>
<?php else: ?>
    <div class="fixed-table">
    <table class="table table-sm align-middle">
        <thead><tr>
            <th></th><th>File Name</th><th>Size</th><th>Uploaded</th><th>Uploader</th><th style="width:120px">Actions</th>
        </tr></thead>
        <tbody>
        <?php foreach ($docs_paginated as $d): ?>
            <tr>
                <td><i class="fas fa-file text-danger"></i></td>
                <td><?php echo htmlspecialchars($d['filename']); ?></td>
                <td><?php echo number_format(((int)$d['file_size'])/1024,1); ?> KB</td>
                <td><?php echo htmlspecialchars(date('Y-m-d H:i', strtotime($d['upload_date']))); ?></td>
                <td><?php echo htmlspecialchars($d['uploaded_by']); ?></td>
                <td>
                    <div class="btn-group">
                        <a class="btn btn-sm btn-success" target="_blank" href="api/download_stream.php?id=<?php echo (int)$d['id']; ?>"><i class="fas fa-eye"></i></a>
                        <a class="btn btn-sm btn-primary" href="api/download_stream.php?id=<?php echo (int)$d['id']; ?>&download=1"><i class="fas fa-download"></i></a>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <div class="pagination-box">
<?php if ($doc_page > 1): ?>
    <a href="?doc_page=<?= $doc_page-1 ?>">Prev</a>
<?php endif; ?>

<?php for ($i=1; $i<=$doc_pages; $i++): ?>
    <a class="<?= $i==$doc_page?'active':'' ?>" href="?doc_page=<?= $i ?>"><?= $i ?></a>
<?php endfor; ?>

<?php if ($doc_page < $doc_pages): ?>
    <a href="?doc_page=<?= $doc_page+1 ?>">Next</a>
<?php endif; ?>
</div>


            </div>
        <?php endif; ?>
        <div class="form-text mt-3">Documents uploaded by Admin are visible to all users.</div>
        <button class="btn btn-primary mt-3" onclick="window.location.href='view_all_documents.php'">View All Documents</button>
        </div>
        </div>
    </div>
</div>

<?php if ($showPatientWidget): ?>
<!-- UI Improvement: Better patient widget layout with improved spacing -->
<div class="row mt-4" id="patient">
    <div class="col-12 mb-4">
        <div class="card">
            <div class="card-header"><i class="fas fa-upload me-2"></i>Upload Medical File</div>
            <div class="card-body">
                <form method="POST" action="patient/patient_file_upload.php" enctype="multipart/form-data" id="patientUploadForm">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="p_file" class="form-label fw-semibold">Select File <span class="text-danger">*</span></label>
                            <input type="file" class="form-control" id="p_file" name="file" required accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.xls,.xlsx">
                            <div class="form-text mt-1">Allowed: PDF, DOC, DOCX, XLS, XLSX, JPG, PNG (Max 10MB)</div>
                        </div>
                        <div class="col-md-6">
                            <label for="p_desc" class="form-label fw-semibold">Description (Optional)</label>
                            <input type="text" class="form-control" id="p_desc" name="description" placeholder="e.g., Lab Results, X-Ray Report">
                        </div>
                    </div>
                    <div class="mt-4">
                        <label class="form-label fw-semibold">Assign to Doctor(s) <span class="text-danger">*</span></label>
                        <div class="border rounded p-3" style="max-height: 220px; overflow-y: auto; background-color: var(--light-gray);">
                            <?php if (empty($doctors)): ?>
                                <div class="text-muted text-center py-3">No doctors available.</div>
                            <?php else: ?>
                                <?php foreach ($doctors as $doc): ?>
                                    <div class="form-check mb-3 p-2 rounded" style="background-color: var(--white);">
                                        <input class="form-check-input p-doctor-checkbox" type="checkbox" name="doctor_ids[]" value="<?php echo (int)$doc['id']; ?>" id="doc_<?php echo (int)$doc['id']; ?>">
                                        <label class="form-check-label" for="doc_<?php echo (int)$doc['id']; ?>">
                                            <strong><?php echo htmlspecialchars($doc['name']); ?></strong>
                                            <small class="text-muted d-block"><?php echo htmlspecialchars($doc['email']); ?></small>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        <div id="p_doctor_error" class="text-danger mt-2" style="display:none;">
                            <i class="fas fa-exclamation-circle me-1"></i>Please select at least one doctor.
                        </div>
                    </div>
                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-cloud-upload-alt me-2"></i>Upload File
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- UI Improvement: Better patient files table layout -->
    <div class="col-12">
        <div class="card">
            <div class="card-header"><i class="fas fa-file-alt me-2"></i>My Uploaded Files</div>
            <div class="card-body">
                <?php if (empty($patientFiles)): ?>
                    <div class="text-center text-muted py-5">
                        <i class="fas fa-folder-open fa-3x mb-3"></i>
                        <p class="mb-2">No files uploaded yet</p>
                        <div class="form-text">Upload your medical documents using the form above.</div>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle">
<thead><tr>
<th><i class="fas fa-file me-2"></i>File Name</th>
<th><i class="fas fa-user-md me-2"></i>Doctor Assigned</th>
<th><i class="fas fa-file-alt me-2"></i>File Type</th>
<th><i class="fas fa-weight me-2"></i>File Size</th>
<th><i class="fas fa-calendar me-2"></i>Uploaded</th>
<th style="width:180px"><i class="fas fa-cogs me-2"></i>Actions</th>
</tr></thead>
<tbody>
<?php foreach ($patientFiles as $pf): 
    $fileType = strtoupper(pathinfo($pf['original_filename'], PATHINFO_EXTENSION));
    $fileSize = number_format(((int)$pf['file_size'])/1024, 1) . ' KB';
    $assignedDoctors = $pf['assigned_doctors'] ?: 'Not assigned';
?>
<tr>
<td><i class="fas fa-file me-2"></i><?php echo htmlspecialchars($pf['original_filename']); ?></td>
<td><small><?php echo htmlspecialchars($assignedDoctors); ?></small></td>
<td><span class="badge bg-info"><?php echo htmlspecialchars($fileType); ?></span></td>
<td><small><?php echo htmlspecialchars($fileSize); ?></small></td>
<td><small><?php echo htmlspecialchars(date('Y-m-d H:i', strtotime($pf['upload_date']))); ?></small></td>
<td>
<div class="btn-group">
<a href="api/download_stream.php?patient_file_id=<?php echo (int)$pf['id']; ?>" target="_blank" class="btn btn-sm btn-success" title="View"><i class="fas fa-eye"></i></a>
<a href="api/download_stream.php?patient_file_id=<?php echo (int)$pf['id']; ?>&download=1" class="btn btn-sm btn-primary" title="Download"><i class="fas fa-download"></i></a>
<button type="button" class="btn btn-sm btn-danger" onclick="deletePatientFile(<?php echo (int)$pf['id']; ?>, '<?php echo htmlspecialchars(addslashes($pf['original_filename'])); ?>')" title="Delete"><i class="fas fa-trash"></i></button>
</div>
</td>
</tr>
<?php endforeach; ?>
                        </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if ($isDoctor): ?>
<!-- Doctor Side: Patient Lists & Files -->
<div class="row mt-4" id="doctor-section">
    <?php 
    // Show error only in development (set ?debug=1 in URL to see errors)
    $showErrors = isset($_GET['debug']) && $_GET['debug'] === '1';
    if ($doctorQueryError && $showErrors): ?>
    <!-- Error Display (for debugging) -->
    <div class="col-12 mb-3">
        <div class="alert alert-warning">
            <strong>Query Error:</strong> <?php echo htmlspecialchars($doctorQueryError); ?>
            <br><small>Check database structure. The query may be failing due to missing columns in users table.</small>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Patient Lists Section -->
    <div class="col-12 mb-4">
        <div class="card">
            <div class="card-header"><i class="fas fa-users me-2"></i>Patient Lists</div>
            <div class="card-body">
                <?php if (empty($doctorPatients)): ?>
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-user-injured fa-3x mb-3"></i>
                        <p>No patients assigned yet</p>
                        <div class="form-text">Patients who upload files and assign them to you will appear here.</div>
                        <?php if ($doctorQueryError && $showErrors): ?>
                        <div class="alert alert-danger mt-3">
                            <small>There was an error loading patient data. Check the error message above or PHP error logs.</small>
                        </div>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="row">
                        <?php foreach ($doctorPatients as $patient): 
                            $age = '';
                            if (!empty($patient['date_of_birth'])) {
                                $birthDate = new DateTime($patient['date_of_birth']);
                                $today = new DateTime();
                                $age = $today->diff($birthDate)->y;
                            }
                            $fileCount = count($patient['files'] ?? []);
                        ?>
                        <div class="col-md-6 col-lg-4 mb-3">
                            <div class="profile-card">
                                <div class="profile-card-header" onclick="togglePatientCard(<?php echo (int)$patient['id']; ?>)">
                                    <div>
                                        <h6 class="mb-0"><i class="fas fa-user me-2"></i><?php echo htmlspecialchars($patient['name']); ?></h6>
                                        <small><?php echo $fileCount; ?> file(s)</small>
                                    </div>
                                    <i class="fas fa-chevron-down" id="chevron_<?php echo (int)$patient['id']; ?>"></i>
                                </div>
                                <div class="profile-card-body" id="patient_card_<?php echo (int)$patient['id']; ?>">
                                    <div class="patient-info-row">
                                        <span class="patient-info-label">Age:</span>
                                        <span class="patient-info-value"><?php echo $age ? $age . ' years' : 'N/A'; ?></span>
                                    </div>
                                    <div class="patient-info-row">
                                        <span class="patient-info-label">Gender:</span>
                                        <span class="patient-info-value"><?php echo htmlspecialchars($patient['gender'] ?? 'N/A'); ?></span>
                                    </div>
                                    <?php if (!empty($patient['phone'])): ?>
                                    <div class="patient-info-row">
                                        <span class="patient-info-label">Contact:</span>
                                        <span class="patient-info-value"><?php echo htmlspecialchars($patient['phone']); ?></span>
                                    </div>
                                    <?php endif; ?>
                                    <div class="patient-info-row">
                                        <span class="patient-info-label">Assigned:</span>
                                        <span class="patient-info-value"><?php echo $patient['assigned_date'] ? date('M j, Y', strtotime($patient['assigned_date'])) : 'N/A'; ?></span>
                                    </div>
                                    
                                    <!-- Files Dropdown -->
                                    <?php if (!empty($patient['files'])): ?>
                                    <div class="mt-3">
                                        <h6 class="mb-2"><i class="fas fa-file-alt me-2"></i>Uploaded Files</h6>
                                        <div style="max-height: 300px; overflow-y: auto;">
                                            <?php foreach ($patient['files'] as $file): 
                                                $fileType = strtoupper(pathinfo($file['original_filename'], PATHINFO_EXTENSION));
                                                $fileSize = number_format(((int)$file['file_size'])/1024, 1) . ' KB';
                                            ?>
                                            <div class="file-dropdown-item">
                                                <div>
                                                    <div><i class="fas fa-file me-2"></i><?php echo htmlspecialchars($file['original_filename']); ?></div>
                                                    <small class="text-muted">
                                                        <?php echo date('M j, Y H:i', strtotime($file['upload_date'])); ?> • 
                                                        <span class="badge bg-info"><?php echo htmlspecialchars($fileType); ?></span> • 
                                                        <?php echo htmlspecialchars($fileSize); ?>
                                                    </small>
                                                </div>
                                                <div class="btn-group">
                                                    <a href="api/download_stream.php?patient_file_id=<?php echo (int)$file['id']; ?>" target="_blank" class="btn btn-sm btn-success" title="View"><i class="fas fa-eye"></i></a>
                                                    <a href="api/download_stream.php?patient_file_id=<?php echo (int)$file['id']; ?>&download=1" class="btn btn-sm btn-primary" title="Download"><i class="fas fa-download"></i></a>
                                                </div>
                                            </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                    <?php else: ?>
                                    <div class="mt-3 text-center text-muted">
                                        <small>No files uploaded yet</small>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Recently Uploaded Files by Patients -->
    <div class="col-12">
        <div class="card">
            <div class="card-header"><i class="fas fa-clock me-2"></i>Recently Uploaded Files by Patients</div>
            <div class="card-body">
                <?php if (empty($recentPatientFiles)): ?>
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-folder-open fa-3x mb-3"></i>
                        <p>No recent files</p>
                        <div class="form-text">Files uploaded by your assigned patients will appear here.</div>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle">
                            <thead><tr>
                                <th><i class="fas fa-file me-2"></i>File Name</th>
                                <th><i class="fas fa-user me-2"></i>Patient Name</th>
                                <th><i class="fas fa-calendar me-2"></i>Timestamp</th>
                                <th><i class="fas fa-weight me-2"></i>Size</th>
                                <th style="width:140px"><i class="fas fa-cogs me-2"></i>Actions</th>
                            </tr></thead>
                            <tbody>
                                <?php foreach ($recentPatientFiles as $rpf): 
                                    $fileSize = number_format(((int)$rpf['file_size'])/1024, 1) . ' KB';
                                ?>
                                <tr>
                                    <td><i class="fas fa-file me-2"></i><?php echo htmlspecialchars($rpf['original_filename']); ?></td>
                                    <td><?php echo htmlspecialchars($rpf['patient_name']); ?></td>
                                    <td><small><?php echo htmlspecialchars(date('Y-m-d H:i', strtotime($rpf['upload_date']))); ?></small></td>
                                    <td><small><?php echo htmlspecialchars($fileSize); ?></small></td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="api/download_stream.php?patient_file_id=<?php echo (int)$rpf['id']; ?>" target="_blank" class="btn btn-sm btn-success" title="View"><i class="fas fa-eye"></i></a>
                                            <a href="api/download_stream.php?patient_file_id=<?php echo (int)$rpf['id']; ?>&download=1" class="btn btn-sm btn-primary" title="Download"><i class="fas fa-download"></i></a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

    <!-- UI Improvement: Better footer spacing -->
    <div class="row mt-5">
        <div class="col-12">
            <footer class="text-center py-4 border-top text-muted">
                &copy; <?php echo date('Y'); ?> DMS - Document Management System
            </footer>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<?php if ($showPatientWidget): ?>
<?php
if (isset($_SESSION['patient_upload_status'])) {
    $status = $_SESSION['patient_upload_status'];
    unset($_SESSION['patient_upload_status']);
    $title = '';
    $icon = '';
    
    if ($status === 'success') {
        $title = 'Successfully uploaded file';
        $icon = 'success';
    } elseif ($status === 'invalid') {
        $title = 'Invalid file';
        $icon = 'error';
    } elseif ($status === 'failed') {
        $title = 'Failed to upload';
        $icon = 'error';
    }
    
    if ($title !== '') {
        echo '<script>
        document.addEventListener("DOMContentLoaded", function() {
            Swal.fire({
                title: "' . htmlspecialchars($title, ENT_QUOTES) . '",
                icon: "' . htmlspecialchars($icon, ENT_QUOTES) . '",
                confirmButtonText: "OK",
                confirmButtonColor: "#2c5f5f"
            });
        });
        </script>';
    }
}
?>
<script>
(function(){
    const form = document.getElementById('patientUploadForm');
    if (form) {
        form.addEventListener('submit', function(e){
            const checked = document.querySelectorAll('.p-doctor-checkbox:checked');
            const err = document.getElementById('p_doctor_error');
            if (checked.length === 0) {
                e.preventDefault();
                if (err) err.style.display = 'block';
                return false;
            }
            if (err) err.style.display = 'none';
            return true;
        });
    }
})();

function deletePatientFile(fileId, fileName) {
    Swal.fire({
        title: 'Delete File?',
        text: 'Are you sure you want to delete "' + fileName + '"? This action cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'patient/patient_file_delete.php';
            
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'file_id';
            input.value = fileId;
            form.appendChild(input);
            
            document.body.appendChild(form);
            form.submit();
        }
    });
}
</script>
<?php endif; ?>

<?php if ($isDoctor): ?>
<script>
// Toggle patient card dropdown
function togglePatientCard(patientId) {
    const cardBody = document.getElementById('patient_card_' + patientId);
    const chevron = document.getElementById('chevron_' + patientId);
    
    if (cardBody && chevron) {
        if (cardBody.classList.contains('show')) {
            cardBody.classList.remove('show');
            chevron.classList.remove('fa-chevron-up');
            chevron.classList.add('fa-chevron-down');
        } else {
            cardBody.classList.add('show');
            chevron.classList.remove('fa-chevron-down');
            chevron.classList.add('fa-chevron-up');
        }
    }
}
</script>
<?php endif; ?>
</body>
</html>
