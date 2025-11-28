<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../init.php';
$pageTitle = "Dashboard"; 
$userName = $_SESSION['user_name'] ?? 'User';
?>     

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manager Dashboard</title>
    <link rel="stylesheet" href="../assets/css/dashboard_style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        :root {
            --accent: #2563EB;
            --white: #ffffff;
        }

        .main-content {
            margin-top: 90px;
            padding: 20px;
        }

        /* Summary Cards */
        .summary-card {
            background-color: var(--accent);
            color: var(--white);
            border: none;
            height: 150px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            border-radius: 10px;
        }

        .summary-card h6 {
            font-weight: 600;
        }

        .summary-card h3,
        .summary-card p {
            margin: 0;
        }

        /* Trend Card */
        .trend-card {
            background-color: var(--accent);
            color: var(--white);
        }

        .trend-select {
            background-color: #93C5FD;
            color: #2563EB;
            border: none;
            font-weight: 500;
        }

        /* Graph Placeholder */
        .graph-placeholder {
            background-color: var(--white);
            color: #2563EB;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 500;
        }

        /* Graph Legend Badges inside Trend Card */
        .legend-badge {
            background-color: #ffffff; 
            padding: 6px 10px;
            font-weight: 500;
            margin-right: 8px;
            margin-bottom: 5px;
            display: inline-block;
            border-radius: 5px;
        }

        /* Notification and Status Legends Card */
        .legend-card {
            background-color: #ffffff; 
            border-radius: 10px;
            padding: 15px;
            margin-top: 15px;
        }

        .legend-card h6 {
            color: #2563EB; 
            font-weight: 600;
        }

        .legend-card p {
            color: #2563EB; 
            font-weight: 500;
            margin-bottom: 5px;
        }

        /* Notification Panel */
        .notification-panel {
            background-color: #ffffff; 
            border: 1px solid var(--accent); 
            border-radius: 10px;
        }

        .notification-panel .card-header {
            color: #2D3436; 
            font-weight: 600;
        }

        .notification-panel .list-group-item {
            text-align: left;
            color: var(--accent); 
            background-color: #ffffff; 
            font-weight: 500;
        }

        /* Right Panel Box Width */
        .quick-links-container {
            width: 350px;
        }

        /* Quick Links Buttons */
        .quick-links button {
            border-color: var(--accent);
            color: var(--accent);
            font-weight: 500;
        }

        .quick-links button:hover {
            background-color: var(--accent);
            color: var(--white);
        }
    </style>
</head>

