<?php
/**
 * Admin Document Management Page (file-path storage)
 * Document Management System
 */

require_once __DIR__ . '/../includes/db_connect.php';

// Check if user is logged in and admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

$user_name = $_SESSION['user_name'];
$user_email = $_SESSION['user_email'];
$user_role = $_SESSION['user_role'];

$success_message = '';
$error_message = '';

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['document'])) {
    $file = $_FILES['document'];
    $filename = $file['name'];
    $file_tmp = $file['tmp_name'];
    $file_size = $file['size'];
    $file_error = $file['error'];

    if ($file_error === UPLOAD_ERR_OK) {
        $file_extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if ($file_extension !== 'pdf') {
            $error_message = 'Only PDF files are allowed.';
        } elseif ($file_size > 10 * 1024 * 1024) {
            $error_message = 'File size must be less than 10MB.';
        } else {
            $description = $_POST['description'] ?? '';
            $mime_type = mime_content_type($file_tmp);

            // Ensure uploads/documents directory exists (file-path storage)
            $baseDir = dirname(__DIR__);
            $uploadDir = $baseDir . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'documents';
            if (!is_dir($uploadDir)) {
                @mkdir($uploadDir, 0775, true);
            }

            // Sanitize and generate conflict-safe file name
            $safeName = preg_replace('/[^A-Za-z0-9_\.-]/', '_', $filename);
            if ($safeName === '' || $safeName === '.' || $safeName === '..') {
                $safeName = 'document';
            }
            $uniqueName = uniqid('doc_', true) . '_' . $safeName;
            $targetPathFs = $uploadDir . DIRECTORY_SEPARATOR . $uniqueName;
            $relativePath = 'uploads/documents/' . $uniqueName;

            if (!move_uploaded_file($file_tmp, $targetPathFs)) {
                $error_message = 'Failed to move uploaded file.';
            } else {
                try {
                    $stmt = $pdo->prepare("INSERT INTO documents (filename, mime_type, file_size, uploaded_by, description, file_path) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->bindParam(1, $filename);
                    $stmt->bindParam(2, $mime_type);
                    $stmt->bindParam(3, $file_size, PDO::PARAM_INT);
                    $stmt->bindParam(4, $user_email);
                    $stmt->bindParam(5, $description);
                    $stmt->bindParam(6, $relativePath);
                    $stmt->execute();

                    $success_message = 'Document uploaded successfully!';
                } catch (PDOException $e) {
                    // Roll back filesystem if DB insert fails
                    @unlink($targetPathFs);
                    $error_message = 'Database error. Please try again.';
                }
            }
        }
    } else {
        $error_message = 'File upload error. Please try again.';
    }
}

// Handle document deletion (also remove physical file if present)
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    try {
        $docId = (int)$_GET['delete'];

        // Fetch file_path for cleanup
        $stmt = $pdo->prepare("SELECT file_path FROM documents WHERE id = ?");
        $stmt->execute([$docId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row && !empty($row['file_path'])) {
            $baseDir = dirname(__DIR__);
            $path = $baseDir . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $row['file_path']);
            if (is_file($path)) {
                @unlink($path);
            }
        }

        $stmt = $pdo->prepare("DELETE FROM documents WHERE id = ?");
        $stmt->execute([$docId]);
        $success_message = 'Document deleted successfully!';
    } catch (PDOException $e) {
        $error_message = 'Failed to delete document.';
    }
}

