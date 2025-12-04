<?php
require_once __DIR__ . '/../includes/db_connect.php';

// Ensure only admin can access
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

// Fetch user info
$user_name = $_SESSION['user_name'];
$user_role = $_SESSION['user_role'];

// Load recent files
$files = [];
try {
    $stmt = $pdo->query('SELECT f.*, u.name AS uploader_name FROM files f LEFT JOIN users u ON u.id = f.uploader_id ORDER BY f.upload_date DESC LIMIT 50');
    $files = $stmt->fetchAll();
} catch (Exception $e) {
    // TIDY: Silent failure for file loading
}

// Load user types once for the modal dropdown
$userTypes = [];
try {
    $typesStmt = $pdo->query('SELECT id, name FROM user_types ORDER BY name');
    $userTypes = $typesStmt->fetchAll();
} catch (Exception $e) {
    // TIDY: Silent failure for user types loading
}

$doctorUsers = [];
$patientUsers = [];
$hasAssignableUsers = false;

try {
    $docStmt = $pdo->query("SELECT id, name, email FROM users WHERE role = 'doctor' ORDER BY name");
    $doctorUsers = $docStmt->fetchAll();
} catch (Exception $e) {
    // TIDY: Silent failure for file loading
}

try {
    $patStmt = $pdo->query("SELECT id, name, email FROM users WHERE role = 'patient' ORDER BY name");
    $patientUsers = $patStmt->fetchAll();
} catch (Exception $e) {
    // TIDY: Silent failure for file loading
}

