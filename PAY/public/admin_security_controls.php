<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../init.php';
$pageTitle = "Security Controls";
$userName = $_SESSION['user_name'] ?? 'User';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Security Controls</title>
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


  #user-avatar {
    width: 50px;
    height: 50px;
  }


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

  /* Top Cards */
  .card-section {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
    margin-bottom: 2rem;
  }

  .role-card {
    flex: 1;
    background: var(--accent);
    color: white;
    border-radius: 12px;
    padding: 25px;
    min-width: 260px;
    text-align: center;
  }

  .override-card,
  .button-card {
    flex: 1;
    background: white;
    border-radius: 12px;
    padding: 25px;
    min-width: 260px;
    box-shadow: 0 0 8px rgba(0, 0, 0, 0.05);
  }

  .override-card h6,
  .button-card h6 {
    font-weight: 700;
    color: var(--accent);
    margin-bottom: 1rem;
  }

  .btn-outline-primary {
    color: var(--accent);
    border-color: var(--accent);
  }

  .btn-outline-primary:hover {
    background-color: var(--accent);
    color: white;
  }

  .btn-primary {
    background-color: var(--accent);
    border-color: var(--accent);
  }

  .btn-action {
    border-radius: 25px;
    font-weight: 500;
    padding: 0.6rem 1rem;
  }

  /* Table Styles */
  .permissions-table {
    width: 100%;
    table-layout: fixed;
  }

  .permissions-table th,
  .permissions-table td {
    vertical-align: middle;
    text-align: center;
    padding: 0.75rem;
  }

  .permissions-table th:first-child,
  .permissions-table td:first-child {
    width: 30%;
    text-align: left;
  }

  .permissions-table thead th {
    background: #f3f6fa;
    color: var(--accent);
    font-weight: 700;
  }

  .table-section-header {
    background: #f3f6fa;
    font-weight: 600;
    color: var(--accent);
  }

  /* Switch Styles */
  .form-switch .form-check-input {
    width: 3em;
    height: 1.5em;
    cursor: pointer;
  }

  .form-check-input:checked {
    background-color: var(--accent);
    border-color: var(--accent);
  }

  /* Responsive tweaks kept */
  @media (max-width: 992px) {
    .main-content {
      margin-left: 0;
    }

    .card-section {
      flex-direction: column;
    }

    .permissions-table {
      table-layout: auto;
    }
  }

  .quick-links button {
    border-color: var(--accent);
    color: var(--accent);
    font-weight: 500;
  }

  .quick-links button:hover {
    background-color: var(--accent);
    color: var(--white);
  }

  .card-header {
    background-color: var(--accent) !important;
    color: var(--white) !important;
    font-weight: 600;
  }

  .text-accent {
    color: var(--accent) !important;
  }
</style>

</head>

<body>
    <?php include __DIR__ . '/../includes/layout/sidebar.php'; ?>
    <?php include __DIR__ . '/../includes/layout/topbar.php'; ?>

  <div class="main-content p-4 mt-5">

 <!-- Password Policy Settings -->
<div class="card shadow-sm border-0 mb-4">
  <div class="card-body">
    <h5 class="fw-bold text-primary mb-4">
      <i class="bi bi-shield-lock me-2"></i>Password Policy Settings
    </h5>

    <form class="px-2">
      <!-- Row 1: Minimum Length -->
      <div class="d-flex align-items-center mb-4">
        <label class="form-label fw-semibold me-3 mb-0" style="width: 160px;">Minimum Length:</label>
        <input type="number" class="form-control form-control-sm w-auto" value="8">
        <span class="ms-2 text-muted small">characters</span>
      </div>

      <!-- Row 2: Checkboxes -->
      <div class="d-flex flex-wrap gap-4 mb-4">
        <div class="form-check">
          <input class="form-check-input" type="checkbox" id="reqUpper" checked>
          <label class="form-check-label" for="reqUpper">Require Uppercase Letters</label>
        </div>

        <div class="form-check">
          <input class="form-check-input" type="checkbox" id="reqLower" checked>
          <label class="form-check-label" for="reqLower">Require Lowercase Letters</label>
        </div>

        <div class="form-check">
          <input class="form-check-input" type="checkbox" id="reqNumbers" checked>
          <label class="form-check-label" for="reqNumbers">Require Numbers</label>
        </div>

        <div class="form-check">
          <input class="form-check-input" type="checkbox" id="reqSymbols" checked>
          <label class="form-check-label" for="reqSymbols">Require Symbols</label>
        </div>
      </div>

      <!-- Row 3: Expiry + Lock -->
      <div class="d-flex flex-wrap align-items-center gap-5 mb-4">
        <div class="d-flex align-items-center">
          <label class="form-label fw-semibold me-3 mb-0" style="width: 160px;">Password Expiry:</label>
          <input type="number" class="form-control form-control-sm w-auto" value="90">
          <span class="ms-2 text-muted small">days</span>
        </div>

        <div class="d-flex align-items-center">
          <label class="form-label fw-semibold me-3 mb-0" style="width: 160px;">Lock Account After:</label>
          <input type="number" class="form-control form-control-sm w-auto" value="5">
          <span class="ms-2 text-muted small">failed attempts</span>
        </div>
      </div>

      <div class="border-top pt-3 text-end">
        <p class="text-muted small mb-3 text-start">These settings apply to all user accounts.</p>
        <button id="savePolicyBtn" type="button" class="btn btn-primary btn-sm px-4">Save Policy</button>
      </div>
    </form>
  </div>
