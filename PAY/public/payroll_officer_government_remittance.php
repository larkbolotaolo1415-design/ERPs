<!-- Copy Paste This php block -->
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../init.php';
$pageTitle = "Government Remittance Reports"; // Change to correct title 
$userName = $_SESSION['user_name'] ?? 'User';
?>     


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payroll Officer | Government Remittance Reports</title>
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
        <div class="d-flex align-items-center mb-3 filter-row" style="gap: 10px;">
            <!-- Date Picker -->
            <b>Payroll Period:</b>
            <input type="date" id="dateFilter" class="form-control"
                style="width: 180px; background-color: #93C5FD; color: #2563EB; border: none;">

            <!-- Remittance Dropdown -->
            <b>Remittance Type:</b>
            <select id="remitType" class="form-select"
                style="width: 120px; background-color: #93C5FD; color: #2563EB; border: none;">
                <option value="sss" selected>SSS</option>
                <option value="philhealth">PhilHealth</option>
                <option value="pagibig">Pag-IBIG</option>
                <option value="bir">BIR</option>
            </select>

            <!-- Name Search -->
            <select id="statusFilter" class="form-select"
                style="width: 120px; background-color: #93C5FD; color: #2563EB; border: none;">
                <option value="all" selected>All</option>
                <option value="pending">Pending</option>
                <option value="approved">Approved</option>
                <option value="locked">Locked</option>
            </select>

            <button id="applyBtn" class="btn btn-primary me-auto">Apply</button>


            <div class="d-flex align-items-center gap-2">
                <input type="text" id="searchInput" class="form-control" placeholder="Search..."
                    style="width: 300px; border-color: #2563EB;">
                <button id="searchButton" class="btn btn-primary fw-semibold"
                    style="background-color: #2563EB; border: none;">
                    Search
                </button>
            </div>
        </div>

        <div class="card p-3" style="border: 1px solid #2563EB;">
            <!-- Employee Table -->
            <div class="table-responsive">
                <table class="table table-striped align-middle">
                    <thead>
                        <tr>
                            <th>Emp ID</th>
                            <th>Emp Name</th>
                            <th>Emp. Share</th>
                            <th>Er. Share</th>
                            <th>Period</th>
                            <th>Total</th>
                            <th>Remittance Status</th>
                            <th>Ref. No.</th>
                        </tr>
                    </thead>
                    <tbody id="remitTbody"></tbody>
                </table>
            </div>
        </div>

        <div class="tab-content" id="employeelistTabsContent"></div>
        <!-- Filters and Search -->
        <div class="tab-pane fade show active" id="employeelist" role="tabpanel" aria-labelledby="employee-list-tab">

            <!-- Bottom Actions and Pagination -->
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div class="d-flex flex-column">
                    <div class="mb-2" style="color: #5B5757;" id="remitShowing"></div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-outline-primary">Print All</button>
                        <button class="btn btn-outline-primary">Export to PDF</button>
                    </div>
                </div>

                <nav aria-label="Page navigation">
                    <ul class="pagination mb-0" id="remitPager">
                        <li class="page-item disabled"><a class="page-link" href="#">Previous</a></li>
                        <li class="page-item active"><a class="page-link" href="#">1</a></li>
                        <li class="page-item"><a class="page-link" href="#">2</a></li>
                        <li class="page-item"><a class="page-link" href="#">3</a></li>
                        <li class="page-item"><a class="page-link" href="#">Next</a></li>
                    </ul>
                </nav>
            </div>
        </div>

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

                const attendanceDonutEl = document.querySelector('.daily-attendance-chart'); if (!attendanceDonutEl) return; const attendanceDonutCtx = attendanceDonutEl.getContext('2d');

                new Chart(attendanceDonutCtx, {
                    type: 'doughnut',
                    data: {
                        labels: ['On Duty', 'Late Arrivals', 'Absent'],
                        datasets: [{
                            data: [70, 15, 15],
                            backgroundColor: ['#0d6efd', '#ffc107', '#dc3545'],
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
                                color: '#000',
                                anchor: 'end',
                                align: 'end',
                                offset: 4,
                                formatter: (v, ctx) => {
                                    const sum = ctx.chart.data.datasets[0].data.reduce((sum, value) => sum + value, 0);
                                    const pct = (v * 100 / sum).toFixed(0) + '%';
                                    return ctx.chart.data.labels[ctx.dataIndex] + ' ' + pct;
                                },
                                font: { weight: 'bold', size: 12 },
                            }
                        }
                    },
                    plugins: [ChartDataLabels]
                });

                const attendanceTrendEl = document.getElementById('attendanceTrendChart'); if (!attendanceTrendEl) return; const attendanceTrendCtx = attendanceTrendEl.getContext('2d');

                const days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
                const onDuty = [35, 40, 38, 41, 39, 25, 15];
                const lateArrivals = [5, 3, 7, 4, 6, 5, 2];
                const absent = [5, 7, 5, 5, 6, 5, 8];

                new Chart(attendanceTrendCtx, {
                    type: 'line',
                    data: {
                        labels: days,
                        datasets: [
                            {
                                label: 'On Duty',
                                data: onDuty,
                                borderColor: '#0d6efd',
                                backgroundColor: 'rgba(13,110,253,.1)',
                                tension: 0.3,
                                fill: false
                            },
                            {
                                label: 'Late Arrivals',
                                data: lateArrivals,
                                borderColor: '#ffc107',
                                backgroundColor: 'rgba(255,193,7,.1)',
                                tension: 0.3,
                                fill: false
                            },
                            {
                                label: 'Absent',
                                data: absent,
                                borderColor: '#dc3545',
                                backgroundColor: 'rgba(220,53,69,.1)',
                                tension: 0.3,
                                fill: false
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        layout: { padding: 20 },
                        plugins: {
                            legend: { position: 'bottom', labels: { boxWidth: 12, padding: 15, font: { size: 11 } } },
                            datalabels: { display: false }
                        },
                        scales: {
                            x: { grid: { display: false } },
                            y: { ticks: { callback: v => v } }
                        }
                    }
                });

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
        <script>
            $(function(){
                function peso(n){ return '₱' + (parseInt(n||0)).toLocaleString(); }
                const tbody = document.getElementById('remitTbody');
                const btnApply = $('#applyBtn');
                const btnSearch = $('#searchButton');
                const inputDate = $('#dateFilter');
                const selType = $('#remitType');
                const selStatus = $('#statusFilter');
                const inputSearch = $('#searchInput');
                const pager = $('#remitPager');
                const showing = $('#remitShowing');
                let rowsCache = [];
                let filtered = [];
                let page = 1;
                const limit = 10;
                function setLoading(v){
                    btnApply.prop('disabled', v);
                    btnSearch.prop('disabled', v);
                    inputDate.prop('disabled', v);
                    selType.prop('disabled', v);
                    selStatus.prop('disabled', v);
                    inputSearch.prop('disabled', v);
                    if (tbody){
                        tbody.innerHTML = '<tr><td colspan="8" class="text-center"><div class="spinner-border me-2" role="status"></div>Loading...</td></tr>';
                    }
                }
                function renderPager(total){
                    const pages = Math.max(1, Math.ceil(total/limit));
                    const items = [];
                    items.push(`<li class="page-item ${page===1?'disabled':''}"><a class="page-link" href="#" data-page="prev">Previous</a></li>`);
                    const startIdx = Math.max(1, page-2);
                    const endIdx = Math.min(pages, startIdx+4);
                    for (let i=startIdx;i<=endIdx;i++){ items.push(`<li class="page-item ${i===page?'active':''}"><a class="page-link" href="#" data-page="${i}">${i}</a></li>`); }
                    items.push(`<li class="page-item ${page===pages?'disabled':''}"><a class="page-link" href="#" data-page="next">Next</a></li>`);
                    pager.html(items.join(''));
                    showing.text(`Showing Page ${page} of ${pages}`);
                }
                function renderRows(){
                    if (tbody) tbody.innerHTML = '';
                    const start = (page-1)*limit;
                    const slice = filtered.slice(start, start+limit);
                    if (slice.length===0){ if (tbody) tbody.innerHTML = '<tr><td colspan="8" class="text-center">No records available</td></tr>'; return; }
                    const t = selType.val()||'sss';
                    slice.forEach(r => {
                        let amt = 0;
                        if (t==='sss') amt = parseInt(r.sss||0);
                        else if (t==='philhealth') amt = parseInt(r.philhealth||0);
                        else if (t==='pagibig') amt = parseInt(r.pag_ibig||0);
                        else if (t==='bir') amt = parseInt(r.tax||0);
                        const statusClass = r.payroll_status==='locked' ? 'bg-success' : (r.payroll_status==='approved' ? 'bg-primary' : 'bg-warning');
                        const tr = document.createElement('tr');
                        tr.innerHTML = `<td>${r.emp_id}</td><td>${r.user_name||''}</td><td>${peso(amt)}</td><td>-</td><td>${r.period_start_date||''} - ${r.period_end_date||''}</td><td>${peso(amt)}</td><td><span class="badge status-badge ${statusClass}">${r.payroll_status}</span></td><td>${r.payroll_id}</td>`;
                        tbody.appendChild(tr);
                    });
                }
                function load(){
                    setLoading(true);
                    $.getJSON('../modules/periods.php', { action: 'list' }, function(rp){
                        const periods = rp.data || [];
                        const d = inputDate.val();
                        let cur = null;
                        if (d){ cur = periods.find(p => p.start_date <= d && p.end_date >= d); }
                        if (!cur) cur = periods.find(p=>p.status==='processing') || periods.find(p=>p.status==='open') || periods[0];
                        if (!cur){ if (tbody) tbody.innerHTML = ''; setLoading(false); return; }
                        $.getJSON('../modules/payroll.php', { action: 'list', period_id: cur.period_id }, function(pr){
                            rowsCache = (pr.data || []).map(r => ({...r, period_start_date: cur.start_date, period_end_date: cur.end_date}));
                            const q = (inputSearch.val()||'').toLowerCase();
                            const st = (selStatus.val()||'all').toLowerCase();
                            filtered = rowsCache.filter(r => {
                                const hay = [r.user_name,r.emp_id,r.payroll_id,r.payroll_status,r.dept_name,r.pos_name].map(x=> String(x||'').toLowerCase()).join(' ');
                                const okQ = !q || hay.includes(q);
                                const okS = st==='all' || String(r.payroll_status||'').toLowerCase()===st;
                                return okQ && okS;
                            });
                            page = 1;
                            renderPager(filtered.length);
                            renderRows();
                            setLoading(false);
                        }).fail(function(){ if (tbody) tbody.innerHTML=''; setLoading(false); });
                    }).fail(function(){ if (tbody) tbody.innerHTML=''; setLoading(false); });
                }
                $('#applyBtn').on('click', load);
                $('#searchButton').on('click', load);
                $('#searchInput').on('keyup', function(e){ if (e.key==='Enter') load(); });
                $('#remitPager').on('click', 'a.page-link', function(e){
                    e.preventDefault();
                    const val = $(this).attr('data-page');
                    const pages = Math.max(1, Math.ceil(filtered.length/limit));
                    if (val==='prev') page = Math.max(1, page-1);
                    else if (val==='next') page = Math.min(pages, page+1);
                    else page = parseInt(val,10);
                    renderPager(filtered.length);
                    renderRows();
                });
                load();
            });
        </script>
</body>

</html>
