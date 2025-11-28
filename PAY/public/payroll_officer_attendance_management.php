<!-- Copy Paste This php block -->
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../init.php';
require_once __DIR__ . '/../includes/db_connect.php';

$pageTitle = "Attendance Management"; // Change to correct title 
$userName = $_SESSION['user_name'] ?? 'User';

// Fetch attendance statistics
$onDuty = 0;
$lateArrivals = 0;
$absent = 0;
$attendanceRate = 0;

$presentToday = 0;
$activeEmployees = 0;

$resActive = $conn->query("SELECT COUNT(*) AS cnt FROM employees WHERE status='active'");
if ($resActive) {
    $row = $resActive->fetch_assoc();
    $activeEmployees = intval($row['cnt']);
}

$resPresent = $conn->query("SELECT COUNT(DISTINCT emp_id) AS cnt FROM attendance WHERE date = CURDATE() AND time_in IS NOT NULL");
if ($resPresent) {
    $row = $resPresent->fetch_assoc();
    $presentToday = intval($row['cnt']);
}

$resOnDuty = $conn->query("SELECT COUNT(*) AS cnt FROM attendance WHERE date = CURDATE() AND time_in IS NOT NULL AND (time_out IS NULL OR time_out='')");
if ($resOnDuty) {
    $row = $resOnDuty->fetch_assoc();
    $onDuty = intval($row['cnt']);
}

$resLate = $conn->query("SELECT COUNT(DISTINCT emp_id) AS cnt FROM attendance WHERE date = CURDATE() AND time_in IS NOT NULL AND time_in > '09:00:00'");
if ($resLate) {
    $row = $resLate->fetch_assoc();
    $lateArrivals = intval($row['cnt']);
}

$onLeaveToday = 0;
$resLeave = $conn->query("SELECT COUNT(DISTINCT emp_id) AS cnt FROM leave_requests WHERE status='approved' AND date_from <= CURDATE() AND date_to >= CURDATE() ");
if ($resLeave) {
    $row = $resLeave->fetch_assoc();
    $onLeaveToday = intval($row['cnt']);
}

$absent = max(0, $activeEmployees - $presentToday - $onLeaveToday);
$denom = max(0, $activeEmployees - $onLeaveToday);
$attendanceRate = $denom > 0 ? round(($presentToday / $denom) * 100, 2) : 0;
$onTimeToday = max(0, $presentToday - $lateArrivals);

// Weekly attendance arrays for last 7 days
$days = [];
$trendOnDuty = [];
$trendLate = [];
$trendAbsent = [];

$startDate = new DateTime();
$startDate->modify('-6 days');
$dailyStats = [];
for ($i = 0; $i < 7; $i++) {
    $d = clone $startDate;
    $d->modify("+{$i} days");
    $key = $d->format('Y-m-d');
    $dailyStats[$key] = ['present' => 0, 'late' => 0];
    $days[] = $d->format('D');
}

$sqlTrend = "SELECT date, COUNT(DISTINCT emp_id) AS present, SUM(CASE WHEN time_in > '09:00:00' THEN 1 ELSE 0 END) AS late FROM attendance WHERE date BETWEEN DATE_SUB(CURDATE(), INTERVAL 6 DAY) AND CURDATE() GROUP BY date ORDER BY date";
$resTrend = $conn->query($sqlTrend);
if ($resTrend) {
    while ($row = $resTrend->fetch_assoc()) {
        $key = $row['date'];
        if (isset($dailyStats[$key])) {
            $dailyStats[$key]['present'] = intval($row['present']);
            $dailyStats[$key]['late'] = intval($row['late']);
        }
    }
}

for ($i = 0; $i < 7; $i++) {
    $d = clone $startDate;
    $d->modify("+{$i} days");
    $key = $d->format('Y-m-d');
    $present = $dailyStats[$key]['present'];
    $late = $dailyStats[$key]['late'];
    $trendOnDuty[] = $present;
    $trendLate[] = $late;
    $trendAbsent[] = max(0, $activeEmployees - $present);
}

$attendanceRecords = [];
$sql = "SELECT a.emp_id, u.user_name AS name, p.pos_name AS position, d.dept_name AS department, a.time_in, a.time_out, a.hours_worked, a.ot_hours AS overtime
        FROM attendance a
        LEFT JOIN employees e ON e.emp_id = a.emp_id
        LEFT JOIN user_table u ON u.user_id = e.user_id
        LEFT JOIN positions p ON p.pos_id = e.pos_id
        LEFT JOIN departments d ON d.dept_id = e.dept_id
        ORDER BY a.date DESC, a.att_id DESC";
