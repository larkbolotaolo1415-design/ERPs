<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../init.php';
$pageTitle = "User Management";
$userName = $_SESSION['user_name'] ?? 'User';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management</title>
    <link rel="stylesheet" href="../assets/css/dashboard_style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
    :root {
        --accent: #2563EB;
        --white: #ffffff;
    }

    #user-avatar {
        width: 50px;
        height: 50px;
    }

    .summary-card {
        background-color: var(--accent);
        color: var(--white);
        border: none;

        #user-avatar {
            width: 50px;
            height: 50px;
        }

        .main-content {
            margin-left: 250px;
            padding: 1rem;
        }
        padding: 25px;
        min-width: 260px;
        box-shadow: 0 0 8px rgba(0, 0, 0, 0.05);
    }

    .override-card h6,
    .button-card h6 {
        font-weight: 700;
        color: var(--accent);
        margin-bottom: 1rem;
    }

    .btn-outline-primary {
        color: var(--accent);
        border-color: var(--accent);
    }

    .btn-outline-primary:hover {
        background-color: var(--accent);
        color: white;
    }

    .btn-primary {
        background-color: var(--accent);
        border-color: var(--accent);
    }

    .btn-action {
        border-radius: 25px;
        font-weight: 500;
        padding: 0.6rem 1rem;
    }

    /* Table Styles */
    .permissions-table {
        width: 100%;
        table-layout: fixed;
    }

    .permissions-table th,
    .permissions-table td {
        vertical-align: middle;
        text-align: center;
        padding: 0.75rem;
    }

    .permissions-table th:first-child,
    .permissions-table td:first-child {
        width: 30%;
        text-align: left;
    }

    .permissions-table thead th {
        background: #f3f6fa;
        color: var(--accent);
        font-weight: 700;
    }

    .table-section-header {
        background: #f3f6fa;
        font-weight: 600;
        color: var(--accent);
    }

    /* Switch Styles */
    .form-switch .form-check-input {
        width: 3em;
        height: 1.5em;
        cursor: pointer;
    }

    .form-check-input:checked {
        background-color: var(--accent);
        border-color: var(--accent);
    }

    /* Loading Spinner */
    .loading-spinner {
        display: inline-block;
        width: 20px;
        height: 20px;
        border: 3px solid rgba(255,255,255,.3);
        border-radius: 50%;
        border-top-color: #fff;
        animation: spin 1s ease-in-out infinite;
    }

    @keyframes spin {
        to { transform: rotate(360deg); }
    }

    /* Responsive */
    @media (max-width: 992px) {
        .main-content {
            margin-left: 0;
        }

        /* Sidebar responsive layout is centralized in assets/css/dashboard_style.css */

        .card-section {
            flex-direction: column;
        }

        .permissions-table {
            table-layout: auto;
        }
    }

    .quick-links button {
        border-color: var(--accent);
        color: var(--accent);
        font-weight: 500;
    }

    .quick-links button:hover {
        background-color: var(--accent);
        color: var(--white);
    }

    .card-header {
        background-color: var(--accent) !important;
        color: var(--white) !important;
        font-weight: 600;
    }

    .text-accent {
        color: var(--accent) !important;
    }

.table-scroll {
    max-height: 700px; /* your desired table height */
    overflow-y: auto;
    position: relative; /* ensures sticky header works inside */
    border-radius: 8px;
    background-color: #fff;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.05);
}

/* sticky header fix */
.table-scroll thead th {
    position: sticky;
    top: 0;
    z-index: 10;
    background: #ffffffff; /* same as your table header bg */
    color: #2563EB;
    font-weight: 700;
    border-bottom: 2px solid #dee2e6;
}

/* Search input group */
.search-group {
    position: relative;
    max-width: 300px;
}

.search-group .form-control {
    padding-right: 45px;
}

.search-group .btn {
    position: absolute;
    right: 0;
    top: 0;
    height: 100%;
    border-top-left-radius: 0;
    border-bottom-left-radius: 0;
    z-index: 5;
}

