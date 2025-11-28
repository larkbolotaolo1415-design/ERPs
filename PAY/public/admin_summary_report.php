<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../init.php';
$pageTitle = "Summary Report";
$userName = $_SESSION['user_name'] ?? 'User';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Summary Report</title>
    <link rel="stylesheet" href="../assets/css/dashboard_style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
:root {
    --accent: #2563EB;
    --accent-dark: #1E40AF;
    --light-bg: #F9FAFB;
    --white: #FFFFFF;
    --gray-text: #6B7280;
}

/* General Layout */
body {
    background-color: var(--light-bg);
    color: #111827;
}
 

#user-avatar {
    width: 45px;
    height: 45px;
    object-fit: cover;
    border: 2px solid var(--accent);
}

/* Greeting Header */
h2 {
    color: #111827;
    font-weight: 700;
}

#greeting {
    color: var(--gray-text);
    font-size: 0.95rem;
}

/* Divider */
hr {
    border: 1px solid #CBD5E1;
    opacity: 1;
}

/* Card Base */
.card {
    border: none;
    border-radius: 12px;
}

/* Filter Card */
.card-body h6 {
    color: var(--accent);
    font-weight: 700;
}

.form-label {
    font-size: 0.85rem;
    font-weight: 600;
    color: var(--gray-text);
}

.form-select,
.form-control {
    border-radius: 8px;
    border: 1px solid #E5E7EB;
    font-size: 0.85rem;
}

/* Buttons */
.btn-primary {
    background-color: var(--accent);
    border-color: var(--accent);
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 500;
    padding: 6px 18px;
}

.btn-primary:hover {
    background-color: var(--accent-dark);
}

.btn-outline-primary {
    color: var(--accent);
    border-color: var(--accent);
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 500;
    padding: 6px 18px;
}

.btn-outline-primary:hover {
    background-color: var(--accent);
    color: var(--white);
}

/* Summary Cards */
.bg-primary {
    background-color: var(--accent) !important;
}

.card .fw-semibold {
    color: var(--white);
}

.card h4 {
    font-size: 1.5rem;
    font-weight: 700;
}

/* Table */
.table {
    border: 1px solid #E5E7EB;
    font-size: 0.9rem;
}

.table thead th {
    background-color: #F3F6FA;
    color: var(--accent);
    font-weight: 700;
    border-bottom: none;
}

.table thead th {
    border: none !important;
}


.table tbody td {
    vertical-align: middle;
}

.table tbody tr:hover {
    background-color: #F9FAFB;
}

/* Pagination */
.page-link {
    color: var(--accent);
    border: none;
}

.page-item.active .page-link {
    background-color: var(--accent);
    border-color: var(--accent);
}

.page-link:hover {
    background-color: var(--accent-dark);
    color: var(--white);
}

/* Charts */
.card canvas {
    width: 100%;
    height: 220px !important;
}

/* Scrollbar */
::-webkit-scrollbar {
    width: 8px;
}

::-webkit-scrollbar-thumb {
    background-color: #93C5FD;
    border-radius: 10px;
}

/* Responsive */
@media (max-width: 992px) {
  /* Sidebar behavior for small screens is handled globally in assets/css/dashboard_style.css */

  .main-content {
    margin-left: 0;
    padding: 1rem;
  }

    .row.g-3 .col-md-3 {
        flex: 0 0 100%;
        max-width: 100%;
    }
}

.form-label {
  font-size: 0.85rem;
  font-weight: 600;
  color: var(--accent) !important; 
  text-align: left;
  display: block;
}

</style>


</head>

<body>
    <?php include __DIR__ . '/../includes/layout/sidebar.php'; ?>
    <?php include __DIR__ . '/../includes/layout/topbar.php'; ?>

  <div class="main-content p-4 mt-5">

        <!-- Payroll Summary Section -->
  <!-- Filter Card -->
