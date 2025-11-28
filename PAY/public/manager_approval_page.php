<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../init.php';
$pageTitle = "Approval Page"; 
$userName = $_SESSION['user_name'] ?? 'User';
?>     

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Approval Page</title>
    <link rel="stylesheet" href="../assets/css/dashboard_style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.sheetjs.com/xlsx-latest/package/dist/xlsx.full.min.js"></script>

    <style>
        :root {
            --accent: #2563EB;
            --accent-light: #93C5FD;
            --text-dark: #2D3436;
            --text-muted: #5B5757;
            --white: #ffffff;
        }

        .btn-accent {
            background-color: var(--accent);
            color: var(--white);
        }

        .btn-accent:hover {
            background-color: #1d4dbf;
        }

        .filter-section select,
        .filter-section input {
            min-width: 150px;
            border-radius: 8px;
        }

        .filter-section select {
            background-color: var(--accent-light);
            color: var(--accent);
            border: none;
        }

        .filter-section input {
            border: 1px solid #1E3A8A;
            color: #1E3A8A;
            text-align: right;
        }

        .filter-section button {
            min-width: 80px;
        }

        .status-badge {
            font-size: 0.85rem;
        }

        .table {
            border-collapse: separate;
            border-spacing: 0;
            width: 100%;
        }

        .table thead th {
            background-color: #f8f9fa; 
            font-weight: 600;
            vertical-align: middle;
            text-align: left;
            border-bottom: 2px solid #dee2e6;
            padding: 12px 16px;
        }

        .table tbody td {
            padding: 10px 16px;
            vertical-align: middle;
            border-bottom: 1px solid #dee2e6;
        }

        .table-actions button {
            padding: 6px 10px;
            border-radius: 8px;
        }

        .table-striped tbody tr:nth-of-type(odd) {
            background-color: #f9fafb;
        }

        .table th, .table td {
            white-space: nowrap;
        }

        .nav-tabs {
            margin-bottom: 0.5rem;
            border-bottom: none;
        }

        .nav-tabs .nav-link {
            background-color: #93C5FD;
            color: #2D3436;
            border: none;
            border-radius: 0.5rem 0.5rem 0 0;
            transition: all 0.3s ease;
            font-weight: 500;
            padding: 6px 14px; 
            margin-right: 1px; 
        }

        .nav-tabs .nav-link.active {
            background-color: #2563EB;
            color: #FFFFFF;
        }

        .nav-tabs .nav-link:hover {
            background-color: #60A5FA;
            color: #FFFFFF;
        }

        .page-info {
            font-size: 0.85rem;
            color: var(--text-muted);
        }

        .btn-outline-primary {
            color: var(--accent);
            border-color: var(--accent);
        }

        .btn-outline-primary:hover {
            background-color: var(--accent);
            color: white;
        }

        h2.text-primary {
            color: #2563EB !important;
        }

        .btn-export {
            border: 1.5px solid #2563EB;
            color: #2563EB;
            font-weight: 500;
        }

        .btn-export:hover {
            background-color: #2563EB;
            color: #ffffff;
        }

        .modal-profile .nav-tabs {
            margin-bottom: 0.5rem; 
            border-bottom: none;
        }

        .modal-profile .nav-tabs .nav-link {
            background-color: #93C5FD;
            color: #2D3436;
            border: none;
            border-radius: 0.5rem 0.5rem 0 0;
            transition: all 0.3s ease;
            font-weight: 500;
            padding: 6px 14px; 
            margin-right: 1px;
        }

        .modal-profile .nav-tabs .nav-link.active {
            background-color: #2563EB;
            color: #FFFFFF;
        }

        .modal-profile .nav-tabs .nav-link:hover {
            background-color: #60A5FA;
            color: #FFFFFF;
        }

        .table thead th {
            color: #2563EB; 
            border-bottom: 2px solid #2563EB;
            vertical-align: middle;
            text-align: center;
            font-weight: 600;
        }

        .table tbody td {
            padding: 10px 16px;
            vertical-align: middle;
            text-align: center;
            border-bottom: 1px solid #dee2e6;
        }

        .table tbody tr:nth-child(odd) {
            background-color: #ffffff;
        }
        .table tbody tr:nth-child(even) {
            background-color: #F5F6FA;
        }

        .badge-success {
            background-color: #28a745 !important;
            color: #ffffff !important;
        }

        .badge-warning {
            background-color: #FFC107 !important;
            color: #2D3436 !important;
        }

        .badge-danger {
            background-color: #DC2626 !important;
            color: #ffffff !important;
        }
        
    </style>
