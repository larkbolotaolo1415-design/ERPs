<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../init.php';
$pageTitle = "Company Settings";
$userName = $_SESSION['user_name'] ?? 'User';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Company Settings Summary</title>
    <link rel="stylesheet" href="../assets/css/dashboard_style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>


</head>

<body>
    <?php include __DIR__ . '/../includes/layout/sidebar.php'; ?>
    <?php include __DIR__ . '/../includes/layout/topbar.php'; ?>

  <div class="main-content p-4 mt-5">


<div class="card shadow-sm border-0">
  <div class="card-body" id="card">
    <ul class="nav nav-tabs mb-4" id="companySettingsTabs" role="tablist">
      <li class="nav-item" role="presentation">
        <button class="nav-link active" id="departments-tab" data-bs-toggle="tab" data-bs-target="#departments" type="button" role="tab">Departments</button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" id="positions-tab" data-bs-toggle="tab" data-bs-target="#positions" type="button" role="tab">Positions</button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" id="structures-tab" data-bs-toggle="tab" data-bs-target="#structures" type="button" role="tab">Salary Structures</button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" id="grades-tab" data-bs-toggle="tab" data-bs-target="#grades" type="button" role="tab">Salary Grades</button>
      </li>
    </ul>

    <div class="tab-content" id="companySettingsTabsContent">

     <!-- Departments Tab -->
<div class="tab-pane fade show active" id="departments" role="tabpanel">
  <div class="row align-items-center mb-3">
    <div class="col-md-6 d-flex align-items-center gap-3">
      <button class="btn btn-outline-primary btn-sm">Export</button>
      <p id="deptCountText" class="text-muted small mb-0">Loading departments...</p>
    </div>
    <div class="col-md-4 d-flex align-items-center gap-2 ms-auto">
      <input type="text" class="form-control form-control-sm" placeholder="Search...">
      <button class="btn btn-primary btn-sm">Search</button>
    </div>
  </div>

  <div class="table-responsive">
    <table class="table table-sm align-middle">
      <thead class="table-light">
        <tr>
          <th>Department Name</th>
          <th>Code</th>
          <th>Head</th>
          <th>No. of Employees</th>
          <th class="text-end">Actions</th>
        </tr>
      </thead>
      <tbody></tbody>
    </table>
  </div>

  <nav class="d-flex justify-content-end">
    <ul class="pagination mb-0"></ul>
  </nav>
</div>


    <!-- Positions Tab -->
<div class="tab-pane fade" id="positions" role="tabpanel">
  <div class="row align-items-center mb-3">
    <div class="col-md-6 d-flex align-items-center gap-3">
      <button class="btn btn-outline-primary btn-sm">Export</button>
      <p id="posCountText" class="text-muted small mb-0">Loading positions...</p>
    </div>
    <div class="col-md-4 d-flex align-items-center gap-2 ms-auto">
      <input type="text" class="form-control form-control-sm" placeholder="Search...">
      <button class="btn btn-primary btn-sm">Search</button>
    </div>
  </div>

  <div class="table-responsive">
    <table class="table table-sm align-middle">
      <thead class="table-light">
        <tr>
          <th>Position Name</th>
          <th>Department</th>
          <th>Base Salary</th>
          <th class="text-end">Actions</th>
        </tr>
      </thead>
      <tbody></tbody>
    </table>
  </div>

  <nav class="d-flex justify-content-end">
    <ul class="pagination mb-0"></ul>
  </nav>
