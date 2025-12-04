<?php
/**
 * View All Templates Page
 * Displays all file templates assigned/permitted to the logged-in user
 */

require_once __DIR__ . '/includes/db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$userId = (int)$_SESSION['user_id'];
$userName = $_SESSION['user_name'] ?? '';
$userTypeId = (int)($_SESSION['user_type_id'] ?? 0);

// Get user role name
$typeMap = [1=>'Admin',2=>'Doctor',3=>'Nurse',4=>'Staff',5=>'Patient'];
$roleName = $typeMap[$userTypeId] ?? ($_SESSION['user_role'] ?? 'User');

// Pagination settings
$perPage = 10;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $perPage;

// Search and sort parameters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$sortColumn = isset($_GET['sort']) ? $_GET['sort'] : 'upload_date';
$sortOrder = isset($_GET['order']) && strtoupper($_GET['order']) === 'ASC' ? 'ASC' : 'DESC';

// Valid sort columns (whitelist for security)
$validSortColumns = ['name', 'mime_type', 'upload_date', 'uploader_name'];
if (!in_array($sortColumn, $validSortColumns)) {
    $sortColumn = 'upload_date';
}
// Ensure sortOrder is safe
$sortOrder = strtoupper($sortOrder) === 'ASC' ? 'ASC' : 'DESC';

// Build query to fetch permitted templates
function fetchPermittedTemplates(PDO $pdo, int $userId, int $userTypeId, string $search = '', string $sortColumn = 'upload_date', string $sortOrder = 'DESC', int $offset = 0, int $perPage = 10): array {
    $sql = "
        SELECT f.id, f.name, f.original_filename, f.mime_type, f.size, f.upload_date,
               u.name AS uploader_name,
               COALESCE(MAX(CASE WHEN fp.can_download=1 THEN 1 ELSE 0 END),0) AS can_download
        FROM files f
        JOIN users ua ON ua.id = f.uploader_id AND ua.role = 'admin'
        JOIN file_permissions fp ON fp.file_id = f.id
        LEFT JOIN users u ON u.id = f.uploader_id
        WHERE (fp.user_type_id = :ut OR fp.user_id = :uid)
    ";
    
    $params = [':ut' => $userTypeId, ':uid' => $userId];
    
    // Add search condition
    if (!empty($search)) {
        $sql .= " AND (f.name LIKE :search OR f.mime_type LIKE :search OR f.original_filename LIKE :search)";
        $params[':search'] = '%' . $search . '%';
    }
    
    $sql .= " GROUP BY f.id, f.name, f.original_filename, f.mime_type, f.size, f.upload_date, u.name";
    
    // Add sorting (using whitelist for security)
    // Map sort column to actual database column/alias
    $sortColumnMap = [
        'name' => 'f.name',
        'mime_type' => 'f.mime_type',
        'upload_date' => 'f.upload_date',
        'uploader_name' => 'u.name'
    ];
    $actualSortColumn = $sortColumnMap[$sortColumn] ?? 'f.upload_date';
    $sql .= " ORDER BY " . $actualSortColumn . " " . $sortOrder;
    
    // Get total count for pagination
    $countSql = "
        SELECT COUNT(DISTINCT f.id) as total
        FROM files f
        JOIN users ua ON ua.id = f.uploader_id AND ua.role = 'admin'
        JOIN file_permissions fp ON fp.file_id = f.id
        WHERE (fp.user_type_id = :ut OR fp.user_id = :uid)
    ";
    
    if (!empty($search)) {
        $countSql .= " AND (f.name LIKE :search OR f.mime_type LIKE :search OR f.original_filename LIKE :search)";
    }
    
    $countStmt = $pdo->prepare($countSql);
    $countStmt->execute($params);
    $totalCount = (int)$countStmt->fetch()['total'];
    
    // Add pagination
    $sql .= " LIMIT :limit OFFSET :offset";
    
    $stmt = $pdo->prepare($sql);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    
    $templates = $stmt->fetchAll();
    
    return [
        'templates' => $templates,
        'total' => $totalCount
    ];
}

// Fetch templates
$result = ['templates' => [], 'total' => 0];
try {
    $result = fetchPermittedTemplates($pdo, $userId, $userTypeId, $search, $sortColumn, $sortOrder, $offset, $perPage);
} catch (Exception $e) {
    error_log("Error fetching templates: " . $e->getMessage());
}

$templates = $result['templates'];
$totalCount = $result['total'];
$totalPages = max(1, ceil($totalCount / $perPage));

// Helper function to get file category from mime type
function getFileCategory($mimeType) {
    if (strpos($mimeType, 'pdf') !== false) return 'PDF';
    if (strpos($mimeType, 'word') !== false || strpos($mimeType, 'document') !== false) return 'Word Document';
    if (strpos($mimeType, 'excel') !== false || strpos($mimeType, 'spreadsheet') !== false) return 'Spreadsheet';
    if (strpos($mimeType, 'image') !== false) return 'Image';
    return ucfirst(explode('/', $mimeType)[1] ?? 'Document');
}