</head>

<body>
    <?php include __DIR__ . '/../includes/layout/sidebar.php'; ?>
    <?php include __DIR__ . '/../includes/layout/topbar.php'; ?>


    <div class="main-content p-5 mt-5">

        <!-- Tabs -->
        <ul class="nav nav-tabs mb-3" id="approvalTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active tab-btn" id="leave-tab" data-bs-toggle="tab" data-bs-target="#leave" type="button" role="tab" aria-controls="leave" aria-selected="true">
                    Leave
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link tab-btn" id="overtime-tab" data-bs-toggle="tab" data-bs-target="#overtime" type="button" role="tab" aria-controls="overtime" aria-selected="false">
                    Overtime
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link tab-btn" id="bonus-tab" data-bs-toggle="tab" data-bs-target="#bonus" type="button" role="tab" aria-controls="bonus" aria-selected="false">
                    Bonus/Adjustments
                </button>
            </li>
        </ul>

        <div class="tab-content" id="approvalTabsContent">
            <!-- Leave -->
            <div class="tab-pane fade show active" id="leave" role="tabpanel" aria-labelledby="leave-tab">
                <div class="d-flex align-items-center mb-3 filter-row" style="gap: 10px;">
                    <select id="statusFilterLeave" class="form-select"
                        style="width: 180px; background-color: #93C5FD; color: #2563EB; border: none;">
                        <option value="all" disabled selected hidden>Status</option>
                        <option value="pending">Pending</option>
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
                    </select>

                    <select id="timeframeFilterLeave" class="form-select"
                        style="width: 180px; background-color: #93C5FD; color: #2563EB; border: none;">
                        <option value="all" disabled selected hidden>Timeframe</option>
                        <option value="today">Today</option>
                        <option value="week">This Week</option>
                        <option value="month">This Month</option>
                    </select>

                    <button id="applyLeave" class="btn btn-primary me-auto">Apply</button>

                    <div class="d-flex align-items-center gap-2">
                        <input type="text" id="searchInputLeave" class="form-control" placeholder="Search..."
                            style="width: 300px; border-color: #2563EB;">
                        <button id="searchButtonLeave" class="btn btn-primary fw-semibold"
                            style="background-color: #2563EB; border: none;">Search</button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-striped align-middle">
                        <thead>
                            <tr>
                                <th>Request ID</th>
                                <th>Employee Name</th>
                                <th>Date</th>
                                <th>Reason</th>
                                <th>Status</th>
                                <th style="min-width:150px">Action</th>
                            </tr>
                        </thead>
                        <tbody id="leaveTbody"></tbody>
                    </table>
                </div>

                <div id="leavePageInfo" class="mb-2" style="color: #5B5757;"></div>

                <!-- Bottom Actions and Pagination -->
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div class="d-flex">
                        <button id="exportLeaveCsv" class="btn btn-export me-2">
                            <i class="bi bi-file-earmark-text me-1"></i> Export CSV
                        </button>
                        <button id="exportLeaveExcel" class="btn btn-export">
                            <i class="bi bi-file-earmark-spreadsheet me-1"></i> Export Excel
                        </button>
                    </div>

                    <nav id="leavePager" aria-label="Page navigation">
                        <ul class="pagination mb-0">
                            
                        </ul>
                    </nav>
                </div>
            </div>

            <!-- Overtime -->
            <div class="tab-pane fade" id="overtime" role="tabpanel" aria-labelledby="overtime-tab">
                <div class="d-flex align-items-center mb-3 filter-row" style="gap: 10px;">
                    <select id="statusFilterOvertime" class="form-select"
                        style="width: 180px; background-color: #93C5FD; color: #2563EB; border: none;">
                        <option value="all" disabled selected hidden>Status</option>
                        <option value="pending">Pending</option>
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
                    </select>

                    <select id="timeframeFilterOvertime" class="form-select"
                        style="width: 180px; background-color: #93C5FD; color: #2563EB; border: none;">
                        <option value="all" disabled selected hidden>Timeframe</option>
                        <option value="today">Today</option>
                        <option value="week">This Week</option>
                        <option value="month">This Month</option>
                    </select>

                    <button id="applyOvertime" class="btn btn-primary me-auto">Apply</button>

                    <div class="d-flex align-items-center gap-2">
                        <input type="text" id="searchInputOvertime" class="form-control" placeholder="Search..."
                            style="width: 300px; border-color: #2563EB;">
                        <button id="searchButtonOvertime" class="btn btn-primary fw-semibold"
                            style="background-color: #2563EB; border: none;">Search</button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-striped align-middle">
                        <thead>
                            <tr>
                                <th>Request ID</th>
                                <th>Employee Name</th>
                                <th>Date</th>
                                <th>Duration</th>
                                <th>Status</th>
                                <th style="min-width:150px">Action</th>
                            </tr>
                        </thead>
                        <tbody id="overtimeTbody"></tbody>
                    </table>
                </div>

                <div id="overtimePageInfo" class="mb-2" style="color: #5B5757;"></div>

                <!-- Bottom Actions and Pagination -->
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div class="d-flex">
                        <button id="exportOvertimeCsv" class="btn btn-export me-2">
                            <i class="bi bi-file-earmark-text me-1"></i> Export CSV
                        </button>
                        <button id="exportOvertimeExcel" class="btn btn-export">
                            <i class="bi bi-file-earmark-spreadsheet me-1"></i> Export Excel
                        </button>
                    </div>

                    <nav id="overtimePager" aria-label="Page navigation">
                        <ul class="pagination mb-0">
                            
                        </ul>
                    </nav>
                </div>
            </div>

            <!-- Bonus / Adjustments -->
            <div class="tab-pane fade" id="bonus" role="tabpanel" aria-labelledby="bonus-tab">
                <div class="d-flex align-items-center mb-3 filter-row" style="gap: 10px;">
                    <select id="statusFilterBonus" class="form-select"
                        style="width: 180px; background-color: #93C5FD; color: #2563EB; border: none;">
                        <option value="all" disabled selected hidden>Status</option>
                        <option value="pending">Pending</option>
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
                    </select>

                    <select id="timeframeFilterBonus" class="form-select"
                        style="width: 180px; background-color: #93C5FD; color: #2563EB; border: none;">
                        <option value="all" disabled selected hidden>Timeframe</option>
                        <option value="today">Today</option>
                        <option value="week">This Week</option>
                        <option value="month">This Month</option>
                    </select>

                    <button id="applyBonus" class="btn btn-primary me-auto">Apply</button>

                    <div class="d-flex align-items-center gap-2">
                        <input type="text" id="searchInputBonus" class="form-control" placeholder="Search..."
                            style="width: 300px; border-color: #2563EB;">
                        <button id="searchButtonBonus" class="btn btn-primary fw-semibold"
                            style="background-color: #2563EB; border: none;">Search</button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-striped align-middle">
                        <thead>
                            <tr>
                                <th>Request ID</th>
                                <th>Employee Name</th>
                                <th>Date Range</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th style="min-width:150px">Action</th>
                            </tr>
                        </thead>
                        <tbody id="bonusTbody"></tbody>
                    </table>
                </div>

                <div id="bonusPageInfo" class="mb-2" style="color: #5B5757;"></div>

                <!-- Bottom Actions and Pagination -->
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div class="d-flex">
                        <button id="exportBonusCsv" class="btn btn-export me-2">
                            <i class="bi bi-file-earmark-text me-1"></i> Export CSV
                        </button>
                        <button id="exportBonusExcel" class="btn btn-export">
                            <i class="bi bi-file-earmark-spreadsheet me-1"></i> Export Excel
                        </button>
                    </div>

                    <nav id="bonusPager" aria-label="Page navigation">
                        <ul class="pagination mb-0">
                            
                        </ul>
                    </nav>
                </div>
            </div>
            </div>
        </div>
    </div>

    <!-- Dynamic View Profile Modal -->
    <div class="modal fade" id="viewProfileModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl modal-profile">
            <div class="modal-content">
                <div class="modal-header" style="border-bottom: 2px solid #2563EB; display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <h5 class="modal-title fw-bold">Employee Profile</h5>
                    </div>
                    <div class="d-flex align-items-center">
                        <div id="profileHeaderMeta" style="font-size: 0.85rem; color: #5B5757; margin-right: 10px;"></div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                </div>
                <div class="modal-body">
                    <ul class="nav nav-tabs mb-3" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#modalSalary" type="button" role="tab">Salary Information</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#modalBenefits" type="button" role="tab">Deductions & Benefits</button>
                        </li>
                    </ul>
                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="modalSalary" role="tabpanel">
                            <div id="modalSalaryContent" class="row"></div>
                        </div>
                        <div class="tab-pane fade" id="modalBenefits" role="tabpanel">
                            <div id="modalBenefitsContent" class="row"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-outline-danger" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="viewRequestModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header" style="border-bottom: 2px solid #2563EB; display: flex; justify-content: space-between; align-items: center;">
                    <h5 class="modal-title fw-bold">Request Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="viewRequestBody"></div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-outline-danger" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