</div>


     <!-- Salary Structures Tab -->
    <div class="tab-pane fade" id="structures" role="tabpanel">
      <!-- Selection Row -->
      <div class="card p-3 mb-3 border-0 shadow-sm">
        <div class="row align-items-center g-3">
          <div class="col-md-4">
            <label class="form-label fw-semibold mb-1 text-primary text-start d-block">Position/Level</label>
            <select class="form-select" id="structures_pos">
              <option selected>- Position/Level -</option>
              <option>Junior Staff</option>
              <option>Senior Staff</option>
              <option>Manager</option>
            </select>
          </div>
          <div class="col-md-4">
            <label class="form-label fw-semibold mb-1 text-primary text-start d-block">Salary Grade</label>
            <select class="form-select" id="structures_grade">
              <option selected>- Salary Grade -</option>
              <option>SG 1</option>
              <option>SG 2</option>
              <option>SG 3</option>
            </select>
          </div>
          <div class="col-md-4">
            <label class="form-label fw-semibold mb-1 text-primary text-start d-block">Basic Pay</label>
            <input type="text" class="form-control" id="structures_basic_pay" placeholder="₱0">
          </div>
        </div>
      </div>

      <!-- Allowance Section -->
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h6 class="fw-bold text-accent mb-0 text-uppercase">Allowances / Bonuses</h6>
        <button id="addAllowanceBtn" class="btn btn-outline-primary btn-sm">
          <i class="bi bi-plus-circle me-1"></i> Add Allowance
        </button>
      </div>

      <!-- Table -->
      <div class="table-responsive">
        <table id="allowancesTable" class="table table-sm table-hover align-middle">
          <thead class="table-light">
            <tr>
              <th>Type</th>
              <th>Amount</th>
              <th>Remarks</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody><tr><td colspan="4" class="text-center">Select a position to see allowances</td></tr></tbody>
        </table>
      </div>

      <div class="d-flex justify-content-end mt-3">
        <button id="structuresSaveBtn" class="btn btn-primary btn-sm">
            Save Changes
        </button>
    </div>

  <!-- Pagination -->
  <nav class="d-flex justify-content-end">
    <ul class="pagination pagination-sm mb-0"></ul>
  </nav>
</div>

     <!-- Salary Grades Tab -->