<div class="card shadow-sm border-0 mb-4">
  <div class="card-body">
    <h6 class="text-start fw-semibold text-primary mb-3">
      Filter Global Payroll Summary Report
    </h6>

    <form>
      <!-- Row 1 -->
      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label small fw-semibold mb-1">Date Range:</label>
          <div class="d-flex align-items-center">
            <input type="date" class="form-control form-control-sm me-2" value="2025-10-01">
            <span class="mx-1">to</span>
            <input type="date" class="form-control form-control-sm" value="2025-10-14">
          </div>
        </div>

        <div class="col-md-6">
          <label class="form-label small fw-semibold mb-1">Department:</label>
          <select class="form-select form-select-sm">
            <option>- All Departments -</option>
          </select>
        </div>
      </div>

      <!-- Row 2 -->
      <div class="row g-3 mt-2">
        <div class="col-md-6">
          <label class="form-label small fw-semibold mb-1">Payroll Period:</label>
          <select class="form-select form-select-sm">
            <option>2nd Cutoff – September 2025</option>
          </select>
        </div>

        <div class="col-md-6">
          <label class="form-label small fw-semibold mb-1">Report Type:</label>
          <select class="form-select form-select-sm">
            <option>Summary / Detailed</option>
          </select>
        </div>
      </div>

      <!-- Row 3 -->
      <div class="row g-3 mt-2 align-items-end">
        <div class="col-md-6">
          <label class="form-label small fw-semibold mb-1">Employee Type:</label>
          <select class="form-select form-select-sm">
            <option>All / Regular / Contractual / Part-time</option>
          </select>
        </div>

        <div class="col-md-6 text-end">
          <button class="btn btn-outline-primary btn-sm me-2">Export as CSV/PDF</button>
          <button class="btn btn-primary btn-sm">Generate Report</button>
        </div>
      </div>
    </form>
  </div>
</div>


  <!-- Summary Cards -->
  <div class="row g-3 mb-4 text-center">
    <div class="col-md-3">
      <div class="card shadow-sm bg-primary text-white border-0">
        <div class="card-body py-4">
          <h6 class="fw-semibold mb-2">Total Gross Pay</h6>
          <h4 id="sumGross" class="fw-bold mb-0">₱0.00</h4>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card shadow-sm bg-primary text-white border-0">
        <div class="card-body py-4">
          <h6 class="fw-semibold mb-2">Total Deductions</h6>
          <h4 id="sumDed" class="fw-bold mb-0">₱0.00</h4>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card shadow-sm bg-primary text-white border-0">
        <div class="card-body py-4">
          <h6 class="fw-semibold mb-2">Total Net Pay</h6>
          <h4 id="sumNet" class="fw-bold mb-0">₱0.00</h4>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card shadow-sm bg-primary text-white border-0">
        <div class="card-body py-4">
          <h6 class="fw-semibold mb-2">Average Net per Employee</h6>
          <h4 id="avgNet" class="fw-bold mb-0">₱0.00</h4>
        </div>
      </div>
    </div>
  </div>

  <!-- Employee Table -->
  <div class="card shadow-sm border-0 mb-4">
    <div class="table-responsive">
      <table class="table table-bordered align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th>Employee Name</th>
            <th>Department</th>
            <th>Gross Pay (₱)</th>
            <th>Deductions (₱)</th>
            <th>Net Pay (₱)</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
    </div>
    
    <div class="card-footer bg-white text-end">
    <nav>
        <ul class="pagination justify-content-end mb-0">
          <li class="page-item"><a class="page-link">First</a></li>
          <li class="page-item"><a class="page-link">&lt;</a></li>
          <li class="page-item"><a class="page-link">10</a></li>
          <li class="page-item"><a class="page-link">11</a></li>
          <li class="page-item disabled"><a class="page-link">...</a></li>
          <li class="page-item"><a class="page-link">20</a></li>
          <li class="page-item active"><a class="page-link">21</a></li>
          <li class="page-item"><a class="page-link">&gt;</a></li>
          <li class="page-item"><a class="page-link">Last</a></li>
        </ul>
      </nav>
    </div>
  </div>

  <!-- Charts -->
  <div class="row g-4">
    <div class="col-md-6">
      <div class="card shadow-sm border-0">
        <div class="card-body">
          <canvas id="salaryBarChart" height="180"></canvas>
        </div>
      </div>
    </div>
    <div class="col-md-6">
      <div class="card shadow-sm border-0">
        <div class="card-body">
          <canvas id="overtimePieChart" height="180"></canvas>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
