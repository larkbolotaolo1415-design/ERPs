<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../init.php';
$pageTitle = "Data Backup & Restore";
$userName = $_SESSION['user_name'] ?? 'User';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Backup & Restore</title>
    <link rel="stylesheet" href="../assets/css/dashboard_style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

</head>

<body>
    <?php include __DIR__ . '/../includes/layout/sidebar.php'; ?>
    <?php include __DIR__ . '/../includes/layout/topbar.php'; ?>

    <div class="main-content p-4 mt-5">

        <div class="container-fluid mt-4">
    <div class="row g-4">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body text-start">
                    <h6 class="fw-bold text-primary mb-2">Recent Backup Activities</h6>
                    <div class="d-flex align-items-center mb-3">
                      <span class="badge bg-primary me-2" id="schedStatus">Schedule: Off</span>
                      <span class="text-muted" id="schedNext">Next run: -</span>
                    </div>
                    <ul class="list-unstyled small" id="recentActivities"></ul>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="d-flex flex-column gap-3">
                <button id="createBackupBtn" class="btn btn-primary w-100 py-2 fw-semibold"><i class="bi bi-cloud-arrow-down"></i> Create Backup Now</button>
                <button id="scheduleBackupBtn" class="btn btn-outline-primary w-100 py-2 fw-semibold"><i class="bi bi-clock-history"></i> Schedule Auto-Backup</button>
            </div>
        </div>
    </div>

    <!-- Backups Table -->
    <div class="card border-0 shadow-sm mt-4">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="bg-light text-primary fw-semibold">
                    <tr>
                        <th>Date & Time Created</th>
                        <th>File Name</th>
                        <th>Size</th>
                        <th>Created By</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="backupsBody"></tbody>
            </table>
        </div>

        <!-- Pagination -->
<div class="d-flex justify-content-end p-4">
    <ul class="pagination mb-0">
        <li class="page-item"><a class="page-link text-primary" href="#">First</a></li>
        <li class="page-item"><a class="page-link text-primary" href="#"><</a></li>
        <li class="page-item"><a class="page-link text-primary" href="#">10</a></li>
        <li class="page-item"><a class="page-link text-primary" href="#">11</a></li>
        <li class="page-item"><a class="page-link text-primary" href="#">...</a></li>
        <li class="page-item"><a class="page-link text-primary" href="#">20</a></li>
        <li class="page-item"><a class="page-link text-primary" href="#">21</a></li>
        <li class="page-item"><a class="page-link text-primary" href="#">></a></li>
        <li class="page-item"><a class="page-link text-primary" href="#">Last</a></li>
    </ul>
</div>

    </div>
