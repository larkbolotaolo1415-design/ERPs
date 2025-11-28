<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include init.php if not already included
if (!isset($conn)) {
    require_once __DIR__ . '/../../init.php';
}

// Security check - redirect to login if not authenticated
if (!isset($_SESSION['user_id']) && !isset($_COOKIE['user_id'])) {
    echo "<script>window.location.href = '../public/login_page.php';</script>";
    exit;
}

// Get role name from role_id
$role_id = $_SESSION['role_id'] ?? null;
$role_name = 'Guest';

if ($role_id && isset($conn)) {
    $stmt = $conn->prepare("SELECT role_name FROM roles_table WHERE role_id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $role_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $roleRow = $result->fetch_assoc();
            $role_name = $roleRow['role_name'];
        }
        $stmt->close();
    }
}

// Build pages from DB only

$pages = [];
$currentPage = basename($_SERVER['PHP_SELF'] ?? '');
if ($role_id) {
    $stmt2 = $conn->prepare("SELECT DISTINCT p.page_key, p.display_name, p.page_link FROM role_page_permissions r JOIN pages_catalog p ON p.page_key=r.page_key WHERE r.role_id=? AND r.can_access=1 ORDER BY CASE WHEN p.page_key IN ('admin_dashboard','payroll_officer_dashboard','manager_dashboard','employee_dashboard') THEN 0 ELSE 1 END, p.display_name");
    if ($stmt2) {
        $stmt2->bind_param('i', $role_id);
        $stmt2->execute();
        $res2 = $stmt2->get_result();
        $pagesMap = [];
        while ($row2 = $res2->fetch_assoc()) {
            $pagesMap[$row2['page_key']] = $row2;
        }
        $pages = array_values($pagesMap);
        $stmt2->close();
    }
}
?>

<div class="sidebar bg-dark text-white p-3">
    <h5 class="text-center mb-4">Hospital Name</h5>

    <ul class="nav flex-column">
        <?php if (count($pages) > 0): ?>
            <?php foreach ($pages as $row): ?>
                <li class="nav-item">
                    <a href="<?php echo $row['page_link']; ?>" class="nav-link text-white <?php echo basename($row['page_link']) === $currentPage ? 'active' : ''; ?>">
                        <?php echo $row['display_name']; ?>
                    </a>
                </li>
            <?php endforeach; ?>

            <li class="nav-item mt-3">
                <a href="#" id="logout-btn" class="nav-link text-white">
                    Logout
                </a>
            </li>

        <?php else: ?>
            <li class="nav-item">
                <p class="text-warning small">No pages available for your role</p>
            </li>
        <?php endif; ?>
    </ul>
</div>

<script>
// Initialize logout handler with priority - use addEventListener directly for faster response
(function() {
    function attachLogoutHandler() {
        const logoutBtn = document.getElementById('logout-btn');
        if (logoutBtn) {
            logoutBtn.addEventListener('click', handleLogout, true); // Use capture phase for priority
        }
    }
    
    function handleLogout(e) {
        e.preventDefault();
        e.stopPropagation();
        console.log("Logout clicked, attempting to send logout request");
        
        fetch("../modules/logout.php", {
            method: "POST",
            credentials: 'include',
            headers: {
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            console.log("Logout response:", data);
            if (data.status === "success") {
                console.log("Logout successful, redirecting to login page");
                window.location.href = "../public/login_page.php";
            } else {
                console.error("Logout error:", data.message);
                alert("Logout failed: " + data.message);
            }
        })
        .catch(error => {
            console.error("Logout error:", error);
            alert("Error during logout. Check console for details.");
        });
    }
    
    // Try to attach immediately if element exists
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', attachLogoutHandler);
    } else {
        attachLogoutHandler();
    }
    
    // Also attach on load to ensure it works after all resources loaded
    window.addEventListener('load', attachLogoutHandler);
})();
</script>

<style>
.nav-link.active { background-color: rgba(255,255,255,0.1); border-radius: 6px; }
</style>