if (!empty($doctorUsers) || !empty($patientUsers)) {
    $hasAssignableUsers = true;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>File Templates - Document Management System</title>

    <!-- Bootstrap & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href='https://fonts.googleapis.com/css?family=Poppins:600,700|Roboto:400,500&display=swap' rel='stylesheet'>

    <!-- Root Styles -->
    <link rel="stylesheet" href="../assets/css/root_colors_fonts.css">
    <style>

      body {
          background-color: var(--light-gray);
          font-family: var(--font-body);
          margin: 0;
      }

/* Navbar */
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
.navbar-brand i {
    font-size: 1.3rem;
}
.navbar-custom .btn {
    color: var(--white);
    border: none;
}
.navbar-custom .btn:hover {
    background-color: var(--trust-blue);
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

/* User Dropdown Button */
.btn-primary.dropdown-toggle {
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

/* Dropdown menu */
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

.dropdown-item:hover, .dropdown-item:focus {
    background-color: var(--sky-blue);
    color: var(--dark-gray);
}

.dropdown-item.text-danger {
    color: var(--red) !important;
}
    /* Page Header */
    .page-header {
        background: var(--trust-blue);
        color: var(--white);
        border-radius: 8px;
        padding: 1.5rem 2rem;
        margin: 2rem 0;
        box-shadow: 0 8px 16px var(--shadow-light);
        display: flex;
    justify-content: space-between;
    align-items: center;
    }
    .page-header h2 {
        font-family: var(--font-headings);
        font-weight: 700;
        margin-bottom: 0.3rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }
    .page-header p { opacity: 0.85; }

    /* Cards */
    .card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 1px 6px var(--shadow-light);
    }
    .card-header i {
      color: var(--trust-blue);
    }
    .card-header {
        background-color: var(--white);
        border-bottom: 2px solid var(--trust-blue);
        font-family: var(--font-headings);
        font-weight: 600;
        color: var(--dark-gray);
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    /* Buttons */
    .btn-primary {
    background-color: var(--trust-blue);
    border: none;
    border-radius: 12px;
    font-weight: 600;
    padding: 0.55rem 1.75rem;
    font-size: 1rem;
    transition: all 0.3s ease;
    color: white;
    }
    .btn-primary:hover {
        background-color: var(--sky-blue);
        box-shadow: 0 6px 20px var(--shadow-light);
    }
.btn-success {
    border: none;
    border-radius: 0px 12px 12px 0px ;
    font-weight: 400;
    color: white;
}
.btn-danger {
    border: none;
    border-radius: 0px 8px 8px 0px !important;
    font-weight: 400;
    color: white;
}
.btn-secondary{
  height: 32px;
}
.btn-group {
  height: 40px;
  align-items: center;
}
.btn-group .btn-sm {
    font-size: 0.85rem;
    padding: 0.35rem 0.50rem;
    border-radius: 8px;
    min-width: 80px;
}
.btn-group > .btn {
    min-width: 100px;
}
.btn-group i {
  margin-right: 5px;
}

        /* Table */
        .table {
        background: white;
        border-radius: 12px;
        width: 100%;
        overflow: hidden;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.06);
        margin-bottom: 0;
    }
        .table th {
            background-color: var(--white);
            color: var(--dark-gray);
            font-weight: 600;
            font-family: var(--font-body);
        }
        .table td { color: var(--dark-text); }
        .table tr {
          align-items: center;
        }

        /* UI Improvement: Enhanced modal styling with better spacing */
        .modal-content {
            border-radius: 12px;
            border: none;
            box-shadow: 0 8px 24px rgba(0,0,0,0.15);
        }
        .modal-header {
            background-color: var(--trust-blue);
            color: var(--white);
            border-bottom: none;
            padding: 1.5rem 2rem;
            border-radius: 12px 12px 0 0;
        }
        .modal-header .modal-title {
            font-family: var(--font-headings);
            font-weight: 600;
            font-size: 1.25rem;
        }
        .modal-body {
            padding: 2rem;
        }
        .modal-footer {
            border-top: 1px solid var(--light-gray);
            padding: 1.25rem 2rem;
            border-radius: 0 0 12px 12px;
        }
        .modal-footer .btn-primary {
            background-color: var(--trust-blue);
            border: none;
            border-radius: 8px;
            padding: 0.625rem 1.5rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        .modal-footer .btn-primary:hover {
            background-color: var(--deep-navy);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }
        .modal-footer .btn-outline-secondary {
            border-radius: 8px;
            padding: 0.625rem 1.5rem;
            font-weight: 500;
        }
        .assign-toggle .btn {
            border-radius: 999px !important;
        }
        /* UI Improvement: Better assignment panel styling */
        .assignment-panel {
            border: 2px solid var(--sky-blue);
            border-radius: 12px;
            padding: 1.5rem;
            background: var(--white);
            box-shadow: 0 4px 12px rgba(32,56,100,0.08);
            transition: all 0.3s ease;
        }
        .assignment-panel.inactive {
            opacity: 0.5;
            background: var(--light-gray);
            border-color: var(--light-gray-2);
        }
        .assignment-panel h6 {
            font-weight: 600;
            margin-bottom: 0.75rem;
            color: var(--dark-gray);
            font-size: 1rem;
        }
        .assign-helper {
            font-size: 0.875rem;
            color: var(--dark-gray);
            opacity: 0.7;
            margin-bottom: 1rem;
        }
        .assign-meta {
            background: var(--light-gray);
            border-radius: 12px;
            padding: 1rem 1.25rem;
            font-size: 0.9rem;
            margin-bottom: 1.5rem;
        }
        .assign-meta .label {
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.08em;
            color: var(--trust-blue);
            font-weight: 600;
        }
        .allow-download-toggle .form-check-input {
            width: 3rem;
            height: 1.5rem;
        }
        .allow-download-toggle .form-check-label {
            font-weight: 600;
            color: var(--dark-gray);
        }
        .permission-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            padding: 0.35rem 0.75rem;
            border-radius: 999px;
            font-size: 0.8rem;
            background: var(--sky-blue);
            color: var(--dark-gray);
            margin-right: 0.5rem;
            margin-bottom: 0.25rem;
        }
        .permission-pill .download-flag {
            color: var(--trust-blue);
        }
        .assign-helper {
            font-size: 0.85rem;
            color: var(--dark-gray);
        }
        .email-feedback {
            font-size: 0.85rem;
        }
        .btn-back {
            background-color: var(--trust-blue);
            border: none;
            border-radius: 6px;
            padding: 0.5rem 1.5rem;
            font-weight: 500;
            transition: background-color 0.3s ease-in-out;
            color: var(--white);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

    .btn-back:hover {
        background-color: var(--sky-blue);
        color: var(--white);
    }
    </style>
</head>
<body>

<!-- Navigation -->
<nav class="navbar navbar-expand-lg navbar-custom">
    <div class="container px-4">
      <a class="navbar-brand" href="../admin/admin_home.php"><img src="../assets/images/hospitallogo.png" style="width:50px; height: 50px"> Blue Pulse</a>

        <!-- User Dropdown -->
        <div class="dropdown ms-auto">
            <button class="btn dropdown-toggle btn-primary" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false" aria-haspopup="true" aria-controls="userDropdownMenu">
                <div class="avatar-circle"><?php echo strtoupper(substr(htmlspecialchars($user_name), 0, 2)); ?></div>
                <span><?php echo ($user_role === 'admin') ? 'System Administrator' : htmlspecialchars($user_name); ?></span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown" id="userDropdownMenu">
                <li><a class="dropdown-item" href="#"><i class="fas fa-user-circle text-primary"></i> Profile</a></li>
                <li><a class="dropdown-item" href="#"><i class="fas fa-cog text-secondary"></i> Settings</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item text-danger" href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>
    </div>
</nav>

<div class="container mb-5">

  <!-- Page Header -->
  <section class="page-header">
    <div>
      <h2><i class="fas fa-folder-open"></i> File Templates</h2>
      <p>Upload, view, and manage document templates used in your system.</p>
    </div>
    <a href="../admin/admin_home.php" class="btn-back">
        <i class="fas fa-arrow-left"></i> Back to Dashboard
    </a>
    
  </section>

  <!-- Upload Section -->
  <div class="card mb-4">
    <div class="card-header"><i class="fas fa-upload"></i> Upload File Template</div>
    <div class="card-body">
      <form method="POST" action="../admin/admin_file_templates_upload.php" enctype="multipart/form-data">
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">Select File</label>
            <input type="file" class="form-control" name="file" required>
            <small class="text-muted">Allowed: PDF, DOCX, XLSX, PNG, JPG (Max 10MB)</small>
          </div>
          <div class="col-md-6">
            <label class="form-label">Display Name</label>
            <input type="text" class="form-control" name="name" placeholder="e.g., Patient Intake Form" required>
          </div>
        </div>
        <div class="row g-3 mt-1">
          <div class="col-md-4">
            <label class="form-label">Assign User Type</label>
            <select class="form-select" name="assigned_user_role" id="assigned_user_role" required <?php echo $hasAssignableUsers ? '' : 'disabled'; ?>>
              <option value="">Select type</option>
              <option value="doctor">Doctor</option>
              <option value="patient">Patient</option>
            </select>
          </div>
          <div class="col-md-8">
            <label class="form-label">Select User</label>
            <select class="form-select" name="assigned_user_id" id="assigned_user_id" required <?php echo $hasAssignableUsers ? '' : 'disabled'; ?>>
              <option value="">Select user</option>
              <?php foreach ($doctorUsers as $doc): ?>
              <option value="<?php echo (int)$doc['id']; ?>" data-role="doctor">Doctor - <?php echo htmlspecialchars($doc['name']); ?> (<?php echo htmlspecialchars($doc['email']); ?>)</option>
              <?php endforeach; ?>
              <?php foreach ($patientUsers as $pat): ?>
              <option value="<?php echo (int)$pat['id']; ?>" data-role="patient">Patient - <?php echo htmlspecialchars($pat['name']); ?> (<?php echo htmlspecialchars($pat['email']); ?>)</option>
              <?php endforeach; ?>
            </select>
            <small class="text-muted">Users matching the selected type will be available.</small>
          </div>
        </div>
        <?php if (!$hasAssignableUsers): ?>
        <div class="mt-2 text-danger">Add doctor or patient accounts before assigning templates.</div>
        <?php endif; ?>
        <div class="mt-3">
          <button class="btn btn-primary"><i class="fas fa-cloud-upload-alt me-2"></i>Upload</button>
        </div>
      </form>
    </div>
  </div>

  <!-- File List -->
  <div class="card">
    <div class="card-header"><i class="fas fa-list"></i> Recent Files</div>
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-hover align-middle">
          <thead>
            <tr>
              <th><i class="fas fa-user me-2"></i>Name</th>
              <th><i class="fas fa-file me-2"></i>Original</th>
              <th><i class="fas fa-file me-2"></i>Type</th>
              <th><i class="fas fa-weight me-2"></i>Size</th>
              <th><i class="fas fa-calendar me-2"></i>Uploaded</th>
              <th><i class="fas fa-cogs me-2"></i>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php if (count($files) > 0): ?>
              <?php foreach ($files as $f): ?>
              <tr>
                <td><?php echo htmlspecialchars($f['name']); ?></td>
                <td><?php echo htmlspecialchars($f['original_filename']); ?></td>
                <td><?php echo htmlspecialchars($f['mime_type']); ?></td>
                <td><?php echo number_format($f['size']/1024,1) . ' KB'; ?></td>
                <td><?php echo htmlspecialchars(date('Y-m-d H:i', strtotime($f['upload_date']))); ?></td>
                <td>
                  <div class="btn-group">
                    <a class="btn btn-sm btn-success" target="_blank" href="view_file.php?id=<?php echo (int)$f['id']; ?>"><i class="fas fa-eye"></i>View</a>
                    <button type="button" class="btn btn-sm btn-secondary assign-permission-btn" data-file-id="<?php echo (int)$f['id']; ?>" data-file-name="<?php echo htmlspecialchars($f['name']); ?>"><i class="fas fa-user-shield"></i>Assign</button>
                    <button type="button" class="btn btn-sm btn-danger delete-file-btn" data-file-id="<?php echo (int)$f['id']; ?>" data-file-name="<?php echo htmlspecialchars($f['name']); ?>"><i class="fas fa-trash"></i>Delete</button>
                  </div>
                </td>
              </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr><td colspan="6" class="text-center text-muted">No files uploaded yet.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Permissions Modal -->
<div class="modal fade" id="permModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fas fa-user-lock me-2"></i> Assign File Visibility</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="permForm" method="POST" action="../admin/admin_permissions.php" novalidate>
        <div class="modal-body">
          <input type="hidden" name="file_id" id="perm_file_id">
          <div class="assign-meta mb-3">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <div class="label">File</div>
                <div id="perm_file_label" class="fw-semibold text-dark">—</div>
              </div>
              <div class="text-end">
                <div class="label">Current Visibility</div>
                <div id="perm_summary" class="small text-muted">No assignment yet.</div>
              </div>
            </div>
          </div>

          <!-- UI Improvement: Better toggle button styling -->
          <div class="mb-4 assign-toggle text-center">
            <div class="btn-group" role="group" aria-label="Assign mode selector">
              <input type="radio" class="btn-check" name="assignment_mode" id="assignment_mode_type" value="user_type" checked>
              <label class="btn btn-outline-primary px-4 py-2" for="assignment_mode_type"><i class="fas fa-users me-2"></i>User Type</label>

              <input type="radio" class="btn-check" name="assignment_mode" id="assignment_mode_email" value="user_email">
              <label class="btn btn-outline-primary px-4 py-2" for="assignment_mode_email"><i class="fas fa-envelope me-2"></i>Specific Email</label>
            </div>
          </div>

          <div class="row g-3">
            <div class="col-12 col-md-6">
              <div id="assign_user_type_panel" class="assignment-panel">
                <h6><i class="fas fa-layer-group me-2 text-primary"></i>User Type</h6>
                <p class="assign-helper">Choose a role to grant access. Users under this role will see the file.</p>
                <select class="form-select" name="user_type_id" id="perm_user_type">
                  <option value="">Select a user type</option>
                  <?php if (empty($userTypes)): ?>
                    <option value="" disabled>No user types available</option>
                  <?php else: ?>
                    <?php foreach ($userTypes as $type): ?>
                      <option value="<?php echo (int)$type['id']; ?>"><?php echo htmlspecialchars($type['name']); ?></option>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </select>
                <div class="invalid-feedback" id="user_type_error">Please select a user type.</div>
              </div>
            </div>
            <div class="col-12 col-md-6">
              <div id="assign_email_panel" class="assignment-panel inactive">
                <h6><i class="fas fa-user-check me-2 text-primary"></i>Specific Email</h6>
                <p class="assign-helper">Grant this file to an individual email. Start typing to search users.</p>
                <input type="email" class="form-control" name="user_email" id="perm_user_email" placeholder="name@example.com" autocomplete="off" list="emailSuggestions" disabled>
                <datalist id="emailSuggestions"></datalist>
                <div class="email-feedback mt-2" id="email_feedback"></div>
              </div>
            </div>
          </div>

          <!-- UI Improvement: Better form spacing -->
          <div class="allow-download-toggle form-check form-switch mt-4 p-3 rounded" style="background-color: var(--light-gray);">
            <input class="form-check-input" type="checkbox" value="1" id="can_download" name="can_download">
            <label class="form-check-label fw-semibold" for="can_download">Allow download for this assignment</label>
            <div class="form-text mt-1">If disabled, users may view but not download.</div>
          </div>

          <div class="mt-4">
            <div class="alert alert-danger d-none" id="perm_form_alert"></div>
            <div class="alert alert-success d-none" id="perm_form_success"></div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">
            <span class="me-2"><i class="fas fa-save"></i></span>Save Assignment
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
const assignedRoleSelect = document.getElementById('assigned_user_role');
const assignedUserSelect = document.getElementById('assigned_user_id');

const filterAssignedUsers = () => {
  if (!assignedUserSelect) return;
  const selectedRole = assignedRoleSelect ? assignedRoleSelect.value : '';
  let firstMatch = null;
  Array.from(assignedUserSelect.options).forEach(option => {
    if (!option.value) {
      option.hidden = false;
      option.disabled = false;
      return;
    }
    const matches = !selectedRole || option.dataset.role === selectedRole;
    option.hidden = !matches;
    option.disabled = !matches;
    if (matches && !firstMatch) {
      firstMatch = option;
    }
  });
  if (assignedUserSelect.selectedOptions.length) {
    const current = assignedUserSelect.selectedOptions[0];
    if (current.disabled) {
      assignedUserSelect.value = '';
    }
  }
  if (!assignedUserSelect.value && firstMatch) {
    assignedUserSelect.value = firstMatch.value;
  }
};

if (assignedRoleSelect && assignedUserSelect) {
  assignedRoleSelect.addEventListener('change', filterAssignedUsers);
  filterAssignedUsers();
}

const assigns = document.querySelectorAll('.assign-permission-btn');
const permModalEl = document.getElementById('permModal');
const permForm = document.getElementById('permForm');
const bsPermModal = new bootstrap.Modal(permModalEl);
const fileIdInput = document.getElementById('perm_file_id');
const fileLabel = document.getElementById('perm_file_label');
const permSummary = document.getElementById('perm_summary');
const assignmentModeRadios = document.querySelectorAll('input[name="assignment_mode"]');
const userTypePanel = document.getElementById('assign_user_type_panel');
const emailPanel = document.getElementById('assign_email_panel');
const userTypeSelect = document.getElementById('perm_user_type');
const emailInput = document.getElementById('perm_user_email');
const emailFeedback = document.getElementById('email_feedback');
const userTypeError = document.getElementById('user_type_error');
const canDownloadToggle = document.getElementById('can_download');
const alertBox = document.getElementById('perm_form_alert');
const successBox = document.getElementById('perm_form_success');
const emailSuggestionsList = document.getElementById('emailSuggestions');

let emailLookupTimer = null;
let emailExists = false;
let currentFileId = null;

const resetForm = () => {
  permForm.reset();
  fileIdInput.value = '';
  fileLabel.textContent = '—';
  permSummary.textContent = 'No assignment yet.';
  canDownloadToggle.checked = false;
  userTypeSelect.value = '';
  emailInput.value = '';
  emailInput.dataset.userId = '';
  emailInput.disabled = true;
  emailPanel.classList.add('inactive');
  userTypePanel.classList.remove('inactive');
  assignmentModeRadios.forEach(radio => radio.checked = radio.value === 'user_type');
  hideAlert();
  hideSuccess();
  emailFeedback.textContent = '';
  emailFeedback.classList.remove('text-success', 'text-danger');
  userTypeError.style.display = 'none';
  emailExists = false;
  emailSuggestionsList.innerHTML = '';
};

const hideAlert = () => {
  alertBox.classList.add('d-none');
  alertBox.textContent = '';
};

const showAlert = (message) => {
  alertBox.textContent = message;
  alertBox.classList.remove('d-none');
};

const hideSuccess = () => {
  successBox.classList.add('d-none');
  successBox.textContent = '';
};

const showSuccess = (message) => {
  successBox.textContent = message;
  successBox.classList.remove('d-none');
};

const togglePanels = (mode) => {
  if (mode === 'user_type') {
    userTypePanel.classList.remove('inactive');
    emailPanel.classList.add('inactive');
    emailInput.disabled = true;
  } else {
    userTypePanel.classList.add('inactive');
    emailPanel.classList.remove('inactive');
    emailInput.disabled = false;
  }
};

assignmentModeRadios.forEach(radio => {
  radio.addEventListener('change', (evt) => {
    togglePanels(evt.target.value);
  });
});

assigns.forEach(btn => {
  btn.addEventListener('click', () => {
    resetForm();
    currentFileId = btn.dataset.fileId;
    fileIdInput.value = currentFileId;
    fileLabel.textContent = btn.dataset.fileName || 'Selected file';
    bsPermModal.show();
    loadPermissions(currentFileId);
  });
});

const escapeHtml = (unsafe = '') => unsafe
  .replace(/&/g, '&amp;')
  .replace(/</g, '&lt;')
  .replace(/>/g, '&gt;')
  .replace(/"/g, '&quot;')
  .replace(/'/g, '&#039;');

const buildSummary = (assignments) => {
  if (!assignments || assignments.length === 0) {
    return 'No assignment yet.';
  }
  return assignments.map(item => {
    const flag = item.can_download ? '<i class="fas fa-download download-flag"></i>' : '<i class="fas fa-ban text-danger"></i>';
    return `<span class="permission-pill">${escapeHtml(item.label)} ${flag}</span>`;
  }).join('');
};

const loadPermissions = async (fileId) => {
  try {
    const response = await fetch('get_file_permissions.php?file_id=' + fileId);
    if (!response.ok) {
      permSummary.textContent = 'Unable to load current permissions.';
      return;
    }
    const data = await response.json();
    permSummary.innerHTML = buildSummary(data.assignments || []);
    const mode = data.assignment_mode || 'user_type';
    assignmentModeRadios.forEach(radio => {
      radio.checked = radio.value === mode;
    });
    togglePanels(mode);

    if (mode === 'user_email' && data.user && data.user.email) {
      emailInput.value = data.user.email;
      emailExists = true;
      emailInput.dataset.userId = data.user.id || '';
      emailFeedback.textContent = `Found ${data.user.name || data.user.email}`;
      emailFeedback.classList.remove('text-danger');
      emailFeedback.classList.add('text-success');
      canDownloadToggle.checked = data.user.can_download === 1;
    } else if (mode === 'user_type' && data.user_type) {
      userTypeSelect.value = data.user_type.id || '';
      canDownloadToggle.checked = data.user_type.can_download === 1;
    } else {
      userTypeSelect.value = '';
      emailInput.value = '';
      canDownloadToggle.checked = false;
    }
  } catch (error) {
    console.error('Error loading permissions', error);
    permSummary.textContent = 'Unable to load current permissions.';
  }
};

const fetchEmailSuggestions = async (query) => {
  emailSuggestionsList.innerHTML = '';
  if (query.length < 2) {
    return;
  }
  try {
    const response = await fetch('user_lookup.php?q=' + encodeURIComponent(query));
    if (!response.ok) return;
    const data = await response.json();
    (data.results || []).forEach(user => {
      const option = document.createElement('option');
      option.value = user.email;
      option.textContent = user.name ? `${user.name} (${user.email})` : user.email;
      emailSuggestionsList.appendChild(option);
    });
  } catch (error) {
    console.error('Email suggestion error', error);
  }
};

const verifyEmailExists = async (email) => {
  if (!email) {
    emailExists = false;
    emailFeedback.textContent = '';
    emailFeedback.classList.remove('text-success', 'text-danger');
    return;
  }
  try {
    const response = await fetch('user_lookup.php?email=' + encodeURIComponent(email));
    if (!response.ok) throw new Error('lookup failed');
    const data = await response.json();
    if (data.exists) {
      emailExists = true;
      emailInput.dataset.userId = data.user.id;
      emailFeedback.textContent = `Assigned to ${data.user.name} (${data.user.email})`;
      emailFeedback.classList.remove('text-danger');
      emailFeedback.classList.add('text-success');
    } else {
      emailExists = false;
      emailInput.dataset.userId = '';
      emailFeedback.textContent = 'No user found with that email.';
      emailFeedback.classList.remove('text-success');
      emailFeedback.classList.add('text-danger');
    }
  } catch (error) {
    emailExists = false;
    emailFeedback.textContent = 'Unable to validate email.';
    emailFeedback.classList.remove('text-success');
    emailFeedback.classList.add('text-danger');
  }
};

emailInput.addEventListener('input', (event) => {
  const value = event.target.value.trim();
  emailExists = false;
  emailFeedback.textContent = '';
  emailFeedback.classList.remove('text-success', 'text-danger');
  clearTimeout(emailLookupTimer);
  fetchEmailSuggestions(value);
  emailLookupTimer = setTimeout(() => verifyEmailExists(value), 500);
});

permForm.addEventListener('submit', async (event) => {
  event.preventDefault();
  hideAlert();
  hideSuccess();

  const mode = document.querySelector('input[name="assignment_mode"]:checked').value;
  const selectedUserTypeId = userTypeSelect.value;
  const emailValue = emailInput.value.trim();
  if (!currentFileId) {
    showAlert('File reference is missing.');
    return;
  }
  if (mode === 'user_type' && !selectedUserTypeId) {
    userTypeError.style.display = 'block';
    showAlert('Please select a user type.');
    return;
  }
  userTypeError.style.display = 'none';
  if (mode === 'user_email') {
    if (!emailValue) {
      showAlert('Please provide the user email.');
      return;
    }
    if (!emailExists) {
      showAlert('Email must match an existing user before saving.');
      return;
    }
  }

  const formData = new FormData();
  formData.append('file_id', currentFileId);
  formData.append('assignment_mode', mode);
  if (mode === 'user_type') {
    formData.append('user_type_id', selectedUserTypeId);
  } else {
    formData.append('user_email', emailValue);
  }
  if (canDownloadToggle.checked) {
    formData.append('can_download', '1');
  }

  try {
    const response = await fetch('../admin/admin_permissions.php', {
      method: 'POST',
      headers: { 'Accept': 'application/json' },
      body: formData
    });
    const result = await response.json();
    if (!response.ok || !result.success) {
      showAlert(result.message || 'Unable to save permissions.');
      return;
    }
    showSuccess('Permissions updated successfully.');
    setTimeout(() => {
      bsPermModal.hide();
      window.location.reload();
    }, 900);
  } catch (error) {
    console.error('Save permissions failed', error);
    showAlert('Unexpected error while saving permissions.');
  }
});

const deleteButtons = document.querySelectorAll('.delete-file-btn');
deleteButtons.forEach(btn => {
  btn.addEventListener('click', function() {
    const fileId = this.dataset.fileId;
    const fileName = this.dataset.fileName;
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
        window.location.href = 'delete_template.php?id=' + fileId;
      }
    });
  });
});
</script>
<?php
if (isset($_SESSION['admin_file_upload_status'])) {
    $status = $_SESSION['admin_file_upload_status'];
    unset($_SESSION['admin_file_upload_status']);
    $title = '';
    $icon = '';
    
    if ($status === 'success') {
        $title = 'File successfully uploaded.';
        $icon = 'success';
    } elseif ($status === 'invalid') {
        $title = 'Invalid or failed upload.';
        $icon = 'error';
    } elseif ($status === 'failed') {
        $title = 'Invalid or failed upload.';
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

if (isset($_SESSION['admin_file_delete_status'])) {
    $status = $_SESSION['admin_file_delete_status'];
    unset($_SESSION['admin_file_delete_status']);
    $title = '';
    $icon = '';
    
    if ($status === 'success') {
        $title = 'File successfully deleted.';
        $icon = 'success';
    } elseif ($status === 'failed') {
        $title = 'Failed to delete file.';
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
</body>
</html>
