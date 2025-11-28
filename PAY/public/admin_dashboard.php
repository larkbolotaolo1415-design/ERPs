<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../init.php';
$pageTitle = "Dashboard";
$userName = $_SESSION['user_name'] ?? 'User';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
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
            height: 150px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            border-radius: 10px;
        }

        .summary-card h6 {
            font-weight: 600;
        }

        .summary-card h3,
        .summary-card p {
            margin: 0;
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
    </style>
</head>

<body>
    <?php include __DIR__ . '/../includes/layout/sidebar.php'; ?>
    <?php include __DIR__ . '/../includes/layout/topbar.php'; ?>

    <div class="main-content p-4 mt-5">

        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card summary-card shadow-sm">
                    <h6>Total Users</h6>
                    <h3>128</h3>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card summary-card shadow-sm">
                    <h6>Departments</h6>
                    <p>8</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card summary-card shadow-sm">
                    <h6>Current Payroll Status</h6>
                    <h3>Processing</h3>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card summary-card shadow-sm">
                    <h6>Pending Approvals</h6>
                    <h3>12</h3>
                </div>
            </div>
        </div>

        <div class="container-fluid py-2">
            <div class="row mb-4">
                <div class="col-lg-6 mb-4">
                    <div class="card shadow-sm h-100 d-flex flex-column">
                        <div class="card-header">Payroll Summary</div>

                        <div class="card-body flex-grow-1 d-flex flex-column justify-content-between">
                            <div>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="text-muted mb-0">Total Gross Pay</h6>
                                    <h6>₱ 1,245,000</h6>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="text-muted mb-0">Total Deductions</h6>
                                    <h6>₱ 325,000</h6>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h6 class="text-muted mb-0">Net Pay</h6>
                                    <h6>₱ 920,000</h6>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h6 class="text-muted mb-0">Average Net Per Employee</h6>
                                    <h6>₱ 920,000</h6>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end gap-2 mt-3">
                                <button class="btn btn-primary fw-semibold">
                                    <i class="bi bi-eye-fill me-2"></i>View Detailed Payroll Report
                                </button>
                                <button class="btn btn-outline-primary fw-semibold">
                                    <i class="bi bi-file-earmark-bar-graph me-2"></i>Generate Cutoff Summary Report
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6 mb-4">
                    <div class="card shadow-sm h-100">
                        <div class="card-header">Department-wise Salary</div>
                        <div class="card-body">
                            <canvas id="salaryChart" height="150"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mb-4 align-items-stretch">
                <div class="col-lg-5 d-flex">
                    <div class="card shadow-sm h-100 flex-fill">
                        <div class="card-header">Overtime Distribution</div>
                        <div class="card-body d-flex justify-content-center align-items-center">
                            <canvas id="overtimeChart" height="100"></canvas>
                        </div>
                    </div>
                </div>

                <div class="col-lg-7 d-flex flex-column">
                    <div class="card shadow-sm mb-4 flex-fill">
                        <div class="card-header">Quick Access</div>
                        <div class="card-body quick-links">
                            <div class="d-flex flex-wrap gap-3">
                                <button class="btn btn-outline-primary">
                                    <i class="bi bi-file-earmark-text me-2"></i>System Configuration
                                </button>
                                <button class="btn btn-outline-primary">
                                    <i class="bi bi-person-plus me-2"></i>Payroll Processing
                                </button>
                                <button class="btn btn-outline-primary">
                                    <i class="bi bi-people me-2"></i>Data Backup
                                </button>
                                <button class="btn btn-outline-primary">
                                    <i class="bi bi-check2-circle me-2"></i>Global Reports
                                </button>
                                <button class="btn btn-outline-primary">
                                    <i class="bi bi-file-earmark-text me-2"></i>Employees & Departments
                                </button>
                                <button class="btn btn-outline-primary">
                                    <i class="bi bi-person-plus me-2"></i>Benefits & Deductions
                                </button>
                                <button class="btn btn-outline-primary">
                                    <i class="bi bi-people me-2"></i>Security Controls
                                </button>
                                <button class="btn btn-outline-primary">
                                    <i class="bi bi-check2-circle me-2"></i>Audit Logs
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="card shadow-sm flex-fill">
                        <div class="card-header">Recent Activity Feed</div>
                        <div class="card-body text-start">
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item text-start">
                                    <span class="fw-bold">Jesserroe Piatos</span> generated the payroll report.
                                    <span class="text-muted float-end small">2 mins ago</span>
                                </li>
                                <li class="list-group-item text-start">
                                    <span class="fw-bold">Maria Santos</span> approved overtime for HR department.
                                    <span class="text-muted float-end small">10 mins ago</span>
                                </li>
                                <li class="list-group-item text-start">
                                    <span class="fw-bold">Admin</span> updated employee salary rates.
                                    <span class="text-muted float-end small">30 mins ago</span>
                                </li>
                                <li class="list-group-item text-start">
                                    <span class="fw-bold">Jesserroe Piatos</span> added a new employee.
                                    <span class="text-muted float-end small">1 hour ago</span>
                                </li>
                            </ul>
                        </div>
                    </div>
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

        // Replace summary cards with backend data
        $(function() {
            $.getJSON('../modules/users.php', { action: 'list', limit: 1, page: 1 }, function(resp) {
                const card = document.querySelectorAll('.summary-card')[0];
                if (card) card.querySelector('h3').textContent = resp.total || (resp.data||[]).length;
            });
            $.getJSON('../modules/company.php', { action: 'list', resource: 'departments' }, function(rd) {
                const card = document.querySelectorAll('.summary-card')[1];
                if (card) card.querySelector('p').textContent = (rd.data||[]).length;
            });
            $.ajax({ url: '../modules/periods.php', method: 'GET', data: { action: 'list' }, dataType: 'json' }).done(function(rp) {
                const periods = rp.data || [];
                const current = periods.find(p => p.status === 'processing') || periods.find(p => p.status === 'open') || periods[0];
                const card = document.querySelectorAll('.summary-card')[2];
                if (card && current) card.querySelector('h3').textContent = current.status;
            });
            $.ajax({ url: '../modules/requests.php', method: 'GET', data: { action: 'list', type: 'leave' }, dataType: 'json' }).done(function(rl) {
                const pendL = (rl.data || []).filter(x => x.status === 'pending').length;
                $.ajax({ url: '../modules/requests.php', method: 'GET', data: { action: 'list', type: 'overtime' }, dataType: 'json' }).done(function(ro) {
                    const pendO = (ro.data || []).filter(x => x.status === 'pending').length;
                    const card = document.querySelectorAll('.summary-card')[3];
                    if (card) card.querySelector('h3').textContent = pendL + pendO;
                });
            });
            // Payroll summary totals
            $.ajax({ url: '../modules/periods.php', method: 'GET', data: { action: 'list' }, dataType: 'json' }).done(function(rp) {
                const periods = rp.data || [];
                const current = periods.find(p => p.status === 'processing') || periods.find(p => p.status === 'open') || periods[0];
                if (!current) return;
                $.ajax({ url: '../modules/payroll.php', method: 'GET', data: { action: 'list', period_id: current.period_id }, dataType: 'json' }).done(function(pr) {
                    const rows = pr.data || [];
                    const gross = rows.reduce((s, r) => s + (parseInt(r.gross_pay || 0)), 0);
                    const ded = rows.reduce((s, r) => s + (parseInt(r.total_deduction || 0)), 0);
                    const net = rows.reduce((s, r) => s + (parseInt(r.net_pay || 0)), 0);
                    const container = document.querySelector('.card .card-body');
                    const texts = document.querySelectorAll('.card .card-body h6');
                    // Using the existing three summary h6 lines order
                    if (texts[1]) texts[1].textContent = '₱ ' + gross.toLocaleString();
                    if (texts[3]) texts[3].textContent = '₱ ' + ded.toLocaleString();
                    if (texts[5]) texts[5].textContent = '₱ ' + net.toLocaleString();
                });
            });
        });

        // CHARTS
        const ctxSalary = document.getElementById('salaryChart').getContext('2d');
        const salaryChart = new Chart(ctxSalary, {
            type: 'bar',
            data: { labels: [], datasets: [{ label: 'Average Salary (₱)', data: [], backgroundColor: 'rgba(37, 99, 235, 0.7)', borderColor: '#2563EB', borderWidth: 1, borderRadius: 5 }] },
            options: {
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });

        const ctxOvertime = document.getElementById('overtimeChart').getContext('2d');
        new Chart(ctxOvertime, {
            type: 'doughnut',
            data: { labels: [], datasets: [{ data: [], backgroundColor: ['#2563EB', '#3B82F6', '#60A5FA', '#93C5FD'], borderWidth: 0 }] },
            options: {
                plugins: { legend: { position: 'bottom' } }
            }
        });

        // Hydrate charts using backend data
        $(function() {
            $.ajax({ url: '../modules/employees.php', method: 'GET', data: { action: 'list' }, dataType: 'json' }).done(function(re) {
                const rows = (re.data || []).filter(r => (r.status||'').toLowerCase() === 'active');
                const byDept = {};
                rows.forEach(r => {
                    const d = r.dept_name || 'Unknown';
                    if (!byDept[d]) byDept[d] = { sal: 0, cnt: 0 };
                    byDept[d].sal += parseInt(r.basic_pay || 0);
                    byDept[d].cnt += 1;
                });
                const labels = Object.keys(byDept);
                const data = labels.map(d => Math.round(byDept[d].sal / Math.max(1, byDept[d].cnt)));
                salaryChart.data.labels = labels;
                salaryChart.data.datasets[0].data = data;
                salaryChart.update();
            });
            $.ajax({ url: '../modules/attendance.php', method: 'GET', data: { action: 'list' }, dataType: 'json' }).done(function(ra) {
                const rows = ra.data || [];
                const byDept = {};
                rows.forEach(r => {
                    const d = r.department || 'Unknown';
                    if (!byDept[d]) byDept[d] = 0;
                    byDept[d] += 1;
                });
                const labels = Object.keys(byDept);
                const data = labels.map(d => byDept[d]);
                const chart = Chart.getChart('overtimeChart');
                if (chart) { chart.data.labels = labels; chart.data.datasets[0].data = data; chart.update(); }
            });
        });
    </script>
</body>

</html>
