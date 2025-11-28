<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$userName = $_SESSION['user_name'] ?? 'User';
// Redirect to login if no session or user cookie
if (!isset($_SESSION['user_id']) && !isset($_COOKIE['user_id'])) {
    echo "<script>window.location.href = '../public/login_page.php';</script>";
    exit;
}
?>

<nav class="navbar navbar-light bg-white shadow-sm px-4" style="position:fixed; top:0; left:var(--sidebar-width); right:0; z-index:999; border-bottom: 1px solid #eee;">
    <div class="container-fluid d-flex justify-content-between align-items-center">
        <!-- Hamburger button (visible on mobile, hidden on desktop) -->
        <div class="d-lg-none">
            <button class="btn btn-link p-0 text-dark" id="sidebarToggle" type="button" title="Toggle Sidebar" style="font-size: 1.5rem;">
                <i class="bi bi-list"></i>
            </button>
        </div>

        <div class="flex-grow-1 ms-2 ms-lg-0">
            <h4 class="mb-0 fw-bold"><?php echo $pageTitle ?? 'Dashboard'; ?></h4>
            <small class="text-muted" id="greeting">Loading...</small>
        </div>

        <div class="d-flex align-items-center gap-2">
            <span class="text-muted small">User: <?php echo $userName; ?></span>
            <img src="<?php echo dirname(__DIR__) . '/assets/images/photo.jpg'; ?>" width="45" height="45" class="rounded-circle" alt="User Avatar" />
        </div>
    </div>
</nav>

<script>
function setGreeting() {
    const hour = new Date().getHours();
    let greet = "Good Evening";
    if (hour < 12) greet = "Good Morning";
    else if (hour < 18) greet = "Good Afternoon";

    document.getElementById("greeting").innerHTML =
        greet + ", <strong><?php echo $userName; ?></strong>";
}
setGreeting();

// Sidebar toggle for mobile - use a more robust approach
(function() {
    function initializeSidebarToggle() {
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebar = document.querySelector('.sidebar');
        
        if (!sidebarToggle || !sidebar) return;
        
        // Toggle button
        sidebarToggle.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            sidebar.classList.toggle('sidebar-visible');
        });

        // Close sidebar when a regular link is clicked (not logout)
        const sidebarLinks = sidebar.querySelectorAll('a:not(#logout-btn)');
        sidebarLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                // Only close on mobile, and allow normal navigation
                if (window.innerWidth <= 992) {
                    sidebar.classList.remove('sidebar-visible');
                }
            });
        });

        // Close sidebar when clicking outside it (on the content area)
        document.addEventListener('click', function(e) {
            if (window.innerWidth <= 992) {
                // Only close if clicking outside sidebar and burger button
                if (!sidebar.contains(e.target) && !sidebarToggle.contains(e.target)) {
                    sidebar.classList.remove('sidebar-visible');
                }
            }
        });
    }
    
    // Initialize when DOM is ready, with fallback
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initializeSidebarToggle);
    } else {
        initializeSidebarToggle();
    }
})();
</script>
<style>
@media (max-width: 992px) {
    nav.navbar { left: 0 !important; }
}
</style>