<div class="tab-pane fade" id="grades" role="tabpanel">
  <div class="d-flex justify-content-end align-items-center mb-3">
    <div class="col-md-3">
      <button class="btn btn-primary btn-sm w-100">Update Tables via Import CSV/Excel</button>
    </div>
  </div>

  <div class="table-responsive">
    <table class="table table-sm align-middle">
      <thead class="table-light">
        <tr>
          <th>Salary Grade</th>
          <th>Step 1</th>
          <th>Step 2</th>
          <th>Step 3</th>
          <th>Step 4</th>
          <th>Step 5</th>
          <th>Step 6</th>
          <th>Step 7</th>
          <th>Step 8</th>
          <th class="text-end">Actions</th>
        </tr>
      </thead>
      <tbody id="gradesTbody">
        <!-- Salary grades will be loaded here by the main script -->
      </tbody>
    </table>
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

    // Enhanced safeJSON function with better error handling
    function safeJSON(url, params, callback) {
        $.ajax({
            url,
            type: "GET",
            data: params,
            dataType: "json",
            success: function (res) {
                console.log('API Response:', url, res); // Debug log
                if (!res || typeof res !== "object") {
                    console.error("Invalid JSON:", res);
                    callback({ data: [] });
                    return;
                }
                callback(res);
            },
            error: function (xhr, status, error) {
                console.error("AJAX ERROR:", url, "Status:", status, "Error:", error, "Response:", xhr.responseText);
                callback({ data: [] });
            }
        });
    }

    // Main data loading function
    $(function () {
        // ----------------------------
        // LOAD DEPARTMENTS
        // ----------------------------
        const deptTbody = document.querySelector('#departments table tbody');
        if (deptTbody) {
            safeJSON('../modules/company.php', { action: 'list', resource: 'departments' }, function (rd) {
                deptTbody.innerHTML = '';
                const data = rd.data || [];
                const txt = document.getElementById('deptCountText');

                if (txt)
                    txt.textContent = `Showing ${data.length} of ${data.length} Total Departments`;

                if (data.length === 0) {
                    deptTbody.innerHTML = '<tr><td colspan="5" class="text-center">No departments found</td></tr>';
                    return;
                }

                data.forEach(d => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td>${d.dept_name}</td>
                        <td>${d.code || ""}</td>
                        <td>${d.head || ""}</td>
                        <td>${d.employee_count || ""}</td>
                        <td class="text-end">
                          <button class="btn btn-outline-secondary btn-sm" data-res="departments" data-id="${d.dept_id}">
                            Edit
                          </button>
                        </td>`;
                    deptTbody.appendChild(tr);
                });
            });
        }

        // ----------------------------
        // LOAD POSITIONS + BASIC PAY
        // ----------------------------
        const posTbody = document.querySelector('#positions table tbody');
        if (posTbody) {
            safeJSON('../modules/company.php', { action: 'list', resource: 'positions' }, function (rp) {
                safeJSON('../modules/company.php', { action: 'list', resource: 'salary_structures' }, function (rs) {
                    const positions = rp.data || [];
                    const structures = rs.data || [];
                    const payMap = {};

                    structures.forEach(s => {
                        payMap[s.pos_id] = s.basic_pay;
                    });

                    posTbody.innerHTML = '';
                    const txt = document.getElementById('posCountText');
                    if (txt)
                        txt.textContent = `Showing ${positions.length} of ${positions.length} Total Positions`;

                    if (positions.length === 0) {
                        posTbody.innerHTML = '<tr><td colspan="4" class="text-center">No positions found</td></tr>';
                        return;
                    }

                    positions.forEach(p => {
                        const pay = payMap[p.pos_id] || 0;
                        const tr = document.createElement('tr');
                        tr.innerHTML = `
                            <td>${p.pos_name}</td>
                            <td>${p.dept_name || ""}</td>
                            <td>₱${parseInt(pay).toLocaleString()}</td>
                            <td class="text-end">
                               <button class="btn btn-outline-secondary btn-sm" data-res="positions" data-id="${p.pos_id}">
                                Edit
                               </button>
                            </td>`;
                        posTbody.appendChild(tr);
                    });
                });
            });
        }

        // ----------------------------
        // LOAD STRUCTURE POSITION DROPDOWN
        // ----------------------------
        const posSel = document.getElementById('structures_pos');
        const gradeSel = document.getElementById('structures_grade');
        const basicPayInput = document.getElementById('structures_basic_pay');

        if (posSel) {
            safeJSON('../modules/company.php', { action: 'list', resource: 'positions' }, function (rp) {
                posSel.innerHTML = '<option selected>- Position/Level -</option>';
                const positions = rp.data || [];
                
                if (positions.length === 0) {
                    const opt = document.createElement('option');
                    opt.textContent = 'No positions available';
                    opt.disabled = true;
                    posSel.appendChild(opt);
                    return;
                }

                positions.forEach(p => {
                    const opt = document.createElement('option');
                    opt.value = p.pos_id;
                    opt.textContent = p.pos_name;
                    posSel.appendChild(opt);
                });
            });

            posSel.addEventListener('change', function () {
                const posId = parseInt(posSel.value || '0', 10);
                if (!posId) {
                    basicPayInput.value = '₱0';
                    return;
                }

                safeJSON('../modules/company.php', { action: 'list', resource: 'salary_structures' }, function (rs) {
                    const structures = rs.data || [];
                    const s = structures.find(x => parseInt(x.pos_id) === posId);
                    basicPayInput.value = s ? `₱${parseInt(s.basic_pay || 0).toLocaleString()}` : '₱0';
                });
            });
        }

        // ----------------------------
        // LOAD SALARY GRADES DROPDOWN
        // ----------------------------
        if (gradeSel) {
            safeJSON('../modules/company.php', { action: 'list', resource: 'salary_grades' }, function (rg) {
                gradeSel.innerHTML = '<option selected>- Salary Grade -</option>';
                const grades = rg.data || [];
                
                if (grades.length === 0) {
                    const opt = document.createElement('option');
                    opt.textContent = 'No salary grades available';
                    opt.disabled = true;
                    gradeSel.appendChild(opt);
                    return;
                }

                grades.forEach(g => {
                    const opt = document.createElement('option');
                    opt.value = g;
                    opt.textContent = 'SG ' + g;
                    gradeSel.appendChild(opt);
                });
            });
        }

      // ----------------------------
      // SALARY STRUCTURES LOGIC
      // ----------------------------
      const structuresPosSel = document.getElementById('structures_pos');
      const allowancesTbody = document.querySelector('#allowancesTable tbody');
      let currentAllowances = [];
      let allowancesMap = {}; // stores allowances per position in memory

      function renderAllowances() {
          allowancesTbody.innerHTML = '';
          if (currentAllowances.length === 0) {
              allowancesTbody.innerHTML = '<tr><td colspan="4" class="text-center">No allowances found</td></tr>';
              return;
          }
          currentAllowances.forEach((a, index) => {
              const tr = document.createElement('tr');
              tr.innerHTML = `
                  <td><input class="form-control form-control-sm" value="${a.type}"></td>
                  <td><input type="number" class="form-control form-control-sm" value="${a.amount}"></td>
                  <td><input class="form-control form-control-sm" value="${a.remarks || ''}"></td>
                  <td class="text-end">
                      <button class="btn btn-outline-danger btn-sm" data-index="${index}">Delete</button>
                  </td>`;
              allowancesTbody.appendChild(tr);
          });
      }

      // Save current state in the map
      function saveAllowancesToMap() {
          const posId = parseInt(structuresPosSel.value || '0', 10);
          if (!posId) return;
          allowancesMap[posId] = [...currentAllowances]; // shallow copy
      }

      // Add allowance button
      $('#addAllowanceBtn').click(() => {
          currentAllowances.push({ type: '', amount: 0, remarks: '' });
          saveAllowancesToMap();
          renderAllowances();
      });

      // Delete allowance
      allowancesTbody.addEventListener('click', e => {
          if (e.target.tagName === 'BUTTON') {
              const index = parseInt(e.target.getAttribute('data-index'));
              currentAllowances.splice(index, 1);
              saveAllowancesToMap();
              renderAllowances();
          }
      });

      // Load allowances when position is selected
      if (structuresPosSel) {
          structuresPosSel.addEventListener('change', function () {
              const posId = parseInt(structuresPosSel.value || '0', 10);
              if (!posId) {
                  basicPayInput.value = '₱0';
                  currentAllowances = [];
                  renderAllowances();
                  return;
              }

              // Check if we already have unsaved data for this position
              if (allowancesMap[posId]) {
                  currentAllowances = [...allowancesMap[posId]];
                  renderAllowances();
                  return;
              }

              // Otherwise, load from database
              safeJSON('../modules/company.php', { action: 'list', resource: 'salary_structures' }, function (resp) {
                  const structure = (resp.data || []).find(x => parseInt(x.pos_id) === posId);
                  basicPayInput.value = structure ? `₱${parseInt(structure.basic_pay).toLocaleString()}` : '₱0';
                  currentAllowances = structure && structure.allowances ? structure.allowances : [];
                  saveAllowancesToMap();
                  renderAllowances();
              });
          });
      }

      // Save changes button
      $('#structuresSaveBtn').click(() => {
          const posId = parseInt(structuresPosSel.value || '0', 10);
          if (!posId) return alert('Select a position first.');

          const updatedAllowances = Array.from(allowancesTbody.querySelectorAll('tr')).map(tr => {
              const inputs = tr.querySelectorAll('input');
              return {
                  type: inputs[0].value.trim(),
                  amount: parseInt(inputs[1].value) || 0,
                  remarks: inputs[2].value.trim()
              };
          }).filter(a => a.type || a.amount > 0);

          const payload = {
              action: 'update',
              resource: 'salary_structures',
              pos_id: posId,
              basic_pay: parseInt(basicPayInput.value.replace(/[₱,]/g, '')) || 0,
              allowances: updatedAllowances
          };

          $.post('../modules/company.php', payload, res => {
              if (res.status === 'success') {
                  allowancesMap[posId] = [...updatedAllowances]; // update map after saving
                  location.reload();
              } else alert('Failed to save changes');
          }, 'json');
      });

        // ----------------------------
        // LOAD SALARY GRADES TABLE - FIXED VERSION
        // ----------------------------
        const gradesTbody = document.getElementById('gradesTbody');
        if (gradesTbody) {
            console.log('Loading salary grades...');
            safeJSON('../modules/company.php', { action: 'list', resource: 'salary_grades_full' }, function (rg) {
                console.log('Salary grades full response:', rg);
                gradesTbody.innerHTML = '';
                
                if (!rg.data || rg.data.length === 0) {
                    gradesTbody.innerHTML = '<tr><td colspan="10" class="text-center">No salary grades found in database</td></tr>';
                    return;
                }

                rg.data.forEach(g => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td>SG ${g.salary_grade}</td>
                        <td>₱${parseInt(g.step_1 || 0).toLocaleString()}</td>
                        <td>₱${parseInt(g.step_2 || 0).toLocaleString()}</td>
                        <td>₱${parseInt(g.step_3 || 0).toLocaleString()}</td>
                        <td>₱${parseInt(g.step_4 || 0).toLocaleString()}</td>
                        <td>₱${parseInt(g.step_5 || 0).toLocaleString()}</td>
                        <td>₱${parseInt(g.step_6 || 0).toLocaleString()}</td>
                        <td>₱${parseInt(g.step_7 || 0).toLocaleString()}</td>
                        <td>₱${parseInt(g.step_8 || 0).toLocaleString()}</td>
                        <td class='text-end'><button class='btn btn-outline-secondary btn-sm' data-res='salary_grades' data-grade='${g.salary_grade}'>Edit</button></td>`;
                    gradesTbody.appendChild(tr);
                });
            });
        }
    });

    // Modal handling
    $(function(){
        const dm = new bootstrap.Modal(document.getElementById('deptModal'));
        const pm = new bootstrap.Modal(document.getElementById('posModal'));
        const sm = new bootstrap.Modal(document.getElementById('salModal'));
        const gm = new bootstrap.Modal(document.getElementById('gradeModal'));
        
        // open edit from table buttons
        document.body.addEventListener('click', function(e){
            const btn = e.target.closest('button[data-res]'); 
            if (!btn) return;
            
            const tr = btn.closest('tr');
            const res = btn.getAttribute('data-res');
            
            if (res === 'departments') {
                $('#dm_dept_id').val(parseInt(btn.getAttribute('data-id') || '0', 10));
                $('#dm_dept_name').val(tr.children[0].textContent.trim());
                dm.show();
            } else if (res === 'positions') {
                $('#pm_pos_id').val(parseInt(btn.getAttribute('data-id') || '0', 10));
                $('#pm_pos_name').val(tr.children[0].textContent.trim());
                
                // Load departments for dropdown
                safeJSON('../modules/company.php', { action: 'list', resource: 'departments' }, function(rd){ 
                    const sel = $('#pm_dept_id'); 
                    sel.empty(); 
                    (rd.data || []).forEach(d => {
                        sel.append(`<option value="${d.dept_id}">${d.dept_name}</option>`);
                    }); 
                });
                pm.show();
            } else if (res === 'salary_grades') {
                const sg = parseInt(btn.getAttribute('data-grade')||'0',10);
                $('#gm_salary_grade').val(sg);
                $('#gm_step_1').val(parseInt(tr.children[1].textContent.replace(/[^0-9]/g,'')||'0',10));
                $('#gm_step_2').val(parseInt(tr.children[2].textContent.replace(/[^0-9]/g,'')||'0',10));
                $('#gm_step_3').val(parseInt(tr.children[3].textContent.replace(/[^0-9]/g,'')||'0',10));
                $('#gm_step_4').val(parseInt(tr.children[4].textContent.replace(/[^0-9]/g,'')||'0',10));
                $('#gm_step_5').val(parseInt(tr.children[5].textContent.replace(/[^0-9]/g,'')||'0',10));
                $('#gm_step_6').val(parseInt(tr.children[6].textContent.replace(/[^0-9]/g,'')||'0',10));
                $('#gm_step_7').val(parseInt(tr.children[7].textContent.replace(/[^0-9]/g,'')||'0',10));
                $('#gm_step_8').val(parseInt(tr.children[8].textContent.replace(/[^0-9]/g,'')||'0',10));
                gm.show();
            }
        });
        
        $('#deptSaveBtn').on('click', function(){ 
            const p = { 
                action: 'update', 
                resource: 'departments', 
                dept_id: $('#dm_dept_id').val(), 
                dept_name: $('#dm_dept_name').val() 
            }; 
            $.post('../modules/company.php', p, function(res){ 
                if (res.status === 'success') location.reload(); 
            }, 'json'); 
        });
        
        $('#posSaveBtn').on('click', function(){ 
            const p = { 
                action: 'update', 
                resource: 'positions', 
                pos_id: $('#pm_pos_id').val(), 
                pos_name: $('#pm_pos_name').val(), 
                dept_id: $('#pm_dept_id').val(), 
                sg_grade: $('#pm_sg_grade').val() 
            }; 
            $.post('../modules/company.php', p, function(res){ 
                if (res.status === 'success') location.reload(); 
            }, 'json'); 
        });
        
        $('#salSaveBtn').on('click', function(){ 
            const p = { 
                action: 'update', 
                resource: 'salary_structures', 
                pos_id: $('#sm_pos_id').val(), 
                basic_pay: $('#sm_basic_pay').val() 
            }; 
            $.post('../modules/company.php', p, function(res){ 
                if (res.status === 'success') location.reload(); 
            }, 'json'); 
        });
        $('#gradeSaveBtn').on('click', function(){ 
            const p = { 
                action: 'update', 
                resource: 'salary_grades', 
                salary_grade: $('#gm_salary_grade').val(), 
                step_1: $('#gm_step_1').val(), 
                step_2: $('#gm_step_2').val(), 
                step_3: $('#gm_step_3').val(), 
                step_4: $('#gm_step_4').val(), 
                step_5: $('#gm_step_5').val(), 
                step_6: $('#gm_step_6').val(), 
                step_7: $('#gm_step_7').val(), 
                step_8: $('#gm_step_8').val() 
            }; 
            $.post('../modules/company.php', p, function(res){ 
                if (res.status === 'success') location.reload(); 
            }, 'json'); 
        });
    });

    // In your main $(function() { ... } block, add this debug code:
