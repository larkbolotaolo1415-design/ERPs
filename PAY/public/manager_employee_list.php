<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../init.php';
$pageTitle = "Department Employee List View";
$userName = $_SESSION['user_name'] ?? 'User';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Department Employee List View</title>
    <link rel="stylesheet" href="../assets/css/dashboard_style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.sheetjs.com/xlsx-latest/package/dist/xlsx.full.min.js"></script>
    <style>
        :root {
            --accent: #2563EB;
            --white: #ffffff;
        }

        .btn-accent {
            background-color: var(--accent);
            color: var(--white);
        }

        .btn-accent:hover {
            background-color: #1d4dbf;
            color: var(--white);
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

        .filter-section select,
        .filter-section input {
            min-width: 150px;
        }

        .filter-section .btn {
            min-width: 80px;
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
            text-align: center;
            border-bottom: 2px solid #dee2e6;
            padding: 12px 16px;
        }

        .table tbody td {
            padding: 10px 16px;
            vertical-align: middle;
            text-align: center;
            border-bottom: 1px solid #dee2e6;
        }

        .table-actions button {
            padding: 6px 10px;
            border-radius: 8px;
        }

        .table-striped tbody tr:nth-of-type(odd) {
            background-color: #f9fafb;
        }

        .table th,
        .table td {
            white-space: nowrap;
        }

        /* Default tab style */
        .nav-tabs .nav-link {
            background-color: #93C5FD;
            color: #2D3436;
            border: none;
            margin-right: 5px;
            border-radius: 0.5rem 0.5rem 0 0;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        /* Active tab style */
        .nav-tabs .nav-link.active {
            background-color: #2563EB;
            color: #FFFFFF;
            border: none;
        }

        /* Hover effect */
        .nav-tabs .nav-link:hover {
            background-color: #60A5FA;
            color: #FFFFFF;
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
            font-weight: 600;
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

        .table-scroll {
            max-height: 360px;
            overflow: auto;
        }
    </style>
</head>

<body>
    <?php include __DIR__ . '/../includes/layout/sidebar.php'; ?>
    <?php include __DIR__ . '/../includes/layout/topbar.php'; ?>

    <div class="main-content p-5 mt-5">

        <!-- Filters & Search -->
        <div class="d-flex align-items-center mb-3 filter-section" style="gap: 10px;">
            <select class="form-select name-filter" style="width: 180px; background-color: #93C5FD; color: #2563EB; border: none;">
                <option value="all" disabled selected hidden>Name</option>
                <option value="name-az">Name (A-Z)</option>
                <option value="name-za">Name (Z-A)</option>
            </select>

            <select id="departmentFilter" class="form-select department-filter" style="width: 180px; background-color: #93C5FD; color: #2563EB; border: none;">
                <option value="" disabled selected hidden>Department</option>
            </select>

            <select id="positionFilter" class="form-select position-filter" style="width: 180px; background-color: #93C5FD; color: #2563EB; border: none;">
                <option value="" disabled selected hidden>Position</option>
            </select>

            <select class="form-select status-filter" style="width: 180px; background-color: #93C5FD; color: #2563EB; border: none;">
                <option value="all" disabled selected hidden>Status</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>

            <button id="applyEmployees" class="btn btn-accent">Apply</button>
            <button id="resetEmployees" class="btn btn-outline-secondary ms-2">Reset Filters</button>

            <div class="d-flex align-items-center gap-2">
                <input type="text" class="form-control search-input" placeholder="Search..." style="width: 300px; border-color: #2563EB;">
                <button class="btn btn-accent search-btn">Search</button>
                <button class="btn btn-outline-secondary clear-search-btn">Clear</button>
            </div>
        </div>

        <!-- Employee Table -->
        <div class="table-responsive">
            <table class="table table-striped align-middle">
                <thead>
                    <tr>
                        <th>Employee ID</th>
                        <th>Name</th>
                        <th>Position</th>
                        <th>Status</th>
                        <th>Attendance Rate</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="empTbody"></tbody>
            </table>
        </div>

        <!-- Showing page text -->
        <div id="empPageInfo" class="mb-2" style="color: #5B5757;"></div>

        <!-- Pagination & Export -->
        <div class="d-flex justify-content-between align-items-center mt-3">
            <div class="d-flex">
                <button class="btn btn-export me-2">
                    <i class="bi bi-file-earmark-text me-1"></i> Export CSV
                </button>
                <button class="btn btn-export">
                    <i class="bi bi-file-earmark-spreadsheet me-1"></i> Export Excel
                </button>
            </div>

            <nav id="empPager" aria-label="Page navigation">
                <ul class="pagination mb-0">

                </ul>
            </nav>
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

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const state = {
                page: 1,
                limit: 10,
                rows: [],
                status: '',
                dept_id: 0,
                pos_id: 0,
                order: '',
                search: '',
                loading: false
            };

            function badgeFor(status) {
                if (status === 'active') return '<span class="badge bg-success">Active</span>';
                if (status === 'inactive') return '<span class="badge bg-danger">Inactive</span>';
                return '<span class="badge bg-warning text-dark">Unknown</span>';
            }

            function paged(rows, page, limit) {
                const total = rows.length;
                const pages = Math.max(1, Math.ceil(total / limit));
                const start = (page - 1) * limit;
                return {
                    total,
                    pages,
                    slice: rows.slice(start, start + limit)
                };
            }

            function renderPager(page, pages) {
                const info = document.getElementById('empPageInfo');
                if (info) info.textContent = `Showing Page ${page} of ${pages}`;
                const nav = document.getElementById('empPager');
                if (!nav) return;
                const ul = nav.querySelector('ul.pagination');
                if (!ul) return;
                ul.innerHTML = '';
                const add = (disabled, active, label, data) => {
                    const li = document.createElement('li');
                    li.className = 'page-item' + (disabled ? ' disabled' : '') + (active ? ' active' : '');
                    const a = document.createElement('a');
                    a.className = 'page-link';
                    a.href = '#';
                    a.textContent = label;
                    if (!disabled) a.addEventListener('click', e => {
                        e.preventDefault();
                        if (data.type === 'prev' && state.page > 1) state.page--;
                        else if (data.type === 'next' && state.page < pages) state.page++;
                        else if (data.type === 'set') state.page = data.page;
                        renderTable();
                    });
                    li.appendChild(a);
                    ul.appendChild(li);
                };
                add(state.page === 1, false, 'Previous', {
                    type: 'prev'
                });
                for (let i = 1; i <= pages; i++) add(false, state.page === i, String(i), {
                    type: 'set',
                    page: i
                });
                add(state.page === pages, false, 'Next', {
                    type: 'next'
                });
            }

            function fetchPositions() {
                $.ajax({
                    url: '../modules/employees.php',
                    method: 'POST',
                    data: {
                        action: 'positions'
                    },
                    dataType: 'json'
                }).done(function(resp) {
                    const rows = resp.data || [];
                    const sel = document.getElementById('positionFilter');
                    if (!sel) return;
                    sel.innerHTML = '<option value="" disabled selected hidden>Position</option>' + rows.map(r => `<option value="${r.pos_id}">${r.pos_name}</option>`).join('');
                });
            }

            function fetchDepartments() {
                $.getJSON('../modules/company.php', {
                    action: 'list',
                    resource: 'departments'
                }, function(resp) {
                    const rows = resp.data || [];
                    const sel = document.getElementById('departmentFilter');
                    if (!sel) return;
                    sel.innerHTML = '<option value="" disabled selected hidden>Department</option>' + rows.map(r => `<option value="${r.dept_id}">${r.dept_name}</option>`).join('');
                });
            }

            function setLoading(l) {
                state.loading = !!l;
                const tbody = document.getElementById('empTbody');
                const info = document.getElementById('empPageInfo');
                const applyBtn = document.getElementById('applyEmployees');
                const searchBtn = document.querySelector('.search-btn');
                const nameSel = document.querySelector('.name-filter');
                const posSel = document.getElementById('positionFilter');
                const statusSel = document.querySelector('.status-filter');
                [applyBtn, searchBtn, nameSel, posSel, statusSel].forEach(el => {
                    if (el) el.disabled = state.loading;
                });
                if (state.loading && tbody) {
                    tbody.innerHTML = `<tr><td colspan="6"><div class="d-flex align-items-center justify-content-center" style="gap:8px; padding:16px;"><div class="spinner-border text-primary" role="status"></div><span>Loading...</span></div></td></tr>`;
                }
                if (state.loading && info) info.textContent = `Loading...`;
            }

            function fetchEmployees() {
                setLoading(true);
                const params = {
                    action: 'list'
                };
                if (state.status) params.status = state.status;
                if (state.dept_id) params.dept_id = state.dept_id;
                if (state.pos_id) params.pos_id = state.pos_id;
                if (state.order) {
                    params.sort = 'name';
                    params.order = state.order;
                }
                $.ajax({
                    url: '../modules/employees.php',
                    type: 'POST',
                    dataType: 'json',
                    data: params,
                    timeout: 10000
                }).done(function(resp) {
                    let rows = resp && resp.data ? resp.data : [];
                    if (state.search) {
                        const q = state.search.toLowerCase();
                        rows = rows.filter(r => (String(r.user_name || '').toLowerCase().includes(q)) || (String(r.pos_name || '').toLowerCase().includes(q)) || (String(r.dept_name || '').toLowerCase().includes(q)) || (String(r.emp_id || '').toLowerCase().includes(q)));
                    }
                    state.rows = rows;
                    state.page = 1;
                    setLoading(false);
                    renderTable();
                }).fail(function() {
                    state.rows = [];
                    state.page = 1;
                    const tbody = document.getElementById('empTbody');
                    if (tbody) tbody.innerHTML = `<tr><td colspan="6">No records available</td></tr>`;
                    const info = document.getElementById('empPageInfo');
                    if (info) info.textContent = `Unable to load data`;
                    setLoading(false);
                });
            }

            function renderTable() {
                const p = paged(state.rows, state.page, state.limit);
                const tbody = document.getElementById('empTbody');
                let html = '';
                if (p.slice.length === 0) {
                    html = `<tr><td colspan="6">No records available</td></tr>`;
                } else {
                    html = p.slice.map(r => `<tr>
                <td>${r.emp_id}</td>
                <td>${r.user_name}</td>
                <td>${r.pos_name||''}</td>
                <td>${badgeFor(r.status)}</td>
                <td><span id="att_${r.emp_id}">Loading...</span></td>
                <td class="table-actions">
                    <button class="btn btn-outline-primary" data-action="view" data-emp="${r.emp_id}"><i class="bi bi-eye"></i></button>
                </td>
            </tr>`).join('');
                }
                if (tbody) tbody.innerHTML = html;
                renderPager(state.page, p.pages);
                computeAttendanceForPage(p.slice);
            }

            let currentPeriod = null;

            function fetchCurrentPeriod(cb) {
                $.getJSON('../modules/periods.php', {
                    action: 'list'
                }, function(rp) {
                    const periods = rp.data || [];
                    const cur = periods.find(p => p.status === 'processing') || periods.find(p => p.status === 'open') || periods[0];
                    currentPeriod = cur || null;
                    if (cb) cb(currentPeriod);
                }).fail(function() {
                    currentPeriod = null;
                    if (cb) cb(null);
                });
            }

            function daysBetweenInclusive(a, b) {
                try {
                    const d1 = new Date(a),
                        d2 = new Date(b);
                    const ms = Math.abs(d2 - d1);
                    return Math.floor(ms / 86400000) + 1;
                } catch (e) {
                    return 0;
                }
            }

            function computeAttendanceForPage(rows) {
                const per = currentPeriod;
                if (!per) {
                    return;
                }
                const totalDays = daysBetweenInclusive(per.start_date, per.end_date);
                rows.forEach(function(r) {
                    const span = document.getElementById('att_' + r.emp_id);
                    if (span) span.textContent = 'Loading...';
                    $.getJSON('../modules/attendance.php', {
                        action: 'list',
                        emp_id: r.emp_id,
                        date_from: per.start_date,
                        date_to: per.end_date
                    }, function(ra) {
                        const att = ra.data || [];
                        const dates = new Set(att.map(x => x.date));
                        const present = dates.size;
                        const rate = totalDays > 0 ? Math.round((present / totalDays) * 100) : 0;
                        if (span) span.textContent = `${present}/${totalDays} (${rate}%)`;
                    }).fail(function() {
                        if (span) span.textContent = '—';
                    });
                });
            }

            function exportRows(rows, filename, excel = false) {
                const headers = ['Employee ID', 'Name', 'Position', 'Status'];

                if (!excel) {
                    // CSV export
                    const lines = [headers.join(',')];
                    rows.forEach(r => {
                        lines.push([r.emp_id, r.user_name, r.pos_name || '', r.status].join(','));
                    });
                    const blob = new Blob([lines.join('\n')], {
                        type: 'text/csv;charset=utf-8;'
                    });
                    const url = URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = filename;
                    a.click();
                    URL.revokeObjectURL(url);
                } else {
                    // XLSX export using SheetJS
                    const wsData = [
                        headers,
                        ...rows.map(r => [r.emp_id, r.user_name, r.pos_name || '', r.status])
                    ];
                    const wb = XLSX.utils.book_new();
                    const ws = XLSX.utils.aoa_to_sheet(wsData);
                    XLSX.utils.book_append_sheet(wb, ws, "Employees");
                    XLSX.writeFile(wb, filename);
                }
            }

            document.body.addEventListener('click', function(e) {
                const btn = e.target.closest('button');
                if (!btn) return;
                const act = btn.getAttribute('data-action');
                if (act === 'view') {
                    const emp = parseInt(btn.getAttribute('data-emp'), 10);
                    openProfile(emp);
                    return;
                }
                if (act === 'activate' || act === 'deactivate') {
                    const emp = parseInt(btn.getAttribute('data-emp'), 10);
                    const next = act === 'activate' ? 'active' : 'inactive';
                    $.post('../modules/employees.php', {
                        action: 'update',
                        emp_id: emp,
                        status: next
                    }, function(res) {
                        if (res.status === 'success') fetchEmployees();
                    }, 'json');
                }
            });

            function peso(n) {
                return '₱' + (parseInt(n || 0).toLocaleString());
            }

            function openProfile(emp_id) {
                $.ajax({
                        url: '../modules/employees.php',
                        method: 'POST',
                        data: {
                            action: 'profile',
                            emp_id
                        },
                        dataType: 'json'
                    })
                    .done(function(resp) {
                        const emp = resp.data || {};
                        $('#profileHeaderMeta').html(`Employee ID: ${emp.emp_id} | ${emp.dept_name||''} | ${emp.pos_name||''} | <span class="badge ${emp.status==='active'?'bg-success':'bg-danger'}">${emp.status||''}</span>`);
                        const allowTotal = (emp.benefits || []).reduce((s, b) => s + (b.type === 'fixed' ? parseFloat(b.rate_or_formula || 0) : 0), 0);
                        const totalComp = parseInt(emp.basic_pay || 0) + allowTotal;
                        const left = [`<h6 class="fw-bold text-primary mb-3">Employment Details</h6>`,
                            `<p><strong>Employment Type:</strong> ${emp.employment_type||''}</p>`,
                            `<p><strong>Hire Date:</strong> ${emp.hire_date||''}</p>`
                        ].join('');
                        const right = [`<h6 class="fw-bold text-primary mb-3">Salary Structure</h6>`,
                            `<div class="d-flex align-items-center mb-2" style="gap:8px"><label class="fw-semibold">Basic Salary:</label><input type="number" class="form-control form-control-sm" id="prof_basic_pay" style="max-width:160px" value="${parseInt(emp.basic_pay||0)}"><button class="btn btn-sm btn-accent" id="prof_basic_save">Save</button></div>`,
                            `<p><strong>Allowances:</strong> ${peso(allowTotal)}</p>`,
                            `<p><strong>Total Monthly Compensation:</strong> ${peso(totalComp)}</p>`
                        ].join('');
                        $('#modalSalaryContent').html(`<div class="col-md-6">${left}</div><div class="col-md-6">${right}</div>`);

                        const benList = (emp.benefits || []).map(b => `<li class="list-group-item d-flex justify-content-between align-items-center">${b.ben_name}<span>${b.type==='fixed'?peso(b.rate_or_formula):b.type}</span><button class="btn btn-sm btn-outline-danger" data-remove-ben="${b.ben_id}">Remove</button></li>`).join('') || '<li class="list-group-item">No benefits</li>';
                        const dedList = (emp.deductions || []).map(d => `<li class="list-group-item d-flex justify-content-between align-items-center">${d.deduct_name}<span>${d.type==='fixed'?peso(d.rate_or_formula):d.type}</span><button class="btn btn-sm btn-outline-danger" data-remove-ded="${d.deduct_id}">Remove</button></li>`).join('') || '<li class="list-group-item">No deductions</li>';
                        $('#modalBenefitsContent').html(`
                        <div class="col-md-6"><h6 class="fw-bold text-danger mb-3">Deductions</h6>
                            <div class="mb-2 d-flex" style="gap:8px">
                                <select class="form-select form-select-sm" id="prof_ded_add" style="max-width:220px"></select>
                                <button class="btn btn-sm btn-accent" id="prof_ded_add_btn">Add</button>
                            </div>
                            <ul class="list-group" id="prof_ded_list">${dedList}</ul>
                        </div>
                        <div class="col-md-6"><h6 class="fw-bold text-success mb-3">Benefits</h6>
                            <div class="mb-2 d-flex" style="gap:8px">
                                <select class="form-select form-select-sm" id="prof_ben_add" style="max-width:220px"></select>
                                <button class="btn btn-sm btn-accent" id="prof_ben_add_btn">Add</button>
                            </div>
                            <ul class="list-group" id="prof_ben_list">${benList}</ul>
                        </div>`);

                        $.post('../modules/employees.php', {
                            action: 'catalogs'
                        }, function(ca) {
                            const cats = ca.data || {};
                            const benSel = document.getElementById('prof_ben_add');
                            const dedSel = document.getElementById('prof_ded_add');
                            if (benSel) benSel.innerHTML = (cats.benefits || []).map(b => `<option value="${b.ben_id}">${b.ben_name}</option>`).join('');
                            if (dedSel) dedSel.innerHTML = (cats.deductions || []).map(d => `<option value="${d.deduct_id}">${d.deduct_name}</option>`).join('');
                        }, 'json');

                        const modal = new bootstrap.Modal(document.getElementById('viewProfileModal'));
                        modal.show();
                        const basicBtn = document.getElementById('prof_basic_save');
                        if (basicBtn) basicBtn.addEventListener('click', function() {
                            const val = parseInt(document.getElementById('prof_basic_pay').value || '0');
                            $.post('../modules/employees.php', {
                                action: 'updateBasicPay',
                                pos_id: emp.pos_id,
                                basic_pay: val
                            }, function(r) {
                                openProfile(emp.emp_id);
                            }, 'json');
                        });
                        const benAddBtn = document.getElementById('prof_ben_add_btn');
                        if (benAddBtn) benAddBtn.addEventListener('click', function() {
                            const sel = document.getElementById('prof_ben_add');
                            const ben_id = parseInt(sel.value || '0');
                            if (!ben_id) return;
                            $.post('../modules/employees.php', {
                                action: 'benefitAssign',
                                emp_id: emp.emp_id,
                                ben_id
                            }, function() {
                                openProfile(emp.emp_id);
                            }, 'json');
                        });
                        const dedAddBtn = document.getElementById('prof_ded_add_btn');
                        if (dedAddBtn) dedAddBtn.addEventListener('click', function() {
                            const sel = document.getElementById('prof_ded_add');
                            const deduct_id = parseInt(sel.value || '0');
                            if (!deduct_id) return;
                            $.post('../modules/employees.php', {
                                action: 'deductionAssign',
                                emp_id: emp.emp_id,
                                deduct_id
                            }, function() {
                                openProfile(emp.emp_id);
                            }, 'json');
                        });
                        document.getElementById('modalBenefitsContent').addEventListener('click', function(ev) {
                            const rmB = ev.target.closest('[data-remove-ben]');
                            if (rmB) {
                                const ben_id = parseInt(rmB.getAttribute('data-remove-ben'), 10);
                                $.post('../modules/employees.php', {
                                    action: 'benefitRemove',
                                    emp_id: emp.emp_id,
                                    ben_id
                                }, function() {
                                    openProfile(emp.emp_id);
                                }, 'json');
                            }
                            const rmD = ev.target.closest('[data-remove-ded]');
                            if (rmD) {
                                const deduct_id = parseInt(rmD.getAttribute('data-remove-ded'), 10);
                                $.post('../modules/employees.php', {
                                    action: 'deductionRemove',
                                    emp_id: emp.emp_id,
                                    deduct_id
                                }, function() {
                                    openProfile(emp.emp_id);
                                }, 'json');
                            }
                        });
                    });
            }

            document.getElementById('applyEmployees').addEventListener('click', function() {
                const nameFilter = document.querySelector('.name-filter');
                const deptSel = document.getElementById('departmentFilter');
                const posSel = document.getElementById('positionFilter');
                const statusSel = document.querySelector('.status-filter');
                state.order = (nameFilter && nameFilter.value === 'name-za') ? 'desc' : 'asc';
                state.dept_id = parseInt(deptSel && deptSel.value ? deptSel.value : '0', 10) || 0;
                state.pos_id = parseInt(posSel.value || '0', 10) || 0;
                state.status = (statusSel && statusSel.value !== 'all') ? statusSel.value : '';
                fetchEmployees();
            });

            document.getElementById('resetEmployees').addEventListener('click', function() {
                const nameFilter = document.querySelector('.name-filter');
                const deptSel = document.getElementById('departmentFilter');
                const posSel = document.getElementById('positionFilter');
                const statusSel = document.querySelector('.status-filter');
                const si = document.querySelector('.search-input');
                if (nameFilter) nameFilter.selectedIndex = 0;
                if (deptSel) deptSel.selectedIndex = 0;
                if (posSel) posSel.selectedIndex = 0;
                if (statusSel) statusSel.selectedIndex = 0;
                if (si) si.value = '';
                state.order = '';
                state.dept_id = 0;
                state.pos_id = 0;
                state.status = '';
                state.search = '';
                fetchEmployees();
            });

            document.querySelector('.search-btn').addEventListener('click', function() {
                const si = document.querySelector('.search-input');
                state.search = (si.value || '').trim();
                fetchEmployees();
            });
            document.querySelector('.clear-search-btn').addEventListener('click', function() {
                const si = document.querySelector('.search-input');
                if (si) si.value = '';
                state.search = '';
                fetchEmployees();
            });
            document.querySelector('.search-input').addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    document.querySelector('.search-btn').click();
                }
            });

            document.querySelector('.btn-export.me-2').addEventListener('click', function() {
                exportRows(state.rows, 'employees.csv');
            });
            document.querySelector('.btn-export:not(.me-2)').addEventListener('click', function() {
                exportRows(state.rows, 'employees.xls', true);
            });

            fetchPositions();
            fetchDepartments();
            fetchCurrentPeriod(function() {
                fetchEmployees();
            });
        });
    </script>
</body>

</html>