/* Loading overlay */
.loading-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255, 255, 255, 0.8);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 100;
    border-radius: 8px;
}

.loading-overlay .spinner-border {
    width: 3rem;
    height: 3rem;
}
</style>

</head>

<body>
    <?php include __DIR__ . '/../includes/layout/sidebar.php'; ?>
    <?php include __DIR__ . '/../includes/layout/topbar.php'; ?>

    <div class="main-content p-4 mt-5">


        <!-- User Management Table Section -->
<div class="container-fluid mt-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <div class="d-flex gap-2">
            <select class="form-select" style="width: 180px;">
                <option selected>Sort by ID</option>
                <option value="1">Sort by Name</option>
                <option value="2">Sort by Role</option>
                <option value="3">Sort by Status</option>
            </select>
        </div>

        <div class="search-group" style="max-width: 300px; width: 100%;">
            <input type="text" class="form-control" placeholder="Search User" id="search-input">
            <button class="btn btn-primary" type="button" id="search-btn">
                <i class="bi bi-search"></i>
            </button>
        </div>
    </div>

<div class="table-responsive table-scroll position-relative">
    <div class="loading-overlay" id="table-loading" style="display: none;">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>
    <table class="table align-middle border rounded">
        <thead class="bg-light text-primary">
            <tr>
                <th scope="col">
                    <input class="form-check-input" type="checkbox">
                </th>
                <th scope="col">Employee Name</th>
                <th scope="col">User Role</th>
                <th scope="col">Status</th>
                <th scope="col">Actions</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
</div>

    <!-- Pagination -->
<div class="position-fixed end-0 p-4">
        <nav>
            <ul class="pagination mb-0"></ul>
        </nav>
    </div>
