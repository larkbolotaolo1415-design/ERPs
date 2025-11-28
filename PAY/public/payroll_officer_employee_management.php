<?php 
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../init.php';
$pageTitle = "Employee Management";
$userName = $_SESSION['user_name'] ?? 'User';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payroll Officer | Employee Management</title>
    <link rel="stylesheet" href="../assets/css/dashboard_style.css">
    <link rel="stylesheet" href="../assets/css/payroll_officer_employee_management_style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>
</head>

<body>
    <?php include __DIR__ . '/../includes/layout/sidebar.php'; ?>
    <?php include __DIR__ . '/../includes/layout/topbar.php'; ?>


    <div class="main-content p-4 mt-5">        
        <div class="stats">
            <div class="card">
                <h3>Total Employees</h3>
                <p><span id="totalEmpCount">0</span></p>
            </div>
            <div class="card">
                <h3>Active Employees</h3>
                <p><span id="activeEmpCount">0</span></p>
            </div>
            <div class="card">
                <h3>On Leave</h3>
                <p><span id="onLeaveCount">0</span></p>
            </div>
        </div>

        <div class="row employee-management-charts mt-3 mb-4">
            <!-- Employment Status Donut -->
            <div class="col-md-6">
                <div class="card p-3">
                    <h5 class="card-title">Employment Status</h5>
                    <div style="height: 250px;">
                        <canvas id="empStatusChart" class="employment-status-donut"></canvas>
                    </div>
                </div>
            </div>

            <!-- Active Employees by Department Donut -->
            <div class="col-md-6">
                <div class="card p-3">
                    <h5 class="card-title">Active Employees by Department</h5>
                    <div style="height: 250px;">
                        <canvas id="deptChart" class="active-per-department-donut"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="tab-content" id="employeelistTabsContent"></div>
        <!-- Filters and Search -->
        <div class="tab-pane fade show active" id="employeelist" role="tabpanel" aria-labelledby="employee-list-tab">
            <div class="d-flex align-items-center mb-3 filter-row" style="gap: 10px;">
                <select id="nameFilter" class="form-select"
                    style="width: 180px; background-color: #93C5FD; color: #2563EB; border: none;">
                    <option value="all" disabled selected hidden>Name</option>
                    <option value="name (a-z)">Name (A-Z)</option>
                    <option value="name (z-a)">Name (Z-A)</option>
                </select>

                <select id="positionFilter" class="form-select"
                    style="width: 180px; background-color: #93C5FD; color: #2563EB; border: none;">
                    <option value="all" disabled selected hidden>Position</option>
                    <option value="accountant">Accountant</option>
                    <option value="head-nurse">Head Nurse</option>
                    <option value="doctor">Doctor</option>
                    <option value="staff">Staff</option>
                </select>

                <select id="positionFilter" class="form-select"
                    style="width: 180px; background-color: #93C5FD; color: #2563EB; border: none;">
                    <option value="all" disabled selected hidden>Status</option>
                    <option value="accountant">Active</option>
                    <option value="head-nurse">Inactive</option>
                </select>

                <button class="btn btn-primary me-auto">Apply</button>

                <div class="d-flex align-items-center gap-2">
                    <input type="text" id="searchInput" class="form-control" placeholder="Search..."
                        style="width: 300px; border-color: #2563EB;">
                    <button id="searchButton" class="btn btn-primary fw-semibold"
                        style="background-color: #2563EB; border: none;">
                        Search
                    </button>
                </div>
            </div>

            <!-- Employee Table -->
            <div class="table-responsive">
                <table class="table table-striped align-middle">
                    <thead>
                        <tr>
                            <th>Emp ID</th>
                            <th>Name</th>
                            <th>Position</th>
                            <th>Department</th>
                            <th>Base Salary</th>
                            <th>Status</th>
                            <th>Attendance Rate</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="empTbody"></tbody>
                </table>
            </div>

            <!-- Showing page text -->
            <div class="mb-2" style="color: #5B5757;">
                <span id="tableCountText">Showing 0 employees</span>
            </div>

            <!-- Bottom Actions and Pagination -->
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div class="d-flex">
                    <button class="btn btn-export me-2">
                        <i class="bi bi-file-earmark-text me-1"></i> Export CSV
                    </button>
                    <button class="btn btn-export">
                        <i class="bi bi-file-earmark-spreadsheet me-1"></i> Export Excel
                    </button>
                </div>

                <nav aria-label="Page navigation">
                    <ul class="pagination mb-0">
                        <li class="page-item disabled"><a class="page-link" href="#">Previous</a></li>
                        <li class="page-item active"><a class="page-link" href="#">1</a></li>
                        <li class="page-item"><a class="page-link" href="#">2</a></li>
                        <li class="page-item"><a class="page-link" href="#">3</a></li>
                        <li class="page-item"><a class="page-link" href="#">Next</a></li>
                    </ul>
                </nav>
            </div>
        </div>

        <!-- View Profile Modal 1 -->
        <div class="modal fade" id="viewProfileModal1" tabindex="-1" aria-labelledby="viewProfileLabel1"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-xl modal-profile">
                <div class="modal-content">

                    <!-- Modal Header -->
                    <div class="modal-header"
                        style="border-bottom: 2px solid #2563EB; display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <h5 class="modal-title fw-bold" id="viewProfileLabel1">Employee Profile</h5>
                        </div>
                        <div class="d-flex align-items-center">
                            <div style="font-size: 0.85rem; color: #5B5757; margin-right: 10px;">
                                Employee ID: 1001 | Pediatrics Department | Staff |
                                <span class="badge bg-success">Active</span>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                    </div>

                    <!-- Modal Body -->
                    <div class="modal-body">
                        <!-- Tabs -->
                        <ul class="nav nav-tabs mb-3" id="profileTab" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="salary-tab" data-bs-toggle="tab"
                                    data-bs-target="#salary" type="button" role="tab" aria-controls="salary"
                                    aria-selected="true">Salary Information</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="deductions-tab" data-bs-toggle="tab"
                                    data-bs-target="#deductions" type="button" role="tab" aria-controls="deductions"
                                    aria-selected="false">Deductions & Benefits</button>
                            </li>
                        </ul>

                        <!-- Tab Contents -->
                        <div class="tab-content" id="profileTabContent">
                            <!-- Salary Information Tab -->
                            <div class="tab-pane fade show active" id="salary" role="tabpanel"
                                aria-labelledby="salary-tab">
                                <div class="row">
                                    <!-- Employment Details -->
                                    <div class="col-md-6">
                                        <h6 class="fw-bold text-primary mb-3">Employment Details</h6>
                                        <p><strong>Employment Type:</strong> Full-Time</p>
                                        <p><strong>Hire Date:</strong> Jan 15, 2023</p>
                                        <p><strong>Payment Schedule:</strong> Monthly</p>
                                        <p><strong>Rate Type:</strong> Hourly</p>
                                        <p><strong>Tax Category:</strong> Regular</p>
                                    </div>

                                    <!-- Salary Structure -->
                                    <div class="col-md-6">
                                        <h6 class="fw-bold text-primary mb-3">Salary Structure</h6>
                                        <p><strong>Basic Salary:</strong> ₱25,000</p>
                                        <p><strong>Allowances:</strong> ₱5,000</p>
                                        <p><strong>Total Monthly Compensation:</strong> ₱30,000</p>
                                        <p><strong>Effective Date:</strong> Oct 1, 2025</p>
                                        <p><strong>Remarks:</strong> -</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Deductions & Benefits Tab -->
                            <div class="tab-pane fade" id="deductions" role="tabpanel" aria-labelledby="deductions-tab">
                                <div class="row">
                                    <!-- Deductions -->
                                    <div class="col-md-6">
                                        <h6 class="fw-bold text-danger mb-3">Deductions</h6>
                                        <p><strong>Tax:</strong> ₱3,000</p>
                                        <p><strong>SSS:</strong> ₱500</p>
                                        <p><strong>PhilHealth:</strong> ₱300</p>
                                        <p><strong>Pag-IBIG:</strong> ₱200</p>
                                    </div>

                                    <!-- Benefits -->
                                    <div class="col-md-6">
                                        <h6 class="fw-bold text-success mb-3">Benefits</h6>
                                        <p><strong>Medical:</strong> ₱1,500</p>
                                        <p><strong>Transportation:</strong> ₱1,000</p>
                                        <p><strong>Meal:</strong> ₱800</p>
                                        <p><strong>Other Benefits:</strong> ₱500</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Modal Footer -->
                    <div class="modal-footer">
                        <button class="btn btn-outline-danger" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- View Profile Modal 02 -->
        <div class="modal fade" id="viewProfileModal2" tabindex="-1" aria-labelledby="viewProfileLabel2"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-xl modal-profile">
                <div class="modal-content">

                    <!-- Modal Header -->
                    <div class="modal-header"
                        style="border-bottom: 2px solid #2563EB; display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <h5 class="modal-title fw-bold" id="viewProfileLabel2">Employee Profile</h5>
                        </div>
                        <div class="d-flex align-items-center">
                            <div style="font-size: 0.85rem; color: #5B5757; margin-right: 10px;">
                                Employee ID: 1002 | Pediatrics Department | Head Nurse |
                                <span class="badge bg-success">Active</span>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                    </div>

                    <!-- Modal Body -->
                    <div class="modal-body">
                        <!-- Tabs -->
                        <ul class="nav nav-tabs mb-3" id="profileTab2" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="salary-tab2" data-bs-toggle="tab"
                                    data-bs-target="#salary2" type="button" role="tab" aria-controls="salary2"
                                    aria-selected="true">Salary Information</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="deductions-tab2" data-bs-toggle="tab"
                                    data-bs-target="#deductions2" type="button" role="tab" aria-controls="deductions2"
                                    aria-selected="false">Deductions & Benefits</button>
                            </li>
                        </ul>

                        <!-- Tab Contents -->
                        <div class="tab-content" id="profileTabContent2">
                            <!-- Salary Information Tab -->
                            <div class="tab-pane fade show active" id="salary2" role="tabpanel"
                                aria-labelledby="salary-tab2">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h6 class="fw-bold text-primary mb-3">Employment Details</h6>
                                        <p><strong>Employment Type:</strong> Full-Time</p>
                                        <p><strong>Hire Date:</strong> Mar 10, 2022</p>
                                        <p><strong>Payment Schedule:</strong> Monthly</p>
                                        <p><strong>Rate Type:</strong> Salary</p>
                                        <p><strong>Tax Category:</strong> Regular</p>
                                    </div>
                                    <div class="col-md-6">
                                        <h6 class="fw-bold text-primary mb-3">Salary Structure</h6>
                                        <p><strong>Basic Salary:</strong> ₱28,000</p>
                                        <p><strong>Allowances:</strong> ₱6,000</p>
                                        <p><strong>Total Monthly Compensation:</strong> ₱34,000</p>
                                        <p><strong>Effective Date:</strong> Oct 1, 2025</p>
                                        <p><strong>Remarks:</strong> Excellent performance</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Deductions & Benefits Tab -->
                            <div class="tab-pane fade" id="deductions2" role="tabpanel"
                                aria-labelledby="deductions-tab2">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h6 class="fw-bold text-danger mb-3">Deductions</h6>
                                        <p><strong>Tax:</strong> ₱3,500</p>
                                        <p><strong>SSS:</strong> ₱550</p>
                                        <p><strong>PhilHealth:</strong> ₱320</p>
                                        <p><strong>Pag-IBIG:</strong> ₱250</p>
                                    </div>
                                    <div class="col-md-6">
                                        <h6 class="fw-bold text-success mb-3">Benefits</h6>
                                        <p><strong>Medical:</strong> ₱1,800</p>
                                        <p><strong>Transportation:</strong> ₱1,200</p>
                                        <p><strong>Meal:</strong> ₱900</p>
                                        <p><strong>Other Benefits:</strong> ₱600</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Modal Footer -->
                    <div class="modal-footer">
                        <button class="btn btn-outline-danger" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- View Profile Modal 03 -->
        <div class="modal fade" id="viewProfileModal3" tabindex="-1" aria-labelledby="viewProfileLabel3"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-xl modal-profile">
                <div class="modal-content">

                    <!-- Modal Header -->
                    <div class="modal-header"
                        style="border-bottom: 2px solid #2563EB; display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <h5 class="modal-title fw-bold" id="viewProfileLabel3">Employee Profile</h5>
                        </div>
                        <div class="d-flex align-items-center">
                            <div style="font-size: 0.85rem; color: #5B5757; margin-right: 10px;">
                                Employee ID: 1003 | Pediatrics Department | Doctor |
                                <span class="badge bg-danger">Inactive</span>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                    </div>

                    <!-- Modal Body -->
                    <div class="modal-body">
                        <!-- Tabs -->
                        <ul class="nav nav-tabs mb-3" id="profileTab3" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="salary-tab3" data-bs-toggle="tab"
                                    data-bs-target="#salary3" type="button" role="tab" aria-controls="salary3"
                                    aria-selected="true">Salary Information</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="deductions-tab3" data-bs-toggle="tab"
                                    data-bs-target="#deductions3" type="button" role="tab" aria-controls="deductions3"
                                    aria-selected="false">Deductions & Benefits</button>
                            </li>
                        </ul>

                        <!-- Tab Contents -->
                        <div class="tab-content" id="profileTabContent3">
                            <!-- Salary Information Tab -->
                            <div class="tab-pane fade show active" id="salary3" role="tabpanel"
                                aria-labelledby="salary-tab3">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h6 class="fw-bold text-primary mb-3">Employment Details</h6>
                                        <p><strong>Employment Type:</strong> Part-Time</p>
                                        <p><strong>Hire Date:</strong> Jul 20, 2021</p>
                                        <p><strong>Payment Schedule:</strong> Bi-Weekly</p>
                                        <p><strong>Rate Type:</strong> Hourly</p>
                                        <p><strong>Tax Category:</strong> Regular</p>
                                    </div>
                                    <div class="col-md-6">
                                        <h6 class="fw-bold text-primary mb-3">Salary Structure</h6>
                                        <p><strong>Basic Salary:</strong> ₱32,000</p>
                                        <p><strong>Allowances:</strong> ₱4,500</p>
                                        <p><strong>Total Monthly Compensation:</strong> ₱36,500</p>
                                        <p><strong>Effective Date:</strong> Oct 1, 2025</p>
                                        <p><strong>Remarks:</strong> On probation</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Deductions & Benefits Tab -->
                            <div class="tab-pane fade" id="deductions3" role="tabpanel"
                                aria-labelledby="deductions-tab3">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h6 class="fw-bold text-danger mb-3">Deductions</h6>
                                        <p><strong>Tax:</strong> ₱4,000</p>
                                        <p><strong>SSS:</strong> ₱600</p>
                                        <p><strong>PhilHealth:</strong> ₱350</p>
                                        <p><strong>Pag-IBIG:</strong> ₱250</p>
                                    </div>
                                    <div class="col-md-6">
                                        <h6 class="fw-bold text-success mb-3">Benefits</h6>
                                        <p><strong>Medical:</strong> ₱2,000</p>
                                        <p><strong>Transportation:</strong> ₱1,000</p>
                                        <p><strong>Meal:</strong> ₱900</p>
                                        <p><strong>Other Benefits:</strong> ₱400</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Modal Footer -->
                    <div class="modal-footer">
                        <button class="btn btn-outline-danger" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
