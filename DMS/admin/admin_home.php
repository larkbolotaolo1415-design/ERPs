<?php
/**
 * Admin Home Page
 * Document Management System
 * 
 * Enhanced Design based on provided layout
 */

require_once __DIR__ . '/../includes/db_connect.php';

// Check if user is logged in and has admin role
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

// Get user information
$user_name = $_SESSION['user_name'];
$user_email = $_SESSION['user_email'];
$user_role = $_SESSION['user_role'];

// Get system statistics
try {
    $stmt = $pdo->query("SELECT COUNT(*) as total_users FROM users");
    $total_users = $stmt->fetch()['total_users'];

    $stmt = $pdo->query("SELECT role, COUNT(*) as count FROM users GROUP BY role");
    $role_counts = $stmt->fetchAll();

    $stmt = $pdo->query("SELECT COUNT(*) as pending_resets FROM password_reset WHERE expires_at > NOW()");
    $pending_resets = $stmt->fetch()['pending_resets'];

} catch (PDOException $e) {
    $total_users = 0;
    $role_counts = [];
    $pending_resets = 0;
}

// Pagination settings for audit logs
$logs_per_page = 12;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($current_page < 1) {
    $current_page = 1;
}

// Get total count of audit logs
$total_logs = 0;
$total_pages = 0;
try {
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM audit_logs");
    $total_logs = (int)($stmt->fetch()['total'] ?? 0);
    $total_pages = $total_logs > 0 ? (int)ceil($total_logs / $logs_per_page) : 0;
} catch (PDOException $e) {
    $total_logs = 0;
    $total_pages = 0;
}

// Ensure current page is not beyond last page
if ($total_pages > 0 && $current_page > $total_pages) {
    $current_page = $total_pages;
} elseif ($total_pages === 0) {
    $current_page = 1;
}

$offset = ($current_page - 1) * $logs_per_page;

