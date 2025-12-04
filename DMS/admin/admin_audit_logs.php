<?php
/**
 * Admin Audit Logs Page
 * Document Management System
 * 
 * Full-page viewer for all audit logs with pagination
 */

require_once __DIR__ . '/../includes/db_connect.php';

// Check if user is logged in and has admin role
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// Get user information
$user_name = $_SESSION['user_name'];
$user_email = $_SESSION['user_email'];
$user_role = $_SESSION['user_role'];

// Pagination settings
$records_per_page = 25;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($current_page < 1) {
    $current_page = 1;
}
// $offset will be set after total pages calculation to avoid out-of-range pages

// Get total count of audit logs
try {
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM audit_logs");
    $total_records = (int)($stmt->fetch()['total'] ?? 0);
    $total_pages = $total_records > 0 ? (int)ceil($total_records / $records_per_page) : 0;
} catch (PDOException $e) {
    $total_records = 0;
    $total_pages = 0;
}

// Ensure current page is not beyond last page (fix: clamp page to valid range)
if ($total_pages > 0 && $current_page > $total_pages) {
    $current_page = $total_pages;
} elseif ($total_pages === 0) {
    // keep page as 1 when there are no records
    $current_page = 1;
}

$offset = ($current_page - 1) * $records_per_page;

// Fetch audit logs with pagination
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
    $stmt->bindValue(':limit', $records_per_page, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $audit_logs = $stmt->fetchAll();
} catch (PDOException $e) {
    $audit_logs = [];
}

