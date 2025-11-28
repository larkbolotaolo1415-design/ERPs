<?php
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
    <title>Payroll Summary Report</title>
    <link rel="stylesheet" href="../assets/css/dashboard_style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        :root {
            --accent: #2563EB;  
            --accent-light: #93C5FD;
            --white: #ffffff;
        }

        .summary-card {
            background-color: var(--accent);
            color: var(--white);
            border: none;
            height: 150px;
            border-radius: 10px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        .graph-box {
            background-color: var(--accent);
            border-radius: 10px;
            padding: 60px 40px;
            color: var(--white);
            text-align: center;
            font-weight: 500;
        }

        .summary-card h6 {
            font-weight: 600;
        }

        select.form-select {
            background-color: var(--accent-light);
            color: var(--accent);
            border: none;
            width: 180px;
        }

        .btn-primary {
            background-color: var(--accent);
            border: none;
        }

        .btn-primary:hover {
            background-color: #1E4FD7;
        }

        .table thead th {
            background-color: #F8F9FA;
            font-weight: 600;
            vertical-align: middle;
            text-align: middle;
            border-bottom: 2px solid #dee2e6;
            padding: 12px 16px;
        }

        .table th, .table td {
            white-space: nowrap;
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
    </style>
</head>

<body>
    <?php include __DIR__ . '/../includes/layout/sidebar.php'; ?>
    <?php include __DIR__ . '/../includes/layout/topbar.php'; ?>

    <div class="main-content p-5 mt-5">
        <!-- Summary Cards -->
        <div class="row g-3 mb-4">
            <div class="col-lg-8">
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="card summary-card shadow-sm">
                        <h6>Department</h6>
                        <h3 id="cardDepartment"></h3>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card summary-card shadow-sm">
                        <h6>Payroll Status</h6>
                        <h3 id="cardStatus"></h3>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card summary-card shadow-sm">
                        <h6>Average Net Pay</h6>
                        <h3 id="cardAvgNet"></h3>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card summary-card shadow-sm">
                        <h6>Cutoff Period</h6>
                        <h3 id="cardCutoff"></h3>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card summary-card shadow-sm">
                        <h6>Total Payroll Expenses</h6>
                        <h3 id="cardTotal"></h3>
                    </div>
                </div>
            </div>
        </div>

    <!-- Payroll Trend Graph -->
    <div class="col-lg-4">
        <div class="card shadow-sm mb-3" style="border-radius: 10px; height: 300px; background-color: #2563EB; color: #ffffff;">
            <div class="card-body d-flex flex-column">
                <h6 class="fw-bold mb-3">Payroll Trend</h6>
                <div id="payTrendWrap" class="flex-fill" style="background-color: #ffffff; border-radius: 10px; padding: 8px; height: 220px; overflow: hidden;">
                    <canvas id="payTrendChart" style="width: 100%; height: 100%"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters and Search -->
    <div class="tab-pane fade show active" id="leave" role="tabpanel" aria-labelledby="leave-tab">
        <div class="d-flex align-items-center mb-3 filter-row" style="gap: 10px;">
            <select id="statusFilterLeave" class="form-select"
                style="width: 180px; background-color: #93C5FD; color: #2563EB; border: none;">
                    <option value="all" disabled selected hidden>Status</option>
                    <option value="pending">Pending</option>
                    <option value="endorsed">Endorsed</option>
                    <option value="generated">Generated</option>
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

        <!-- Table Section -->
            <div class="table-responsive">
                <table class="table table-striped align-middle">
                        <thead class="table-light">
                            <tr class="text-center">
                                <th>Employee</th>
                                <th>Gross Pay</th>
                                <th>Deductions</th>
                                <th>Net Pay</th>
                                <th>Payroll Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="payrollTbody" class="text-center"></tbody>
                    </table>

                <!-- Footer Pagination -->
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <p id="payPageInfo" class="mb-0 text-secondary"></p>
                    <nav aria-label="Page navigation">
                        <ul id="payPager" class="pagination mb-0"></ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>

        <!-- Payroll Summary Modal -->
        <div class="modal fade" id="payrollSummaryModal" tabindex="-1" aria-labelledby="payrollSummaryLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header" style="border-bottom: 2px solid #2563EB; display: flex; justify-content: space-between; align-items: center;">
                        <h5 class="modal-title" id="payrollSummaryLabel">Payroll Summary</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body" id="modalBodyContent">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

    <script>
        function peso(n){ return '₱' + (Number(n||0)).toLocaleString('en-PH'); }
        function badge(status){
            if (status === 'pending') return '<span class="badge bg-warning text-dark">Pending</span>';
            if (status === 'approved') return '<span class="badge bg-info text-dark">Endorsed</span>';
            if (status === 'locked') return '<span class="badge bg-success">Locked</span>';
            return '<span class="badge bg-secondary">Unknown</span>';
        }

        $(function(){
            const statusSel = document.getElementById('statusFilterLeave');
            const timeSel = document.getElementById('timeframeFilterLeave');
            const applyBtn = document.getElementById('applyLeave');
            const searchInput = document.getElementById('searchInputLeave');
            const searchBtn = document.getElementById('searchButtonLeave');

            const tbody = document.getElementById('payrollTbody');
            const pageInfo = document.getElementById('payPageInfo');
            const pager = document.getElementById('payPager');

            const cardDept = document.getElementById('cardDepartment');
            const cardStatus = document.getElementById('cardStatus');
            const cardAvg = document.getElementById('cardAvgNet');
            const cardCutoff = document.getElementById('cardCutoff');
            const cardTotal = document.getElementById('cardTotal');

            let rows = [];
            let page = 1;
            const limit = 10;
            let search = '';
            let statusUi = '';
            let timeframe = 'month';
            let currentPeriod = null;

            function pickPeriod(periods, timeframe){
                const today = new Date();
                const toDate = s => new Date(s);
                let chosen = null;
                if (timeframe === 'today') {
                    chosen = periods.find(p => toDate(p.start_date) <= today && toDate(p.end_date) >= today) || periods[0];
                } else if (timeframe === 'week') {
                    const weekAgo = new Date(); weekAgo.setDate(today.getDate()-7);
                    chosen = periods.find(p => toDate(p.end_date) >= weekAgo) || periods[0];
                } else {
                    chosen = periods.find(p => (new Date(p.end_date)).getMonth() === today.getMonth() && (new Date(p.end_date)).getFullYear() === today.getFullYear()) || periods[0];
                }
                return chosen;
            }

            function renderSummary(period, data){
                const byDept = {};
                let totalNet = 0;
                data.forEach(r => {
                    totalNet += Number(r.net_pay||0);
                    const d = r.dept_name || 'Unknown';
                    if (!byDept[d]) byDept[d] = 0;
                    byDept[d] += Number(r.net_pay||0);
                });
                const topDept = Object.keys(byDept).sort((a,b)=>byDept[b]-byDept[a])[0] || 'All Departments';
                if (cardDept) cardDept.textContent = topDept;
                if (cardStatus) cardStatus.textContent = period.status || '';
                const avg = data.length ? Math.round(totalNet / data.length) : 0;
                if (cardAvg) cardAvg.textContent = peso(avg);
                if (cardCutoff) cardCutoff.textContent = `${period.start_date}–${period.end_date}`;
                if (cardTotal) cardTotal.textContent = peso(totalNet);
            }

            function renderRows(rowsPage, page, pages){
                tbody.innerHTML = rowsPage.map(r=>{
                    return `<tr data-id="${r.payroll_id}">
                        <td>${r.user_name}</td>
                        <td>${peso(r.gross_pay)}</td>
                        <td>${peso(r.total_deduction)}</td>
                        <td>${peso(r.net_pay)}</td>
                        <td>${badge(r.payroll_status)}</td>
                        <td>
                            <button class="btn btn-outline-primary btn-sm" data-action="view"><i class="bi bi-eye"></i></button>
                        </td>
                    </tr>`;
                }).join('');
                if (pageInfo) pageInfo.textContent = `Showing page ${page} of ${pages}`;
                if (pager) {
                    const items = [];
                    items.push(`<li class="page-item ${page===1?'disabled':''}"><a class="page-link" href="#" data-page="prev">Previous</a></li>`);
                    const start = Math.max(1, page-2);
                    const end = Math.min(pages, start+4);
                    for (let i=start;i<=end;i++){ items.push(`<li class="page-item ${i===page?'active':''}"><a class="page-link" href="#" data-page="${i}">${i}</a></li>`); }
                    items.push(`<li class="page-item ${page===pages?'disabled':''}"><a class="page-link" href="#" data-page="next">Next</a></li>`);
                    pager.innerHTML = items.join('');
                }
            }

            function paginate(data){
                const total = data.length;
                const pages = Math.max(1, Math.ceil(total/limit));
                const start = (page-1)*limit;
                renderRows(data.slice(start, start+limit), page, pages);
            }

            function applyFilters(data){
                let filtered = data.slice();
                if (statusUi) {
                    if (statusUi === 'endorsed') filtered = filtered.filter(r=>r.payroll_status==='approved');
                    else if (statusUi === 'generated') filtered = filtered.filter(r=>r.payroll_status==='pending');
                    else filtered = filtered.filter(r=>r.payroll_status===statusUi);
                }
                if (search) {
                    const q = search.toLowerCase();
                    filtered = filtered.filter(r => (r.user_name||'').toLowerCase().includes(q) || (r.dept_name||'').toLowerCase().includes(q));
                }
                return filtered;
            }

            function fetchPeriodAndData(){
                tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted">Loading...</td></tr>';
                $.getJSON('../modules/periods.php', { action: 'list' }, function(rp){
                    const periods = rp.data || [];
                    currentPeriod = pickPeriod(periods, timeframe) || null;
                    if (!currentPeriod) { tbody.innerHTML = '<tr><td colspan="6" class="text-center text-danger">No period found</td></tr>'; return; }
                    $.getJSON('../modules/payroll.php', { action: 'list', period_id: currentPeriod.period_id }, function(rr){
                        rows = rr.data || [];
                        renderSummary(currentPeriod, rows);
                        page = 1;
                        paginate(applyFilters(rows));
                    });
                });
            }

            if (pager) {
                pager.addEventListener('click', function(e){
                    const a = e.target.closest('a.page-link'); if (!a) return; e.preventDefault();
                    const dp = a.getAttribute('data-page');
                    const totalPages = Math.max(1, Math.ceil(applyFilters(rows).length/limit));
                    if (dp==='prev') page = Math.max(1, page-1);
                    else if (dp==='next') page = Math.min(totalPages, page+1);
                    else page = parseInt(dp,10) || 1;
                    paginate(applyFilters(rows));
                });
            }

            if (tbody) {
                tbody.addEventListener('click', function(e){
                    const btn = e.target.closest('button'); if (!btn) return;
                    const tr = btn.closest('tr');
                    const id = parseInt(tr.getAttribute('data-id'),10);
                    const row = rows.find(r=>r.payroll_id===id);
                    const action = btn.getAttribute('data-action');
                    if (action==='view'){
                        const content = `
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <p><strong>Employee:</strong> ${row.user_name}</p>
                                    <p><strong>Gross Pay:</strong> ${peso(row.gross_pay)}</p>
                                    <p><strong>Deductions:</strong> ${peso(row.total_deduction)}</p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Net Pay:</strong> ${peso(row.net_pay)}</p>
                                    <p><strong>Payroll Status:</strong> ${badge(row.payroll_status)}</p>
                                    <p><strong>Cutoff Period:</strong> ${currentPeriod.start_date}–${currentPeriod.end_date}</p>
                                </div>
                            </div>`;
                        $('#modalBodyContent').html(content);
                        const m = new bootstrap.Modal(document.getElementById('payrollSummaryModal')); m.show();
                    }
                });
            }

            applyBtn.addEventListener('click', function(){
                statusUi = (statusSel && statusSel.value && statusSel.value!=='all') ? statusSel.value : '';
                timeframe = (timeSel && timeSel.value && timeSel.value!=='all') ? timeSel.value : 'month';
                fetchPeriodAndData();
            });
            searchBtn.addEventListener('click', function(){
                search = searchInput ? searchInput.value.trim() : '';
                page = 1; paginate(applyFilters(rows));
                if (searchInput) searchInput.value = '';
            });

            $.getJSON('../modules/periods.php', { action:'list' }, function(rp){
                const periods = rp.data || [];
                const last = periods.slice(-6).sort((a,b)=> new Date(a.start_date) - new Date(b.start_date));
                const labels = last.map(p=>p.start_date);
                const reqs = last.map(p=>$.getJSON('../modules/payroll.php', { action:'list', period_id:p.period_id }));
                $.when.apply($, reqs).done(function(){
                    const args = arguments.length===1 ? [arguments] : arguments;
                    const totals = [];
                    for (let i=0;i<args.length;i++){
                        const rows = args[i][0].data || [];
                        const sum = rows.reduce((acc,r)=>acc+Number(r.net_pay||0),0);
                        totals.push(sum);
                    }
                    const ctx = document.getElementById('payTrendChart');
                    if (ctx) new Chart(ctx, { type:'line', data:{ labels, datasets:[{ label:'Total Net Pay', data: totals, borderColor:'#2563EB', tension:0.25, backgroundColor:'rgba(37,99,235,0.2)', fill:true }] }, options:{ responsive:true, maintainAspectRatio:false, plugins:{ legend:{ display:false } }, scales:{ x:{ grid:{ display:false } }, y:{ ticks:{ callback:v=>peso(v) } } } } });
                });
            });

            fetchPeriodAndData();
        });
    </script>

</body>
</html>
