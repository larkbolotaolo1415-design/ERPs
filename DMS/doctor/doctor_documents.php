<?php
/**
 * Doctor Document Viewing Page
 * Document Management System
 * 
 * This page allows doctors to view and download PDF documents uploaded by admins,
 * and patient files explicitly shared with them.
 */

require_once __DIR__ . '/../includes/db_connect.php';

// Check if user is logged in and has doctor role
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'doctor') {
    header('Location: ../login.php');
    exit();
}

$user_name = $_SESSION['user_name'];
$user_email = $_SESSION['user_email'];
$doctorId = (int)$_SESSION['user_id'];

// Get all admin documents
try {
    $stmt = $pdo->query("SELECT * FROM documents ORDER BY upload_date DESC");
    $documents = $stmt->fetchAll();
} catch (PDOException $e) {
    $documents = [];
}

// Get patient files shared with this doctor
$patientFiles = [];
try {
    $sql = "
        SELECT pf.id, pf.original_filename, pf.file_path, pf.upload_date, pf.file_size, pf.mime_type,
               u.name AS patient_name, u.email AS patient_email
        FROM patient_file_access pfa
        JOIN patient_files pf ON pf.id = pfa.file_id
        JOIN users u ON u.id = pf.patient_id
        WHERE pfa.doctor_id = :did
        ORDER BY pf.upload_date DESC
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':did' => $doctorId]);
    $patientFiles = $stmt->fetchAll();
} catch (PDOException $e) {
    $patientFiles = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document Library - Doctor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2C5F5F;
            --secondary-color: #4A90E2;
            --accent-color: #FF6B6B;
            --success-color: #28A745;
            --warning-color: #FFC107;
            --light-bg: #F8F9FA;
            --dark-text: #2C3E50;
            --light-text: #6C757D;
        }
        
        body {
            background-color: var(--light-bg);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .navbar-brand {
            font-weight: bold;
            color: white !important;
        }
        .navbar-dark {
            background: linear-gradient(135deg, var(--success-color) 0%, #20C997 100%) !important;
            box-shadow: 0 2px 10px rgba(40, 167, 69, 0.1);
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
        }
        .card-header {
            background: linear-gradient(135deg, var(--light-bg) 0%, #E9ECEF 100%);
            border-bottom: 2px solid var(--success-color);
            border-radius: 15px 15px 0 0 !important;
            font-weight: 600;
            color: var(--dark-text);
        }
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            border: none;
            border-radius: 10px;
            padding: 10px 20px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(44, 95, 95, 0.3);
        }
        .btn-success {
            background: linear-gradient(135deg, var(--success-color) 0%, #20C997 100%);
            border: none;
            border-radius: 10px;
        }
        .btn-info {
            background: linear-gradient(135deg, var(--secondary-color) 0%, #6F42C1 100%);
            border: none;
            border-radius: 10px;
        }
        .table {
            border-radius: 10px;
            overflow: hidden;
        }
        .table td {
            border: none;
            padding: 12px 16px;
            vertical-align: middle;
        }
        .table tr:nth-child(even) {
            background-color: rgba(40, 167, 69, 0.05);
        }
        .file-icon {
            color: var(--accent-color);
            font-size: 1.5rem;
        }
        .document-card {
            transition: transform 0.3s ease;
        }
        .document-card:hover {
            transform: translateY(-5px);
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-success">
        <div class="container">
            <a class="navbar-brand" href="doctor_home.php">
                <i class="fas fa-user-md me-2"></i>DMS Doctor
            </a>
            <div class="navbar-nav ms-auto">
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user-circle me-2"></i><?php echo htmlspecialchars($user_name); ?>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="doctor_home.php"><i class="fas fa-home me-2"></i>Dashboard</a></li>
                        <li><a class="dropdown-item" href="#"><i class="fas fa-user me-2"></i>Profile</a></li>
                        <li><a class="dropdown-item" href="#"><i class="fas fa-cog me-2"></i>Settings</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="../logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <!-- Page Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body p-4">
                        <h2><i class="fas fa-file-medical me-3"></i>Document Library</h2>
                        <p class="mb-0">View and download administrator documents and patient files shared with you.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Patient Files Shared With You -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-user-injured me-2"></i>Patient Files Shared With You</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($patientFiles)): ?>
                            <div class="text-center text-muted py-4">
                                <i class="fas fa-folder-open fa-3x mb-3"></i>
                                <p>No patient files shared with you</p>
                                <small>Files shared by your patients will appear here.</small>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th><i class="fas fa-file me-2"></i>File</th>
                                            <th><i class="fas fa-user me-2"></i>Patient</th>
                                            <th><i class="fas fa-calendar me-2"></i>Uploaded</th>
                                            <th><i class="fas fa-weight me-2"></i>Size</th>
                                            <th><i class="fas fa-cogs me-2"></i>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($patientFiles as $pf): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($pf['original_filename']); ?></td>
                                            <td>
                                                <?php echo htmlspecialchars($pf['patient_name']); ?><br>
                                                <small class="text-muted"><?php echo htmlspecialchars($pf['patient_email']); ?></small>
                                            </td>
                                            <td><?php echo date('M j, Y g:i A', strtotime($pf['upload_date'])); ?></td>
                                            <td><?php echo number_format(((int)$pf['file_size'])/1024,1); ?> KB</td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="patient_file_view.php?id=<?php echo (int)$pf['id']; ?>" target="_blank" class="btn btn-success btn-sm">
                                                        <i class="fas fa-eye me-1"></i>View
                                                    </a>
                                                    <a href="patient_file_download.php?id=<?php echo (int)$pf['id']; ?>" class="btn btn-primary btn-sm">
                                                        <i class="fas fa-download me-1"></i>Download
                                                    </a>
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

        <!-- Documents Grid -->
        <div class="row">
            <?php if (empty($documents)): ?>
                <div class="col-12">
                    <div class="card">
                        <div class="card-body text-center py-5">
                            <i class="fas fa-folder-open fa-4x text-muted mb-4"></i>
                            <h4 class="text-muted">No Documents Available</h4>
                            <p class="text-muted">No documents have been uploaded by administrators yet.</p>
                            <small class="text-muted">Check back later for updates.</small>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($documents as $doc): ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card document-card h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-start mb-3">
                                <i class="fas fa-file-pdf file-icon me-3"></i>
                                <div class="flex-grow-1">
                                    <h6 class="card-title mb-1"><?php echo htmlspecialchars($doc['filename']); ?></h6>
                                    <?php if ($doc['description']): ?>
                                        <p class="card-text text-muted small mb-2"><?php echo htmlspecialchars($doc['description']); ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <small class="text-muted">
                                    <i class="fas fa-user me-1"></i><?php echo htmlspecialchars($doc['uploaded_by']); ?><br>
                                    <i class="fas fa-calendar me-1"></i><?php echo date('M j, Y', strtotime($doc['upload_date'])); ?><br>
                                    <i class="fas fa-weight me-1"></i><?php echo number_format($doc['file_size'] / 1024, 1) . ' KB'; ?>
                                </small>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <a href="<?php echo htmlspecialchars($doc['filepath']); ?>" 
                                   target="_blank" class="btn btn-success">
                                    <i class="fas fa-eye me-2"></i>View Document
                                </a>
                                <a href="<?php echo htmlspecialchars($doc['filepath']); ?>" 
                                   download class="btn btn-primary">
                                    <i class="fas fa-download me-2"></i>Download
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Documents Table View (Alternative) -->
        <?php if (!empty($documents)): ?>
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-list me-2"></i>Document List</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th><i class="fas fa-file me-2"></i>Document</th>
                                        <th><i class="fas fa-user me-2"></i>Uploaded By</th>
                                        <th><i class="fas fa-calendar me-2"></i>Date</th>
                                        <th><i class="fas fa-weight me-2"></i>Size</th>
                                        <th><i class="fas fa-cogs me-2"></i>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($documents as $doc): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-file-pdf file-icon me-3"></i>
                                                <div>
                                                    <div class="fw-bold"><?php echo htmlspecialchars($doc['filename']); ?></div>
                                                    <?php if ($doc['description']): ?>
                                                        <small class="text-muted"><?php echo htmlspecialchars($doc['description']); ?></small>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </td>
                                        <td><?php echo htmlspecialchars($doc['uploaded_by']); ?></td>
                                        <td><?php echo date('M j, Y g:i A', strtotime($doc['upload_date'])); ?></td>
                                        <td><?php echo number_format($doc['file_size'] / 1024, 1) . ' KB'; ?></td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="<?php echo htmlspecialchars($doc['filepath']); ?>" 
                                                   target="_blank" class="btn btn-success btn-sm">
                                                    <i class="fas fa-eye me-1"></i>View
                                                </a>
                                                <a href="<?php echo htmlspecialchars($doc['filepath']); ?>" 
                                                   download class="btn btn-primary btn-sm">
                                                    <i class="fas fa-download me-1"></i>Download
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