// Helper function to format action for display
function formatAction($action, $target_type, $target_id, $details) {
    $formatted = htmlspecialchars($action);
    if (!empty($target_type)) {
        $formatted .= ' ' . htmlspecialchars($target_type);
        if (!empty($target_id)) {
            $formatted .= ' #' . htmlspecialchars($target_id);
        }
    }
    if (!empty($details)) {
        $decoded = json_decode($details, true);
        if ($decoded !== null && is_array($decoded)) {
            $formatted .= ' (' . htmlspecialchars(implode(', ', array_map(function ($k, $v) {
                return "$k: $v";
            }, array_keys($decoded), $decoded))) . ')';
        } else {
            $formatted .= ' (' . htmlspecialchars($details) . ')';
        }
    }
    return $formatted;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Audit Logs - Admin Dashboard</title>

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
    background-color: var(--light-gray);
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

/* Page Header */
.page-header {
    background: var(--trust-blue);
    color: var(--white);
    border-radius: 8px;
    box-shadow: 0 8px 16px var(--shadow-light);
    padding: 1.5rem 2rem;
    margin-bottom: 1.75rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.page-header h2 {
    font-family: var(--font-headings);
    font-weight: 700;
    font-size: 1.5rem;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}
.page-header h2 i {
    font-size: 1.5rem;
}

/* Content Card */
.content-card {
    background: var(--white);
    border-radius: 8px;
    box-shadow: 0 1px 4px var(--shadow-light);
    padding: 1.5rem 2rem;
    margin-bottom: 1.75rem;
}

.card-title {
    font-family: var(--font-headings);
    font-weight: 600;
    font-size: 1.1rem;
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: var(--dark-gray);
}

.card-title i {
    font-size: 1.25rem;
    color: var(--trust-blue);
}

/* Audit Logs Table */
.audit-logs-table {
    width: 100%;
    font-size: 0.9rem;
    border-collapse: collapse;
}

.audit-logs-table th {
    font-weight: 600;
    color: var(--dark-gray);
    background-color: var(--light-gray);
    border-bottom: 2px solid var(--light-gray-2);
    padding: 0.75rem 1rem;
    text-align: left;
}

.audit-logs-table td {
    padding: 0.75rem 1rem;
    vertical-align: middle;
    border-bottom: 1px solid var(--light-gray-2);
    color: var(--dark-gray);
}

.audit-logs-table tbody tr:hover {
    background-color: var(--light-gray);
}

.audit-logs-table .badge {
    font-weight: 500;
    padding: 0.25rem 0.5rem;
    font-size: 0.85rem;
}

.role-badge {
    display: inline-block;
    padding: 3px 10px;
    background-color: var(--trust-blue);
    color: var(--white);
    font-weight: 600;
    border-radius: 9999px;
    font-size: 0.75rem;
    text-transform: capitalize;
}

.role-badge.admin {
    background-color: var(--red);
}

.role-badge.doctor {
    background-color: var(--green-teal);
}

.role-badge.patient {
    background-color: var(--deep-navy);
}

/* Pagination */
.pagination-container {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 1rem;
    margin-top: 1.5rem;
    padding-top: 1.5rem;
    border-top: 1px solid var(--light-gray-2);
}

.pagination-info {
    color: var(--dark-gray);
    font-size: 0.9rem;
}

.page-link {
    color: var(--trust-blue);
    padding: 0.5rem 0.75rem;
    border: 1px solid var(--light-gray-2);
    border-radius: 6px;
    text-decoration: none;
    transition: all 0.3s ease;
}

.page-link:hover {
    background-color: var(--trust-blue);
    color: var(--white);
    border-color: var(--trust-blue);
}

.page-link.active {
    background-color: var(--trust-blue);
    color: var(--white);
    border-color: var(--trust-blue);
}

.page-link.disabled {
    color: var(--light-gray-2);
    cursor: not-allowed;
    pointer-events: none;
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

<div class="container mt-4 mb-5">
    <!-- Page Header -->
    <section class="page-header">
        <h2><i class="fas fa-history"></i> Audit Logs</h2>
        <a href="admin_home.php" class="btn-back">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>
    </section>

    <!-- Audit Logs Table -->
    <section class="content-card">
        <h5 class="card-title"><i class="fas fa-list"></i> All System Actions</h5>
        
        <?php if (empty($audit_logs)): ?>
            <div class="text-muted text-center py-5">
                <i class="fas fa-inbox fa-3x mb-3"></i>
                <p>No audit logs found.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table audit-logs-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>User</th>
                            <th>Role</th>
                            <th>Action</th>
                            <th>Details</th>
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
                                    <?php if (!empty($log['user_role'])): ?>
                                        <span class="role-badge <?php echo htmlspecialchars($log['user_role']); ?>">
                                            <?php echo htmlspecialchars(ucfirst($log['user_role'])); ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">N/A</span>
                                    <?php endif; ?>
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
                                    <?php if (!empty($log['details'])): ?>
                                        <?php
                                        $details = $log['details'];
                                        $decoded = json_decode($details, true);
                                        if ($decoded !== null && is_array($decoded)) {
                                            echo '<small>';
                                            foreach ($decoded as $key => $value) {
                                                echo '<strong>' . htmlspecialchars($key) . ':</strong> ' . htmlspecialchars($value) . '<br>';
                                            }
                                            echo '</small>';
                                        } else {
                                            echo '<small class="text-muted">' . htmlspecialchars($details) . '</small>';
                                        }
                                        ?>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php 
                                    $timestamp = strtotime($log['timestamp']);
                                    echo '<strong>' . htmlspecialchars(date('Y-m-d', $timestamp)) . '</strong><br>';
                                    echo '<small class="text-muted">' . htmlspecialchars(date('H:i:s', $timestamp)) . '</small>';
                                    ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <!-- Pagination (rendered as long as there are records) -->
        <?php if ($total_records > 0): ?>
            <?php if ($total_pages > 1): ?>
                <div class="pagination-container">
                    <div class="pagination-info">
                        Showing <?php echo $offset + 1; ?>-<?php echo min($offset + $records_per_page, $total_records); ?> of <?php echo $total_records; ?> logs
                    </div>
                    <nav>
                        <ul class="pagination mb-0">
                            <?php if ($current_page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $current_page - 1; ?>">
                                        <i class="fas fa-chevron-left"></i> Previous
                                    </a>
                                </li>
                            <?php else: ?>
                                <li class="page-item">
                                    <span class="page-link disabled">
                                        <i class="fas fa-chevron-left"></i> Previous
                                    </span>
                                </li>
                            <?php endif; ?>

                            <?php
                            // Show page numbers
                            $start_page = max(1, $current_page - 2);
                            $end_page = min($total_pages, $current_page + 2);

                            if ($start_page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=1">1</a>
                                </li>
                                <?php if ($start_page > 2): ?>
                                    <li class="page-item">
                                        <span class="page-link disabled">...</span>
                                    </li>
                                <?php endif; ?>
                            <?php endif; ?>

                            <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                                <li class="page-item">
                                    <a class="page-link <?php echo $i === $current_page ? 'active' : ''; ?>" href="?page=<?php echo $i; ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                            <?php endfor; ?>

                            <?php if ($end_page < $total_pages): ?>
                                <?php if ($end_page < $total_pages - 1): ?>
                                    <li class="page-item">
                                        <span class="page-link disabled">...</span>
                                    </li>
                                <?php endif; ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $total_pages; ?>"><?php echo $total_pages; ?></a>
                                </li>
                            <?php endif; ?>

                            <?php if ($current_page < $total_pages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $current_page + 1; ?>">
                                        Next <i class="fas fa-chevron-right"></i>
                                    </a>
                                </li>
                            <?php else: ?>
                                <li class="page-item">
                                    <span class="page-link disabled">
                                        Next <i class="fas fa-chevron-right"></i>
                                    </span>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                </div>
            <?php else: ?>
                <div class="pagination-container">
                    <div class="pagination-info">
                        Showing <?php echo $total_records; ?> log<?php echo $total_records !== 1 ? 's' : ''; ?>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>

    </section>
</div>

<!-- Bootstrap Bundle JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>