// Get audit logs with pagination
$audit_logs = [];
try {
    $stmt = $pdo->prepare("
        SELECT al.id, al.admin_id, al.action, al.target_type, al.target_id, al.details, al.timestamp,
               u.name as user_name, u.email as user_email, u.role as user_role
        FROM audit_logs al
        LEFT JOIN users u ON u.id = al.admin_id
        ORDER BY al.timestamp DESC
        LIMIT :limit OFFSET :offset
    ");
    $stmt->bindValue(':limit', $logs_per_page, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $audit_logs = $stmt->fetchAll();
} catch (PDOException $e) {
    $audit_logs = [];
}

// Helper function for role count
function getRoleCount($role_counts, $role_name) {
    foreach ($role_counts as $role) {
        if ($role['role'] === $role_name) {
            return $role['count'];
        }
    }
    return 0;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Admin Dashboard - Document Management System</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />

    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet" />

    <!-- Google Fonts -->
    <link href='https://fonts.googleapis.com/css?family=Poppins:600,700|Roboto:400,500&display=swap' rel='stylesheet' />

    <!-- Root Styles -->
    <link rel="stylesheet" href="../assets/css/root_colors_fonts.css">

<style>

/* General */
body {
    background-color: var(--white);
    font-family: var(--font-body);
    margin: 0;
    padding: 0;
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

/* Dashboard Header */
.dashboard-header {
    background: var(--trust-blue);
    color: var(--white);
    border-radius: 8px;
    box-shadow: 0 8px 16px var(--shadow-light);
    padding: 1.5rem 2rem;
    margin-bottom: 1.75rem;
    display: flex;
    flex-direction: column;
}
.dashboard-header h2 {
    font-family: var(--font-headings);
    font-weight: 700;
    font-size: 1.5rem;
    margin-bottom: 0.3rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}
.dashboard-header h2 i {
    font-size: 1.5rem;
}
.dashboard-header p {
    font-weight: 400;
    font-size: 1rem;
    opacity: 0.85;
    margin: 0;
}

/* UI Improvement: Better stats cards layout with responsive grid */
.stats-container {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}
.stats-card {
    background: var(--white);
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    padding: 1.5rem 1rem;
    text-align: center;
    transition: box-shadow 0.3s ease, transform 0.3s ease;
    cursor: default;
    border: 1px solid var(--light-gray);
}
.stats-card:hover {
    box-shadow: 0 8px 16px var(--shadow-light);
    transform: translateY(-3px);
}
.stats-icon {
    font-size: 2.5rem;
    margin-bottom: 0.5rem;
    border-radius: 50%;
    width: 56px;
    height: 56px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: auto;
    padding: 2rem;
}
.icon-blue {
    color: var(--trust-blue);
}
.icon-green {
    color: var(--green-teal);
}
.icon-darkblue {
    color: var(--deep-navy);
}
.stats-number {
    font-size: 1.75rem;
    font-weight: 700;
    margin-bottom: 0.25rem;
    color: var(--dark-gray);
}
.stats-label {
    font-weight: 500;
    font-size: 0.9rem;
    color: var(--dark-gray);
    opacity: 0.85
}

/* UI Improvement: Better info and quick actions container with responsive grid */
.info-quick-container {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}
.info-card, .quick-actions-card {
    background: var(--white);
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    padding: 1.5rem;
    border: 1px solid var(--light-gray);
    transition: box-shadow 0.3s ease;
}
.info-card:hover, .quick-actions-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.12);
}

/* Card Titles */
h5.card-title {
    font-family: var(--font-headings);
    font-weight: 500;
    font-size: 1.1rem;
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: var(--dark-gray);
}

h5.card-title i {
    font-size: 1.25rem;
    color: var(--trust-blue);
}


/* UI Improvement: Better user information table styling */
.info-table {
    width: 100%;
    font-size: 1rem;
    color: var(--dark-gray);
    border-collapse: separate;
    border-spacing: 0 0.75rem;
}

.info-table td:first-child {
    font-weight: 600;
    width: 120px;
    padding: 0.5rem 0;
    color: var(--dark-gray);
}

.info-table td:last-child {
    font-weight: 500;
    padding: 0.5rem 0;
    text-align: right;
    color: var(--dark-gray);
}

/* Role Badge */
.role-badge {
    display: inline-block;
    padding: 3px 10px;
    background-color: var(--red);
    color: var(--white);
    font-weight: 600;
    border-radius: 9999px;
    font-size: 0.8rem;
    text-transform: capitalize;
}

/* UI Improvement: Better quick actions buttons with improved spacing */
.quick-actions-card .btn {
    width: 100%;
    margin-bottom: 0.75rem;
    font-weight: 500;
    border-radius: 8px;
    padding: 0.75rem 1rem;
    font-size: 1rem;
    transition: all 0.3s ease-in-out;
    color: var(--white);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
}
.quick-actions-card .btn:last-child {
    margin-bottom: 0;
}
.quick-actions-card .btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}
.btn-file-templates {
    background-color: var(--trust-blue);
}
.btn-file-templates:hover {
    background-color: var(--sky-blue);
}
.btn-manage-documents {
    background-color: var(--trust-blue);
}
.btn-manage-documents:hover {
    background-color: var(--sky-blue);
}
.btn-add-user {
    background-color: var(--trust-blue);
}
.btn-add-user:hover {
    background-color: var(--sky-blue);
}
.quick-actions-note {
    font-size: 0.75rem;
    color: var(--dark-gray);
    text-align: center;
    margin-top: 6px;
}

/* UI Improvement: Better role distribution card styling */
.role-distribution-card {
    background: var(--white);
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    padding: 1.5rem 2rem;
    font-size: 0.9rem;
    border: 1px solid var(--light-gray);
    margin-bottom: 2rem;
}
.role-distribution-title {
    font-family: 'Poppins', sans-serif;
    font-weight: 600;
    font-size: 1.1rem;
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    color: var(--dark-text);
    gap: 0.5rem;
}
.role-distribution-title i {
    font-size: 1.25rem;
    color: var(--trust-blue);
}

.roles-flex {
    display: flex;
    justify-content: space-around;
    text-align: center;
}

.role-item {
    flex: 1;
    color: var(--dark-gray);
}

.role-count {
    font-weight: 700;
    font-size: 1.5rem;
    margin-bottom: 4px;
}

.text-admin {
    color: var(--red);
}

.text-doctor {
    color: var(--green-teal);
}

.text-patient {
    color: var(--deep-navy);
}

/* UI Improvement: Enhanced audit logs table styling */
.audit-logs-table {
    font-size: 0.9rem;
    margin-bottom: 0;
    width: 100%;
    border-radius: 8px;
    overflow: hidden;
}

.audit-logs-table th {
    font-weight: 600;
    color: var(--dark-gray);
    background-color: var(--light-gray);
    border-bottom: 2px solid var(--light-gray-2);
    padding: 1rem 1.25rem;
    white-space: nowrap;
    text-transform: uppercase;
    font-size: 0.8rem;
    letter-spacing: 0.5px;
}

.audit-logs-table td {
    padding: 1rem 1.25rem;
    vertical-align: middle;
    border-bottom: 1px solid var(--light-gray);
    color: var(--dark-gray);
}

.audit-logs-table tbody tr:hover {
    background-color: var(--light-gray);
    transition: background-color 0.2s ease;
}

.audit-logs-table .badge {
    font-weight: 500;
    padding: 0.2rem 0.4rem;
    font-size: 0.75rem;
}

.audit-logs-table small {
    font-size: 0.75rem;
}

/* Pagination */
.pagination-container {
    margin-top: 1rem;
    padding-top: 1rem;
    border-top: 1px solid var(--light-gray-2);
}

.pagination-info {
    text-align: center;
    margin-bottom: 0.5rem;
    color: var(--dark-gray);
    font-size: 0.85rem;
}

.pagination-buttons {
    display: flex;
    justify-content: center;
    gap: 0.5rem;
}

.btn-pagination {
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
}

.btn-pagination:hover {
    background-color: var(--sky-blue);
    color: var(--white);
}

.btn-pagination.disabled {
    background-color: var(--light-gray-2);
    color: var(--dark-gray);
    cursor: not-allowed;
    pointer-events: none;
}

</style>
</head>
<body>

<!-- Navigation -->
<nav class="navbar navbar-expand-lg navbar-custom">
    <div class="container px-4">
        <a class="navbar-brand" href="admin_home.php"><img src="../assets/images/hospitallogo.png" style="width:50px; height: 50px"> Blue Pulse</a>

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



    <!-- UI Improvement: Better container spacing -->
    <div class="container mt-4 mb-5 px-4">

        <!-- Dashboard Header -->
        <section class="dashboard-header">
            <h2><i class="fas fa-wave-square"></i> Admin Dashboard</h2>
            <p>Welcome back, <?php echo htmlspecialchars($user_name); ?>! Manage your document management system.</p>
        </section>

        <!-- Stats Cards -->
        <section class="stats-container">
            <div class="stats-card">
                <div class="stats-icon icon-blue">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stats-number"><?php echo $total_users; ?></div>
                <div class="stats-label">Total Users</div>
            </div>
            <div class="stats-card">
                <div class="stats-icon icon-green">
                    <i class="fas fa-heartbeat"></i>
                </div>
                <div class="stats-number"><?php echo getRoleCount($role_counts, 'doctor'); ?></div>
                <div class="stats-label">Doctors</div>
            </div>
            <div class="stats-card">
                <div class="stats-icon icon-purple">
                    <i class="fas fa-user"></i>
                </div>
                <div class="stats-number"><?php echo getRoleCount($role_counts, 'patient'); ?></div>
                <div class="stats-label">Patients</div>
            </div>
        </section>

        <!-- Information and Quick Actions -->
        <section class="info-quick-container">
            <!-- Your Information -->
            <div class="info-card">
                <div class="card-header">
                    <h5 class="card-title"><i class="fas fa-user"></i> Your Information</h5>
                </div>
                <hr>
                <table class="info-table">
                    <tr>
                        <td>Name:</td>
                        <td><?php echo htmlspecialchars($user_name); ?></td>
                    </tr>
                    <tr>
                        <td>Email:</td>
                        <td><?php echo htmlspecialchars($user_email); ?></td>
                    </tr>
                    <tr>
                        <td>Role:</td>
                        <td>
                            <span class="role-badge"><?php echo ucfirst($user_role); ?></span>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- Quick Actions -->
            <div class="quick-actions-card">
                <h5 class="card-title"><i class="fas fa-folder"></i> Quick Actions</h5>
                <hr>
                <a href="../api/file_templates.php" class="btn btn-file-templates mb-2">
                    <i class="fas fa-folder-plus me-2"></i> File Templates
                </a>
                <a href="admin_documents.php" class="btn btn-manage-documents mb-2">
                    <i class="fas fa-file-alt me-2"></i> Manage Documents
                </a>
                <a href="admin_add_user.php" class="btn btn-add-user">
                    <i class="fas fa-user-plus me-2"></i> Add New User
                </a>
                <div class="quick-actions-note mt-2">
                    Admin-only actions. All access is logged.
                </div>
            </div>
        </section>


        <!-- UI Improvement: Better audit logs section spacing -->
        <section class="audit-logs-card mt-4">
            <div class="info-card">
                <h5 class="card-title"><i class="fas fa-history"></i> Audit Logs</h5>
                <hr>
                <?php if (empty($audit_logs)): ?>
                    <div class="text-muted text-center py-5">
                        <i class="fas fa-history fa-3x mb-3"></i>
                        <p>No audit logs available.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm audit-logs-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>User</th>
                                    <th>Action</th>
                                    <th>Timestamp</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($audit_logs as $log): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($log['id']); ?></td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($log['user_name'] ?? 'Unknown'); ?></strong>
                                            <br>
                                            <small class="text-muted"><?php echo htmlspecialchars($log['user_email'] ?? 'N/A'); ?></small>
                                        </td>
                                        <td>
                                            <span class="badge bg-primary"><?php echo htmlspecialchars($log['action']); ?></span>
                                            <?php if (!empty($log['target_type'])): ?>
                                                <br>
                                                <small class="text-muted">
                                                    <?php echo htmlspecialchars($log['target_type']); ?>
                                                    <?php if (!empty($log['target_id'])): ?>
                                                        #<?php echo htmlspecialchars($log['target_id']); ?>
                                                    <?php endif; ?>
                                                </small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php 
                                            $timestamp = strtotime($log['timestamp']);
                                            echo htmlspecialchars(date('Y-m-d H:i:s', $timestamp));
                                            ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <?php if ($total_logs > 0 && $total_pages > 1): ?>
                        <div class="pagination-container mt-3">
                            <div class="pagination-info text-muted small">
                                Showing <?php echo $offset + 1; ?>-<?php echo min($offset + $logs_per_page, $total_logs); ?> of <?php echo $total_logs; ?> logs
                            </div>
                            <div class="pagination-buttons d-flex justify-content-center gap-2 mt-2">
                                <?php if ($current_page > 1): ?>
                                    <a href="admin_home.php?page=<?php echo htmlspecialchars($current_page - 1); ?>" class="btn btn-primary btn-pagination">
                                        <i class="fas fa-chevron-left me-1"></i> Previous
                                    </a>
                                <?php else: ?>
                                    <span class="btn btn-secondary btn-pagination disabled" style="pointer-events: none; opacity: 0.6;">
                                        <i class="fas fa-chevron-left me-1"></i> Previous
                                    </span>
                                <?php endif; ?>
                                
                                <?php if ($current_page < $total_pages): ?>
                                    <a href="admin_home.php?page=<?php echo htmlspecialchars($current_page + 1); ?>" class="btn btn-primary btn-pagination">
                                        Next <i class="fas fa-chevron-right ms-1"></i>
                                    </a>
                                <?php else: ?>
                                    <span class="btn btn-secondary btn-pagination disabled" style="pointer-events: none; opacity: 0.6;">
                                        Next <i class="fas fa-chevron-right ms-1"></i>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                            <div class="d-flex mt-3 justify-content-center">
                                <a href="admin_audit_logs.php" class="btn btn-primary">
                                    <i class="fas fa-list me-2"></i>View All Audit Logs
                                </a>

                    <?php elseif ($total_logs > 0): ?>
                        <div class="pagination-container mt-3">
                            <div class="pagination-info text-muted small text-center">
                                Showing all <?php echo $total_logs; ?> log<?php echo $total_logs !== 1 ? 's' : ''; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </section>

    </div>

    <!-- Bootstrap Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
