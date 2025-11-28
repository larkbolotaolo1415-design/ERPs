<!-- Copy Paste This php block -->
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../init.php';
$pageTitle = "Payroll Computation"; // Change to correct title 
$userName = $_SESSION['user_name'] ?? 'User';
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payroll Officer | Payroll Computation</title>
    <link rel="stylesheet" href="../assets/css/dashboard_style.css">
    <link rel="stylesheet" href="../assets/css/payroll_officer_employee_management_style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.sheetjs.com/xlsx-latest/package/dist/xlsx.full.min.js"></script>
</head>

<body>
    <!-- Place includes at top -->
    <?php include __DIR__ . '/../includes/layout/sidebar.php'; ?>
    <?php include __DIR__ . '/../includes/layout/topbar.php'; ?>

    <div class="main-content p-4 mt-5">
        <div class="stats">
            <div class="card">
                <h3>Total Employees</h3>
                <p></p>
            </div>

            <div class="card">
                <h3>Total Gross Pay</h3>
                <p></p>
            </div>

            <div class="card">
                <h3>Total Deductions</h3>
                <p></p>
            </div>

            <div class="card">
                <h3 style="font-size: 24px;">Total Net Pay</h3>
                <p></p>
            </div>
        </div>

        <div class="d-flex gap-3" style="margin:10px;">
            <!-- Left card: datepicker + announcement -->
            <div class="card w-50 d-flex justify-content-between align-items-center p-3">
                <div class="d-flex align-items-center gap-2">
                    <label for="dateFilter" class="mb-0">Payroll Period:</label>
                    <select id="dateFilter" class="form-select"></select>
                </div>
                <div class="payroll-announcement" style="font-size:24px; color:#2563EB;" id="periodLabel"></div>
            </div>

            <!-- Right side: stacked buttons -->
            <div class="d-flex flex-column gap-2">
                <button id="lockBtn" class="btn btn-outline-primary">Lock Payroll</button>
                <button id="approveBtn" class="btn btn-primary" style="background-color:#93C5FD; color:#2D3436; border:none;">Approve Payroll</button>
                <button id="recomputeBtn" class="btn btn-primary">Compute</button>
                <button id="resetStatusBtn" class="btn btn-outline-secondary">Reset Status to Pending</button>
            </div>
        </div>


        <div class="tab-content" id="employeelistTabsContent"></div>
        <!-- Filters and Search -->
        <div class="tab-pane fade show active" id="employeelist" role="tabpanel" aria-labelledby="employee-list-tab">
            <div class="d-flex align-items-center mb-3 filter-row" style="gap: 10px;">

                <select id="nameFilter" class="form-select"
                    style="width: 150px; background-color: #93C5FD; color: #2563EB; border: none;">
                    <option value="all" selected>Status: All</option>
                    <option value="pending">Pending</option>
                    <option value="approved">Approved</option>
                    <option value="locked">Locked</option>
                </select>

                <!-- Department Dropdown -->
                <select id="departmentFilter" class="form-select"
                    style="width: 150px; background-color: #93C5FD; color: #2563EB; border: none;">
                    <option value="" disabled selected hidden>Department</option>
                </select>

                <select id="paygradeFilter" class="form-select"
                    style="width: 150px; background-color: #93C5FD; color: #2563EB; border: none;">
                    <option value="" disabled selected hidden>Salary Grade</option>
                </select>

                <button id="applyFilters" class="btn btn-primary me-auto">Apply</button>
                <button id="resetFilters" class="btn btn-outline-secondary">Reset Filters</button>

                <div class="d-flex align-items-center gap-2">
                    <input type="text" id="searchInput" class="form-control" placeholder="Search..."
                        style="width: 300px; border-color: #2563EB;">
                    <button id="searchButton" class="btn btn-primary fw-semibold"
                        style="background-color: #2563EB; border: none;">
                        Search
                    </button>
                    <button id="clearSearchButton" class="btn btn-outline-secondary fw-semibold">Clear</button>
                </div>
            </div>

            <!-- Employee Table -->
            <div class="table-responsive">
                <table class="table table-striped align-middle" id='payrolls_table'>
                    <thead>
                        <tr>
                            <th><input type="checkbox" id="selectAll" /></th>
                            <th>Emp ID</th>
                            <th>Name</th>
                            <th>Salary Grade</th>
                            <th>Basic Pay</th>
                            <th>Allowances</th>
                            <th>OT Pay</th>
                            <th>Gross Salary</th>
                            <th>Deductions</th>
                            <th>Net Pay</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody id='tbody_table'>
                        <tr>
                            <td></td>
                            <td>01</td>
                            <td>Charl Joven Castro</td>
                            <td>5</td>
                            <td>P32,000.0</td>
                            <td></td>
                            <td></td>
                            <td> </td>
                            <td></td>
                            <td></td>
                            <td><span class="badge status-badge bg-danger">Locked</span></td>
                        </tr>

                        <tr>
                            <td></td>
                            <td>02</td>
                            <td>Klarenz Cobie Manrique</td>
                            <td>10</td>
                            <td>P65,000.00</td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td><span class="badge status-badge bg-danger">Locked</span></td>
                        </tr>

                        <tr>
                            <td></td>
                            <td>03</td>
                            <td>Graci Al Dei Medrano</td>
                            <td>14</td>
                            <td>P176,000.00</td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td><span class="badge status-badge bg-danger">Locked</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Showing page text -->
            <div class="mb-2" style="color: #5B5757;" id="pageInfo"></div>

            <!-- Bottom Actions and Pagination -->
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div class="d-flex">
                    <button id="exportCsv" class="btn btn-export me-2">
                        <i class="bi bi-file-earmark-text me-1"></i> Export CSV
                    </button>
                    <button id="exportExcel" class="btn btn-export">
                        <i class="bi bi-file-earmark-spreadsheet me-1"></i> Export Excel
                    </button>
                    <button id="exportPdf" class="btn btn-export ms-2">
                        <i class="bi bi-filetype-pdf me-1"></i> Export PDF
                    </button>
                </div>

                <nav aria-label="Page navigation">
                    <ul class="pagination mb-0" id="pager"></ul>
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

        <!-- Reset Filters Script -->
        <script>
            document.addEventListener("DOMContentLoaded", () => {
                const applyBtn = document.querySelector(".btn-accent");
                const filters = document.querySelectorAll(".filter-section select");

                const dateElement = document.querySelector('.current-date');
                if (dateElement) {
                    const options = {
                        weekday: 'long',
                        year: 'numeric',
                        month: 'long',
                        day: 'numeric'
                    };
                    dateElement.textContent = new Date().toLocaleDateString(undefined, options);
                }
            });

            $("#logout-btn").click(() => {
                // REMOVE SESSION ID
                // GO TO LOGIN PAGE
                $.ajax({
                    url: "../modules/logout.php",
                    type: "POST",
                    dataType: "json",
                    success: function(response) {
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
        </script>
        <script>
            $(function() {
                const periodSel = $('#dateFilter');
                const ann = $('#periodLabel');
                const tbody = $('#tbody_table');
                const pager = $('#pager');
                const pageInfo = $('#pageInfo');
                const selectAll = $('#selectAll');
                const searchInput = $('#searchInput');
                const searchBtn = $('#searchButton');
                const clearSearchBtn = $('#clearSearchButton');
                const deptSel = $('#departmentFilter');
                const gradeSel = $('#paygradeFilter');
                const statusSel = $('#nameFilter');
                const cards = $('.stats .card p');
                const limit = 10;
                let allRows = [];
                let filteredRows = [];
                let page = 1;
                const selected = new Set();

                function fmt(n) {
                    return '₱' + parseInt(n || 0).toLocaleString();
                }

                function setCards(rows) {
                    let gross = 0,
                        ded = 0,
                        net = 0;
                    rows.forEach(row => {
                        gross += parseInt(row.gross_pay || 0);
                        ded += parseInt(row.total_deduction || 0);
                        net += parseInt(row.net_pay || 0);
                    });
                    if (cards[0]) $(cards[0]).text(rows.length);
                    if (cards[1]) $(cards[1]).text(fmt(gross));
                    if (cards[2]) $(cards[2]).text(fmt(ded));
                    if (cards[3]) $(cards[3]).text(fmt(net));
                }

                function renderPager() {
                    const totalPages = Math.max(1, Math.ceil(filteredRows.length / limit));
                    page = Math.min(page, totalPages);
                    pageInfo.text(`Showing Page ${page} of ${totalPages}`);
                    pager.empty();
                    const prev = $(`<li class="page-item ${page===1?'disabled':''}"><a class="page-link" href="#">Previous</a></li>`);
                    pager.append(prev);
                    for (let i = 1; i <= totalPages; i++) {
                        const li = $(`<li class="page-item ${i===page?'active':''}"><a class="page-link" href="#">${i}</a></li>`);
                        pager.append(li);
                    }
                    const next = $(`<li class="page-item ${page===totalPages?'disabled':''}"><a class="page-link" href="#">Next</a></li>`);
                    pager.append(next);
                }

                function renderTable() {
                    tbody.empty();
                    const start = (page - 1) * limit;
                    const slice = filteredRows.slice(start, start + limit);
                    slice.forEach(row => {
                        const checked = selected.has(row.payroll_id) ? 'checked' : '';
                        const tr = `<tr data-id="${row.payroll_id}" data-dept="${row.dept_name||''}" data-sg="${row.sg_grade||''}" data-status="${row.payroll_status||''}">
                            <td><input type="checkbox" class="row-check" ${checked} /></td>
                            <td>${row.emp_id}</td>
                            <td>${row.user_name||''}</td>
                            <td>${row.sg_grade||''}</td>
                            <td>${fmt(row.basic_pay)}</td>
                            <td>${fmt(row.allowances)}</td>
                            <td>${fmt(row.ot_pay)}</td>
                            <td>${fmt(row.gross_pay)}</td>
                            <td>${fmt(row.total_deduction)}</td>
                            <td>${fmt(row.net_pay)}</td>
                            <td><span class="badge status-badge ${row.payroll_status==='locked'?'bg-danger':row.payroll_status==='approved'?'bg-success':'bg-secondary'}">${row.payroll_status}</span></td>
                        </tr>`;
                        tbody.append(tr);
                    });
                    selectAll.prop('checked', slice.length > 0 && slice.every(r => selected.has(r.payroll_id)));
                }

                function applyFilters() {
                    const term = (searchInput.val() || '').toLowerCase();
                    const dept = deptSel.val() || '';
                    const grade = gradeSel.val() || '';
                    const status = statusSel.val() || 'all';
                    filteredRows = allRows.filter(r => {
                        const termMatch = term ? (
                            String(r.user_name || '').toLowerCase().includes(term) ||
                            String(r.emp_id || '').toLowerCase().includes(term) ||
                            String(r.dept_name || '').toLowerCase().includes(term) ||
                            String(r.sg_grade || '').toLowerCase().includes(term) ||
                            String(r.payroll_status || '').toLowerCase().includes(term)
                        ) : true;
                        const deptMatch = dept ? String(r.dept_id || '') === String(dept) || String(r.dept_name || '') === String(dept) : true;
                        const gradeMatch = grade ? String(r.sg_grade || '') === String(grade) : true;
                        const statusMatch = status === 'all' ? true : (r.payroll_status === status);
                        return termMatch && deptMatch && gradeMatch && statusMatch;
                    });
                    page = 1;
                    setCards(filteredRows);
                    renderPager();
                    renderTable();
                }

                function loadPeriods() {
                    $.getJSON('../modules/periods.php', {
                        action: 'list'
                    }, function(rp) {
                        const periods = rp.data || [];
                        periodSel.empty();
                        periods.forEach(p => periodSel.append(`<option value="${p.period_id}">${p.start_date} - ${p.end_date} (${p.status})</option>`));
                        const current = periods[0];
                        if (current) {
                            periodSel.val(current.period_id);
                            ann.text(`Payroll Period: ${current.start_date} to ${current.end_date}`);
                            loadPayroll(current.period_id, current);
                        }
                    });
                }

                function populateFilters() {
                    $.getJSON('../modules/company.php', {
                        action: 'list',
                        resource: 'departments'
                    }, function(rd) {
                        const depts = rd.data || [];
                        deptSel.empty().append('<option value="" selected>Department</option>');
                        depts.forEach(d => deptSel.append(`<option value="${d.dept_id}">${d.dept_name}</option>`));
                    });
                    $.getJSON('../modules/company.php', {
                        action: 'list',
                        resource: 'salary_grades'
                    }, function(rg) {
                        const grades = rg.data || [];
                        gradeSel.empty().append('<option value="" selected>Salary Grade</option>');
                        grades.forEach(g => gradeSel.append(`<option value="${g}">${g}</option>`));
                    });
                }

                function loadPayroll(pid, period) {
                    $.ajax({
                        url: '../modules/payroll.php',
                        method: 'POST',
                        data: {
                            action: 'list',
                            period_id: pid
                        },
                        dataType: 'json',
                        success: function(pr) {
                            allRows = pr.data || [];
                            selected.clear();
                            allRows.forEach(r => selected.add(r.payroll_id));
                            applyFilters();
                            if (period) ann.text(`Payroll Period: ${period.start_date} to ${period.end_date}`);
                        }
                    });
                }
                pager.on('click', function(e) {
                    const a = $(e.target).closest('a.page-link');
                    if (!a.length) return;
                    e.preventDefault();
                    const txt = a.text();
                    const totalPages = Math.max(1, Math.ceil(filteredRows.length / limit));
                    if (txt === 'Previous') {
                        page = Math.max(1, page - 1);
                    } else if (txt === 'Next') {
                        page = Math.min(totalPages, page + 1);
                    } else {
                        page = parseInt(txt, 10) || 1;
                    }
                    renderPager();
                    renderTable();
                });
                tbody.on('change', '.row-check', function() {
                    const id = $(this).closest('tr').data('id');
                    if (this.checked) selected.add(id);
                    else selected.delete(id);
                    const start = (page - 1) * limit;
                    const slice = filteredRows.slice(start, start + limit);
                    selectAll.prop('checked', slice.length > 0 && slice.every(r => selected.has(r.payroll_id)));
                });
                selectAll.on('change', function() {
                    const start = (page - 1) * limit;
                    const slice = filteredRows.slice(start, start + limit);
                    if (this.checked) {
                        slice.forEach(r => selected.add(r.payroll_id));
                    } else {
                        slice.forEach(r => selected.delete(r.payroll_id));
                    }
                    renderTable();
                });
                searchInput.on('keydown', function(e) {
                    if (e.key === 'Enter') {
                        applyFilters();
                    }
                });
                searchBtn.on('click', function() {
                    applyFilters();
                });
                clearSearchBtn.on('click', function() {
                    searchInput.val('');
                    applyFilters();
                });
                $('#resetFilters').on('click', function() {
                    deptSel.val('');
                    gradeSel.val('');
                    statusSel.val('all');
                    searchInput.val('');
                    applyFilters();
                });
                $('#applyFilters').on('click', function() {
                    applyFilters();
                });
                periodSel.on('change', function() {
                    const pid = $(this).val();
                    const txt = $(this).find('option:selected').text();
                    const m = txt.match(/(\d{4}-\d{2}-\d{2})\s-\s(\d{4}-\d{2}-\d{2})/);
                    const period = m ? {
                        start_date: m[1],
                        end_date: m[2]
                    } : null;
                    loadPayroll(pid, period);
                });

                function withSelected(fn) {
                    if (selected.size === 0) {
                        alert('No rows selected');
                        return;
                    }
                    fn(Array.from(selected));
                }
                $('#approveBtn').on('click', function() {
                    if ((allRows || []).length === 0) {
                        alert('Compute or Generate some payrolls first using the compute button');
                        return;
                    }
                    withSelected(function(ids) {
                        const reqs = ids.map(id => $.ajax({
                            url: '../modules/payroll.php',
                            method: 'POST',
                            data: {
                                action: 'setStatus',
                                payroll_id: id,
                                status: 'approved'
                            },
                            dataType: 'json'
                        }));
                        $.when.apply($, reqs).done(function() {
                            loadPayroll(periodSel.val());
                        }).fail(function() {
                            alert('Approve failed');
                        });
                    });
                });
                $('#lockBtn').on('click', function() {
                    if ((allRows || []).length === 0) {
                        alert('Compute or Generate some payrolls first using the compute button');
                        return;
                    }
                    withSelected(function(ids) {
                        const reqs = ids.map(id => $.ajax({
                            url: '../modules/payroll.php',
                            method: 'POST',
                            data: {
                                action: 'setStatus',
                                payroll_id: id,
                                status: 'locked'
                            },
                            dataType: 'json'
                        }));
                        $.when.apply($, reqs).done(function() {
                            loadPayroll(periodSel.val());
                        }).fail(function() {
                            alert('Lock failed');
                        });
                    });
                });
                $('#recomputeBtn').on('click', function() {
                    const pid = periodSel.val();
                    if ((allRows || []).length === 0) {
                        $.ajax({
                                url: '../modules/payroll.php',
                                method: 'POST',
                                data: {
                                    action: 'recompute',
                                    period_id: pid
                                },
                                dataType: 'json'
                            })
                            .done(function() {
                                loadPayroll(pid);
                            })
                            .fail(function() {
                                alert('Compute failed');
                            });
                        return;
                    }
                    withSelected(function(ids) {
                        $.ajax({
                                url: '../modules/payroll.php',
                                method: 'POST',
                                data: {
                                    action: 'recomputeSelected',
                                    period_id: pid,
                                    payroll_ids: JSON.stringify(ids)
                                },
                                dataType: 'json'
                            })
                            .done(function() {
                                loadPayroll(pid);
                            }).fail(function() {
                                alert('Recompute failed');
                            });
                    });
                });
                $('#resetStatusBtn').on('click', function() {
                    if ((allRows || []).length === 0) {
                        alert('Compute or Generate some payrolls first using the compute button');
                        return;
                    }
                    withSelected(function(ids) {
                        const reqs = ids.map(id => $.ajax({
                            url: '../modules/payroll.php',
                            method: 'POST',
                            data: {
                                action: 'setStatus',
                                payroll_id: id,
                                status: 'pending'
                            },
                            dataType: 'json'
                        }));
                        $.when.apply($, reqs).done(function() {
                            loadPayroll(periodSel.val());
                        }).fail(function() {
                            alert('Reset failed');
                        });
                    });
                });

                function exportSelection() {
                    const headers = [];
                    $('#payrolls_table thead th').each(function() {
                        headers.push($(this).text().trim());
                    });
                    const data = [];
                    $('#tbody_table tr').each(function() {
                        const id = $(this).data('id');
                        if (!selected.has(id)) return;
                        const row = [];
                        $(this).find('td').each(function(idx) {
                            if (idx === 0) return;
                            row.push($(this).text().trim());
                        });
                        data.push(row);
                    });
                    return {
                        headers: headers.slice(1),
                        data
                    };
                }
                $('#exportCsv').on('click', function() {
                    const {
                        headers,
                        data
                    } = exportSelection();
                    const csv = [headers.join(','), ...data.map(r => r.map(v => `"${v.replace(/"/g,'""')}"`).join(','))].join('\n');
                    const blob = new Blob([csv], {
                        type: 'text/csv'
                    });
                    const a = document.createElement('a');
                    a.href = URL.createObjectURL(blob);
                    a.download = 'payroll.csv';
                    a.click();
                });
                $('#exportExcel').on('click', function() {
                    const {
                        headers,
                        data
                    } = exportSelection();
                    const ws = XLSX.utils.aoa_to_sheet([headers, ...data]);
                    const wb = XLSX.utils.book_new();
                    XLSX.utils.book_append_sheet(wb, ws, 'Payroll');
                    XLSX.writeFile(wb, 'payroll.xlsx');
                });
                $('#exportPdf').on('click', function() {
                    const {
                        headers,
                        data
                    } = exportSelection();
                    const payload = {
                        template: 'payroll_table',
                        filename: 'payrolls.pdf',
                        title: $('#periodLabel').text(),
                        headers,
                        rows: data
                    };
                    $.ajax({
                        url: '../modules/pdf.php',
                        method: 'POST',
                        data: JSON.stringify(payload),
                        processData: false,
                        contentType: 'application/json',
                        xhrFields: {
                            responseType: 'blob'
                        }
                    }).done(function(blob) {
                        const url = window.URL.createObjectURL(blob);
                        const a = document.createElement('a');
                        a.href = url;
                        a.download = payload.filename;
                        document.body.appendChild(a);
                        a.click();
                        a.remove();
                        window.URL.revokeObjectURL(url);
                    });
                });
                populateFilters();
                loadPeriods();
            });
        </script>
</body>

</html>