const ctxBar = document.getElementById('salaryBarChart').getContext('2d');
const bar = new Chart(ctxBar, {
  type: 'bar',
  data: {
    labels: ['IT', 'Finance', 'HR', 'Operations', 'Marketing'],
    datasets: [{
      label: 'Department-wise Salary Cost (₱)',
      data: [],
      backgroundColor: 'rgba(37, 99, 235, 0.7)',
      borderColor: '#2563EB',
      borderWidth: 1,
      borderRadius: 5
    }]
  },
  options: {
    responsive: true,
    scales: {
      y: { beginAtZero: true },
      x: { ticks: { font: { size: 12 } } }
    },
    plugins: { legend: { display: false } }
  }
});

const ctxPie = document.getElementById('overtimePieChart').getContext('2d');
const pie = new Chart(ctxPie, {
  type: 'pie',
  data: {
    labels: ['IT', 'Finance', 'HR', 'Operations', 'Marketing'],
    datasets: [{
      data: [],
      backgroundColor: ['#2563EB', '#3B82F6', '#60A5FA', '#93C5FD', '#BFDBFE'],
      borderWidth: 0
    }]
  },
  options: {
    responsive: true,
    plugins: {
      legend: { position: 'bottom' }
    }
  }
});

$(function(){
  $.getJSON('../modules/periods.php', { action: 'list' }, function(rp){
    const periods = rp.data||[];
    const current = periods.find(p=>p.status==='processing')||periods.find(p=>p.status==='open')||periods[0];
    if (!current) return;
    $.getJSON('../modules/payroll.php', { action: 'list', period_id: current.period_id }, function(pr){
      const rows = pr.data||[];
      const tbody = document.querySelector('table.table tbody');
      tbody.innerHTML='';
      let sumGross=0, sumDed=0, sumNet=0;
      const byDept={};
      rows.forEach(r=>{
        const tr = document.createElement('tr');
        tr.innerHTML = `<td>${r.user_name||''}</td><td>${r.dept_name||''}</td><td>${parseInt(r.gross_pay||0).toLocaleString()}</td><td>${parseInt(r.total_deduction||0).toLocaleString()}</td><td>${parseInt(r.net_pay||0).toLocaleString()}</td>`;
        tbody.appendChild(tr);
        sumGross += parseInt(r.gross_pay||0);
        sumDed += parseInt(r.total_deduction||0);
        sumNet += parseInt(r.net_pay||0);
        const d = r.dept_name||'Unknown';
        if (!byDept[d]) byDept[d] = 0;
        byDept[d] += parseInt(r.gross_pay||0);
      });
      $('#sumGross').text('₱'+sumGross.toLocaleString());
      $('#sumDed').text('₱'+sumDed.toLocaleString());
      $('#sumNet').text('₱'+sumNet.toLocaleString());
      $('#avgNet').text('₱'+Math.round(sumNet/Math.max(1,rows.length)).toLocaleString());
      const labels = Object.keys(byDept);
      const data = labels.map(d=>byDept[d]);
      bar.data.labels = labels;
      bar.data.datasets[0].data = data;
      bar.update();
      pie.data.labels = labels;
      pie.data.datasets[0].data = labels.map(d=>Math.round(byDept[d]/Math.max(1,rows.length)));
      pie.update();
    });
  });
});
</script>

        
    </div>

    <script>
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
        const ctxSalary = document.getElementById('salaryChart').getContext('2d');
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

        const ctxOvertime = document.getElementById('overtimeChart').getContext('2d');
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
    </script>
</body>

</html>