</div>
        
    </div>

    <script>
        // GREETING FUNCTION
        function setGreeting() {
            const hour = new Date().getHours();
            let greeting = "Good Evening";
            if (hour < 12) greeting = "Good Morning";
            else if (hour < 18) greeting = "Good Afternoon";

            const name = "<?php echo $userName ?? 'Manager'; ?>";
            document.getElementById("greeting").innerHTML = `${greeting}, <strong>${name}</strong>`;
        }
        setGreeting();

        // LOGOUT
        $("#logout-btn").click(() => {
            $.ajax({
                url: "../modules/logout.php",
                type: "POST",
                dataType: "json",
                success: function (response) {
                    if (response.status === "success") {
                        window.location.href = "login_page.php";
                    } else {
                        console.log(response.message);
                    }
                },
                error: (xhr, status, error) => {
                    console.log("Server error:", error);
                }
            });
        });

        // NAVIGATION
        $(document).ready(function () {
            $('#dashboard-btn').on('click', function () {
                window.location.href = 'admin_dashboard.php';
            });
            
            $('#user-management-btn').on('click', function () {
                window.location.href = 'admin_user_management.php';
            });

            $('#role-management-btn').on('click', function () {
                window.location.href = 'admin_role_management.php';
            });

            $('#company-settings-btn').on('click', function () {
                window.location.href = 'admin_company_settings.php';
            });

            $('#system-configuration-btn').on('click', function () {
                window.location.href = 'admin_system_configuration.php';
            });

            $('#data-backup-and-restore-btn').on('click', function () {
                window.location.href = 'admin_data_backup_and_restore.php';
            });

            $('#audit-logs-btn').on('click', function () {
                window.location.href = 'admin_audit_logs.php';
            });

            $('#summary-report-btn').on('click', function () {
                window.location.href = 'admin_summary_report.php';
            });

            $('#security-controls-btn').on('click', function () {
                window.location.href = 'admin_security_controls.php';
            });
        });

        // CHARTS
        const salaryEl = document.getElementById('salaryChart');
        if (salaryEl) {
        const ctxSalary = salaryEl.getContext('2d');
        new Chart(ctxSalary, {
            type: 'bar',
            data: {
                labels: ['HR', 'IT', 'Finance', 'Admin', 'Marketing', 'Operations'],
                datasets: [{
                    label: 'Average Salary (₱)',
                    data: [48000, 62000, 55000, 50000, 47000, 53000],
                    backgroundColor: 'rgba(37, 99, 235, 0.7)',
                    borderColor: '#2563EB',
                    borderWidth: 1,
                    borderRadius: 5
                }]
            },
            options: {
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });
        }

        const overtimeEl = document.getElementById('overtimeChart');
        if (overtimeEl) {
        const ctxOvertime = overtimeEl.getContext('2d');
        new Chart(ctxOvertime, {
            type: 'doughnut',
            data: {
                labels: ['HR', 'IT', 'Finance', 'Marketing'],
                datasets: [{
                    data: [20, 35, 25, 20],
                    backgroundColor: ['#2563EB', '#3B82F6', '#60A5FA', '#93C5FD'],
                    borderWidth: 0
                }]
            },
            options: {
                plugins: { legend: { position: 'bottom' } }
            }
        });
        }

        // Backend-driven users table with search, sort, pagination
        $(function() {
            const tbody = document.querySelector('table.table tbody');
            const sortSelect = document.querySelector('.form-select');
            const searchInput = document.getElementById('search-input');
            const searchBtn = document.getElementById('search-btn');
            const tableLoading = document.getElementById('table-loading');
            const pager = document.querySelector('.pagination');
            let pagesTotal = 1;
            if (!tbody) return;
            let page = 1;
            let sort = 'user_id';
            let order = 'ASC';
            let search = '';
            const limit = 20;

            function mapSort(val) {
                if (val.includes('Name')) return 'user_name';
                if (val.includes('Role')) return 'role_name';
                if (val.includes('Status')) return 'status';
                return 'user_id';
            }

            function showLoading() {
                if (tableLoading) {
                    tableLoading.style.display = 'flex';
                }
            }

            function hideLoading() {
                if (tableLoading) {
                    tableLoading.style.display = 'none';
                }
            }

            function renderRows(rows) {
                tbody.innerHTML = '';
                if (rows.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted">No users found</td></tr>';
                    return;
                }
                rows.forEach(r => {
                    const tr = document.createElement('tr');
                    const statusClass = r.status === 'active' ? 'text-success' : 'text-danger';
                    const toggleText = r.status === 'active' ? 'Disable' : 'Enable';
                    tr.innerHTML = `
                        <td><input class="form-check-input" type="checkbox"></td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="rounded-circle bg-primary me-3" style="width:40px; height:40px;"></div>
                                <div>
                                    <strong>${r.user_name}</strong><br>
                                    <small class="text-muted">#${r.user_id}</small>
                                </div>
                            </div>
                        </td>
                        <td><span class="badge bg-primary bg-opacity-10 text-primary border border-primary px-3 py-2">${r.role_name || 'N/A'}</span></td>
                        <td><span class="${statusClass} fw-semibold">${r.status}</span></td>
                        <td>
                            <button class="btn btn-sm btn-outline-secondary me-2" data-user="${r.user_id}" data-action="modify"><i class="bi bi-gear"></i> Modify User</button>
                            <button class="btn btn-sm btn-outline-danger me-2" data-user="${r.user_id}" data-action="toggle"><i class="bi bi-x-circle"></i> ${toggleText} User</button>
                            <button class="btn btn-sm btn-outline-danger" data-user="${r.user_id}" data-action="delete"><i class="bi bi-trash"></i> Delete</button>
                        </td>`;
                    tbody.appendChild(tr);
                });
            }

            function renderPager(total) {
                if (!pager) return;
                const pages = Math.max(1, Math.ceil(total / limit));
                pagesTotal = pages;
                const items = [];
                items.push(`<li class="page-item"><a class="page-link" href="#" data-page="first">First</a></li>`);
                items.push(`<li class="page-item"><a class="page-link" href="#" data-page="prev">&lt;</a></li>`);
                const start = Math.max(1, page - 2);
                const end = Math.min(pages, start + 4);
                for (let i = start; i <= end; i++) {
                    items.push(`<li class="page-item ${i===page?'active':''}"><a class="page-link" href="#" data-page="${i}">${i}</a></li>`);
                }
                items.push(`<li class="page-item"><a class="page-link" href="#" data-page="next">&gt;</a></li>`);
                items.push(`<li class="page-item"><a class="page-link" href="#" data-page="last">Last</a></li>`);
                pager.innerHTML = items.join('');
            }

           // Replace the existing fetchUsers function with this one
function fetchUsers() {
    showLoading();
    tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted">Loading...</td></tr>';
    
    // Add minimum loading time to make it more visible
    const loadingStartTime = Date.now();
    const minLoadingTime = 800; // 800ms minimum loading time
    
    $.getJSON('../modules/users.php', { action: 'list', limit, page, sort, order, search }, function(resp) {
        const elapsed = Date.now() - loadingStartTime;
        const remainingTime = Math.max(0, minLoadingTime - elapsed);
        
        setTimeout(function() {
            hideLoading();
            if (resp.status !== 'success') { 
                tbody.innerHTML = '<tr><td colspan="5" class="text-center text-danger">Failed to load users</td></tr>'; 
                return; 
            }
            renderRows(resp.data || []);
            renderPager(resp.total || 0);
        }, remainingTime);
    }).fail(function() {
        const elapsed = Date.now() - loadingStartTime;
        const remainingTime = Math.max(0, minLoadingTime - elapsed);
        
        setTimeout(function() {
            hideLoading();
            tbody.innerHTML = '<tr><td colspan="5" class="text-center text-danger">Failed to load users</td></tr>';
        }, remainingTime);
    });
}

// Also update the action handlers to show loading longer
function showLoadingWithDelay() {
    showLoading();
    // Auto-hide after 2 seconds if something goes wrong
    setTimeout(hideLoading, 1000);
}

// Update the toggle and delete actions to show loading longer
tbody.addEventListener('click', function(e) {
    const btn = e.target.closest('button');
    if (!btn) return;
    const userId = parseInt(btn.getAttribute('data-user'), 10);
    const action = btn.getAttribute('data-action');
    
    if (action === 'toggle') {
        const row = btn.closest('tr');
        const statusEl = row.querySelector('td:nth-child(4) span');
        const current = statusEl.textContent.trim();
        const next = current === 'active' ? 'inactive' : 'active';
        
        // Show loading on button with minimum time
        const originalHtml = btn.innerHTML;
        btn.innerHTML = '<span class="loading-spinner"></span>';
        btn.disabled = true;
        
        const loadingStart = Date.now();
        const minLoadingTime = 600;
        
        $.post('../modules/users.php', { action: 'updateStatus', user_id: userId, status: next }, function(res) {
            const elapsed = Date.now() - loadingStart;
            const remaining = Math.max(0, minLoadingTime - elapsed);
            
            setTimeout(function() {
                btn.innerHTML = originalHtml;
                btn.disabled = false;
                
                if (res.status === 'success') { 
                    statusEl.textContent = next; 
                    statusEl.className = next === 'active' ? 'text-success fw-semibold' : 'text-danger fw-semibold'; 
                    btn.innerHTML = `<i class=\"bi bi-x-circle\"></i> ${next === 'active' ? 'Disable' : 'Enable'} User`; 
                }
            }, remaining);
        }, 'json').fail(function() {
            const elapsed = Date.now() - loadingStart;
            const remaining = Math.max(0, minLoadingTime - elapsed);
            
            setTimeout(function() {
                btn.innerHTML = originalHtml;
                btn.disabled = false;
                alert('Failed to update user status');
            }, remaining);
        });
    } else if (action === 'delete') {
        if (!confirm('Delete this user? This action cannot be undone.')) return;
        
        // Show loading on button with minimum time
        const originalHtml = btn.innerHTML;
        btn.innerHTML = '<span class="loading-spinner"></span>';
        btn.disabled = true;
        
        const loadingStart = Date.now();
        const minLoadingTime = 600;
        
        $.post('../modules/users.php', { action: 'delete', user_id: userId }, function(res){
            const elapsed = Date.now() - loadingStart;
            const remaining = Math.max(0, minLoadingTime - elapsed);
            
            setTimeout(function() {
                btn.innerHTML = originalHtml;
                btn.disabled = false;
                
                if (res.status === 'success') { 
                    btn.closest('tr').remove(); 
                    // Refresh the table if we deleted the last item on the page
                    if (tbody.querySelectorAll('tr').length === 0) {
                        fetchUsers();
                    }
                } else { 
                    alert(res.message || 'Delete failed'); 
                }
            }, remaining);
        }, 'json').fail(function() {
            const elapsed = Date.now() - loadingStart;
            const remaining = Math.max(0, minLoadingTime - elapsed);
            
            setTimeout(function() {
                btn.innerHTML = originalHtml;
                btn.disabled = false;
                alert('Failed to delete user');
            }, remaining);
        });
    }
});

            function performSearch() {
                search = searchInput.value.trim();
                page = 1;
                fetchUsers();
            }

            // Initial load
            sort = mapSort(sortSelect ? sortSelect.options[sortSelect.selectedIndex].text : 'Sort by ID');
            fetchUsers();

            // Sorting and search
            if (sortSelect) sortSelect.addEventListener('change', function(){ 
                sort = mapSort(sortSelect.options[sortSelect.selectedIndex].text); 
                page = 1; 
                fetchUsers(); 
            });
            
            if (searchInput) {
                searchInput.addEventListener('keypress', function(e){ 
                    if (e.key === 'Enter') {
                        performSearch();
                    }
                });
            }
            
            if (searchBtn) {
                searchBtn.addEventListener('click', performSearch);
            }
            
            if (pager) pager.addEventListener('click', function(e){ 
                const a = e.target.closest('a[data-page]'); 
                if (!a) return; 
                e.preventDefault(); 
                const val = a.getAttribute('data-page'); 
                if (val==='first') page=1; 
                else if (val==='prev') page=Math.max(1,page-1); 
                else if (val==='next') page=Math.min(pagesTotal,page+1); 
                else if (val==='last') { page=pagesTotal; } 
                else page=parseInt(val,10); 
                fetchUsers(); 
            });

            // action handlers
            tbody.addEventListener('click', function(e) {
                const btn = e.target.closest('button');
                if (!btn) return;
                const userId = parseInt(btn.getAttribute('data-user'), 10);
                const action = btn.getAttribute('data-action');
                
                if (action === 'toggle') {
                    const row = btn.closest('tr');
                    const statusEl = row.querySelector('td:nth-child(4) span');
                    const current = statusEl.textContent.trim();
                    const next = current === 'active' ? 'inactive' : 'active';
                    
                    // Show loading on button
                    const originalHtml = btn.innerHTML;
                    btn.innerHTML = '<span class="loading-spinner"></span>';
                    btn.disabled = true;
                    
                    $.post('../modules/users.php', { action: 'updateStatus', user_id: userId, status: next }, function(res) {
                        btn.innerHTML = originalHtml;
                        btn.disabled = false;
                        
                        if (res.status === 'success') { 
                            statusEl.textContent = next; 
                            statusEl.className = next === 'active' ? 'text-success fw-semibold' : 'text-danger fw-semibold'; 
                            btn.innerHTML = `<i class=\"bi bi-x-circle\"></i> ${next === 'active' ? 'Disable' : 'Enable'} User`; 
                        }
                    }, 'json').fail(function() {
                        btn.innerHTML = originalHtml;
                        btn.disabled = false;
                        alert('Failed to update user status');
                    });
                } else if (action === 'delete') {
                    if (!confirm('Delete this user? This action cannot be undone.')) return;
                    
                    // Show loading on button
                    const originalHtml = btn.innerHTML;
                    btn.innerHTML = '<span class="loading-spinner"></span>';
                    btn.disabled = true;
                    
                    $.post('../modules/users.php', { action: 'delete', user_id: userId }, function(res){
                        btn.innerHTML = originalHtml;
                        btn.disabled = false;
                        
                        if (res.status === 'success') { 
                            btn.closest('tr').remove(); 
                            // Refresh the table if we deleted the last item on the page
                            if (tbody.querySelectorAll('tr').length === 0) {
                                fetchUsers();
                            }
                        } else { 
                            alert(res.message || 'Delete failed'); 
                        }
                    }, 'json').fail(function() {
                        btn.innerHTML = originalHtml;
                        btn.disabled = false;
                        alert('Failed to delete user');
                    });
                }
            });
        });
    </script>
    <!-- User Modal -->
    <div class="modal fade" id="userModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="userModalTitle">User</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <form id="userForm">
              <input type="hidden" name="user_id" id="um_user_id">
              <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label">Name</label>
                  <input type="text" class="form-control" name="user_name" id="um_user_name">
                </div>
                <div class="col-md-6">
                  <label class="form-label">Email</label>
                  <input type="email" class="form-control" name="user_email" id="um_user_email">
                  <div class="invalid-feedback d-none" id="um_email_invalid">Please enter a valid email address.</div>
                  <div class="small mt-1"><span id="um_email_hint" class="badge bg-secondary">Email format</span></div>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Password</label>
                  <input type="password" class="form-control" name="password" id="um_password" placeholder="">
                </div>
                <div class="col-md-6" id="um_confirm_wrap" style="display:none;">
                  <label class="form-label">Confirm Password</label>
                  <input type="password" class="form-control" id="um_password_confirm" placeholder="">
                </div>
                <div class="col-12" id="um_policy_hints" style="display:none;">
                  <div class="small d-flex flex-wrap gap-2 align-items-center mt-1">
                    <span id="um_hint_len" class="badge bg-secondary">Min length</span>
                    <span id="um_hint_upper" class="badge bg-secondary">Uppercase</span>
                    <span id="um_hint_lower" class="badge bg-secondary">Lowercase</span>
                    <span id="um_hint_num" class="badge bg-secondary">Number</span>
                    <span id="um_hint_sym" class="badge bg-secondary">Symbol</span>
                    <span id="um_hint_text" class="text-muted"></span>
                  </div>
                </div>
                <div class="col-md-3">
                  <label class="form-label">Role</label>
                  <select class="form-select" name="role_id" id="um_role_id"></select>
                </div>
                <div class="col-md-3">
                  <label class="form-label">Status</label>
                  <select class="form-select" name="status" id="um_status">
                    <option value="active">active</option>
                    <option value="inactive">inactive</option>
                  </select>
                </div>
              </div>
            </form>
          </div>
          <div class="modal-footer">
            <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button id="userSaveBtn" class="btn btn-primary">
              <span id="saveBtnText">Save</span>
              <span id="saveBtnSpinner" class="spinner-border spinner-border-sm d-none" role="status"></span>
            </button>
          </div>
        </div>
      </div>
    </div>
 <script>
$(function(){
    const modalEl = document.getElementById('userModal');
    const modal = new bootstrap.Modal(modalEl);

    // Load roles into dropdown
    function loadRoles() {
        $.getJSON('../modules/roles.php', { action: 'list' }, function(rr){
            const sel = $('#um_role_id'); 
            sel.empty();
            (rr.data||[]).forEach(r => sel.append(`<option value="${r.role_id}">${r.role_name}</option>`));
        });
    }
    loadRoles();

    // Password policy defaults
    let policy = { len: 8, upper: 1, lower: 1, num: 1, sym: 1 };
    $.getJSON('../modules/settings.php', { action: 'list' }, function(rs){
        const items = rs.data || [];
        function val(name) { const f = items.find(x=>x.setting_name===name); return f ? f.value : ''; }
        policy.len = parseInt(val('Minimum Password Length')||8);
        policy.upper = val('Require Uppercase Letters')==='1'?1:0;
        policy.lower = val('Require Lowercase Letters')==='1'?1:0;
        policy.num = val('Require Numbers')==='1'?1:0;
        policy.sym = val('Require Symbols')==='1'?1:0;
    });

    // Update password hints
    function updateHints(){
        const v = $('#um_password').val()||'';
        const okLen = v.length >= policy.len;
        const okU = /[A-Z]/.test(v) || !policy.upper;
        const okL = /[a-z]/.test(v) || !policy.lower;
        const okN = /\d/.test(v) || !policy.num;
        const okS = /[^A-Za-z0-9]/.test(v) || !policy.sym;

        $('#um_hint_len').toggleClass('bg-secondary', !okLen).toggleClass('bg-success', okLen).text('Min '+policy.len);
        $('#um_hint_upper').toggleClass('bg-secondary', !okU).toggleClass('bg-success', okU);
        $('#um_hint_lower').toggleClass('bg-secondary', !okL).toggleClass('bg-success', okL);
        $('#um_hint_num').toggleClass('bg-secondary', !okN).toggleClass('bg-success', okN);
        $('#um_hint_sym').toggleClass('bg-secondary', !okS).toggleClass('bg-success', okS);
    }

    $('#um_password').on('input', function(){ 
        $('#um_policy_hints').show(); 
        updateHints(); 
    });

    // Strict email regex
    const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
    $('#um_user_email').on('input', function(){
        const v = $(this).val().trim();
        const valid = emailRegex.test(v);
        $(this).toggleClass('is-invalid', !valid);
        $('#um_email_invalid').toggleClass('d-block', !valid).toggleClass('d-none', valid);
        $('#um_email_hint').removeClass('bg-secondary bg-success bg-danger')
                           .addClass(valid ? 'bg-success' : 'bg-danger')
                           .text(valid ? 'Valid email' : 'Invalid email');
    });

    // Open user modal for modification
    document.body.addEventListener('click', function(e){
        const btn = e.target.closest('button[data-action="modify"]');
        if (!btn) return;

        const tr = btn.closest('tr');
        const idText = tr.querySelector('td:nth-child(2) small')?.textContent || '#0';
        const user_id = parseInt(idText.replace('#',''),10);

        $.getJSON('../modules/users.php', { action: 'get', user_id }, function(res){
            if (res.status !== 'success') return;
            const u = res.data || {};
            $('#um_user_id').val(u.user_id);
            $('#um_user_name').val(u.user_name||'');
            $('#um_user_email').val(u.user_email||'');
            $('#um_password').val('');
            $('#um_password_confirm').val('');
            $('#um_confirm_wrap').hide();
            $('#um_password').attr('placeholder','Leave blank to keep');
            $('#userModalTitle').text('Edit User');
            $('#um_status').val(u.status||'active');
            $('#um_role_id').val(u.role_id||'');
            $('#um_policy_hints').show();
            $('#um_hint_text').text('Leave blank to keep existing password');
            updateHints();
            modal.show();
        });
    });

    // Inject create user button if missing
    if (!$('.create-user-btn').length) {
        const header = document.querySelector('.main-content h2, .main-content h3') || document.querySelector('.main-content');
        if (header) {
            const b = document.createElement('button');
            b.className = 'btn btn-primary btn-sm create-user-btn ms-2';
            b.textContent = 'Create User';
            b.addEventListener('click', function(){
                $('#um_user_id').val(''); 
                $('#userForm')[0].reset(); 
                $('#um_confirm_wrap').show(); 
                $('#um_password').attr('placeholder',''); 
                $('#userModalTitle').text('Create User'); 
                $('#um_status').val('active');
                $('#um_role_id').val('');
                $('#um_policy_hints').show();
                $('#um_hint_text').text('Enter a strong password'); 
                updateHints();
                modal.show(); 
            });
            header.appendChild(b);
        }
        
        $('#userSaveBtn').on('click', function(){
          const payload = $('#userForm').serializeArray().reduce((a,x)=>{a[x.name]=x.value; return a;},{});
          const isCreate = !payload.user_id;
          const emailVal = $('#um_user_email').val().trim();
          const emailOk = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailVal);
          
          if (!emailOk) { 
              $('#um_user_email').addClass('is-invalid'); 
              $('#um_email_hint').removeClass('bg-success').addClass('bg-danger').text('Invalid email'); 
              return; 
          }
          
          if (isCreate) {
            if (!payload.user_name || !payload.user_email || !payload.password || !parseInt(payload.role_id||'0',10)) {
              alert('Please fill in name, email, password, and role.');
              return;
            }
            const confirmVal = $('#um_password_confirm').val();
            if (payload.password !== confirmVal) { alert('Passwords do not match.'); return; }
          }
          
          const url = '../modules/users.php';
          const action = isCreate ? 'create' : 'update';
          payload.action = action;
          
          setSaveButtonLoading(true);
          
          $.post(url, payload, function(res){
            setSaveButtonLoading(false);
            if (res.status==='success') { 
                modal.hide();
                location.reload(); 
            } else { 
                alert(res.message||'Save failed'); 
            }
          }, 'json').fail(function() {
            setSaveButtonLoading(false);
            alert('Failed to save user');
          });
        });

        let policy = { len: 8, upper: 1, lower: 1, num: 1, sym: 1 };
        $.getJSON('../modules/settings.php', { action: 'list' }, function(rs){
          const items = rs.data || [];
          function val(name) { const f = items.find(x=>x.setting_name===name); return f?f.value:''; }
          policy.len = parseInt(val('Minimum Password Length')||8);
          policy.upper = val('Require Uppercase Letters')==='1'?1:0;
          policy.lower = val('Require Lowercase Letters')==='1'?1:0;
          policy.num = val('Require Numbers')==='1'?1:0;
          policy.sym = val('Require Symbols')==='1'?1:0;
        });
        
        function updateHints(){
          const v = $('#um_password').val()||'';
          const okLen = v.length >= policy.len;
          const okU = /[A-Z]/.test(v) || !policy.upper;
          const okL = /[a-z]/.test(v) || !policy.lower;
          const okN = /\d/.test(v) || !policy.num;
          const okS = /[^A-Za-z0-9]/.test(v) || !policy.sym;
          $('#um_hint_len').toggleClass('bg-secondary', !okLen).toggleClass('bg-success', okLen).text('Min '+policy.len);
          $('#um_hint_upper').toggleClass('bg-secondary', !okU).toggleClass('bg-success', okU);
          $('#um_hint_lower').toggleClass('bg-secondary', !okL).toggleClass('bg-success', okL);
          $('#um_hint_num').toggleClass('bg-secondary', !okN).toggleClass('bg-success', okN);
          $('#um_hint_sym').toggleClass('bg-secondary', !okS).toggleClass('bg-success', okS);
        }
        
        $('#um_password').on('input', function(){ $('#um_policy_hints').show(); updateHints(); });
        $('#um_user_email').on('input', function(){
          const v = $('#um_user_email').val().trim();
          const ok = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v);
          $('#um_user_email').toggleClass('is-invalid', !ok);
          $('#um_email_hint').toggleClass('bg-secondary', !ok).toggleClass('bg-success', ok).toggleClass('bg-danger', !ok).text(ok ? 'Valid email' : 'Invalid email');
        });
        
        document.body.addEventListener('click', function(e){
          const btn = e.target.closest('button[data-action="modify"]'); if (!btn) return;
          $('#um_policy_hints').show(); $('#um_hint_text').text('Leave blank to keep existing password'); updateHints();
        });
        
        $('.create-user-btn').on('click', function(){ $('#um_policy_hints').show(); $('#um_hint_text').text('Enter a strong password'); updateHints(); });
      });
    </script>
</body>

</html> 