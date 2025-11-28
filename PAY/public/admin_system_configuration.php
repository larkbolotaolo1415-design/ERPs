<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../init.php';
$pageTitle = "System Configuration";
$userName = $_SESSION['user_name'] ?? 'User';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Configuration</title>
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

  /* Responsive */
  @media (max-width: 992px) {
    .main-content {
      margin-left: 0;
    }

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

  #card {
    height: 800px;
  }
</style>

</head>

<body>
    <?php include __DIR__ . '/../includes/layout/sidebar.php'; ?>
    <?php include __DIR__ . '/../includes/layout/topbar.php'; ?>

  <div class="main-content p-4 mt-5">

    <div class="card shadow-sm border-0">
      <div class="card-body" id="card">
        <ul class="nav nav-tabs mb-4" id="companySettingsTabs" role="tablist">
          <li class="nav-item" role="presentation">
            <button class="nav-link active" id="deductions-tab" data-bs-toggle="tab" data-bs-target="#deductions" type="button" role="tab">Deductions</button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link" id="tax-tab" data-bs-toggle="tab" data-bs-target="#tax" type="button" role="tab">Tax Table</button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link" id="benefits-tab" data-bs-toggle="tab" data-bs-target="#benefits" type="button" role="tab">Benefits</button>
          </li>
        </ul>

        <div class="tab-content" id="companySettingsTabsContent">

          <!-- Deductions Tab -->
          <div class="tab-pane fade show active" id="deductions" role="tabpanel">
            <div class="row align-items-center mb-3">
              <div class="col-md-6 d-flex align-items-center gap-3">
                <button class="btn btn-outline-primary btn-sm">Export</button>
                <p class="text-muted small mb-0">Showing 1-10 of 10 Total Deductions</p>
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
                    <th>Deduction Name</th>
                    <th>Type</th>
                    <th>Amount / Formula</th>
                    <th>Eligibility</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                  </tr>
                </thead>
                <tbody></tbody>
              </table>
            </div>

            <div class="d-flex justify-content-end align-items-center gap-3 mt-3">
              <button class="btn btn-primary btn-sm">Import CSV / Excel</button>
              <nav>
                <ul class="pagination mb-0"></ul>
              </nav>
            </div>
          </div>

          <!-- Tax Table Tab -->
          <div class="tab-pane fade" id="tax" role="tabpanel">
            <div class="row align-items-center mb-3">
              <div class="col-md-6 d-flex align-items-center gap-3">
                <button class="btn btn-outline-primary btn-sm">Export</button>
                <p class="text-muted small mb-0">Showing 1-10 of 10 Total Tax Ranges</p>
              </div>
            </div>

            <div class="table-responsive">
              <table class="table table-sm align-middle">
                <thead class="table-light">
                  <tr>
                    <th>Range From</th>
                    <th>Range To</th>
                    <th>Rate on Excess</th>
                    <th>Additional Amount</th>
                    <th class="text-end">Actions</th>
                  </tr>
                </thead>
                <tbody></tbody>
              </table>
            </div>

            <div class="d-flex justify-content-end align-items-center gap-3 mt-3">
              <button class="btn btn-primary btn-sm">Import CSV / Excel</button>
              <nav>
                <ul class="pagination mb-0"></ul>
              </nav>
            </div>
          <div class="col-md-4 d-flex align-items-center gap-2 ms-auto">
            <input type="text" class="form-control form-control-sm" placeholder="Search..." id="dedSearchInput">
          <button class="btn btn-primary btn-sm" id="dedSearchBtn">Search</button>
          </div>

          <!-- Benefits Tab -->
          <div class="tab-pane fade" id="benefits" role="tabpanel">
            <div class="row align-items-center mb-3">
              <div class="col-md-6 d-flex align-items-center gap-3">
                <button class="btn btn-outline-primary btn-sm">Export</button>
                <p class="text-muted small mb-0">Showing 1-10 of 10 Total Benefits</p>
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
                    <th>Benefit Name</th>
                    <th>Type</th>
                    <th>Amount / Formula</th>
                    <th>Eligibility</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                  </tr>
                </thead>
                <tbody></tbody>
              </table>
            </div>

            <div class="d-flex justify-content-end align-items-center gap-3 mt-3">
              <button class="btn btn-primary btn-sm">Import CSV / Excel</button>
              <nav>
                <ul class="pagination mb-0"></ul>
              </nav>
            </div>
          </div>

        </div>
      </div>
    </div>
  </div>

  <!-- Deductions Modal Only -->
  <div class="modal fade" id="dedModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Edit Deduction</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form id="dedForm">
            <input type="hidden" id="df_id" name="deduct_id">
            <div class="mb-2">
              <label class="form-label">Name</label>
              <input class="form-control" id="df_name" name="deduct_name">
            </div>
            <div class="mb-2">
              <label class="form-label">Type</label>
              <select class="form-select" id="df_type" name="type">
                <option value="percentage">Percentage</option>
                <option value="fixed">Fixed</option>
                <option value="custom_formula">Custom Formula</option>
              </select>
            </div>
            <div class="mb-2">
              <label class="form-label">Rate or Formula</label>
              <input class="form-control" id="df_rof" name="rate_or_formula">
            </div>
            <div class="mb-2">
              <label class="form-label">Minimum</label>
              <input type="number" class="form-control" id="df_min" name="minimum">
            </div>
            <div class="mb-2">
              <label class="form-label">Maximum</label>
              <input type="number" class="form-control" id="df_max" name="maximum">
            </div>
            <div class="mb-2">
              <label class="form-label">Status</label>
              <select class="form-select" id="df_status" name="status">
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
              </select>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button id="dedSaveBtn" type="button" class="btn btn-primary">Save Changes</button>
        </div>
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

    // Load data and setup modals - DEDUCTIONS ONLY
    $(function(){
      const dedTbody = document.querySelector('#deductions tbody');
      const taxTbody = document.querySelector('#tax tbody');
      const benTbody = document.querySelector('#benefits tbody');
      
      // Initialize Bootstrap modal for deductions only
      const dModal = new bootstrap.Modal(document.getElementById('dedModal'));

      // Load deductions data
      if (dedTbody) {
        $.getJSON('../modules/config_data.php', { action: 'list', resource: 'deductions' }, function(rd){
          dedTbody.innerHTML = '';
          (rd.data||[]).forEach(d => {
            const tr = document.createElement('tr');
            tr.innerHTML = `<td>${d.deduct_name}</td><td>${d.type}</td><td>${d.rate_or_formula || ''}</td><td>${d.minimum || ''} - ${d.maximum || ''}</td><td>${d.status}</td><td class="text-end"><button class="btn btn-outline-secondary btn-sm edit-deduction-btn" data-res="deductions" data-id="${d.deduct_id}">Edit</button></td>`;
            dedTbody.appendChild(tr);
          });
        });
      }

      // Load tax data (keeping original alert method)
      if (taxTbody) {
        $.getJSON('../modules/config_data.php', { action: 'list', resource: 'taxes' }, function(rt){
          taxTbody.innerHTML = '';
          (rt.data||[]).forEach(t => {
            const tr = document.createElement('tr');
            tr.innerHTML = `<td>₱${parseInt(t.range_from||0).toLocaleString()}</td><td>₱${parseInt(t.range_to||0).toLocaleString()}</td><td>${t.rate_on_excess}%</td><td>₱${parseInt(t.additional_amount||0).toLocaleString()}</td><td class="text-end"><button class="btn btn-outline-secondary btn-sm" data-res="taxes" data-id="${t.tax_id}">Edit</button></td>`;
            taxTbody.appendChild(tr);
          });
        });
      }

      // Load benefits data (keeping original alert method)
      if (benTbody) {
        $.getJSON('../modules/config_data.php', { action: 'list', resource: 'benefits' }, function(rb){
          benTbody.innerHTML = '';
          (rb.data||[]).forEach(b => {
            const tr = document.createElement('tr');
            tr.innerHTML = `<td>${b.ben_name}</td><td>${b.type}</td><td></td><td>${b.eligibility||''}</td><td>${b.status}</td><td class="text-end"><button class="btn btn-outline-secondary btn-sm" data-res="benefits" data-id="${b.ben_id}">Edit</button></td>`;
            benTbody.appendChild(tr);
          });
        });
      }

      // Handle edit button clicks for DEDUCTIONS ONLY
      $(document).on('click', '.edit-deduction-btn', function() {
        const res = $(this).data('res');
        const id = $(this).data('id');
        const tr = $(this).closest('tr');
        
        if (res === 'deductions') {
          // Populate deduction modal
          $('#df_id').val(id);
          $('#df_name').val(tr.find('td:eq(0)').text().trim());
          $('#df_type').val(tr.find('td:eq(1)').text().trim());
          $('#df_rof').val(tr.find('td:eq(2)').text().trim());
          
          // Parse eligibility range
          const range = tr.find('td:eq(3)').text().trim().split(' - ');
          $('#df_min').val(parseInt(range[0]||'0',10));
          $('#df_max').val(parseInt(range[1]||'0',10));
          
          $('#df_status').val(tr.find('td:eq(4)').text().trim());
          dModal.show();
        }
      });

      // Save deduction changes
      $('#dedSaveBtn').on('click', function() {
        const p = { 
          action: 'update', 
          resource: 'deductions', 
          deduct_id: $('#df_id').val(), 
          deduct_name: $('#df_name').val(), 
          type: $('#df_type').val(), 
          rate_or_formula: $('#df_rof').val(), 
          minimum: $('#df_min').val(), 
          maximum: $('#df_max').val(), 
          status: $('#df_status').val() 
        };
        
        $.post('../modules/config_data.php', p, function(res) {
          if (res.status === 'success') {
            dModal.hide();
            location.reload();
          } else {
            alert('Error saving deduction: ' + res.message);
          }
        }, 'json');
        });
        $('#dedSaveBtn').on('click', function(){ const p = { action: 'update', resource: 'deductions', deduct_id: $('#df_id').val(), deduct_name: $('#df_name').val(), type: $('#df_type').val(), rate_or_formula: $('#df_rof').val(), minimum: $('#df_min').val(), maximum: $('#df_max').val(), status: $('#df_status').val() }; $.post('../modules/config_data.php', p, function(res){ if (res.status==='success') location.reload(); }, 'json'); });
        $('#taxSaveBtn').on('click', function(){ const p = { action: 'update', resource: 'taxes', tax_id: $('#tf_id').val(), range_from: $('#tf_rf').val(), range_to: $('#tf_rt').val(), rate_on_excess: $('#tf_roe').val(), additional_amount: $('#tf_add').val() }; $.post('../modules/config_data.php', p, function(res){ if (res.status==='success') location.reload(); }, 'json'); });
        $('#benSaveBtn').on('click', function(){ const p = { action: 'update', resource: 'benefits', ben_id: $('#bf_id').val(), ben_name: $('#bf_name').val(), type: $('#bf_type').val(), eligibility: $('#bf_elig').val(), status: $('#bf_status').val() }; $.post('../modules/config_data.php', p, function(res){ if (res.status==='success') location.reload(); }, 'json'); });

        $(function() {
            // Deduction search
            $('#dedSearchBtn').on('click', function() {
                const query = $('#dedSearchInput').val().trim().toLowerCase();
                $('#deductions tbody tr').each(function() {
                    const name = $(this).find('td').eq(0).text().toLowerCase();
                    const type = $(this).find('td').eq(1).text().toLowerCase();
                    $(this).toggle(name.includes(query) || type.includes(query));
                });
            });

            // Trigger search when Enter is pressed in input
            $('#dedSearchInput').on('keypress', function(e) {
                if (e.which === 13) $('#dedSearchBtn').click();
            });

            // Benefits search
            $('#benSearchBtn').on('click', function() {
                const query = $('#benSearchInput').val().trim().toLowerCase();
                $('#benefits tbody tr').each(function() {
                    const name = $(this).find('td').eq(0).text().toLowerCase();
                    const type = $(this).find('td').eq(1).text().toLowerCase();
                    $(this).toggle(name.includes(query) || type.includes(query));
                });
            });

            $('#benSearchInput').on('keypress', function(e) {
                if (e.which === 13) $('#benSearchBtn').click();
            });
        });
      });

      // Keep original alert-based editing for tax and benefits
      document.body.addEventListener('click', function(e){
        const btn = e.target.closest('button[data-res]');
        if (!btn || btn.classList.contains('edit-deduction-btn')) return;
        
        const res = btn.getAttribute('data-res');
        const id = parseInt(btn.getAttribute('data-id'), 10);
        const tr = btn.closest('tr');
        
        if (res === 'taxes') {
          const rf = parseInt(prompt('Range From:' )||'0',10);
          const rt = parseInt(prompt('Range To:' )||'0',10);
          const roe = parseInt(prompt('Rate on Excess (%):' )||'0',10);
          const add = parseInt(prompt('Additional Amount:' )||'0',10);
          $.post('../modules/config_data.php', { action: 'update', resource: 'taxes', tax_id: id, range_from: rf, range_to: rt, rate_on_excess: roe, additional_amount: add }, function(res){ if (res.status==='success') location.reload(); }, 'json');
        } else if (res === 'benefits') {
          const name = prompt('Benefit Name:')||'';
          const type = prompt('Type (fixed|percentage|variable):')||'';
          const elig = prompt('Eligibility:')||'';
          const status = prompt('Status (active|inactive):')||'active';
          $.post('../modules/config_data.php', { action: 'update', resource: 'benefits', ben_id: id, ben_name: name, type, eligibility: elig, status }, function(res){ if (res.status==='success') location.reload(); }, 'json');
        }
      });
    });

  </script>
</body>
</html>