</div>

        
    </div>

    <script>
        // GREETING FUNCTION
        function setGreeting() {
            const hour = new Date().getHours();
            let greeting = "Good Evening";
            if (hour < 12) greeting = "Good Morning";
            else if (hour < 18) greeting = "Good Afternoon";

            const name = "<?php echo $userName ?? 'Manager'; ?>";
            document.getElementById("greeting").innerHTML = `${greeting}, <strong>${name}</strong>`;
        }
        setGreeting();

        // LOGOUT
        $("#logout-btn").click(() => {
            $.ajax({
                url: "../modules/logout.php",
                type: "POST",
                dataType: "json",
                success: function (response) {
                    if (response.status === "success") {
                        window.location.href = "login_page.php";
                    } else {
                        console.log(response.message);
                    }
                },
                error: (xhr, status, error) => {
                    console.log("Server error:", error);
                }
            });
        });

        // NAVIGATION

        // NAVIGATION
        $(document).ready(function () {
            $('#dashboard-btn').on('click', function () {
                window.location.href = 'admin_dashboard.php';
            });
            
            $('#user-management-btn').on('click', function () {
                window.location.href = 'admin_user_management.php';
            });

            $('#role-management-btn').on('click', function () {
                window.location.href = 'admin_role_management.php';
            });

            $('#company-settings-btn').on('click', function () {
                window.location.href = 'admin_company_settings.php';
            });

            $('#system-configuration-btn').on('click', function () {
                window.location.href = 'admin_system_configuration.php';
            });

            $('#data-backup-and-restore-btn').on('click', function () {
                window.location.href = 'admin_data_backup_and_restore.php';
            });

            $('#audit-logs-btn').on('click', function () {
                window.location.href = 'admin_audit_logs.php';
            });

            $('#summary-report-btn').on('click', function () {
                window.location.href = 'admin_summary_report.php';
            });

            $('#security-controls-btn').on('click', function () {
                window.location.href = 'admin_security_controls.php';
            });
        });

        // CHARTS
        const salaryEl = document.getElementById('salaryChart');
        if (salaryEl) {
            const ctxSalary = salaryEl.getContext('2d');
            new Chart(ctxSalary, {
                type: 'bar',
                data: {
                    labels: ['HR', 'IT', 'Finance', 'Admin', 'Marketing', 'Operations'],
                    datasets: [{
                        label: 'Average Salary (₱)',
                        data: [48000, 62000, 55000, 50000, 47000, 53000],
                        backgroundColor: 'rgba(37, 99, 235, 0.7)',
                        borderColor: '#2563EB',
                        borderWidth: 1,
                        borderRadius: 5
                    }]
                },
                options: {
                    scales: {
                        y: { beginAtZero: true }
                    }
                }
            });
        }

        const overtimeEl = document.getElementById('overtimeChart');
        if (overtimeEl) {
            const ctxOvertime = overtimeEl.getContext('2d');
            new Chart(ctxOvertime, {
                type: 'doughnut',
                data: {
                    labels: ['HR', 'IT', 'Finance', 'Marketing'],
                    datasets: [{
                        data: [20, 35, 25, 20],
                        backgroundColor: ['#2563EB', '#3B82F6', '#60A5FA', '#93C5FD'],
                        borderWidth: 0
                    }]
                },
                options: {
                    plugins: { legend: { position: 'bottom' } }
                }
            });
        }
    </script>
    <div class="modal fade" id="scheduleModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">Auto-Backup Schedule</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
          <div class="form-check form-switch mb-3">
            <input class="form-check-input" type="checkbox" id="schedEnabled">
            <label class="form-check-label" for="schedEnabled">Enable schedule</label>
          </div>
          <div class="mb-3">
            <label class="form-label">Frequency</label>
            <select class="form-select" id="schedFrequency">
              <option value="hourly">Hourly</option>
              <option value="daily">Daily</option>
              <option value="weekly">Weekly</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Run time</label>
            <input type="time" class="form-control" id="schedRunTime" value="22:00">
          </div>
          <div class="d-flex justify-content-between small">
            <span id="schedLastRun">Last run: -</span>
            <span id="schedNextRun">Next run: -</span>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
          <button id="runScheduledBtn" class="btn btn-secondary">Run Now</button>
          <button id="scheduleSaveBtn" class="btn btn-primary">Save</button>
        </div>
      </div></div>
    </div>
    <script>
      $(function(){
        const schedModal = new bootstrap.Modal(document.getElementById('scheduleModal'));
        $('#scheduleBackupBtn').on('click', function(){
          $.ajax({ url: '../modules/backup.php', method: 'GET', data: { action: 'schedule_get' }, dataType: 'json' })
            .done(function(res){
              const s = res.data||{};
              $('#schedEnabled').prop('checked', (parseInt(s.enabled||0,10)===1));
              $('#schedFrequency').val(s.frequency||'daily');
              $('#schedRunTime').val(s.run_time||'22:00');
              $('#schedLastRun').text('Last run: ' + (s.last_run||'-'));
              $('#schedNextRun').text('Next run: ' + (s.next_run||'-'));
              schedModal.show();
            });
        });
        $('#scheduleSaveBtn').on('click', function(){
          const payload = {
            action: 'schedule_set',
            enabled: $('#schedEnabled').is(':checked') ? 1 : 0,
            frequency: $('#schedFrequency').val(),
            run_time: $('#schedRunTime').val()
          };
          $.post('../modules/backup.php', payload, function(res){
            if(res.status==='success'){
              schedModal.hide();
              loadBackups();
            } else {
              alert(res.message||'Save failed');
            }
          }, 'json');
        });
        $('#runScheduledBtn').on('click', function(){
          $.ajax({ url: '../modules/backup.php', method: 'GET', data: { action: 'run_scheduled' }, dataType: 'json' })
            .done(function(res){
              if(res.ran){ alert('Backup created: ' + (res.file_name||'')); loadBackups(); }
              else { alert('No backup due. Next: ' + (res.next_run||'-')); }
            });
        });
      });
    </script>
    <script>
      $(function(){
        function fmtSize(bytes){ if (bytes >= 1048576) return (bytes/1048576).toFixed(1) + ' MB'; if (bytes >= 1024) return (bytes/1024).toFixed(1) + ' KB'; return bytes + ' B'; }
        function renderScheduleMeta(s){ const on = s && parseInt(s.enabled||0,10)===1; const status=document.getElementById('schedStatus'); const next=document.getElementById('schedNext'); if(status) status.textContent = 'Schedule: ' + (on ? 'On' : 'Off'); if(next) next.textContent = 'Next run: ' + (s && s.next_run ? s.next_run : '-'); }
        function renderRecent(rows, s){ const ul=document.getElementById('recentActivities'); if(!ul) return; ul.innerHTML=''; const items=[]; if(s && s.last_run){ items.push('Auto backup ran at ' + s.last_run); } const recent = (rows||[]).slice(0,3); recent.forEach(r=>{ items.push('Backup created ' + r.created_at + ' (' + (r.file_name||'') + ')'); }); if(items.length===0){ ul.innerHTML = '<li class="text-muted">No recent activity</li>'; return; } items.forEach(t=>{ const li=document.createElement('li'); li.className='mb-2'; li.textContent=t; ul.appendChild(li); }); }
        function renderBackups(rows){ const tbody=document.getElementById('backupsBody'); if(!tbody) return; tbody.innerHTML=''; rows.forEach(r=>{ const tr=document.createElement('tr'); tr.innerHTML = `<td><a href="#" class="text-primary text-decoration-none">${r.created_at}</a></td><td>${r.file_name}</td><td>${fmtSize(parseInt(r.size_bytes||0))}</td><td>${r.user_name||''}</td><td><a href="#" class="text-primary text-decoration-none me-3" data-id="${r.backup_id}" data-act="download"><i class="bi bi-download"></i> Download</a><a href="#" class="text-danger text-decoration-none" data-id="${r.backup_id}" data-act="delete"><i class="bi bi-trash"></i> Delete</a></td>`; tbody.appendChild(tr); }); if(rows.length===0) tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted">No backups</td></tr>'; }
        function loadBackups(){ $.ajax({ url: '../modules/backup.php', method: 'GET', data: { action: 'list' }, dataType: 'json' }).done(function(res){ renderBackups(res.data||[]); renderScheduleMeta(res.schedule||{}); renderRecent(res.data||[], res.schedule||{}); }); }
        loadBackups();
        $('#createBackupBtn').on('click', function(){ $.ajax({ url: '../modules/backup.php', method: 'GET', data: { action: 'export' }, dataType: 'json' }).done(function(){ loadBackups(); }); });
        document.getElementById('backupsBody').addEventListener('click', function(e){ const a=e.target.closest('a[data-act]'); if(!a) return; e.preventDefault(); const idAttr=a.getAttribute('data-id'); const id=parseInt(idAttr||'0',10); const act=a.getAttribute('data-act'); if(!id||isNaN(id)){ alert('Invalid backup item'); return; } if(act==='download'){ $.ajax({ url: '../modules/backup.php', method: 'GET', data: { action: 'download', backup_id: id }, dataType: 'json' }).done(function(res){ const blob=new Blob([res.data||''],{type:'application/sql'}); const url=URL.createObjectURL(blob); const link=document.createElement('a'); link.href=url; link.download=res.file_name||('backup_'+id+'.sql'); document.body.appendChild(link); link.click(); document.body.removeChild(link); URL.revokeObjectURL(url); }); } else if(act==='delete'){ if(!confirm('Delete this backup?')) return; $.post('../modules/backup.php', { action: 'delete', backup_id: id }, function(res){ if(res.status==='success'){ loadBackups(); } else { alert(res.message||'Delete failed'); } }, 'json'); } });
        window.loadBackups = loadBackups;
      });
    </script>
</body>

</html>
