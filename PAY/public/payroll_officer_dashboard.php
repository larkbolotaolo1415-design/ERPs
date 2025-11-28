<!-- Copy Paste This php block -->
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../init.php';
$pageTitle = "Dashboard"; // Change to correct title 
$userName = $_SESSION['user_name'] ?? 'User';
?>     

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payroll Officer | Dashboard</title>
    <link rel="stylesheet" href="../assets/css/dashboard_style.css">
    <link rel="stylesheet" href="../assets/css/payroll_officer_employee_management_style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</head>

<body>
    <!-- Place includes at top -->
    <?php include __DIR__ . '/../includes/layout/sidebar.php'; ?>
    <?php include __DIR__ . '/../includes/layout/topbar.php'; ?>

        <div class="main-content p-4 mt-5">
            <div class="stats">
                <div class="card" id="card-total-employees">
                    <h3>Total Employees</h3>
                    <p>Active: <span id="activeCount">0</span></p>
                    <p>Inactive: <span id="inactiveCount">0</span></p>
                    <p>On Leave: <span id="onLeaveCount">0</span></p>
                </div>
                <div class="card" id="card-current-period">
                    <h3>Current Payroll Period</h3>
                    <p id="periodRange">-</p>
                    <h3>Total Gross Pay</h3>
                    <p id="grossTotal">₱0</p>
                </div>
                <div class="card" id="card-pending-requests">
                    <h3>Pending Requests</h3>
                    <p>OT: <span id="pendingOT">0</span></p>
                    <p>Leave: <span id="pendingLeave">0</span></p>
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

            <!-- Monthly Gross vs Net Pay Line -->
            <div class="col-md-6">
                <div class="card p-3">
                    <h5 class="card-title">Monthly Gross vs Net Pay</h5>
                    <div style="height: 250px;">
                        <canvas id="payTrendChart"></canvas>
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
                    <option value="all" disabled selected hidden>Category</option>
                    <option value="name (a-z)">Name (A-Z)</option>
                    <option value="name (z-a)">Name (Z-A)</option>
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
                            <th>Upd ID</th>
                            <th>Category</th>
                            <th>Details</th>
                            <th>From</th>
                            <th>Date/Time</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>U-1042</td>
                            <td>Payroll</td>
                            <td>Payroll Successful</td>
                            <td>Payroll Officer</td>
                            <td>Nov 5, 2025 - 10:18 AM</td>
                            <td><span class="badge status-badge bg-success">Succesful</span></td>
                            <td class="table-actions">
                                <button class="btn btn-outline-primary me-1" data-bs-toggle="modal"
                                    data-bs-target="#viewProfileModal1">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <button class="btn btn-outline-info me-1">
                                    <i class="bi bi-box-arrow-up-right"></i>
                                </button>
                            </td>
                        </tr>

                        <tr>
                            <td>U-1050</td>
                            <td>Overtime</td>
                            <td>OT Request</td>
                            <td>HR System</td>
                            <td>Nov 6, 2025 - 08:42 AM</td>
                            <td><span class="badge status-badge bg-warning text-white">Pending</span></td>
                            <td class="table-actions">
                                <button class="btn btn-outline-primary me-1" data-bs-toggle="modal"
                                    data-bs-target="#viewProfileModal2">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <button class="btn btn-outline-info me-1">
                                    <i class="bi bi-box-arrow-up-right"></i>
                                </button>
                            </td>
                        </tr>

                        <tr>
                            <td>U-1061</td>
                            <td>Leave</td>
                            <td>Denied</td>
                            <td>Head Nurse</td>
                            <td>Nov 4, 2025 - 04:57 PM</td>
                            <td><span class="badge status-badge bg-danger">Rejected</span></td>
                            <td class="table-actions">
                                <button class="btn btn-outline-primary me-1" data-bs-toggle="modal"
                                    data-bs-target="#viewProfileModal3">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <button class="btn btn-outline-info me-1">
                                    <i class="bi bi-box-arrow-up-right"></i>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Showing page text -->
            <div class="mb-2" style="color: #5B5757;">
                Showing Page 1 of 3
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
        </div>

        <!-- Chart.js CDN -->
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <!-- Datalabels plugin CDN -->
        <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels"></script>

        <!-- Dashboard Script -->
        <script>
            document.addEventListener("DOMContentLoaded", () => {
                const dateElement = document.querySelector('.current-date');
                const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
                if (dateElement) dateElement.textContent = new Date().toLocaleDateString(undefined, options);
            });

            /*$("#logout-btn").click(() => {
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
            });*/
            // Dynamic stats fetch
            $(function() {
                const searchBtn = document.getElementById('searchButton');
                // Total Employees
                $.getJSON('../modules/employees.php', { action: 'list', status: 'active' }, function(r1) {
                    const active = (r1.data || []).length;
                    $.getJSON('../modules/employees.php', { action: 'list', status: 'inactive' }, function(r2) {
                        const inactive = (r2.data || []).length;
                        const aEl = document.getElementById('activeCount');
                        const iEl = document.getElementById('inactiveCount');
                        if (aEl) aEl.textContent = active;
                        if (iEl) iEl.textContent = inactive;
                        $.getJSON('../modules/requests.php', { action: 'list', type: 'leave' }, function(rlv) {
                            const leaves = (rlv.data || []).filter(x => (String(x.status||'').toLowerCase()) === 'approved');
                            const today = new Date().toISOString().slice(0,10);
                            const setIds = new Set();
                            leaves.forEach(l => {
                                const df = (l.date_from || '').slice(0,10);
                                const dt = (l.date_to || '').slice(0,10);
                                if (df && dt && df <= today && today <= dt) setIds.add(l.emp_id);
                            });
                            const lvEl = document.getElementById('onLeaveCount');
                            if (lvEl) lvEl.textContent = setIds.size;
                        });
                    });
                });

                // Period and payroll totals
                $.getJSON('../modules/periods.php', { action: 'list' }, function(rp) {
                    const periods = rp.data || [];
                    const current = periods.find(p => (String(p.status||'').toLowerCase()) === 'processing') || periods.find(p => (String(p.status||'').toLowerCase()) === 'open') || periods[0];
                    const rangeEl = document.getElementById('periodRange');
                    const grossEl = document.getElementById('grossTotal');
                    if (current && rangeEl) rangeEl.textContent = current.start_date + ' - ' + current.end_date;
                    if (current) {
                        $.getJSON('../modules/payroll.php', { action: 'list', period_id: current.period_id }, function(pr) {
                            const rows = pr.data || [];
                            const gross = rows.reduce((s, r) => s + (parseInt(r.gross_pay || 0)), 0);
                            if (grossEl) grossEl.textContent = '₱' + gross.toLocaleString();
                        });
                    }
                });

                // Pending requests (OT, Leave)
                $.getJSON('../modules/requests.php', { action: 'list', type: 'overtime' }, function(ro) {
                    const pendOT = (ro.data || []).filter(x => (String(x.status||'').toLowerCase()) === 'pending').length;
                    const otEl = document.getElementById('pendingOT');
                    if (otEl) otEl.textContent = pendOT;
                });
                $.getJSON('../modules/requests.php', { action: 'list', type: 'leave' }, function(rl) {
                    const pendLeave = (rl.data || []).filter(x => (String(x.status||'').toLowerCase()) === 'pending').length;
                    const lvEl = document.getElementById('pendingLeave');
                    if (lvEl) lvEl.textContent = pendLeave;
                });

                // Charts hydration
                const empCanvas = document.getElementById('empStatusChart');
                const payCanvas = document.getElementById('payTrendChart');

                // Placeholder charts
                let empChart = null;
                let payChart = null;
                if (empCanvas) {
                    const ctx = empCanvas.getContext('2d');
                    empChart = new Chart(ctx, {
                        type: 'doughnut',
                        data: { labels: ['No Data'], datasets: [{ data: [1], backgroundColor: ['#e5e7eb'] }] },
                        options: { plugins: { legend: { position: 'bottom' } }, cutout: '70%' }
                    });
                    $.when(
                        $.getJSON('../modules/employees.php', { action: 'list', status: 'active' }),
                        $.getJSON('../modules/employees.php', { action: 'list', status: 'inactive' }),
                        $.getJSON('../modules/requests.php', { action: 'list', type: 'leave' })
                    ).done(function(a1, a2, a3) {
                        const act = (a1[0].data || []).length;
                        const inact = (a2[0].data || []).length;
                        const leaves = (a3[0].data || []).filter(x => (String(x.status||'').toLowerCase()) === 'approved');
                        const today = new Date().toISOString().slice(0,10);
                        const onLeaveSet = new Set();
                        leaves.forEach(l => {
                            const df = (l.date_from || '').slice(0,10);
                            const dt = (l.date_to || '').slice(0,10);
                            if (df && dt && df <= today && today <= dt) { onLeaveSet.add(l.emp_id); }
                        });
                        const onLeave = onLeaveSet.size;
                        const activeOnDuty = Math.max(0, act - onLeave);
                        const sum = activeOnDuty + onLeave + inact;
                        empChart.data.labels = sum ? ['Active', 'On Leave', 'Inactive'] : ['No Data'];
                        empChart.data.datasets[0].data = sum ? [activeOnDuty, onLeave, inact] : [1];
                        empChart.data.datasets[0].backgroundColor = sum ? ['#2563EB','#F59E0B','#93C5FD'] : ['#e5e7eb'];
                        empChart.update();
                    });
                }

                if (payCanvas) {
                    const ctx = payCanvas.getContext('2d');
                    payChart = new Chart(ctx, {
                        type: 'line',
                        data: { labels: [], datasets: [
                            { label: 'Gross Pay', data: [], borderColor: '#2563EB', backgroundColor: 'rgba(37,99,235,.1)', tension: .3, fill: false },
                            { label: 'Net Pay', data: [], borderColor: '#198754', backgroundColor: 'rgba(25,135,84,.1)', tension: .3, fill: false }
                        ] },
                        options: { responsive: true, maintainAspectRatio: false }
                    });
                    $.getJSON('../modules/periods.php', { action: 'list' }, function(rp) {
                        const periods = (rp.data || []).slice(-6);
                        const labels = periods.map(p => p.start_date);
                        const reqs = periods.map(p => $.getJSON('../modules/payroll.php', { action: 'list', period_id: p.period_id }));
                        if (!reqs.length) { payChart.data.labels = labels; payChart.update(); return; }
                        $.when.apply($, reqs).done(function() {
                            const args = arguments;
                            const gross = []; const net = [];
                            for (let i = 0; i < periods.length; i++) {
                                const rows = (args[i][0].data || []);
                                gross.push(rows.reduce((s,r)=> s + (parseInt(r.gross_pay||0)),0));
                                net.push(rows.reduce((s,r)=> s + (parseInt(r.net_pay||0)),0));
                            }
                            payChart.data.labels = labels;
                            payChart.data.datasets[0].data = gross;
                            payChart.data.datasets[1].data = net;
                            payChart.update();
                        });
                    });
                }

                // Dynamic update feed table
                const feedBody = document.querySelector('.table tbody');
                if (feedBody) {
                    $.getJSON('../modules/employees.php', { action: 'list' }, function(re) {
                        const empMap = {};
                        (re.data || []).forEach(r => { empMap[r.user_id] = r.user_name; });
                        $.when(
                            $.getJSON('../modules/requests.php', { action: 'list', type: 'leave' }),
                            $.getJSON('../modules/requests.php', { action: 'list', type: 'overtime' }),
                            $.getJSON('../modules/requests.php', { action: 'list', type: 'bonus' })
                        ).done(function(lv, ot, ba) {
                            const rows = [];
                            (lv[0].data || []).forEach(x => {
                                rows.push({
                                    id: 'LV-' + x.leave_id,
                                    category: 'Leave',
                                    details: x.leave_type + ' (' + (x.days||0) + 'd)',
                                    from: empMap[x.emp_id] || ('User ' + x.emp_id),
                                    dt: x.date_from || x.created_at || '',
                                    status: (x.status||'').charAt(0).toUpperCase() + (x.status||'').slice(1)
                                });
                            });
                            (ot[0].data || []).forEach(x => {
                                rows.push({
                                    id: 'OT-' + x.overtime_id,
                                    category: 'Overtime',
                                    details: 'OT ' + (x.hours||0) + 'h',
                                    from: empMap[x.emp_id] || ('User ' + x.emp_id),
                                    dt: x.date_from || x.created_at || '',
                                    status: (x.status||'').charAt(0).toUpperCase() + (x.status||'').slice(1)
                                });
                            });
                            (ba[0].data || []).forEach(x => {
                                rows.push({
                                    id: 'BA-' + x.ba_id,
                                    category: (x.type||'').toUpperCase(),
                                    details: x.description || 'Adjustment',
                                    from: empMap[x.emp_id] || ('User ' + x.emp_id),
                                    dt: x.date_from || x.created_at || '',
                                    status: (x.status||'').charAt(0).toUpperCase() + (x.status||'').slice(1)
                                });
                            });
                            rows.sort((a,b)=> (new Date(b.dt||0)) - (new Date(a.dt||0)));
                            const latest = rows.slice(0, 10);
                            feedBody.innerHTML = latest.map(r => `
                                <tr>
                                    <td>${r.id}</td>
                                    <td>${r.category}</td>
                                    <td>${r.details}</td>
                                    <td>${r.from}</td>
                                    <td>${r.dt || '-'}</td>
                                    <td><span class="badge status-badge ${r.status==='Approved'?'bg-success': r.status==='Rejected'?'bg-danger':'bg-warning text-white'}">${r.status||'-'}</span></td>
                                    <td class="table-actions">
                                        <button class="btn btn-outline-primary me-1"><i class="bi bi-eye"></i></button>
                                        <button class="btn btn-outline-info me-1"><i class="bi bi-box-arrow-up-right"></i></button>
                                    </td>
                                </tr>
                            `).join('');
                        });
                    });
                }
            });
        </script>
</body>

</html>
