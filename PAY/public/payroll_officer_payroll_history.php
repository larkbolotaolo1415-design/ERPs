 <!-- Copy Paste This php block -->
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../init.php';
$pageTitle = "Payroll History Archive"; // Change to correct title 
$userName = $_SESSION['user_name'] ?? 'User';
?>     


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payroll Officer | Payroll History Archive</title>
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
        <div class="d-flex justify-content-center" style="margin:10px;">
            <!-- Left card: datepicker + announcement -->
            <div class="card w-50 p-3">
                <div class="d-flex align-items-center justify-content-center payroll-announcement"
                    style="font-size:24px; color:#2563EB; gap:8px;">
                    <strong>Payroll Period Range:</strong>
                    <input type="date" id="rangeStart" class="form-control"
                        style="width: 160px; background-color: #93C5FD; color:#2563EB; border:none; height:38px;">
                    <span>to</span>
                    <input type="date" id="rangeEnd" class="form-control"
                        style="width: 160px; background-color: #93C5FD; color:#2563EB; border:none; height:38px;">
                    <button id="applyRange" class="btn btn-primary" style="height:38px;">Apply</button>
                </div>
            </div>
        </div>



        <div class="tab-pane fade show active mb-4" id="employeelist" role="tabpanel"
            aria-labelledby="employee-list-tab">
            <div class="d-flex align-items-center justify-content-between mb-3 filter-row" style="gap: 10px;">
                <div class="payroll-announcement" style="font-size:24px; color:#2563EB;">
                    <strong>Payroll Archive Table</strong>
                </div>

                <div class="d-flex align-items-center gap-2">
                    <input type="text" id="historySearchInput" class="form-control" placeholder="Search..."
                        style="width: 300px; border-color: #2563EB;">
                    <button id="historySearchButton" class="btn btn-primary fw-semibold"
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
                            <th>Period ID</th>
                            <th>Date Processed</th>
                            <th>Status</th>
                            <th>Total Net Pay</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="historyBody"></tbody>
                </table>
            </div>

            <!-- Bottom Actions and Pagination -->
            <div class="d-flex justify-content-between align-items-center mt-1">
                <div class="mb-2" style="color: #5B5757;">
                    Showing Page 1 of 3
                </div>

                <nav aria-label="Page navigation">
                    <ul class="pagination mb-0" id="histPager"></ul>
                </nav>
            </div>
        </div>

        <div class="tab-pane fade show active" id="employeelist" role="tabpanel" aria-labelledby="employee-list-tab">
            <div class="d-flex align-items-center justify-content-between mb-3 filter-row" style="gap: 10px;">
                <div class="payroll-announcement" style="font-size:24px; color:#2563EB;">
                    <strong>Logs Table</strong>
                </div>

                <div class="d-flex align-items-center gap-2">
                    <input type="text" id="logsSearchInput" class="form-control" placeholder="Search..."
                        style="width: 300px; border-color: #2563EB;">
                    <button id="logsSearchButton" class="btn btn-primary fw-semibold"
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
                            <th>Audit ID</th>
                            <th>Timestamp</th>
                            <th>User</th>
                            <th>Employee</th>
                            <th>Actions</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>01</td>
                            <td>5</td>
                            <td>PayrollOfficer1</td>
                            <td>Klarenz Cobie Manrique</td>
                            <td></td>
                            <td></td>
                        </tr>

                        <tr>
                            <td>02</td>
                            <td>10</td>
                            <td>Admin2</td>
                            <td>Graci Al Dei Medrano</td>
                            <td></td>
                            <td></td>
                        </tr>

                        <tr>
                            <td>03</td>
                            <td>14</td>
                            <td>PayrollOfficer3</td>
                            <td>Charl Joven Castro</td>
                            <td></td>
                            <td></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Bottom Actions and Pagination -->
            <div class="d-flex justify-content-between align-items-center mt-1">
                <div class="mb-2" style="color: #5B5757;">
                    Showing Page 1 of 3
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

        <!-- Reset Filters Script -->
        <script>
            document.addEventListener("DOMContentLoaded", () => {
                const applyBtn = document.querySelector(".btn-accent");
                const filters = document.querySelectorAll(".filter-section select");

                const dateElement = document.querySelector('.current-date');
                const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
                dateElement.textContent = new Date().toLocaleDateString(undefined, options);

                applyBtn.addEventListener("click", () => {
                    tabFilters[tabName].status = statusSelect.value;
                    tabFilters[tabName].timeframe = timeframeSelect.value;
                    alert(`${tabName.toUpperCase()} filters applied:\nStatus: ${statusSelect.value}\nTimeframe: ${timeframeSelect.value}`);
                });

                searchBtn.addEventListener("click", () => {
                    tabFilters[tabName].search = searchInput.value;
                    alert(`${tabName.toUpperCase()} search:\nKeyword: ${searchInput.value}`);

                    // Clear search input
                    const searchInput = document.querySelector(".filter-section input");
                    if (searchInput) searchInput.value = '';
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
        </script>
</body>

</html>
        <script>
            $(function(){
                const startEl = $('#rangeStart'); const endEl = $('#rangeEnd'); const tbody = $('#historyBody'); const pager = $('#histPager');
                const hSearch = $('#historySearchInput');
                const limit = 10; let page = 1; let periods = []; let currentTotal = 0;
                function render(){
                    tbody.empty();
                    const sVal = startEl.val(); const eVal = endEl.val(); const term = (hSearch.val()||'').trim().toLowerCase();
                    const matches = p => {
                        if (!term) return true;
                        return String(p.period_id).toLowerCase().includes(term) ||
                               (p.status||'').toLowerCase().includes(term) ||
                               (p.start_date||'').toLowerCase().includes(term) ||
                               (p.end_date||'').toLowerCase().includes(term);
                    };
                    const filtered = periods.filter(p => {
                        const inRange = (!sVal || !eVal) ? true : (p.start_date >= sVal && p.end_date <= eVal);
                        return inRange && matches(p);
                    });
                    currentTotal = filtered.length;
                    const start = (page-1)*limit; const slice = filtered.slice(start, start+limit);
                    const pages = Math.max(1, Math.ceil(filtered.length / limit));
                    if (slice.length === 0) {
                        const items = [];
                        items.push(`<li class="page-item ${page===1?'disabled':''}"><a class="page-link" href="#" data-page="prev">Previous</a></li>`);
                        const startIdx = Math.max(1, page - 2);
                        const endIdx = Math.min(pages, startIdx + 4);
                        for (let i = startIdx; i <= endIdx; i++) items.push(`<li class="page-item ${i===page?'active':''}"><a class="page-link" href="#" data-page="${i}">${i}</a></li>`);
                        items.push(`<li class="page-item ${page===pages?'disabled':''}"><a class="page-link" href="#" data-page="next">Next</a></li>`);
                        pager.html(items.join(''));
                        return;
                    }
                    let done = 0;
                    slice.forEach(p => {
                        $.getJSON('../modules/payroll.php', { action:'list', period_id: p.period_id }, function(pr){
                            const rows = pr.data||[]; const totalNet = rows.reduce((sum,r)=> sum + parseInt(r.net_pay||0), 0);
                            const tr = $(`<tr>
                                <td>${p.period_id}</td>
                                <td>${p.end_date}</td>
                                <td><span class="badge ${p.status==='locked'?'bg-danger':p.status==='approved'?'bg-success':'bg-secondary'}">${p.status}</span></td>
                                <td>₱${totalNet.toLocaleString()}</td>
                                <td></td>
                            </tr>`);
                            tbody.append(tr);
                            done++;
                            if (done === slice.length) {
                                const items = [];
                                items.push(`<li class="page-item ${page===1?'disabled':''}"><a class="page-link" href="#" data-page="prev">Previous</a></li>`);
                                const startIdx = Math.max(1, page - 2);
                                const endIdx = Math.min(pages, startIdx + 4);
                                for (let i = startIdx; i <= endIdx; i++) items.push(`<li class="page-item ${i===page?'active':''}"><a class="page-link" href="#" data-page="${i}">${i}</a></li>`);
                                items.push(`<li class="page-item ${page===pages?'disabled':''}"><a class="page-link" href="#" data-page="next">Next</a></li>`);
                                pager.html(items.join(''));
                            }
                        });
                    });
                }
                $(document).on('click', '#histPager a.page-link', function(e){ e.preventDefault(); const val=$(this).data('page'); const pages = Math.max(1, Math.ceil(currentTotal/limit)); if (val==='prev') page=Math.max(1,page-1); else if (val==='next') page=Math.min(pages,page+1); else page=parseInt(val,10); render(); });
                $('#applyRange').on('click', function(){ page=1; render(); });
                $('#historySearchButton').on('click', function(){ page=1; render(); });
                $('#historySearchInput').on('keydown', function(e){ if (e.key==='Enter') { e.preventDefault(); $('#historySearchButton').click(); } });
                $.getJSON('../modules/periods.php', { action:'list' }, function(rp){ periods = rp.data||[]; render(); });
            });
        </script>