// Helper function to generate sort URL
function getSortUrl($column, $currentColumn, $currentOrder) {
    $params = $_GET;
    $params['sort'] = $column;
    $params['order'] = ($currentColumn === $column && $currentOrder === 'DESC') ? 'ASC' : 'DESC';
    return '?' . http_build_query($params);
}

// Helper function to get sort icon
function getSortIcon($column, $currentColumn, $currentOrder) {
    if ($currentColumn !== $column) {
        return '<i class="fas fa-sort text-muted"></i>';
    }
    return $currentOrder === 'ASC' ? '<i class="fas fa-sort-up"></i>' : '<i class="fas fa-sort-down"></i>';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>View All Templates - <?php echo htmlspecialchars($roleName); ?> Dashboard</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
<link href='https://fonts.googleapis.com/css?family=Poppins:600,700|Roboto:400,500&display=swap' rel='stylesheet' />
<link rel="stylesheet" href="assets/css/root_colors_fonts.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
body { 
    background-color: var(--white); 
    font-family: var(--font-body);
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
/* UI Improvement: Enhanced welcome card with better spacing */
.welcome-card { 
    background: var(--trust-blue);
    color: var(--white);
    border-radius: 12px;
    box-shadow: 0 8px 16px var(--shadow-light);
    margin-bottom: 2rem;
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
/* UI Improvement: Enhanced card styling */
.card { 
    border: none; 
    border-radius: 12px; 
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    transition: box-shadow 0.3s ease;
    margin-bottom: 2rem;
}
.card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.12);
}
.card-header { 
    font-family: var(--font-headings);
    font-weight: 600;
    font-size: 1.1rem;
    padding: 1.25rem 1.5rem;
    margin-bottom: 0;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: var(--dark-gray);
    background-color: var(--white);
    border-bottom: 2px solid var(--light-gray);
}
.card-body {
    padding: 1.5rem;
}
.card-header i {
    font-size: 1.25rem;
    color: var(--trust-blue);
}
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
.btn-back{
    background-color:var(--trust-blue);
    border:none;border-radius:6px;
    padding:0.5rem 1.5rem;
    font-weight:500;color:white;
    text-decoration:none;
    display:inline-flex;
    align-items:center;
    gap:0.5rem;
}
.btn-back:hover{
    background-color:var(--sky-blue);
    color:white;
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
.table th {
    border: none;
    padding: 1rem 1.25rem;
    vertical-align: middle;
    cursor: pointer;
    user-select: none;
    border-bottom: 2px solid var(--light-gray-2);
    font-size: 0.85rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.table th:hover {
    background-color: var(--light-gray-2);
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
/* UI Improvement: Better fixed table container */
.fixed-table {
    max-height: 550px;
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
/* UI Improvement: Enhanced search box styling */
.search-box {
    margin-bottom: 2rem;
}
.search-box .form-control {
    border-radius: 8px;
    border: 1px solid var(--light-gray-2);
    padding: 0.75rem 1rem;
    transition: all 0.2s ease;
}
.search-box .form-control:focus {
    border-color: var(--trust-blue);
    box-shadow: 0 0 0 0.2rem rgba(37, 99, 235, 0.15);
    outline: none;
}
.search-box .btn {
    border-radius: 8px;
    padding: 0.75rem 1.5rem;
    font-weight: 500;
}
.pagination-box {
    width: 100%;
    margin-top: 15px;
    white-space: nowrap;
    overflow-x: auto;
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 5px;
}
.pagination-box a,
.pagination-box span {
    display: inline-block;
    padding: 6px 12px;
    border: 1px solid #ccc;
    border-radius: 6px;
    background: white;
    text-decoration: none;
    color: black;
    transition: all 0.2s ease;
}
.pagination-box a:hover {
    background: var(--sky-blue);
    border-color: var(--trust-blue);
}
.pagination-box .active {
    background: var(--trust-blue);
    color: white;
    border-color: var(--trust-blue);
}
.pagination-box .disabled {
    opacity: 0.5;
    cursor: not-allowed;
    pointer-events: none;
}
</style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-head">
    <div class="container px-4">
        <a class="navbar-brand" href="dashboard.php"><img src="hospitallogo.png" style="width:50px; height: 50px"> Blue Pulse</a>
        <div class="navbar-nav ms-auto">
            <div class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" style="color: white;" href="#" role="button" data-bs-toggle="dropdown">
                    <i class="fas fa-user-circle me-2"></i><?php echo htmlspecialchars($userName); ?>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="#"><i class="fas fa-user-circle text-primary"></i> Profile</a></li>
                    <li><a class="dropdown-item" href="#"><i class="fas fa-cog text-secondary"></i> Settings</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </div>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <div class="welcome-card">
                <div class="card-body p-4">
                    <h2><i class="fas fa-folder-open"></i>All File Templates</h2>
                    <p class="mb-0">View and manage all templates assigned to you.</p>
                    <a href="dashboard.php" class="btn-back mt-4"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-folder-open me-2"></i>File Templates
                </div>
                <div class="card-body">
                    <!-- Search Box -->
                    <div class="search-box">
                        <form method="GET" action="" class="d-flex gap-2">
                            <input type="text" 
                                   class="form-control" 
                                   name="search" 
                                   placeholder="Search by template name, category, or description..." 
                                   value="<?php echo htmlspecialchars($search); ?>">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i> Search
                            </button>
                            <?php if (!empty($search)): ?>
                                <a href="view_all_templates.php" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Clear
                                </a>
                            <?php endif; ?>
                            <input type="hidden" name="sort" value="<?php echo htmlspecialchars($sortColumn); ?>">
                            <input type="hidden" name="order" value="<?php echo htmlspecialchars($sortOrder); ?>">
                        </form>
                    </div>

                    <?php if (empty($templates)): ?>
                        <div class="text-center text-muted py-5">
                            <i class="fas fa-folder-open fa-3x mb-3"></i>
                            <p>No templates found.</p>
                            <?php if (!empty($search)): ?>
                                <p class="small">Try adjusting your search criteria.</p>
                            <?php else: ?>
                                <p class="small">No templates have been assigned to you yet.</p>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="fixed-table">
                            <table class="table table-sm align-middle">
                                <thead>
                                    <tr>
                                        <th onclick="window.location.href='<?php echo getSortUrl('name', $sortColumn, $sortOrder); ?>'">
                                            Template Name <?php echo getSortIcon('name', $sortColumn, $sortOrder); ?>
                                        </th>
                                        <th onclick="window.location.href='<?php echo getSortUrl('mime_type', $sortColumn, $sortOrder); ?>'">
                                            Category/Type <?php echo getSortIcon('mime_type', $sortColumn, $sortOrder); ?>
                                        </th>
                                        <th>Description</th>
                                        <th onclick="window.location.href='<?php echo getSortUrl('upload_date', $sortColumn, $sortOrder); ?>'">
                                            Date Created <?php echo getSortIcon('upload_date', $sortColumn, $sortOrder); ?>
                                        </th>
                                        <th onclick="window.location.href='<?php echo getSortUrl('uploader_name', $sortColumn, $sortOrder); ?>'">
                                            File Owner / Assigned By <?php echo getSortIcon('uploader_name', $sortColumn, $sortOrder); ?>
                                        </th>
                                        <th style="width:120px">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($templates as $t): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($t['name']); ?></strong></td>
                                        <td>
                                            <span class="badge bg-info">
                                                <?php echo htmlspecialchars(getFileCategory($t['mime_type'])); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                <?php echo htmlspecialchars($t['original_filename']); ?>
                                            </small>
                                        </td>
                                        <td>
                                            <small><?php echo htmlspecialchars(date('Y-m-d H:i', strtotime($t['upload_date']))); ?></small>
                                        </td>
                                        <td><?php echo htmlspecialchars($t['uploader_name'] ?? 'Unknown'); ?></td>
                                        <td>
                                            <div class="btn-group">
                                                <a class="btn btn-sm btn-success" 
                                                   target="_blank" 
                                                   href="api/files.php?id=<?php echo (int)$t['id']; ?>" 
                                                   title="View">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <?php if ((int)$t['can_download'] === 1): ?>
                                                <a class="btn btn-sm btn-primary" 
                                                   href="api/files.php?id=<?php echo (int)$t['id']; ?>&download=1" 
                                                   title="Download">
                                                    <i class="fas fa-download"></i>
                                                </a>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <?php if ($totalPages > 1): ?>
                        <div class="pagination-box">
                            <?php if ($page > 1): ?>
                                <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>">
                                    <i class="fas fa-chevron-left"></i> Prev
                                </a>
                            <?php else: ?>
                                <span class="disabled"><i class="fas fa-chevron-left"></i> Prev</span>
                            <?php endif; ?>

                            <?php
                            $startPage = max(1, $page - 2);
                            $endPage = min($totalPages, $page + 2);
                            
                            if ($startPage > 1): ?>
                                <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => 1])); ?>">1</a>
                                <?php if ($startPage > 2): ?>
                                    <span>...</span>
                                <?php endif; ?>
                            <?php endif; ?>

                            <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                                <a class="<?php echo $i == $page ? 'active' : ''; ?>" 
                                   href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>">
                                    <?php echo $i; ?>
                                </a>
                            <?php endfor; ?>

                            <?php if ($endPage < $totalPages): ?>
                                <?php if ($endPage < $totalPages - 1): ?>
                                    <span>...</span>
                                <?php endif; ?>
                                <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $totalPages])); ?>">
                                    <?php echo $totalPages; ?>
                                </a>
                            <?php endif; ?>

                            <?php if ($page < $totalPages): ?>
                                <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>">
                                    Next <i class="fas fa-chevron-right"></i>
                                </a>
                            <?php else: ?>
                                <span class="disabled">Next <i class="fas fa-chevron-right"></i></span>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>

                        <div class="mt-3 text-muted small">
                            Showing <?php echo count($templates); ?> of <?php echo $totalCount; ?> template(s)
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-12">
            <footer class="text-center py-4 border-top">&copy; <?php echo date('Y'); ?> DMS</footer>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