$res = $conn->query($sql);
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $attendanceRecords[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payroll Officer | Attendance Management</title>
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
            <div class="card">
                <h3>Currently On-Duty</h3>
                <p><?php echo $onDuty; ?></p>
            </div>


            <div class="card">
                <h3>Late Arrivals</h3>
                <p><?php echo $lateArrivals; ?></p>
            </div>

            <div class="card">
                <h3>Currently Absent</h3>
                <p><?php echo $absent; ?></p>
            </div>

            <div class="card">
                <h3 style="font-size: 24px;">Attendance Rate</h3>
                <p><?php echo $attendanceRate; ?>%</p>
            </div>
        </div>

        <div class="row employee-management-charts mt-3 mb-4">
            <!-- Daily Attendance Donut -->
            <div class="col-md-6">
                <div class="card p-3">
                    <h5 class="card-title">Daily Attendance Breakdown</h5>
                    <div style="height: 250px;">
                        <canvas id="dailyAttendanceChart" class="daily-attendance-chart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Attendance Trend Line -->
            <div class="col-md-6">
                <div class="card p-3">
                    <h5 class="card-title">Weekly Attendance Trend</h5>
                    <div style="height: 250px;">
                        <canvas id="attendanceTrendChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="tab-content" id="employeelistTabsContent"></div>
        <!-- Filters and Search -->
        <div class="tab-pane fade show active" id="employeelist" role="tabpanel" aria-labelledby="employee-list-tab">
            <div class="d-flex align-items-center mb-3 filter-row" style="gap: 10px;">
                <!-- Name Search -->
                <select id="nameFilter" class="form-select"
                    style="width: 180px; background-color: #93C5FD; color: #2563EB; border: none;">
                    <option value="all" disabled selected hidden>Name</option>
                    <option value="name (a-z)">Name (A-Z)</option>
                    <option value="name (z-a)">Name (Z-A)</option>
                </select>

                <!-- Department Dropdown -->
                <select id="departmentFilter" class="form-select"
                    style="width: 180px; background-color: #93C5FD; color: #2563EB; border: none;">
                    <option value="" disabled selected hidden>Department</option>
                    <option value="hr">HR</option>
                    <option value="it">IT</option>
                    <option value="finance">Finance</option>
                    <option value="admin">Admin</option>
                    <option value="operations">Operations</option>
                </select>

                <!-- Date Picker -->
                <input type="date" id="dateFilter" class="form-control"
                    style="width: 180px; background-color: #93C5FD; color: #2563EB; border: none;">


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
                            <th>Time In</th>
                            <th>Time Out</th>
                            <th>Hours Worked</th>
                            <th>Overtime</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($attendanceRecords as $record): ?>
                            <tr>
                                <td><?php echo $record['emp_id']; ?></td>
                                <td><?php echo $record['name']; ?></td>
                                <td><?php echo $record['position']; ?></td>
                                <td><?php echo $record['department']; ?></td>
                                <td><?php echo $record['time_in']; ?></td>
                                <td><?php echo $record['time_out']; ?></td>
                                <td><?php echo $record['hours_worked']; ?></td>
                                <td><?php echo $record['overtime']; ?></td>
                                <td class="table-actions">
                                    <button class="btn btn-outline-primary">
                                        <i class="bi bi-check-circle"></i>
                                    </button>

                                    <button class="btn btn-outline-warning me-1">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
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

        <!-- Chart.js CDN -->
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <!-- Datalabels plugin CDN -->
        <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels"></script>

        <!-- =====================  COMBINED SCRIPT  ===================== -->
        <!-- =====================  SCRIPT  ===================== -->
        <script>
            /* ----------  FIRST LISTENER  (kept for compatibility)  ---------- */
            document.addEventListener("DOMContentLoaded", () => {
                const dateElement = document.querySelector('.current-date');
                const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
                if (dateElement) dateElement.textContent = new Date().toLocaleDateString(undefined, options);
            });

            /* ----------  SECOND LISTENER – CHARTS + TABLE  ---------- */
            document.addEventListener("DOMContentLoaded", () => {
                if (typeof Chart !== 'undefined' && typeof ChartDataLabels !== 'undefined') {
                    Chart.register(ChartDataLabels);
                }
                /* =====  PHP NUMBERS  ===== */
                const onDuty = <?php echo $onDuty; ?>;
                const onTime = <?php echo $onTimeToday; ?>;
                const lateArrivals = <?php echo $lateArrivals; ?>;
                const absent = <?php echo $absent; ?>;

                /* =====  DOUGHNUT  (moved here → canvas exists)  ===== */
                const donutCanvas = document.getElementById('dailyAttendanceChart');
                const donutCtx = donutCanvas ? donutCanvas.getContext('2d') : null;
                if (donutCtx) {
                    const labels = ['On-Time', 'Late', 'Absent'];
                    const values = [onTime, lateArrivals, absent];
                    const sum = values.reduce((a, b) => a + b, 0);
                    const placeholder = sum === 0;
                    new Chart(donutCtx, {
                        type: 'doughnut',
                        data: {
                            labels: placeholder ? ['No Data'] : labels,
                            datasets: [{
                                data: placeholder ? [1] : values,
                                backgroundColor: placeholder ? ['#e5e7eb'] : ['#0d6efd', '#ffc107', '#dc3545'],
                                borderWidth: 0
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            layout: { padding: 20 },
                            cutout: '80%',
                            plugins: {
                                legend: { display: false },
                                datalabels: {
                                    display: !placeholder,
                                    color: '#000', anchor: 'end', align: 'end', offset: 8, clip: false,
                                    formatter: (v, ctx) => {
                                        const s = ctx.chart.data.datasets[0].data.reduce((a, b) => a + b, 0);
                                        if (!s) return '';
                                        const pct = (v * 100 / s).toFixed(0) + '%';
                                        return ctx.chart.data.labels[ctx.dataIndex] + ' ' + pct;
                                    },
                                    font: { weight: 'bold', size: 12 }
                                }
                            }
                        },
                        plugins: typeof ChartDataLabels !== 'undefined' ? [ChartDataLabels] : []
                    });
                }

                /* =====  LINE CHART  ===== */
                const lineCtx = document.getElementById('attendanceTrendChart')?.getContext('2d');
                if (lineCtx) new Chart(lineCtx, {
                    type: 'line',
                    data: {
                        labels: <?php echo json_encode($days); ?>,
                        datasets: [
                            { label: 'Present', data: <?php echo json_encode($trendOnDuty); ?>, borderColor: '#0d6efd', backgroundColor: 'rgba(13,110,253,.1)', tension: 0.3, fill: false },
                            { label: 'Late Arrivals', data: <?php echo json_encode($trendLate); ?>, borderColor: '#ffc107', backgroundColor: 'rgba(255,193,7,.1)', tension: 0.3, fill: false },
                            { label: 'Absent', data: <?php echo json_encode($trendAbsent); ?>, borderColor: '#dc3545', backgroundColor: 'rgba(220,53,69,.1)', tension: 0.3, fill: false }
                        ]
                    },
                    options: {
                        responsive: true, maintainAspectRatio: false, layout: { padding: 20 },
                        plugins: {
                            legend: { position: 'bottom', labels: { boxWidth: 12, padding: 15, font: { size: 11 } } },
                            datalabels: { display: false }
                        },
                        scales: { x: { grid: { display: false } }, y: { ticks: { callback: v => v } } }
                    }
                });

                /* =====  STAT CARDS  ===== */
                document.querySelector(".stats .card:nth-child(1) p").textContent = onDuty;
                document.querySelector(".stats .card:nth-child(2) p").textContent = lateArrivals;
                document.querySelector(".stats .card:nth-child(3) p").textContent = absent;
                document.querySelector(".stats .card:nth-child(4) p").textContent = `<?php echo $attendanceRate; ?>%`;

                /* =====  TABLE  ===== */
                const tableBody = document.querySelector("table tbody");
                const records = <?php echo json_encode($attendanceRecords); ?>;
                if (tableBody && records) {
                    tableBody.innerHTML = records.map(r => `
            <tr>
                <td>${r.emp_id}</td>
                <td>${r.name}</td>
                <td>${r.position}</td>
                <td>${r.department}</td>
                <td>${r.time_in || ''}</td>
                <td>${r.time_out || ''}</td>
                <td>${r.hours_worked || ''}</td>
                <td>${r.overtime || ''}</td>
                <td class="table-actions">
                    <button class="btn btn-outline-primary"><i class="bi bi-check-circle"></i></button>
                    <button class="btn btn-outline-warning me-1"><i class="bi bi-pencil"></i></button>
                </td>
            </tr>`).join('');
                }
            });
        </script>

        <!-- logout ajax (unchanged) -->
        <script>
            $("#logout-btn").click(() => {
                $.ajax({
                    url: "../modules/logout.php", type: "POST", dataType: "json",
                    success: r => { if (r.status === "success") window.location.href = "login_page.php"; },
                    error: (x, s, e) => console.log(x, s, e)
                });
            });
        </script>
</body>

</html>
