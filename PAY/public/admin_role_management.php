<?php 
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../init.php';
$pageTitle = "Role Management";
$userName = $_SESSION['user_name'] ?? 'User';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>

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

        .role-card {
            flex: 1;
            background: var(--accent);
            color: white;
            border-radius: 12px;
            padding: 25px;
            min-width: 260px;
            text-align: center;
            transition: all 0.3s ease;
        }

        .override-card,
        .button-card {
            flex: 1;
            background: white;
            border-radius: 12px;
            padding: 25px;
            min-width: 260px;
            box-shadow: 0 0 8px rgba(0, 0, 0, 0.05);
        }

        .card-section {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            margin-bottom: 2rem;
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

        /* TABLES */
        .permissions-table {
            width: 100%;
            table-layout: fixed;
        }

        .permissions-table th,
        .permissions-table td {
            text-align: center;
            vertical-align: middle;
            padding: 0.75rem;
        }

        .permissions-table th:first-child,
        .permissions-table td:first-child {
            text-align: start;
            width: 80%;
        }

        .permissions-table thead th {
            background-color: #f3f6fa;
            color: var(--accent);
            font-weight: 600;
        }

        .table-section-header td {
            background-color: #f3f6fa;
            font-weight: 600;
            color: var(--accent);
        }

        .form-check-input {
            transform: scale(1.2);
            cursor: pointer;
        }

        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
        }

        .loading-content {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            text-align: center;
        }

        @media (max-width: 992px) {
            .main-content {
                margin-left: 0;
            }
        }
        .unsaved-prompt { position: fixed; top: 12px; left: 0; right: 0; display: flex; justify-content: center; z-index: 1100; }
        .unsaved-box { background: #fff; border: 1px solid var(--accent); box-shadow: 0 4px 14px rgba(0,0,0,.15); border-radius: 8px; padding: 10px 14px; display: flex; align-items: center; gap: 10px; }
        .unsaved-msg { color: var(--accent); font-weight: 600; }
        .d-none { display: none !important; }
    </style>
</head>

<body>

    <?php include __DIR__ . '/../includes/layout/sidebar.php'; ?>
    <?php include __DIR__ . '/../includes/layout/topbar.php'; ?>
    <div id="unsavedPrompt" class="unsaved-prompt d-none">
        <div class="unsaved-box">
            <div class="unsaved-msg">You have unsaved changes. Leave this page?</div>
            <button id="unsavedConfirm" class="btn btn-primary btn-sm">Confirm</button>
            <button id="unsavedCancel" class="btn btn-outline-secondary btn-sm">Cancel</button>
        </div>
    </div>
    <div class="main-content p-4 mt-5">        

            <!-- Role Cards -->
            <div class="card-section">
                <!-- Single Role Card that will be updated dynamically -->
                <div class="role-card" id="dynamicRoleCard">
                    <h3 class="fw-bold" id="roleCardTitle">SELECT A ROLE</h3>
                    <p id="roleCardDescription">Please select a role from the dropdown to view details</p>
                </div>

                <div class="override-card">
                    <h6>User Override</h6>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="override" id="allUsers" checked>
                        <label class="form-check-label" for="allUsers">All users</label>
                    </div>

                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="override" id="individual">
                        <label class="form-check-label" for="individual">Individual user only</label>
                    </div>

                    <select id="userSelect" class="form-select mt-3"></select>
                </div>

                <div class="button-card text-center">
                    <h6>Role Actions</h6>
                    <button id="addRoleBtn" class="btn btn-outline-primary btn-action w-100 mb-2"><i class="bi bi-plus-circle"></i> Add Role</button>
                    <button id="editRoleBtn" class="btn btn-primary btn-action w-100"><i class="bi bi-pencil-square"></i> Edit Role</button>
                </div>
            </div>

            <div class="bg-white p-3 rounded shadow-sm">
                <div class="d-flex justify-content-end gap-2 mb-3">
                    <select id="permRoleSelect" class="form-select w-auto"></select>
                    <button id="permResetBtn" class="btn btn-secondary">Reset Filter</button>
                    <button id="permSaveBtn" class="btn btn-primary">Save Access</button>
                </div>
                <div class="table-responsive permissions-group"></div>
            </div>

    </div>

<!-- Modals -->
<div class="modal fade" id="roleModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title">Role</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <form id="roleForm">
          <input type="hidden" name="role_id" id="rm_role_id">
          <label class="form-label">Role Name</label>
          <input type="text" class="form-control" name="role_name" id="rm_role_name">
        </form>
      </div>
      <div class="modal-footer"><button class="btn btn-secondary" data-bs-dismiss="modal">Close</button><button id="roleSaveBtn" class="btn btn-primary">Save</button></div>
    </div>
  </div>
</div>

<div class="modal fade" id="assignModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title">Assign Role</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <form id="assignForm">
          <label class="form-label">User</label>
          <select class="form-select" id="am_user" name="user_id"></select>
          <label class="form-label mt-3">Role</label>
          <select class="form-select" id="am_role" name="role_id"></select>
        </form>
      </div>
      <div class="modal-footer"><button class="btn btn-secondary" data-bs-dismiss="modal">Close</button><button id="assignSaveBtn" class="btn btn-primary">Assign</button></div>
    </div>
  </div>
</div>

<!-- Confirmation Modal -->
<div class="modal fade" id="confirmModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Confirm Save</h5>
        <button class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p class="mb-0">Are you sure you want to save these permission changes?</p>
        <p class="text-muted small mb-0">This will update the access rights for the selected role.</p>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button id="confirmSaveBtn" class="btn btn-primary">Yes, Save Changes</button>
      </div>
    </div>
  </div>
</div>


<!-- Loading Overlay -->
<div id="loadingOverlay" class="loading-overlay" style="display:none;">
  <div class="loading-content">
    <div class="spinner-border text-primary mb-3" role="status" style="width: 3rem; height: 3rem;">
      <span class="visually-hidden">Loading...</span>
    </div>
    <h5>Saving Changes...</h5>
    <p class="text-muted mb-0">Please wait</p>
  </div>
</div>

<!-- Success Modal -->
<div class="modal fade" id="successModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title"><i class="bi bi-check-circle-fill me-2"></i>Success</h5>
        <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p class="mb-0" id="successMessage">Permissions saved successfully!</p>
      </div>
      <div class="modal-footer">
        <button class="btn btn-success" data-bs-dismiss="modal">OK</button>
      </div>
    </div>
  </div>
</div>

<!-- Error Modal -->
<div class="modal fade" id="errorModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title"><i class="bi bi-exclamation-triangle-fill me-2"></i>Error</h5>
        <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p class="mb-0" id="errorMessage">An error occurred while saving permissions</p>
      </div>
      <div class="modal-footer">
        <button class="btn btn-danger" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<script>
    $(function(){
        const cardSection = document.querySelector('.card-section');
        const userSelect = document.getElementById('userSelect');
        const roleModal = new bootstrap.Modal(document.getElementById('roleModal'));
        const assignModal = new bootstrap.Modal(document.getElementById('assignModal'));
        const confirmModal = new bootstrap.Modal(document.getElementById('confirmModal'));
        const successModal = new bootstrap.Modal(document.getElementById('successModal'));
        document.getElementById('successModal').addEventListener('hidden.bs.modal', function () {
    location.reload();
});

        const errorModal = new bootstrap.Modal(document.getElementById('errorModal'));
        const loadingOverlay = document.getElementById('loadingOverlay');
        

        // ------------------------------------
        // User Override Change Handler
        // ------------------------------------
        $('input[name="override"]').on('change', function () {
            if ($('#allUsers').is(':checked')) {
                $('#userSelect').prop('disabled', true);
            } else {
                $('#userSelect').prop('disabled', false);
            }
        });

        // Default state: disabled
        $('#userSelect').prop('disabled', true);

        // Get references to the single role card elements
        const roleCard = document.getElementById('dynamicRoleCard');
        const roleCardTitle = document.getElementById('roleCardTitle');
        const roleCardDescription = document.getElementById('roleCardDescription');
        
        let roleMap = {};
        let allRoles = [];

        function showLoading() {
            if (loadingOverlay) loadingOverlay.style.display = 'flex';
        }

        function hideLoading() {
            if (loadingOverlay) loadingOverlay.style.display = 'none';
        }

        function showSuccess(message) {
            document.getElementById('successMessage').textContent = message;
            successModal.show();
        }

        function showError(message) {
            document.getElementById('errorMessage').textContent = message;
            errorModal.show();
        }

        // Function to update the role card based on selected role
        function updateRoleCard(roleId) {
            const role = allRoles.find(r => r.role_id === roleId);
            if (role) {
                roleCardTitle.textContent = role.role_name.toUpperCase();
                roleCardDescription.textContent = getRoleDescription(role.role_name);
            } else {
                roleCardTitle.textContent = 'SELECT A ROLE';
                roleCardDescription.textContent = 'Please select a role from the dropdown to view details';
            }
        }

        // Function to get role descriptions
        function getRoleDescription(roleName) {
            const descriptions = {
                'Admin': 'This role oversees every single operation in the payroll system with full administrative access.',
                'Payroll Officer': 'Manages employee payroll, attendance, and government remittance reports.',
                'Manager': 'Oversees department employees, approves leaves and attendance, and generates reports.',
                'Employee': 'Views personal payroll history, attendance logs, and submits leave/overtime requests.'
            };
            return descriptions[roleName] || `This role has specific permissions for ${roleName} functions.`;
        }

        // Load roles and populate dropdown
        $.getJSON('../modules/roles.php', { action: 'list' }, function(rr){
            allRoles = rr.data || [];
            
            // Populate role map and dropdown
            const permRoleSelect = document.getElementById('permRoleSelect');
            permRoleSelect.innerHTML = '';
            
            allRoles.forEach(r => {
                roleMap[r.role_id] = r.role_name;
                const opt = document.createElement('option');
                opt.value = r.role_id;
                opt.textContent = r.role_name;
                permRoleSelect.appendChild(opt);
            });

            // Set default selection and update role card
            const defaultRole = allRoles.find(x => x.role_name === 'Admin') || allRoles[0];
            if (defaultRole) {
                permRoleSelect.value = defaultRole.role_id;
                updateRoleCard(defaultRole.role_id);
            }
        });

        $.getJSON('../modules/users.php', { action: 'list' }, function(ru){
            const rows = ru.data || [];
            userSelect.innerHTML = '<option value="">- Select User -</option>';
            rows.forEach(u => {
                const opt = document.createElement('option');
                opt.value = u.user_id;
                opt.textContent = `${u.user_name} (#${u.user_id})`;
                userSelect.appendChild(opt);
            });
        });

        $('#addRoleBtn').on('click', function(){ 
            $('#rm_role_id').val(''); 
            $('#rm_role_name').val(''); 
            roleModal.show(); 
        });

        $('#editRoleBtn').on('click', function(){
            const currentRoleId = parseInt($('#permRoleSelect').val() || '0', 10);
            if (currentRoleId) {
                const role = allRoles.find(x => x.role_id === currentRoleId);
                if (role) {
                    $('#rm_role_id').val(role.role_id);
                    $('#rm_role_name').val(role.role_name);
                    roleModal.show();
                }
            } else {
                showError('Please select a role first');
            }
        });

        $('#roleSaveBtn').on('click', function(){
            const payload = { 
                action: $('#rm_role_id').val() ? 'update' : 'create', 
                role_id: $('#rm_role_id').val(), 
                role_name: $('#rm_role_name').val() 
            };
            $.post('../modules/roles.php', payload, function(res){ 
                if (res.status === 'success') location.reload(); 
            }, 'json');
        });

        const permContainer = document.querySelector('.permissions-group');
        
        function renderPermissions(roleId) {
            updateRoleCard(roleId); // Update the role card when permissions are rendered
            
            $.getJSON('../modules/roles.php', { action: 'pagesCatalog' }, function(pc){
                const catalog = pc.data||[];
                $.getJSON('../modules/roles.php', { action: 'getPermissions', role_id: roleId }, function(rp){
                    const perms = rp.data||{};
                    const roleName = roleMap[roleId] || '';
                    let prefix = '';
                    if (roleName.toLowerCase().includes('admin')) prefix = 'admin_';
                    else if (roleName.toLowerCase().includes('payroll')) prefix = 'payroll_officer_';
                    else if (roleName.toLowerCase().includes('manager')) prefix = 'manager_';
                    else if (roleName.toLowerCase().includes('employee')) prefix = 'employee_';
                    
                    const filtered = prefix ? catalog.filter(item => (item.key||'').startsWith(prefix)) : catalog;
                    let html = '';
                    html += '<div class="d-flex justify-content-end gap-2 mb-2">';
                    html += '<button id="permSelectAll" class="btn btn-outline-primary btn-sm">Select All</button>';
                    html += '<button id="permDeselectAll" class="btn btn-outline-secondary btn-sm">Deselect All</button>';
                    html += '</div>';
                    html += '<table class="table permissions-table align-middle mb-0">';
                    html += '<thead><tr><th>Module Name</th><th>Access</th></tr></thead><tbody>';
                    const sectionTitle = prefix==='admin_'?'Admin Modules':prefix==='payroll_officer_'?'Payroll Officer Modules':prefix==='manager_'?'Manager Modules':prefix==='employee_'?'Employee Modules':'Modules';
                    html += '<tr class="table-section-header"><td colspan="2">'+sectionTitle+'</td></tr>';
                    filtered.forEach(item => {
                        const checked = perms[item.key] ? 'checked' : '';
                        html += '<tr><td>'+item.name+'</td><td><div class="form-check form-switch d-flex justify-content-center align-items-center m-0"><input class="form-check-input" type="checkbox" data-key="'+item.key+'" '+checked+'></div></td></tr>';
                    });
                    html += '</tbody></table>';
                    permContainer.innerHTML = html;
                    
                    const selectAllBtn = document.getElementById('permSelectAll');
                    const deselectAllBtn = document.getElementById('permDeselectAll');
                    if (selectAllBtn) selectAllBtn.addEventListener('click', function(){ 
                        document.querySelectorAll('.permissions-group input[type="checkbox"]').forEach(chk => { chk.checked = true; }); 
                        window.__rmDirty = true;
                    });
                    if (deselectAllBtn) deselectAllBtn.addEventListener('click', function(){ 
                        document.querySelectorAll('.permissions-group input[type="checkbox"]').forEach(chk => { chk.checked = false; }); 
                        window.__rmDirty = true;
                    });
                });
            });
        }

        // Initialize permissions dropdown
        $.getJSON('../modules/roles.php', { action: 'list' }, function(rr){
            const sel = document.getElementById('permRoleSelect');
            sel.innerHTML = '';
            const roles = rr.data||[];
            roles.forEach(r => { 
                const opt = document.createElement('option'); 
                opt.value = r.role_id; 
                opt.textContent = r.role_name; 
                sel.appendChild(opt); 
            });
            const def = roles.find(x => x.role_name === 'Admin') || roles[0];
            if (def) { 
                sel.value = def.role_id; 
                renderPermissions(def.role_id); 
            }
            
            // Add event listener for dropdown change
            sel.addEventListener('change', function(){ 
                const selectedRoleId = parseInt(sel.value,10);
                updateRoleCard(selectedRoleId);
                renderPermissions(selectedRoleId); 
            });
        });

        // Show confirmation modal when Save Access is clicked
        $('#permSaveBtn').on('click', function(){
            confirmModal.show();
        });

        // Handle actual save when user confirms
        $('#confirmSaveBtn').on('click', function(){
            confirmModal.hide();
            showLoading();
            
            const roleId = parseInt($('#permRoleSelect').val()||'0',10);
            const payload = {};
            document.querySelectorAll('.permissions-group input[type="checkbox"][data-key]').forEach(chk => { 
                payload[chk.getAttribute('data-key')] = chk.checked ? 1 : 0; 
            });
            
            $.post('../modules/roles.php', { 
                action: 'setPermissions', 
                role_id: roleId, 
                payload: JSON.stringify(payload) 
            }, function(res){ 
                // Minimum delay to show loading
                setTimeout(function(){
                    hideLoading();
                    if (res.status==='success') {
                        showSuccess('Permissions saved successfully!');
                    } else {
                        showError(res.message || 'Save failed');
                    }
                }, 800);
            }, 'json').fail(function(){
                setTimeout(function(){
                    hideLoading();
                    showError('An error occurred while saving permissions');
                }, 800);
            });
        });

        // ------------------------------------
        // Reset Filter Button (INSIDE)
        // ------------------------------------
        $('#permResetBtn').on('click', function () {
            const defaultRole = allRoles.find(r => r.role_name === 'Admin') || allRoles[0];
            if (defaultRole) {
                $('#permRoleSelect').val(defaultRole.role_id);
                renderPermissions(defaultRole.role_id);
                updateRoleCard(defaultRole.role_id);
            }
            $('#allUsers').prop('checked', true);
            $('#individual').prop('checked', false);
            $('#userSelect').val('');
            $('#userSelect').prop('disabled', true);
            roleCardTitle.textContent = 'SELECT A ROLE';
            roleCardDescription.textContent = 'Please select a role from the dropdown to view details';
        });
        document.addEventListener('change', function(e){
            if (e.target && e.target.matches('.permissions-group input[type="checkbox"]')) { window.__rmDirty = true; }
        });
        (function(){
            window.__rmDirty = false;
            let pendingHref = null;
            const promptEl = document.getElementById('unsavedPrompt');
            const btnConfirm = document.getElementById('unsavedConfirm');
            const btnCancel = document.getElementById('unsavedCancel');
            function showPrompt(){ if (promptEl) promptEl.classList.remove('d-none'); }
            function hidePrompt(){ if (promptEl) promptEl.classList.add('d-none'); }
            document.addEventListener('click', function(e){
                const link = e.target.closest('a');
                if (!link) return;
                const href = link.getAttribute('href') || '';
                if (!href || href.startsWith('#')) return;
                if (window.__rmDirty) { e.preventDefault(); e.stopPropagation(); pendingHref = href; showPrompt(); }
            }, true);
            if (btnConfirm) btnConfirm.addEventListener('click', function(){ hidePrompt(); window.__rmDirty = false; if (pendingHref) window.location.href = pendingHref; });
            if (btnCancel) btnCancel.addEventListener('click', function(){ hidePrompt(); pendingHref = null; });
        })();
    });

    function setGreeting() {
        const hour = new Date().getHours();
        let greeting = "Good Evening";
        if (hour < 12) greeting = "Good Morning";
        else if (hour < 18) greeting = "Good Afternoon";
        const name = "<?php echo $userName ?? 'Manager'; ?>";
        document.getElementById("greeting").innerHTML = `${greeting}, <strong>${name}</strong>`;
    }
    setGreeting();
</script>

</body>
</html>