// Fetch all documents
try {
    $stmt = $pdo->query("SELECT id, filename, uploaded_by, file_size, description, upload_date FROM documents ORDER BY upload_date DESC");
    $documents = $stmt->fetchAll();
} catch (PDOException $e) {
    $documents = [];
    $error_message = 'Failed to load documents.';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Document Management - Admin</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet" />
<link href='https://fonts.googleapis.com/css?family=Poppins:600,700|Roboto:400,500&display=swap' rel='stylesheet' />
<link rel="stylesheet" href="../assets/css/root_colors_fonts.css">
<style>
/* Styles remain the same as previous code */
body {
    background-color: var(--light-gray);
    font-family: var(--font-body);
    margin: 0;
    padding: 0;
}
.navbar-custom {
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
.avatar-circle {width:32px;height:32px;background-color:var(--trust-blue);border-radius:50%;color:white;font-weight:600;display:flex;justify-content:center;align-items:center;}
.page-header-card {background: var(--trust-blue); color:white; border-radius:8px; padding:1.5rem 2rem; margin:1rem 0 2rem; display:flex; justify-content:space-between; align-items:center; gap:1rem;}
.page-header-card h2{margin:0;font-weight:700;font-size:1.5rem;}
.page-header-card p{margin:0; font-weight:400;font-size:1rem;opacity:0.85;}
.card{border:none;border-radius:12px;box-shadow:0 4px 16px var(--shadow-light);margin-bottom:1.5rem;}
.card-header{background:white;border-radius:12px 12px 0 0 !important;border-bottom:2px solid var(--trust-blue); font-weight:600; color:var(--dark-text);}
.btn-primary{background-color:var(--trust-blue); border:none; border-radius:12px; font-weight:600;padding:0.55rem 1.75rem;color:white;}
.btn-success{background-color:var(--green-teal);border:none;border-radius:12px;color:white;}
.btn-danger{background-color:var(--red);border:none;border-radius:12px;color:white;}
.btn-group .btn-sm{font-size:0.85rem;padding:0.35rem 0.75rem;border-radius:8px;}
.table {background:white;border-radius:12px;width:100%;overflow:hidden;box-shadow:0 4px 16px rgba(0,0,0,0.06);}
.table thead tr {background-color:var(--primary-color); color:white;}
.file-icon {font-size:1.5rem;color:var(--accent-color);}
.fw-bold {color:var(--dark-text);}
.text-muted {color:var(--gray-text)!important;font-size:0.85rem;}
.btn-back{background-color:var(--trust-blue);border:none;border-radius:6px;padding:0.5rem 1.5rem;font-weight:500;color:white;text-decoration:none;display:inline-flex;align-items:center;gap:0.5rem;}
.btn-back:hover{background-color:var(--sky-blue);color:white;}
</style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-custom">
    <div class="container px-4">
        <a class="navbar-brand" href="admin_home.php"><img src="../assets/images/hospitallogo.png" style="width:50px; height: 50px"> Blue Pulse</a>
        <div class="dropdown ms-auto">
            <button class="btn dropdown-toggle btn-primary" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                <div class="avatar-circle"><?php echo strtoupper(substr($user_name, 0, 2)); ?></div>
                <span><?php echo ($user_role === 'admin') ? 'System Administrator' : htmlspecialchars($user_name); ?></span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                <li><a class="dropdown-item" href="#"><i class="fas fa-user-circle text-primary"></i> Profile</a></li>
                <li><a class="dropdown-item" href="#"><i class="fas fa-cog text-secondary"></i> Settings</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item text-danger" href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-4 px-4">
    <div class="page-header-card">
        <div>
            <h2><i class="fas fa-file-upload"></i> Document Management</h2>
            <p>Upload and manage PDF documents for doctors to access.</p>
        </div>
        <a href="admin_home.php" class="btn-back"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
    </div>

    <?php if($success_message): ?>
        <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success_message); ?></div>
    <?php endif; ?>
    <?php if($error_message): ?>
        <div class="alert alert-danger"><i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($error_message); ?></div>
    <?php endif; ?>

    <!-- Upload Form -->
    <div class="card">
        <div class="card-header"><i class="fas fa-upload"></i> Upload New Document</div>
        <div class="card-body">
            <form method="POST" enctype="multipart/form-data" novalidate>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="document" class="form-label"><i class="fas fa-file-pdf me-2"></i>Select PDF File</label>
                        <input type="file" class="form-control" id="document" name="document" accept=".pdf" required />
                        <div class="form-text">Only PDF files allowed. Max size: 10MB</div>
                    </div>
                    <div class="col-md-6">
                        <label for="description" class="form-label"><i class="fas fa-comment me-2"></i>Description (Optional)</label>
                        <input type="text" class="form-control" id="description" name="description" placeholder="Brief description" />
                    </div>
                </div>
                <div class="d-grid mt-3">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-upload me-2"></i> Upload Document</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Documents List -->
    <div class="card">
        <div class="card-header"><i class="fas fa-list"></i> Uploaded Documents</div>
        <div class="card-body p-0">
            <?php if (empty($documents)): ?>
                <div class="text-center text-muted py-5">
                    <i class="fas fa-folder-open fa-3x mb-3"></i>
                    <p>No documents uploaded yet.</p>
                    <small>Upload your first document using the form above.</small>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th>Document</th>
                                <th>Uploaded By</th>
                                <th>Date</th>
                                <th>Size</th>
                                <th>Actions</th>
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
                                            <?php if (!empty($doc['description'])): ?>
                                                <small class="text-muted"><?php echo htmlspecialchars($doc['description']); ?></small>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td><?php echo htmlspecialchars($doc['uploaded_by']); ?></td>
                                <td><?php echo date('M j, Y g:i A', strtotime($doc['upload_date'])); ?></td>
                                <td><?php echo number_format($doc['file_size']/1024,1).' KB'; ?></td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="document_view.php?id=<?php echo $doc['id']; ?>" target="_blank" class="btn btn-success btn-sm"><i class="fas fa-eye me-1"></i>View</a>
                                        <a href="document_download.php?id=<?php echo $doc['id']; ?>" class="btn btn-primary btn-sm"><i class="fas fa-download me-1"></i>Download</a>
                                        <button type="button" class="btn btn-danger btn-sm delete-doc-btn" data-doc-id="<?php echo $doc['id']; ?>" data-doc-name="<?php echo htmlspecialchars($doc['filename']); ?>"><i class="fas fa-trash me-1"></i>Delete</button>
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
const deleteDocButtons = document.querySelectorAll('.delete-doc-btn');
deleteDocButtons.forEach(btn => {
  btn.addEventListener('click', function() {
    const docId = this.dataset.docId;
    const docName = this.dataset.docName;
    Swal.fire({
      title: 'Delete Document?',
      text: 'Are you sure you want to delete "' + docName + '"? This action cannot be undone.',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#d33',
      cancelButtonColor: '#3085d6',
      confirmButtonText: 'Yes, delete it',
      cancelButtonText: 'Cancel'
    }).then((result) => {
      if (result.isConfirmed) {
        window.location.href = '?delete=' + docId;
      }
    });
  });
});
</script>
</body>
</html>
