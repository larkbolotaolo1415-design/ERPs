<!-- Copy Paste This php block -->
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../init.php';
$pageTitle = "Overtime, Leave & Bonuses"; // Change to correct title 
$userName = $_SESSION['user_name'] ?? 'User';
?>     


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payroll Officer | Overtime, Leave & Bonuses</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/dashboard_style.css">
    <link rel="stylesheet" href="../assets/css/payroll_officer_employee_management_style.css">
    <link rel="stylesheet" href="../assets/css/payroll_officer_ot_leave_bonus_style.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</head>

<body>
    <!-- Place includes at top -->
    <?php include __DIR__ . '/../includes/layout/sidebar.php'; ?>
    <?php include __DIR__ . '/../includes/layout/topbar.php'; ?>

        <div class="main-content p-4 mt-5">
      <div class="row g-3 mb-3">
            <div class="col-md-4">
                <div class="card p-3">
                    <h6 class="mb-2">Overtime</h6>
                    <p class="mb-1">Pending: <span id="otPendingCount">0</span></p>
                    <p class="mb-1">Approved: <span id="otApprovedCount">0</span></p>
                    <p class="mb-0">Rejected: <span id="otRejectedCount">0</span></p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card p-3">
                    <h6 class="mb-2">Leave</h6>
                    <p class="mb-1">Pending: <span id="lvPendingCount">0</span></p>
                    <p class="mb-1">Approved: <span id="lvApprovedCount">0</span></p>
                    <p class="mb-0">Rejected: <span id="lvRejectedCount">0</span></p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card p-3">
                    <h6 class="mb-2">Bonuses</h6>
                    <p class="mb-1">Approved: <span id="bnApprovedCount">0</span></p>
                    <p class="mb-1">Scheduled: <span id="bnScheduledCount">0</span></p>
                    <p class="mb-0">Released: <span id="bnReleasedCount">0</span></p>
                </div>
            </div>
        </div>

        <div class="tab-content" id="employeelistTabsContent"></div>
        <div class="mb-3 filter-row" style="gap: 10px;">
            <!-- Category Filter -->
            <select id="categoryFilter" class="form-select"
                style="width: 180px; background-color: #93C5FD; color: #2563EB; border: none;">
                <option value="all" disabled selected hidden>Category</option>
                <option value="ot">Overtime</option>
                <option value="leave">Leave</option>
                <option value="bonus">Bonus</option>
            </select>

            <!-- Department Filter -->
            <select id="departmentFilter" class="form-select"
                style="width: 180px; background-color: #93C5FD; color: #2563EB; border: none;">
                <option value="all" disabled selected hidden>Department</option>
                <option value="accountant">Accountant</option>
                <option value="head-nurse">Head Nurse</option>
                <option value="doctor">Doctor</option>
                <option value="staff">Staff</option>
            </select>

            <!-- Status Filter -->
            <select id="statusFilter" class="form-select"
                style="width: 180px; background-color: #93C5FD; color: #2563EB; border: none;">
                <option value="all" disabled selected hidden>Status</option>
                <option value="pending">Pending</option>
                <option value="approved">Approved</option>
                <option value="scheduled">Scheduled</option>
                <option value="released">Released</option>
                <option value="rejected">Rejected</option>
            </select>

            <!-- OT/Leave Type Filter -->
            <select id="otLeaveTypeFilter" class="form-select"
                style="width: 180px; background-color: #93C5FD; color: #2563EB; border: none;">
                <option value="all" disabled selected hidden>OT/Leave Type</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>

            <button class="btn btn-primary me-auto" id="applyFilters">Apply</button>

            <!-- Search Input -->
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
                <thead id="requests_thead">
                    <tr>
                        <th>Req ID</th>
                        <th>Emp ID</th>
                        <th>Name</th>
                        <th>Department</th>
                        <th>Position</th>
                        <th>Date</th>
                        <th>Hours</th>
                        <th>OT Type</th>
                        <th>Computed Amount</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="requests_tbody"></tbody>
            </table>
        </div>

        <!-- Showing page text -->
        <div class="mb-2" style="color: #5B5757;" id="reqShowing"></div>

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
                <ul class="pagination mb-0" id="reqPager"></ul>
            </nav>
        </div>

        <script>
            $(function(){
                const searchInput = $('#searchInput');
                const limit = 10; let page = 1; let all = { ot:[], leave:[], bonus:[] };
                let empMap = {};
                let ready = { emp:false, ot:false, leave:false, bonus:false };
                function buildEmpMap(rows){
                    rows.forEach(r => { empMap[r.emp_id] = { user_name: r.user_name || '', dept_name: r.dept_name || '', pos_name: r.pos_name || '' }; });
                }
                function enrich(r){ const m = empMap[r.emp_id] || {}; return { ...r, user_name: m.user_name||'', dept_name: m.dept_name||'', pos_name: m.pos_name||'' }; }
                function maybeInitialRender(){ if (ready.emp && ready.ot) { page=1; render('ot'); } }
                function match(k, t){ return (k||'').toLowerCase().includes((t||'').toLowerCase()); }
                function renderPager(total){
                    const pager = $('#reqPager');
                    const pages = Math.max(1, Math.ceil(total/limit));
                    const items = [];
                    items.push(`<li class="page-item ${page===1?'disabled':''}"><a class="page-link" href="#" data-page="prev">Previous</a></li>`);
                    const startIdx = Math.max(1, page - 2);
                    const endIdx = Math.min(pages, startIdx + 4);
                    for (let i = startIdx; i <= endIdx; i++) items.push(`<li class="page-item ${i===page?'active':''}"><a class="page-link" href="#" data-page="${i}">${i}</a></li>`);
                    items.push(`<li class="page-item ${page===pages?'disabled':''}"><a class="page-link" href="#" data-page="next">Next</a></li>`);
                    pager.html(items.join(''));
                }
                function activeType(){ const v = $('#categoryFilter').val(); return v==='leave' ? 'leave' : v==='bonus' ? 'bonus' : 'ot'; }
                function render(type){
                    const term = (searchInput.val()||'').trim();
                    let rows = (all[type]||[]).map(enrich);
                    if (term) {
                        const terms = term.toLowerCase().split(/\s+/).filter(Boolean);
                        rows = rows.filter(r => {
                            if (type==='ot') {
                                const hay = [r.user_name,r.dept_name,r.pos_name,r.emp_id,r.status,r.date_from,r.date_to,String(r.hours),String(r.computed_amount)].map(x=> (x||'').toLowerCase()).join(' ');
                                return terms.every(t => hay.includes(t));
                            }
                            if (type==='leave') {
                                const hay = [r.user_name,r.dept_name,r.pos_name,r.emp_id,r.status,r.leave_type,r.date_from,r.date_to,r.pay_types].map(x=> (x||'').toLowerCase()).join(' ');
                                return terms.every(t => hay.includes(t));
                            }
                            const hay = [r.user_name,r.dept_name,r.pos_name,r.emp_id,r.status,r.description,r.type,r.date_from,r.date_to,String(r.amount)].map(x=> (x||'').toLowerCase()).join(' ');
                            return terms.every(t => hay.includes(t));
                        });
                    }
                    const start = (page-1)*limit; const slice = rows.slice(start, start+limit);
                    renderPager(rows.length);
                    const pages = Math.max(1, Math.ceil(rows.length/limit));
                    $('#reqShowing').text(`Showing Page ${page} of ${pages}`);
                    if (type==='ot') {
                        const tbody = $('#tbody-ot').empty();
                        slice.forEach(r=> tbody.append(`<tr><td>${r.overtime_id||r.req_id||''}</td><td>${r.emp_id}</td><td>${r.user_name||''}</td><td>${r.dept_name||''}</td><td>${r.pos_name||''}</td><td>${r.date_from||''} - ${r.date_to||''}</td><td>${r.hours||''}</td><td>₱${parseInt(r.computed_amount||0).toLocaleString()}</td><td><span class="badge ${r.status==='approved'?'bg-success':r.status==='rejected'?'bg-danger':'bg-warning'}">${r.status||''}</span></td></tr>`));
                    } else if (type==='leave') {
                        const tbody = $('#tbody-leave').empty();
                        slice.forEach(r=> tbody.append(`<tr><td>${r.leave_id||r.req_id||''}</td><td>${r.emp_id}</td><td>${r.user_name||''}</td><td>${r.dept_name||''}</td><td>${r.pos_name||''}</td><td>${r.date_from||''}</td><td>${r.date_to||''}</td><td>${r.leave_type||''}</td><td><span class="badge ${r.status==='approved'?'bg-success':r.status==='rejected'?'bg-danger':'bg-warning'}">${r.status||''}</span></td></tr>`));
                    } else {
                        const tbody = $('#tbody-bonus').empty();
                        slice.forEach(r=> tbody.append(`<tr><td>${r.ba_id||r.req_id||''}</td><td>${r.emp_id}</td><td>${r.user_name||''}</td><td>${r.dept_name||''}</td><td>${r.pos_name||''}</td><td>${r.description||''}</td><td>₱${parseInt(r.amount||0).toLocaleString()}</td><td><span class="badge ${r.status==='approved'?'bg-success':r.status==='released'?'bg-primary':'bg-warning'}">${r.status||''}</span></td></tr>`));
                    }
                }
                function loadCounts(){
                    const c = (arr, st) => arr.filter(x=>x.status===st).length;
                    $('#otPendingCount').text(c(all.ot,'pending'));
                    $('#otApprovedCount').text(c(all.ot,'approved'));
                    $('#otRejectedCount').text(c(all.ot,'rejected'));
                    $('#lvPendingCount').text(c(all.leave,'pending'));
                    $('#lvApprovedCount').text(c(all.leave,'approved'));
                    $('#lvRejectedCount').text(c(all.leave,'rejected'));
                    $('#bnApprovedCount').text(c(all.bonus,'approved'));
                    $('#bnScheduledCount').text(c(all.bonus,'scheduled'));
                    $('#bnReleasedCount').text(c(all.bonus,'released'));
                }
                $(document).on('click', '#reqPager a.page-link', function(e){
                    e.preventDefault();
                    const val = $(this).data('page');
                    const type = activeType();
                    const pages = Math.max(1, Math.ceil(all[type].length/limit));
                    if (val==='prev') page = Math.max(1, page-1); else if (val==='next') page = Math.min(pages, page+1); else page = parseInt(val,10);
                    render(type);
                });
                $('#searchButton').on('click', function(){page = 1;const type = activeType();render(type);});
                $('#searchInput').on('keydown', function(e){if(e.key === 'Enter'){e.preventDefault();$('#searchButton').click();}});
                $('#searchInput').on('keydown', function(e){ if (e.key==='Enter') { e.preventDefault(); $('#searchButton').click(); } });
                $.getJSON('../modules/employees.php', { action: 'list' }, function(er){ buildEmpMap((er.data||[])); ready.emp = true; maybeInitialRender(); });
                $.getJSON('../modules/requests.php', { action: 'list', type: 'overtime' }, function(resp){ all.ot = resp.data||[]; ready.ot = true; loadCounts(); maybeInitialRender(); });
                $.getJSON('../modules/requests.php', { action: 'list', type: 'leave' }, function(resp){ all.leave = resp.data||[]; ready.leave = true; loadCounts(); });
                $.getJSON('../modules/requests.php', { action: 'list', type: 'bonus' }, function(resp){ all.bonus = resp.data||[]; ready.bonus = true; loadCounts(); });
                $('#categoryFilter').on('change', function(){ const v=$(this).val(); page=1; if(v==='leave') render('leave'); else if(v==='bonus') render('bonus'); else render('ot'); });
            });
        </script>

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
        <script>
            $(function(){
                function setCounts(idMap, rows){
                    const c = function(s){ return rows.filter(function(r){ return String(r.status||'') === s; }).length; };
                    if(idMap.pending) $(idMap.pending).text(c('pending'));
                    if(idMap.approved) $(idMap.approved).text(c('approved'));
                    if(idMap.rejected) $(idMap.rejected).text(c('rejected'));
                }
                $.when(
                    $.getJSON('../modules/requests.php', { action: 'list', type: 'overtime' }),
                    $.getJSON('../modules/requests.php', { action: 'list', type: 'leave' }),
                    $.getJSON('../modules/requests.php', { action: 'list', type: 'bonus' })
                ).done(function(otRes, lvRes, bnRes){
                    const otRows = otRes[0].data||[];
                    const lvRows = lvRes[0].data||[];
                    const bnRows = bnRes[0].data||[];
                    setCounts({ pending:'#otPendingCount', approved:'#otApprovedCount', rejected:'#otRejectedCount' }, otRows);
                    setCounts({ pending:'#lvPendingCount', approved:'#lvApprovedCount', rejected:'#lvRejectedCount' }, lvRows);
                    var baApproved = bnRows.filter(function(r){ return String(r.status||'')==='approved'; }).length;
                    $('#bnApprovedCount').text(baApproved);
                    $('#bnScheduledCount').text(0);
                    $('#bnReleasedCount').text(0);
                }).fail(function(){
                    $('#otPendingCount,#otApprovedCount,#otRejectedCount,#lvPendingCount,#lvApprovedCount,#lvRejectedCount,#bnApprovedCount,#bnScheduledCount,#bnReleasedCount').text('-');
                });

                const thead = $('#requests_thead');
                const tbody = $('#requests_tbody');
                let empMap = null;

                function badge(status){
                    return status==='approved' ? 'bg-success' : status==='rejected' ? 'bg-danger' : 'bg-secondary';
                }

                function setHeader(type){
                    if(type==='overtime'){
                        thead.html('<tr>\
                            <th>Req ID</th><th>Emp ID</th><th>Name</th><th>Department</th><th>Position</th>\
                            <th>Date</th><th>Hours</th><th>Rate</th><th>Computed Amount</th><th>Status</th><th>Actions</th>\
                        </tr>');
                    } else if(type==='leave'){
                        thead.html('<tr>\
                            <th>Req ID</th><th>Emp ID</th><th>Name</th><th>Department</th><th>Position</th>\
                            <th>Date Range</th><th>Days</th><th>Leave Type</th><th>Pay Type</th><th>Status</th><th>Actions</th>\
                        </tr>');
                    } else {
                        thead.html('<tr>\
                            <th>Req ID</th><th>Emp ID</th><th>Name</th><th>Department</th><th>Position</th>\
                            <th>Date Range</th><th></th><th>Type</th><th>Amount</th><th>Status</th><th>Actions</th>\
                        </tr>');
                    }
                }

                function ensureEmpMap(){
                    return new Promise(function(resolve){
                        if (empMap) { resolve(empMap); return; }
                        $.getJSON('../modules/employees.php', { action: 'list' }, function(r){
                            const rows = r.data||[];
                            empMap = {};
                            rows.forEach(function(e){ empMap[e.emp_id] = e; });
                            resolve(empMap);
                        }).fail(function(){ empMap = {}; resolve(empMap); });
                    });
                }

                function loadRequests(type){
                    setHeader(type);
                    tbody.empty();
                    $.getJSON('../modules/requests.php', { action: 'list', type: type }, function(r){
                        const rows = r.data||[];
                        ensureEmpMap().then(function(map){
                            rows.forEach(function(row){
                                const e = map[row.emp_id]||{};
                                const name = e.user_name||'';
                                const dept = e.dept_name||'';
                                const pos = e.pos_name||'';
                                let tr = '';
                                if(type==='overtime'){
                                    tr = '<tr>'+
                                        '<td>'+ (row.overtime_id||'') +'</td>'+
                                        '<td>'+ (row.emp_id||'') +'</td>'+
                                        '<td>'+ name +'</td>'+
                                        '<td>'+ dept +'</td>'+
                                        '<td>'+ pos +'</td>'+
                                        '<td>'+ (row.date||'') +'</td>'+
                                        '<td>-</td>'+
                                        '<td>'+ (row.rate||'') +'</td>'+
                                        '<td>-</td>'+
                                        '<td><span class="badge '+badge(row.status||'')+'">'+ (row.status||'') +'</span></td>'+
                                        '<td class="table-actions"><button class="btn btn-outline-primary me-1"><i class="bi bi-eye"></i></button></td>'+
                                    '</tr>';
                                } else if(type==='leave'){
                                    tr = '<tr>'+
                                        '<td>'+ (row.leave_id||'') +'</td>'+
                                        '<td>'+ (row.emp_id||'') +'</td>'+
                                        '<td>'+ name +'</td>'+
                                        '<td>'+ dept +'</td>'+
                                        '<td>'+ pos +'</td>'+
                                        '<td>'+ (row.date_from||'') +' - '+ (row.date_to||'') +'</td>'+
                                        '<td>'+ (row.days||'') +'</td>'+
                                        '<td>'+ (row.leave_type||'') +'</td>'+
                                        '<td>'+ (row.pay_types||'') +'</td>'+
                                        '<td><span class="badge '+badge(row.status||'')+'">'+ (row.status||'') +'</span></td>'+
                                        '<td class="table-actions"><button class="btn btn-outline-primary me-1"><i class="bi bi-eye"></i></button></td>'+
                                    '</tr>';
                                } else {
                                    tr = '<tr>'+
                                        '<td>'+ (row.ba_id||'') +'</td>'+
                                        '<td>'+ (row.emp_id||'') +'</td>'+
                                        '<td>'+ name +'</td>'+
                                        '<td>'+ dept +'</td>'+
                                        '<td>'+ pos +'</td>'+
                                        '<td>'+ (row.date_from||'') +' - '+ (row.date_to||'') +'</td>'+
                                        '<td></td>'+
                                        '<td>'+ (row.type||'') +'</td>'+
                                        '<td>'+ (row.amount||'') +'</td>'+
                                        '<td><span class="badge '+badge(row.status||'')+'">'+ (row.status||'') +'</span></td>'+
                                        '<td class="table-actions"><button class="btn btn-outline-primary me-1"><i class="bi bi-eye"></i></button></td>'+
                                    '</tr>';
                                }
                                tbody.append(tr);
                            });
                        });
                    });
                }

                loadRequests('overtime');
                $('#nameFilter').on('change', function(){
                    const val = $(this).val();
                    const type = /Overtime/i.test(val) ? 'overtime' : /Leave/i.test(val) ? 'leave' : 'bonus';
                    loadRequests(type);
                });
            });
        </script>
</body>

</html>