</div>


  <!-- Filter Access Logs -->
  <div class="card shadow-sm border-0">
    <div class="card-body">
      <h5 class="fw-bold text-primary mb-3">
        <i class="bi bi-funnel me-2"></i>Filter Access Logs
      </h5>

      <div class="row g-3 align-items-end mb-3">
        <div class="col-md-3">
          <label class="form-label fw-semibold">Status</label>
          <select id="logStatus" class="form-select form-select-sm">
            <option value="">All</option>
            <option value="active">Active</option>
            <option value="ended">Ended</option>
          </select>
        </div>
        
        <div class="col-md-4">
          <label class="form-label fw-semibold">Search by Username</label>
          <div class="input-group input-group-sm">
            <input type="text" id="logUser" class="form-control" placeholder="Enter username">
            <button id="logSearchBtn" class="btn btn-primary">Search</button>
          </div>
        </div>
        
        <div class="col-md-5">
          <label class="form-label fw-semibold">Date Range</label>
          <div class="d-flex flex-wrap align-items-center gap-2">
            <input type="date" id="logFrom" class="form-control form-control-sm w-auto">
            <span>to</span>
            <input type="date" id="logTo" class="form-control form-control-sm w-auto">
            <button id="logExportBtn" class="btn btn-outline-primary btn-sm">Export Logs</button>
            <button id="logApplyBtn" class="btn btn-primary btn-sm">Apply Filter</button>
          </div>
        </div>
      </div>

      <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle">
          <thead class="table-light">
            <tr>
              <th>User</th>
              <th>Login Time</th>
              <th>Logout Time</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody id="accessLogsBody"></tbody>
        </table>
      </div>

      <div class="mt-4 d-flex flex-column align-items-end">
  <nav aria-label="Page navigation" class="mb-2">
       <ul id="logsPager" class="pagination mb-0"></ul>

  </nav>
</div>

    </div>
  </div>