</div>d
        <!-- Chart.js CDN -->
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <!-- Datalabels plugin CDN -->
        <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels"></script>

        <!-- Reset Filters Script -->
        <script>
            document.addEventListener("DOMContentLoaded", () => {
                const applyBtn = document.querySelector(".btn-accent");
                const filters = document.querySelectorAll(".filter-section select");

                const dateElement = document.querySelector('.current-date');
                const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
                dateElement.textContent = new Date().toLocaleDateString(undefined, options);

                // Employment Status Donut
                const empStatusCtx = document.querySelector('.employment-status-donut').getContext('2d');

                const empChart = new Chart(empStatusCtx, {
                    type: 'doughnut',
                    data: { labels: ['No Data'], datasets: [{ data: [1], backgroundColor: ['#e5e7eb'], borderWidth: 0 }] },
                    options: { responsive: true, maintainAspectRatio: false, layout: { padding: 20 }, cutout: '80%', plugins: { legend: { display: false }, datalabels: { color: '#000', anchor: 'end', align: 'end', offset: 4, formatter: (v, ctx) => { const sum = ctx.chart.data.datasets[0].data.reduce((a, b) => a + b, 0); const pct = sum ? ((v * 100 / sum).toFixed(0) + '%') : '0%'; return ctx.chart.data.labels[ctx.dataIndex] + ' ' + pct; }, font: { weight: 'bold', size: 12 } } } },
                    plugins: [ChartDataLabels]
                });
                $.when(
                    $.getJSON('../modules/employees.php', { action: 'list', status: 'active' }),
                    $.getJSON('../modules/employees.php', { action: 'list', status: 'inactive' }),
                    $.getJSON('../modules/requests.php', { action: 'list', type: 'leave' })
                ).done(function(a1, a2, a3){
                    const active = (a1[0].data || []).length;
                    const inactive = (a2[0].data || []).length;
                    const leaves = (a3[0].data || []).filter(x => (String(x.status||'').toLowerCase()) === 'approved');
                    const today = new Date().toISOString().slice(0,10);
                    const ids = new Set();
                    leaves.forEach(l => { const df=(l.date_from||'').slice(0,10); const dt=(l.date_to||'').slice(0,10); if (df && dt && df <= today && today <= dt) ids.add(l.emp_id); });
                    const onLeave = ids.size;
                    const activeOnDuty = Math.max(0, active - onLeave);
                    const sum = activeOnDuty + onLeave + inactive;
                    empChart.data.labels = sum ? ['Active','On Leave','Inactive'] : ['No Data'];
                    empChart.data.datasets[0].data = sum ? [activeOnDuty,onLeave,inactive] : [1];
                    empChart.data.datasets[0].backgroundColor = sum ? ['#0d6efd','#ffc107','#dc3545'] : ['#e5e7eb'];
                    empChart.update();
                });

                // Active Employees by Department Donut
                const deptCtx = document.querySelector('.active-per-department-donut').getContext('2d');

                $.getJSON('../modules/employees.php', { action: 'list', status: 'active' }, function(resp){
                    const rows = resp.data || [];
                    const byDept = {};
                    rows.forEach(r => { const d = r.dept_name || 'Unknown'; byDept[d] = (byDept[d]||0)+1; });
                    const labels = Object.keys(byDept);
                    const data = labels.map(l => byDept[l]);
                    new Chart(deptCtx, {
                        type: 'doughnut',
                        data: { labels, datasets: [{ data, backgroundColor: labels.map((_,i)=>['#0d6efd','#6f42c1','#198754','#fd7e14','#0dcaf0','#d63384','#20c997','#ffc107','#6610f2','#93C5FD','#3B82F6'][i%11]), borderWidth: 0 }] },
                        options: { responsive: true, maintainAspectRatio: false, layout: { padding: 20 }, cutout: '80%', plugins: { legend: { display: false }, datalabels: { color: '#000', anchor: 'end', align: 'end', offset: 4, formatter: (v, ctx) => { const sum = ctx.chart.data.datasets[0].data.reduce((a,b)=>a+b,0); const pct = sum?((v*100/sum).toFixed(0)+'%'):'0%'; return ctx.chart.data.labels[ctx.dataIndex] + ' ' + pct; }, font: { weight: 'bold', size: 10 } } } },
                        plugins: [ChartDataLabels]
                    });
                });

            });

            $("#logout-btn").click(() => {
                // REMOVE SESSION ID
                // GO TO LOGIN PAGE
                $.ajax({
                    url: "../modules/logout.php",
                    type: "POST",
                    dataType: "json",
                    success: function (response) {
                        if (response.status === "success") {
                            // $("#successMsg").fadeIn().delay(1000).fadeOut(function() {
                            window.location.href = "login_page.php";
                            // });
                        } else {
                            // $("#failMsg").text(response.message).fadeIn().delay(2000).fadeOut();
                            // console.log(response.message)
                        }
                    },
                    error: (xhr, status, error) => {
                        $("#failMsg").text("Server error. Please try again.").fadeIn().delay(2000).fadeOut();
                        console.log(xhr)
                        console.log(status)
                        console.log(error)
                    }
                });
            });
            $(function() {
                const cards = Array.from(document.querySelectorAll('.stats .card'));
                const totalEl = cards[0]?.querySelector('p');
                const activeEl = cards[1]?.querySelector('p');
                const leaveEl = cards[2]?.querySelector('p');
                const tbody = document.querySelector('table.table tbody');
                if (tbody) tbody.innerHTML = '';
                $.getJSON('../modules/employees.php', { action: 'list' }, function(resp) {
                    let rows = resp.data || [];
                    let filtered = rows;
                    const act = rows.filter(r => r.status === 'active').length;
                    if (totalEl) totalEl.textContent = rows.length;
                    if (activeEl) activeEl.textContent = act;
                    const limit = 10; let page = 1;
                    const pagerUl = document.querySelector('nav[aria-label="Page navigation"] ul.pagination');
                    const showingEl = document.querySelector('.mb-2');
                    function renderPage() {
                        if (!tbody) return;
                        tbody.innerHTML = '';
                        const pages = Math.max(1, Math.ceil(filtered.length / limit));
                        const start = (page - 1) * limit;
                        const slice = filtered.slice(start, start + limit);
                        slice.forEach(r => {
                            const tr = document.createElement('tr');
                            tr.innerHTML = `
                                <td>${r.emp_id}</td>
                                <td>${r.dept_name || ''}</td>
                                <td>
                                    <div><strong>${r.user_name}</strong></div>
                                    <div class=\"text-muted small\">${r.pos_name || ''}</div>
                                </td>
                                <td>${r.hire_date || ''}</td>
                                <td><span class=\"badge ${r.status === 'active' ? 'bg-success' : 'bg-secondary'}\">${r.status}</span></td>
                                <td>
                                    <button class=\"btn btn-outline-primary btn-sm\" data-emp=\"${r.emp_id}\"><i class=\"bi bi-eye\"></i></button>
                                </td>`;
                            tbody.appendChild(tr);
                        });
                        if (showingEl) showingEl.textContent = `Showing Page ${page} of ${pages}`;
                        if (pagerUl) {
                            const items = [];
                            items.push(`<li class=\"page-item ${page===1?'disabled':''}\"><a class=\"page-link\" href=\"#\" data-page=\"prev\">Previous</a></li>`);
                            const startIdx = Math.max(1, page - 2);
                            const endIdx = Math.min(pages, startIdx + 4);
                            for (let i = startIdx; i <= endIdx; i++) {
                                items.push(`<li class=\"page-item ${i===page?'active':''}\"><a class=\"page-link\" href=\"#\" data-page=\"${i}\">${i}</a></li>`);
                            }
                            items.push(`<li class=\"page-item ${page===pages?'disabled':''}\"><a class=\"page-link\" href=\"#\" data-page=\"next\">Next</a></li>`);
                            pagerUl.innerHTML = items.join('');
                        }
                    }
                    renderPage();
                    if (pagerUl) {
                        pagerUl.addEventListener('click', function(e){
                            const a = e.target.closest('a.page-link');
                            if (!a) return;
                            e.preventDefault();
                            const val = a.getAttribute('data-page');
                            const pages = Math.max(1, Math.ceil(filtered.length / limit));
                            if (val==='prev') page = Math.max(1, page-1);
                            else if (val==='next') page = Math.min(pages, page+1);
                            else page = parseInt(val,10);
                            renderPage();
                        });
                    }
                    const btn = document.getElementById('searchButton');
                    const input = document.getElementById('searchInput');
                    function applySearch(){ const q = (input?.value||'').toLowerCase(); filtered = q ? rows.filter(r => String(r.user_name||'').toLowerCase().includes(q)) : rows; page = 1; renderPage(); }
                    if (btn) btn.addEventListener('click', function(e){ e.preventDefault(); applySearch(); });
                    if (input) input.addEventListener('keyup', function(e){ if (e.key === 'Enter') applySearch(); });
                });
                $.getJSON('../modules/requests.php', { action: 'list', type: 'leave', status: 'approved' }, function(rl){
                    const today = new Date();
                    const onLeave = (rl.data || []).filter(x => {
                        const df = new Date(x.date_from);
                        const dt = new Date(x.date_to);
                        return df <= today && dt >= today;
                    }).length;
                    if (leaveEl) leaveEl.textContent = onLeave;
                });
            });
        </script>
</body>

</html>