<!-- Update Header -->
<script>
    document.querySelectorAll('#approvalTabs .nav-link').forEach(tab => {
        tab.addEventListener('shown.bs.tab', function (event) {
            const activeTab = event.target.textContent.trim();
            document.getElementById('page-title').textContent = `Approval Page - ${activeTab}`;
        });
    });

    document.addEventListener("DOMContentLoaded", function() {
        const state = {
            leave: { page: 1, limit: 10, rows: [], status: '', timeframe: '', search: '' },
            overtime: { page: 1, limit: 10, rows: [], status: '', timeframe: '', search: '' },
            bonus: { page: 1, limit: 10, rows: [], status: '', timeframe: '', search: '' },
            employeesById: {}
        };

        function computeRange(tf) {
            const today = new Date();
            const ymd = d => d.toISOString().slice(0,10);
            if (tf === 'today') return { date_from: ymd(today), date_to: ymd(today) };
            if (tf === 'week') {
                const start = new Date(); start.setDate(today.getDate() - 6);
                return { date_from: ymd(start), date_to: ymd(today) };
            }
            if (tf === 'month') {
                const start = new Date(today.getFullYear(), today.getMonth(), 1);
                return { date_from: ymd(start), date_to: ymd(today) };
            }
            return { date_from: '', date_to: '' };
        }

        function badgeFor(status) {
            if (status === 'approved') return '<span class="badge bg-success">Approved</span>';
            if (status === 'rejected') return '<span class="badge bg-danger">Rejected</span>';
            return '<span class="badge bg-warning text-dark">Pending</span>';
        }

        function paged(rows, page, limit) {
            const total = rows.length;
            const pages = Math.max(1, Math.ceil(total / limit));
            const start = (page - 1) * limit;
            return { total, pages, slice: rows.slice(start, start + limit) };
        }

        function renderPager(containerSelector, infoSelector, page, pages, onChange) {
            const nav = document.querySelector(containerSelector);
            const info = document.querySelector(infoSelector);
            if (info) info.textContent = `Showing Page ${page} of ${pages}`;
            if (!nav) return;
            const ul = nav.querySelector('ul.pagination');
            if (!ul) return;
            ul.innerHTML = '';
            const add = (disabled, active, label, data) => {
                const li = document.createElement('li');
                li.className = 'page-item' + (disabled ? ' disabled' : '') + (active ? ' active' : '');
                const a = document.createElement('a'); a.className = 'page-link'; a.href = '#'; a.textContent = label;
                if (!disabled) a.addEventListener('click', e => { e.preventDefault(); onChange(data); });
                li.appendChild(a); ul.appendChild(li);
            };
            add(page===1, false, 'Previous', { type: 'prev' });
            for (let i=1; i<=pages; i++) add(false, page===i, String(i), { type: 'set', page: i });
            add(page===pages, false, 'Next', { type: 'next' });
        }

        function employeeName(emp_id) {
            const e = state.employeesById[emp_id];
            return e ? e.user_name : ('Employee ' + emp_id);
        }

        function fetchEmployees(cb) {
            $.getJSON('../modules/employees.php', { action: 'list', status: 'active' }, function(resp){
                const rows = resp.data || [];
                state.employeesById = {};
                rows.forEach(r => { state.employeesById[r.emp_id] = r; });
                if (cb) cb();
            });
        }

        function fetchRequests(type) {
            const st = state[type];
            const range = computeRange(st.timeframe);
            const params = { action: 'list', type };
            if (st.status) params.status = st.status;
            if (range.date_from) params.date_from = range.date_from;
            if (range.date_to) params.date_to = range.date_to;
            $.getJSON('../modules/requests.php', params, function(resp){
                let rows = resp.data || [];
                if (st.search) {
                    const q = st.search.toLowerCase();
                    if (type === 'overtime') {
                        rows = rows.filter(r => (
                            employeeName(r.emp_id).toLowerCase().includes(q)
                            || String(r.hours||'').toLowerCase().includes(q)
                            || String(r.rate||'').toLowerCase().includes(q)
                            || String(r.date||'').toLowerCase().includes(q)
                            || `${r.date_from||''} ${r.date_to||''}`.toLowerCase().includes(q)
                        ));
                    } else if (type === 'leave') {
                        rows = rows.filter(r => (
                            employeeName(r.emp_id).toLowerCase().includes(q)
                            || String(r.leave_type||'').toLowerCase().includes(q)
                        ));
                    } else {
                        rows = rows.filter(r => (
                            employeeName(r.emp_id).toLowerCase().includes(q)
                            || String(r.type||'').toLowerCase().includes(q)
                            || String(r.description||'').toLowerCase().includes(q)
                        ));
                    }
                }
                st.rows = rows;
                renderTable(type);
            });
        }

        function renderTable(type) {
            const st = state[type];
            const p = paged(st.rows, st.page, st.limit);
            let html = '';
            p.slice.forEach(r => {
                if (type === 'leave') {
                    html += `<tr>
                        <td>${r.leave_id}</td>
                        <td>${employeeName(r.emp_id)}</td>
                        <td>${r.date_from||''} - ${r.date_to||''}</td>
                        <td>${r.leave_type||''}</td>
                        <td>${badgeFor(r.status)}</td>
                        <td class="table-actions">
                            <button class="btn btn-outline-primary me-1" data-action="view" data-emp="${r.emp_id}"><i class="bi bi-eye"></i></button>
                            <button class="btn btn-outline-success me-1" data-action="approve" data-type="leave" data-id="${r.leave_id}"><i class="bi bi-check2-circle"></i></button>
                            <button class="btn btn-outline-danger" data-action="reject" data-type="leave" data-id="${r.leave_id}"><i class="bi bi-x-circle"></i></button>
                        </td>
                    </tr>`;
                } else if (type === 'overtime') {
                    const dateRange = (r.date_from && r.date_to) ? `${r.date_from} - ${r.date_to}` : (r.date||'');
                    const duration = (r.hours !== null && r.hours !== undefined) ? `${r.hours} hours` : '-';
                    html += `<tr>
                        <td>${r.overtime_id}</td>
                        <td>${employeeName(r.emp_id)}</td>
                        <td>${dateRange}</td>
                        <td>${duration}</td>
                        <td>${badgeFor(r.status)}</td>
                        <td class="table-actions">
                            <button class="btn btn-outline-primary me-1" data-action="view" data-type="overtime" data-id="${r.overtime_id}" data-emp="${r.emp_id}"><i class="bi bi-eye"></i></button>
                            <button class="btn btn-outline-success me-1" data-action="approve" data-type="overtime" data-id="${r.overtime_id}"><i class="bi bi-check2-circle"></i></button>
                            <button class="btn btn-outline-danger" data-action="reject" data-type="overtime" data-id="${r.overtime_id}"><i class="bi bi-x-circle"></i></button>
                        </td>
                    </tr>`;
                } else {
                    html += `<tr>
                        <td>${r.ba_id}</td>
                        <td>${employeeName(r.emp_id)}</td>
                        <td>${r.date_from||''} - ${r.date_to||''}</td>
                        <td>${r.type||''}</td>
                        <td>${badgeFor(r.status)}</td>
                        <td class="table-actions">
                            <button class="btn btn-outline-primary me-1" data-action="view" data-emp="${r.emp_id}"><i class="bi bi-eye"></i></button>
                            <button class="btn btn-outline-success me-1" data-action="approve" data-type="bonus" data-id="${r.ba_id}"><i class="bi bi-check2-circle"></i></button>
                            <button class="btn btn-outline-danger" data-action="reject" data-type="bonus" data-id="${r.ba_id}"><i class="bi bi-x-circle"></i></button>
                        </td>
                    </tr>`;
                }
            });
            if (type === 'leave') document.getElementById('leaveTbody').innerHTML = html;
            else if (type === 'overtime') document.getElementById('overtimeTbody').innerHTML = html;
            else document.getElementById('bonusTbody').innerHTML = html;
            const pagerSelector = type==='leave' ? '#leavePager' : type==='overtime' ? '#overtimePager' : '#bonusPager';
            const infoSelector = type==='leave' ? '#leavePageInfo' : type==='overtime' ? '#overtimePageInfo' : '#bonusPageInfo';
            renderPager(pagerSelector, infoSelector, st.page, p.pages, (ev) => {
                if (ev.type==='prev' && st.page>1) st.page--; else if (ev.type==='next' && st.page<p.pages) st.page++; else if (ev.type==='set') st.page=ev.page;
                renderTable(type);
            });
        }

        function exportRows(rows, filename, excel=false) {
        // CSV headers
        const headers = ['Request ID','Employee Name','Date Range','Type/Reason','Status'];

        if (!excel) {
            // CSV export
            const lines = [headers.join(',')];
            rows.forEach(r => {
                if (r.leave_id) lines.push([r.leave_id, employeeName(r.emp_id), `${r.date_from||''} - ${r.date_to||''}`, r.leave_type||'', r.status].join(','));
                else if (r.overtime_id) lines.push([r.overtime_id, employeeName(r.emp_id), `${r.date_from||''} - ${r.date_to||''}`, `${r.hours||0} hours`, r.status].join(','));
                else if (r.ba_id) lines.push([r.ba_id, employeeName(r.emp_id), `${r.date_from||''} - ${r.date_to||''}`, r.type||'', r.status].join(','));
            });
            const blob = new Blob([lines.join('\n')], { type: 'text/csv;charset=utf-8;' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a'); a.href = url; a.download = filename; a.click();
            URL.revokeObjectURL(url);
        } else {
            // XLSX export using SheetJS
            const wsData = [
                headers,
                ...rows.map(r => r.leave_id 
                    ? [r.leave_id, employeeName(r.emp_id), `${r.date_from||''} - ${r.date_to||''}`, r.leave_type||'', r.status]
                    : r.overtime_id
                    ? [r.overtime_id, employeeName(r.emp_id), `${r.date_from||''} - ${r.date_to||''}`, `${r.hours||0} hours`, r.status]
                    : [r.ba_id, employeeName(r.emp_id), `${r.date_from||''} - ${r.date_to||''}`, r.type||'', r.status]
                )
            ];
            const wb = XLSX.utils.book_new();
            const ws = XLSX.utils.aoa_to_sheet(wsData);
            XLSX.utils.book_append_sheet(wb, ws, "Sheet1");
            XLSX.writeFile(wb, filename);
        }
    }

        function attachFilters() {
            $('#applyLeave').on('click', function(){ state.leave.status = $('#statusFilterLeave').val()||''; state.leave.timeframe = $('#timeframeFilterLeave').val()||''; state.leave.page=1; fetchRequests('leave'); });
            $('#searchButtonLeave').on('click', function(){ state.leave.search = ($('#searchInputLeave').val()||'').trim(); state.leave.page=1; fetchRequests('leave'); });
            $('#applyOvertime').on('click', function(){ state.overtime.status = $('#statusFilterOvertime').val()||''; state.overtime.timeframe = $('#timeframeFilterOvertime').val()||''; state.overtime.page=1; fetchRequests('overtime'); });
            $('#searchButtonOvertime').on('click', function(){ state.overtime.search = ($('#searchInputOvertime').val()||'').trim(); state.overtime.page=1; fetchRequests('overtime'); });
            $('#applyBonus').on('click', function(){ state.bonus.status = $('#statusFilterBonus').val()||''; state.bonus.timeframe = $('#timeframeFilterBonus').val()||''; state.bonus.page=1; fetchRequests('bonus'); });
            $('#searchButtonBonus').on('click', function(){ state.bonus.search = ($('#searchInputBonus').val()||'').trim(); state.bonus.page=1; fetchRequests('bonus'); });
            $('#exportLeaveCsv').on('click', function(){ exportRows(state.leave.rows, 'leave_requests.csv'); });
            $('#exportLeaveExcel').on('click', function(){ exportRows(state.leave.rows, 'leave_requests.xls', true); });
            $('#exportOvertimeCsv').on('click', function(){ exportRows(state.overtime.rows, 'overtime_requests.csv'); });
            $('#exportOvertimeExcel').on('click', function(){ exportRows(state.overtime.rows, 'overtime_requests.xls', true); });
            $('#exportBonusCsv').on('click', function(){ exportRows(state.bonus.rows, 'bonus_adjustments.csv'); });
            $('#exportBonusExcel').on('click', function(){ exportRows(state.bonus.rows, 'bonus_adjustments.xls', true); });
        }

        document.body.addEventListener('click', function(e){
            const btn = e.target.closest('button');
            if (!btn) return;
            const act = btn.getAttribute('data-action');
            if (act === 'view') {
                const t = btn.getAttribute('data-type') || '';
                if (t === 'overtime') {
                    const id = parseInt(btn.getAttribute('data-id'), 10);
                    openOvertime(id);
                } else {
                    const emp = parseInt(btn.getAttribute('data-emp'), 10);
                    openProfile(emp);
                }
                return;
            }
            if (act === 'approve' || act === 'reject') {
                const type = btn.getAttribute('data-type');
                const id = parseInt(btn.getAttribute('data-id'), 10);
                const payload = { action: act, type };
                if (type==='leave') payload.leave_id = id; else if (type==='overtime') payload.overtime_id = id; else payload.ba_id = id;
                $.post('../modules/requests.php', payload, function(res){ if (res.status==='success') fetchRequests(type); }, 'json');
            }
        });

        function openProfile(emp_id) {
            $.getJSON('../modules/employees.php', { action: 'list', user_id: 0 }, function(resp){
                const rows = resp.data||[];
                const emp = rows.find(x=>x.emp_id===emp_id);
                if (!emp) return;
                $('#profileHeaderMeta').html(`Employee ID: ${emp.emp_id} | ${emp.dept_name||''} | ${emp.pos_name||''} | <span class="badge bg-success">${emp.status||'active'}</span>`);
                $('#modalSalaryContent').html(`<div class="col-md-6"><h6 class="fw-bold text-primary mb-3">Employment Details</h6>
                    <p><strong>Employment Type:</strong> ${emp.employment_type||''}</p>
                    <p><strong>Hire Date:</strong> ${emp.hire_date||''}</p>
                    <p><strong>Payment Schedule:</strong> Monthly</p>
                    <p><strong>Rate Type:</strong> Salary</p>
                    <p><strong>Tax Category:</strong> Regular</p></div>
                    <div class="col-md-6"><h6 class="fw-bold text-primary mb-3">Salary Structure</h6>
                    <p><strong>Basic Salary:</strong> ₱${emp.basic_pay||0}</p>
                    <p><strong>Allowances:</strong> ₱0</p>
                    <p><strong>Total Monthly Compensation:</strong> ₱${(emp.basic_pay||0)}</p>
                    <p><strong>Effective Date:</strong> -</p>
                    <p><strong>Remarks:</strong> -</p></div>`);
                $('#modalBenefitsContent').html(`<div class="col-md-6"><h6 class="fw-bold text-danger mb-3">Deductions</h6>
                    <p><strong>Tax:</strong> ₱0</p>
                    <p><strong>SSS:</strong> ₱0</p>
                    <p><strong>PhilHealth:</strong> ₱0</p>
                    <p><strong>Pag-IBIG:</strong> ₱0</p></div>
                    <div class="col-md-6"><h6 class="fw-bold text-success mb-3">Benefits</h6>
                    <p><strong>Medical:</strong> ₱0</p>
                    <p><strong>Transportation:</strong> ₱0</p>
                    <p><strong>Meal:</strong> ₱0</p>
                    <p><strong>Other Benefits:</strong> ₱0</p></div>`);
                const modal = new bootstrap.Modal(document.getElementById('viewProfileModal'));
                modal.show();
            });
        }

        function openOvertime(overtime_id) {
            const r = (state.overtime.rows||[]).find(x => x.overtime_id === overtime_id);
            if (!r) return;
            const name = employeeName(r.emp_id);
            const dateRange = (r.date_from && r.date_to) ? `${r.date_from} - ${r.date_to}` : (r.date||'-');
            const hours = (r.hours !== null && r.hours !== undefined) ? `${r.hours}` : '-';
            const rate = (r.rate !== null && r.rate !== undefined) ? `${r.rate}` : '-';
            const amount = (r.computed_amount !== null && r.computed_amount !== undefined) ? `${r.computed_amount}` : '-';
            const status = r.status || 'pending';
            const html = `
                <div class="mb-2"><strong>Request ID:</strong> ${r.overtime_id}</div>
                <div class="mb-2"><strong>Employee:</strong> ${name}</div>
                <div class="mb-2"><strong>Date:</strong> ${dateRange}</div>
                <div class="mb-2"><strong>Hours:</strong> ${hours}</div>
                <div class="mb-2"><strong>Rate:</strong> ${rate}</div>
                <div class="mb-2"><strong>Computed Amount:</strong> ${amount}</div>
                <div class="mb-2"><strong>Status:</strong> ${status}</div>
            `;
            const body = document.getElementById('viewRequestBody');
            if (body) body.innerHTML = html;
            const modal = new bootstrap.Modal(document.getElementById('viewRequestModal'));
            modal.show();
        }

        fetchEmployees(function(){ fetchRequests('leave'); fetchRequests('overtime'); fetchRequests('bonus'); });
        attachFilters();
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
        // Populate pending requests for approval
        
    </script>
</body>
</html>