</div>
        
    </div>
    <script>
      $(function(){
        $.getJSON('../modules/settings.php', { action: 'list' }, function(rs){
          const items = rs.data || [];
          function val(name) { const f = items.find(x=>x.setting_name===name); return f?f.value:''; }
          $('input[type=number]').eq(0).val(parseInt(val('Minimum Password Length')||8));
          $('#reqUpper').prop('checked', val('Require Uppercase Letters')==='1');
          $('#reqLower').prop('checked', val('Require Lowercase Letters')==='1');
          $('#reqNumbers').prop('checked', val('Require Numbers')==='1');
          $('#reqSymbols').prop('checked', val('Require Symbols')==='1');
          $('input[type=number]').eq(1).val(parseInt(val('Password Expiry')||90));
          $('input[type=number]').eq(2).val(parseInt(val('Lock Account After')||5));
        });
        $('#savePolicyBtn').on('click', function(){
          function upd(name, value) { $.post('../modules/settings.php', { action: 'update', setting_name: name, value: value }, function(){}, 'json'); }
          upd('Minimum Password Length', $('input[type=number]').eq(0).val());
          upd('Require Uppercase Letters', $('#reqUpper').prop('checked')?1:0);
          upd('Require Lowercase Letters', $('#reqLower').prop('checked')?1:0);
          upd('Require Numbers', $('#reqNumbers').prop('checked')?1:0);
          upd('Require Symbols', $('#reqSymbols').prop('checked')?1:0);
          upd('Password Expiry', $('input[type=number]').eq(1).val());
          upd('Lock Account After', $('input[type=number]').eq(2).val());
          alert('Policy saved');
        });

        let lastLogs = [];
        function renderLogs(rows){
          const tbody = document.getElementById('accessLogsBody');
          if (!tbody) return;
          tbody.innerHTML = '';
          lastLogs = rows.slice();
          rows.forEach(r => {
            const statusSpan = r.session_status==='active' ? '<span class="text-primary">Active</span>' : '<span class="text-muted">Ended</span>';
            const tr = document.createElement('tr');
            tr.innerHTML = `<td>${r.user_name||''}</td><td>${r.login_time||''}</td><td>${r.logout_time||''}</td><td>${statusSpan}</td>`;
            tbody.appendChild(tr);
          });
          if (rows.length===0) tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted">No logs</td></tr>';
        }
        function fetchLogs(){
          const status = $('#logStatus').val();
          const user = $('#logUser').val().trim();
          const from = $('#logFrom').val();
          const to = $('#logTo').val();
          $.getJSON('../modules/audit.php', { action: 'accessLogs', status, user, from, to }, function(resp){ renderLogs(resp.data||[]); });
        }
        $('#logApplyBtn').on('click', fetchLogs);
        $('#logSearchBtn').on('click', fetchLogs);
        $('#logStatus').on('change', fetchLogs);
        $('#logExportBtn').on('click', function(){
          const header = ['User','Login Time','Logout Time','Status'];
          const lines = [header.join(',')];
          lastLogs.forEach(r => { lines.push([r.user_name||'', r.login_time||'', r.logout_time||'', r.session_status||''].map(x=>`"${String(x).replace(/"/g,'\"')}"`).join(',')); });
          const blob = new Blob([lines.join('\n')], { type: 'text/csv;charset=utf-8;' });
          const url = URL.createObjectURL(blob);
          const a = document.createElement('a'); a.href = url; a.download = 'access_logs.csv'; document.body.appendChild(a); a.click(); document.body.removeChild(a); URL.revokeObjectURL(url);
        });
        fetchLogs();
      });
    </script>
    <script>
      $(function(){
        let logsPage = 1; const logsLimit = 5; let logsPagesTotal = 1; let status='', user='', from='', to='';
        function renderLogsPager(total){
          const pager = document.getElementById('logsPager'); if (!pager) return;
          const pages = Math.max(1, Math.ceil(total / logsLimit));
          logsPagesTotal = pages;
          const items = [];
          items.push(`<li class="page-item"><a class="page-link" href="#" data-page="first">First</a></li>`);
          items.push(`<li class="page-item"><a class="page-link" href="#" data-page="prev">&lt;</a></li>`);
          const start = Math.max(1, logsPage - 2);
          const end = Math.min(pages, start + 4);
          for (let i = start; i <= end; i++) { items.push(`<li class="page-item ${i===logsPage?'active':''}"><a class="page-link" href="#" data-page="${i}">${i}</a></li>`); }
          items.push(`<li class="page-item"><a class="page-link" href="#" data-page="next">&gt;</a></li>`);
          items.push(`<li class="page-item"><a class="page-link" href="#" data-page="last">Last</a></li>`);
          pager.innerHTML = items.join('');
        }
        function fetchLogsPaged(){
          $.getJSON('../modules/audit.php', { action: 'accessLogs', status, user, from, to, limit: logsLimit, page: logsPage }, function(resp){
            const rows = resp.data||[]; const total = resp.total||0;
            const tbody = document.getElementById('accessLogsBody'); if (tbody) { tbody.innerHTML=''; rows.forEach(r=>{ const statusSpan = r.session_status==='active' ? '<span class="text-primary">Active</span>' : '<span class="text-muted">Ended</span>'; const tr=document.createElement('tr'); tr.innerHTML=`<td>${r.user_name||''}</td><td>${r.login_time||''}</td><td>${r.logout_time||''}</td><td>${statusSpan}</td>`; tbody.appendChild(tr); }); if(rows.length===0) tbody.innerHTML='<tr><td colspan="4" class="text-center text-muted">No logs</td></tr>'; }
            renderLogsPager(total);
          });
        }
        $('#logApplyBtn').off('click').on('click', function(){ status=$('#logStatus').val(); user=$('#logUser').val().trim(); from=$('#logFrom').val(); to=$('#logTo').val(); logsPage=1; fetchLogsPaged(); });
        $('#logSearchBtn').off('click').on('click', function(){ status=$('#logStatus').val(); user=$('#logUser').val().trim(); from=$('#logFrom').val(); to=$('#logTo').val(); logsPage=1; fetchLogsPaged(); });
        $('#logStatus').off('change').on('change', function(){ status=$('#logStatus').val(); logsPage=1; fetchLogsPaged(); });
        document.getElementById('logsPager').addEventListener('click', function(e){ const a=e.target.closest('a[data-page]'); if(!a) return; e.preventDefault(); const val=a.getAttribute('data-page'); if(val==='first') logsPage=1; else if(val==='prev') logsPage=Math.max(1,logsPage-1); else if(val==='next') logsPage=Math.min(logsPagesTotal,logsPage+1); else if(val==='last') logsPage=logsPagesTotal; else logsPage=parseInt(val,10); fetchLogsPaged(); });
        status=$('#logStatus').val(); user=$('#logUser').val().trim(); from=$('#logFrom').val(); to=$('#logTo').val(); fetchLogsPaged();
      });
    </script>
</body>

</html>