<body>
    <?php include __DIR__ . '/../includes/layout/sidebar.php'; ?>
    <?php include __DIR__ . '/../includes/layout/topbar.php'; ?>

    <div class="main-content p-5 mt-5">

        <!-- Summary Cards -->
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card summary-card shadow-sm">
                    <h6>Pending Approvals</h6>
                    <h3 id="pendingApprovalsCount">0</h3>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card summary-card shadow-sm">
                    <h6>Attendance Today</h6>
                    <p>Present: <span id="attendancePresentCount">0</span></p>
                    <p>Absent: <span id="attendanceAbsentCount">0</span></p>
                    <p>On Leave: <span id="attendanceLeaveCount">0</span></p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card summary-card shadow-sm">
                    <h6>Department Payroll Status</h6>
                    <h3 id="departmentPayrollStatusText">—</h3>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card summary-card shadow-sm">
                    <h6>Cutoff Period</h6>
                    <h3 id="cutoffPeriodText">—</h3>
                </div>
            </div>
        </div>

        <div class="d-flex align-items-start">

            <!-- Left Section -->
            <div class="flex-fill me-4">

                <!-- Attendance Trend -->
                <div class="card trend-card shadow-sm mb-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h5 class="card-title mb-0">Attendance Trend</h5>
                            <select class="form-select form-select-sm w-auto trend-select">
                                <option>Last 5 days</option>
                                <option>Last 7 days</option>
                                <option>Last 30 days</option>
                            </select>
                        </div>

                        <div class="graph-placeholder text-center py-3">
                            <canvas id="attendanceTrend" height="140"></canvas>
                        </div>

                        <div class="legend mt-3">
                            <span class="badge legend-badge text-success border-success">
                                <i class="bi bi-circle-fill me-1 text-success"></i> Present
                            </span>
                            <span class="badge legend-badge text-warning border-warning">
                                <i class="bi bi-circle-fill me-1 text-warning"></i> Late
                            </span>
                            <span class="badge legend-badge text-danger border-danger">
                                <i class="bi bi-circle-fill me-1 text-danger"></i> Absent
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Legend Box -->
                <div class="card shadow-sm p-3">
                    <h6 class="fw-bold accent-title mb-2">Notification and Status Legends</h6>
                    <p class="mb-1">
                        <i class="bi bi-circle-fill text-success"></i> Approved &nbsp;
                        <i class="bi bi-circle-fill text-warning"></i> Pending &nbsp;
                        <i class="bi bi-circle-fill text-danger"></i> Overdue
                    </p>
                    <p class="mb-0">
                        <i class="bi bi-clock-history text-primary"></i> Attendance &nbsp;
                        <i class="bi bi-cash-coin text-success"></i> Payroll &nbsp;
                        <i class="bi bi-envelope-paper text-info"></i> Requests
                    </p>
                </div>

            </div>

            <!-- Right Section -->
            <div class="quick-links-container">

                <!-- Notification Panel -->
                <div class="notification-panel card shadow-sm mb-3">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span>Notifications</span>
                        <button
                            class="btn btn-sm btn-outline-secondary notif-toggle-btn"
                            type="button"
                            data-bs-toggle="collapse"
                            data-bs-target="#notifContent"
                            aria-expanded="true">
                            <i class="bi bi-chevron-up"></i>
                        </button>
                    </div>

                    <div class="collapse show" id="notifContent">
                        <div class="card-body">
                            <ul id="notifList" class="list-group list-group-flush text-start"></ul>
                        </div>
                    </div>
                </div>

                <!-- Quick Links -->
                <div class="quick-links">
                    <button id="approveRequestsBtn" class="btn btn-outline-primary w-100 mb-2">Approve Requests</button>
                    <button id="departmentAttendanceBtn" class="btn btn-outline-primary w-100 mb-2">Department Attendance</button>
                    <button id="payrollSummaryBtn" class="btn btn-outline-primary w-100 mb-2">Payroll Summary</button>
                    <button id="generateReportsBtn" class="btn btn-outline-primary w-100">Generate Reports</button>
                </div>
            </div>
        </div>
    </div> <!-- end main-content -->

    <script>
        let employeesById = {};
        let totalEmployees = 0;
        let attendanceChart = null;

        function setGreeting() {
            const hour = new Date().getHours();
            let greeting = "Good Evening";
            if (hour < 12) greeting = "Good Morning";
            else if (hour < 18) greeting = "Good Afternoon";
            const greetingElement = document.getElementById("greeting");
            if (greetingElement) {
                greetingElement.innerHTML = `${greeting}, <strong><?php echo $userName ?? 'Manager'; ?></strong>`;
            }
        }
        setGreeting();

        document.addEventListener("DOMContentLoaded", () => {
            const toggleBtn = document.querySelector('.notif-toggle-btn');
            const icon = toggleBtn.querySelector("i");
            const content = document.querySelector("#notifContent");
            content.addEventListener("shown.bs.collapse", () => { icon.classList.replace("bi-chevron-down", "bi-chevron-up"); });
            content.addEventListener("hidden.bs.collapse", () => { icon.classList.replace("bi-chevron-up", "bi-chevron-down"); });
            $('#approveRequestsBtn').on('click', function(){ window.location.href = 'manager_approval_page.php'; });
            $('#departmentAttendanceBtn').on('click', function(){ window.location.href = 'payroll_officer_attendance_management.php'; });
            $('#payrollSummaryBtn').on('click', function(){ window.location.href = 'manager_payroll_summary_generation.php'; });
            $('#generateReportsBtn').on('click', function(){ window.location.href = 'manager_report_generation.php'; });
            initDashboard();
        });

        function initDashboard() {
            $.getJSON('../modules/employees.php', { action: 'list', status: 'active' }, function(resp) {
                const rows = resp.data || [];
                totalEmployees = rows.length;
                employeesById = {};
                rows.forEach(r => { employeesById[r.emp_id] = r; });
                updateSummaryCards();
                updatePeriodInfo();
                loadNotifications();
                const sel = document.querySelector('.trend-select');
                const n = parseInt((sel && sel.value.match(/\d+/)) ? sel.value.match(/\d+/)[0] : '5', 10);
                buildAttendanceTrend(n);
                if (sel) sel.addEventListener('change', function(){
                    const days = parseInt(this.value.match(/\d+/)[0], 10);
                    buildAttendanceTrend(days);
                });
            });
        }

        function todayStr() { const d = new Date(); return d.toISOString().slice(0,10); }

        function updateSummaryCards() {
            const t = todayStr();
            $.when(
                $.getJSON('../modules/attendance.php', { action: 'list', date_from: t, date_to: t }),
                $.getJSON('../modules/requests.php', { action: 'list', type: 'leave' }),
                $.getJSON('../modules/requests.php', { action: 'list', type: 'overtime' }),
                $.getJSON('../modules/requests.php', { action: 'list', type: 'bonus' })
            ).done(function(attRes, leaveRes, otRes, bonusRes){
                const attRows = (attRes[0].data||[]);
                const presentSet = new Set();
                let lateCount = 0;
                attRows.forEach(r => { if (r.emp_id) presentSet.add(r.emp_id); if ((r.time_in||'') > '09:00:00') lateCount++; });
                const leaveRows = (leaveRes[0].data||[]).filter(r => r.status==='approved' && r.date_from && r.date_to && t >= r.date_from && t <= r.date_to);
                const onLeaveSet = new Set(leaveRows.map(r => r.emp_id));
                const present = presentSet.size;
                const onLeave = onLeaveSet.size;
                const absent = Math.max(0, totalEmployees - present - onLeave);
                $('#attendancePresentCount').text(present);
                $('#attendanceAbsentCount').text(absent);
                $('#attendanceLeaveCount').text(onLeave);
                const pendingCount = ((leaveRes[0].data||[]).filter(r=>r.status==='pending').length)
                    + ((otRes[0].data||[]).filter(r=>r.status==='pending').length)
                    + ((bonusRes[0].data||[]).filter(r=>r.status==='pending').length);
                $('#pendingApprovalsCount').text(pendingCount);
            });
        }

        function updatePeriodInfo() {
            $.getJSON('../modules/periods.php', { action: 'list' }, function(resp){
                const rows = resp.data || [];
                const latest = rows[0] || null;
                if (!latest) return;
                const s = latest.start_date;
                const e = latest.end_date;
                const status = latest.status || '';
                $('#cutoffPeriodText').text(formatRange(s, e));
                $('#departmentPayrollStatusText').text(statusLabel(status));
            });
        }

        function formatRange(s, e) {
            const sd = new Date(s), ed = new Date(e);
            const opts = { month: 'short', day: 'numeric' };
            const sm = sd.toLocaleDateString(undefined, opts);
            const em = ed.toLocaleDateString(undefined, opts);
            return sm + '–' + em;
        }

        function statusLabel(s) {
            if (s==='open') return 'Open';
            if (s==='processing') return 'Processing';
            if (s==='locked') return 'Locked';
            if (s==='archived') return 'Archived';
            return s||'—';
        }

        function buildAttendanceTrend(days) {
            const end = new Date();
            const start = new Date();
            start.setDate(end.getDate() - (days-1));
            const dateList = [];
            const ymd = d => d.toISOString().slice(0,10);
            for (let d = new Date(start); d <= end; d.setDate(d.getDate()+1)) { dateList.push(ymd(new Date(d))); }
            $.when(
                $.getJSON('../modules/attendance.php', { action: 'list', date_from: ymd(start), date_to: ymd(end) }),
                $.getJSON('../modules/requests.php', { action: 'list', type: 'leave' })
            ).done(function(attRes, leaveRes){
                const attRows = attRes[0].data||[];
                const leaveRowsAll = leaveRes[0].data||[];
                const presentPerDay = [];
                const latePerDay = [];
                const absentPerDay = [];
                dateList.forEach(dt => {
                    const attForDay = attRows.filter(r => r.date === dt);
                    const presentSet = new Set(attForDay.map(r => r.emp_id));
                    const late = attForDay.filter(r => (r.time_in||'') > '09:00:00').length;
                    const leavesForDay = leaveRowsAll.filter(r => r.status==='approved' && r.date_from && r.date_to && dt >= r.date_from && dt <= r.date_to);
                    const onLeaveSet = new Set(leavesForDay.map(r => r.emp_id));
                    const present = presentSet.size;
                    const onLeave = onLeaveSet.size;
                    const absent = Math.max(0, totalEmployees - present - onLeave);
                    presentPerDay.push(present);
                    latePerDay.push(late);
                    absentPerDay.push(absent);
                });
                renderTrendChart(dateList, presentPerDay, latePerDay, absentPerDay);
            });
        }

        function renderTrendChart(labels, presentData, lateData, absentData) {
            const ctx = document.getElementById('attendanceTrend');
            if (!ctx) return;
            if (attendanceChart) { attendanceChart.destroy(); }
            attendanceChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [
                        { label: 'Present', data: presentData, borderColor: '#198754', backgroundColor: 'rgba(25,135,84,.1)', tension: 0.3, fill: false },
                        { label: 'Late', data: lateData, borderColor: '#ffc107', backgroundColor: 'rgba(255,193,7,.1)', tension: 0.3, fill: false },
                        { label: 'Absent', data: absentData, borderColor: '#dc3545', backgroundColor: 'rgba(220,53,69,.1)', tension: 0.3, fill: false }
                    ]
                },
                options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true } } }
            });
        }

        function loadNotifications() {
            $.when(
                $.getJSON('../modules/requests.php', { action: 'list', type: 'leave' }),
                $.getJSON('../modules/requests.php', { action: 'list', type: 'overtime' }),
                $.getJSON('../modules/requests.php', { action: 'list', type: 'bonus' })
            ).done(function(leaveRes, otRes, bonusRes){
                const items = [];
                const addItem = (text) => { items.push(text); };
                const leaves = (leaveRes[0].data||[]).filter(r=>r.status==='pending');
                leaves.forEach(r => { const emp = employeesById[r.emp_id]; addItem('Leave request from ' + (emp?emp.user_name:('Employee '+r.emp_id))); });
                const ots = (otRes[0].data||[]).filter(r=>r.status==='pending');
                ots.forEach(r => { const emp = employeesById[r.emp_id]; addItem('Overtime request pending approval' + (emp?(' by '+emp.user_name):'')); });
                const bonuses = (bonusRes[0].data||[]).filter(r=>r.status==='pending');
                bonuses.forEach(r => { const emp = employeesById[r.emp_id]; addItem('Bonus/Adjustment request pending approval' + (emp?(' for '+emp.user_name):'')); });
                const list = $('#notifList'); list.empty();
                items.slice(0,5).forEach(txt => { list.append('<li class="list-group-item">'+txt+'</li>'); });
            });
        }
    </script>

</body>
</html>