console.log('Checking salary structures loading...');

// Add this after your safeJSON call for salary structures:
safeJSON('../modules/company.php', { action: 'list', resource: 'salary_structures' }, function (rs) {
    console.log('Salary structures raw response:', rs);
    console.log('Salary structures data:', rs.data);
    // ... rest of your code
});
</script>


    <!-- Modals -->
    <div class="modal fade" id="deptModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">Department</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
          <form id="deptForm"><input type="hidden" id="dm_dept_id" name="dept_id"><label class="form-label">Name</label><input class="form-control" id="dm_dept_name" name="dept_name"></form>
        </div>
        <div class="modal-footer"><button class="btn btn-secondary" data-bs-dismiss="modal">Close</button><button id="deptSaveBtn" class="btn btn-primary">Save</button></div>
      </div></div>
    </div>
    <div class="modal fade" id="posModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">Position</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
          <form id="posForm">
            <input type="hidden" id="pm_pos_id" name="pos_id">
            <label class="form-label">Position Name</label><input class="form-control" id="pm_pos_name" name="pos_name">
            <label class="form-label mt-2">Department</label><select class="form-select" id="pm_dept_id" name="dept_id"></select>
            <label class="form-label mt-2">Salary Grade</label><input type="number" class="form-control" id="pm_sg_grade" name="sg_grade">
          </form>
        </div>
        <div class="modal-footer"><button class="btn btn-secondary" data-bs-dismiss="modal">Close</button><button id="posSaveBtn" class="btn btn-primary">Save</button></div>
      </div></div>
    </div>
    <div class="modal fade" id="salModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">Salary Structure</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
          <form id="salForm"><label class="form-label">Basic Pay</label><input type="number" class="form-control" id="sm_basic_pay" name="basic_pay"><input type="hidden" id="sm_pos_id" name="pos_id"></form>
        </div>
        <div class="modal-footer"><button class="btn btn-secondary" data-bs-dismiss="modal">Close</button><button id="salSaveBtn" class="btn btn-primary">Save</button></div>
      </div></div>
    </div>
    <div class="modal fade" id="gradeModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">Salary Grade</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
          <form id="gradeForm">
            <div class="row g-2">
              <div class="col-12"><label class="form-label">Salary Grade</label><input type="number" class="form-control" id="gm_salary_grade" name="salary_grade" readonly></div>
              <div class="col-6"><label class="form-label">Step 1</label><input type="number" class="form-control" id="gm_step_1" name="step_1"></div>
              <div class="col-6"><label class="form-label">Step 2</label><input type="number" class="form-control" id="gm_step_2" name="step_2"></div>
              <div class="col-6"><label class="form-label">Step 3</label><input type="number" class="form-control" id="gm_step_3" name="step_3"></div>
              <div class="col-6"><label class="form-label">Step 4</label><input type="number" class="form-control" id="gm_step_4" name="step_4"></div>
              <div class="col-6"><label class="form-label">Step 5</label><input type="number" class="form-control" id="gm_step_5" name="step_5"></div>
              <div class="col-6"><label class="form-label">Step 6</label><input type="number" class="form-control" id="gm_step_6" name="step_6"></div>
              <div class="col-6"><label class="form-label">Step 7</label><input type="number" class="form-control" id="gm_step_7" name="step_7"></div>
              <div class="col-6"><label class="form-label">Step 8</label><input type="number" class="form-control" id="gm_step_8" name="step_8"></div>
            </div>
          </form>
        </div>
        <div class="modal-footer"><button class="btn btn-secondary" data-bs-dismiss="modal">Close</button><button id="gradeSaveBtn" class="btn btn-primary">Save</button></div>
      </div></div>
    </div>
</body>

</html>
