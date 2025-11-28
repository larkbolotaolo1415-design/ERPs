<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../init.php';
$pageTitle = "Audit Logs";
$userName = $_SESSION['user_name'] ?? 'User';
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Audit Logs</title>
  <link rel="stylesheet" href="../assets/css/dashboard_style.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <style>
    .loading-overlay {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(255, 255, 255, 0.8);
      display: flex;
      align-items: center;
      justify-content: center;
      z-index: 1000;
    }
  </style>
</head>

<body>
    <?php include __DIR__ . '/../includes/layout/sidebar.php'; ?>
    <?php include __DIR__ . '/../includes/layout/topbar.php'; ?>

  <div class="main-content p-4 mt-5">

    <div class="container-fluid mt-4">
      <div class="filter-card mb-4">
        <h6 class="fw-bold text-primary mb-3">Filter Logs</h6>
        <div class="row g-3 align-items-end mb-3">
          <div class="col-md-3">
            <label class="form-label fw-semibold">Role</label>
            <select class="form-select form-select-sm" id="alRole">
              <option value="">All</option>
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label fw-semibold">Action</label>
            <select class="form-select form-select-sm" id="alAction">
              <option value="">All</option>
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label fw-semibold">Module</label>
            <select class="form-select form-select-sm" id="alModule">
              <option value="">All</option>
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label fw-semibold">Search by Username</label>
            <div class="input-group input-group-sm">
              <input type="text" id="alUser" class="form-control" placeholder="Enter username">
              <button id="alSearchBtn" class="btn btn-primary">Search</button>
            </div>
          </div>
        </div>
        <div class="row g-3 align-items-end">
          <div class="col-md-6">
            <label class="form-label fw-semibold">Date Range</label>
            <div class="d-flex flex-wrap align-items-center gap-2">
              <input type="date" id="alFrom" class="form-control form-control-sm w-auto">
              <span>to</span>
              <input type="date" id="alTo" class="form-control form-control-sm w-auto">
              <button id="alExportBtn" class="btn btn-outline-primary btn-sm">Export Logs</button>
              <button id="alApplyBtn" class="btn btn-primary btn-sm">Apply Filter</button>
            </div>
          </div>
          <div class="col-md-6 text-end">
            <button id="alResetBtn" class="btn btn-outline-primary btn-sm">Reset Filter</button>
          </div>
        </div>
      </div>

      <div class="table-card position-relative">
        <div id="auditLoading" class="loading-overlay" style="display:none;">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
          </div>
        </div>
        <div class="table-responsive">
          <table class="table align-middle">
            <thead>
              <tr>
                <th>Timestamp</th>
                <th>User</th>
                <th>Role</th>
                <th>Action</th>
                <th>Module</th>
                <th>Affected Record</th>
              </tr>
            </thead>
            <tbody id="auditLogsBody"></tbody>
          </table>
        </div>

        <div class="d-flex justify-content-end p-3">
          <ul id="auditPager" class="pagination mb-0">
            <li class="page-item"><a class="page-link" href="#">First</a></li>
            <li class="page-item"><a class="page-link" href="#">&lt;</a></li>
            <li class="page-item"><a class="page-link" href="#">10</a></li>
            <li class="page-item"><a class="page-link" href="#">11</a></li>
            <li class="page-item"><a class="page-link" href="#">...</a></li>
            <li class="page-item"><a class="page-link" href="#">20</a></li>
            <li class="page-item"><a class="page-link" href="#">21</a></li>
            <li class="page-item"><a class="page-link" href="#">&gt;</a></li>
            <li class="page-item"><a class="page-link" href="#">Last</a></li>
          </ul>
        </div>
      </div>
    </div>
  </div>

  <script>
    $(function(){
      const tbody = document.getElementById('auditLogsBody');
      const pager = document.getElementById('auditPager');
      const loading = document.getElementById('auditLoading');
      let auditPage = 1; const auditLimit = 10; let auditPagesTotal = 1;
      let fUser='', fRole='', fAction='', fModule='', fFrom='', fTo='';

      function showLoading() {
        if (loading) loading.style.display = 'flex';
      }

      function hideLoading() {
        if (loading) loading.style.display = 'none';
      }

      function renderPager(total){
        if (!pager) return;
        const pages = Math.max(1, Math.ceil(total / auditLimit));
        auditPagesTotal = pages;
        const items = [];
        items.push(`<li class="page-item"><a class="page-link" href="#" data-page="first">First</a></li>`);
        items.push(`<li class="page-item"><a class="page-link" href="#" data-page="prev">&lt;</a></li>`);
        const start = Math.max(1, auditPage - 2);
        const end = Math.min(pages, start + 4);
        for (let i = start; i <= end; i++) { items.push(`<li class="page-item ${i===auditPage?'active':''}"><a class="page-link" href="#" data-page="${i}">${i}</a></li>`); }
        items.push(`<li class="page-item"><a class="page-link" href="#" data-page="next">&gt;</a></li>`);
        items.push(`<li class="page-item"><a class="page-link" href="#" data-page="last">Last</a></li>`);
        pager.innerHTML = items.join('');
      }

      function fetchLogs(){
        showLoading();
        $.getJSON('../modules/audit.php', { action: 'list', user: fUser, role: fRole, actionType: fAction, module: fModule, from: fFrom, to: fTo, limit: auditLimit, page: auditPage }, function(resp){
          // Minimum 500ms delay to make loading visible
          setTimeout(function(){
            const rows = resp.data||[]; const total = resp.total||0;
            if (tbody) {
              tbody.innerHTML='';
              rows.forEach(r=>{ const tr=document.createElement('tr'); tr.innerHTML=`<td>${r.timestamp||''}</td><td>${r.user_name||r.user_id||''}</td><td>${r.role_name||''}</td><td>${r.action||''}</td><td>${r.module||''}</td><td>${r.affected_record||''}</td>`; tbody.appendChild(tr); });
              if (rows.length===0) tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted">No logs</td></tr>';
            }
            renderPager(total);
            hideLoading();
          }, 500);
        }).fail(function(){
          setTimeout(function(){
            hideLoading();
            if (tbody) tbody.innerHTML = '<tr><td colspan="6" class="text-center text-danger">Error loading logs</td></tr>';
          }, 500);
        });
      }

      document.getElementById('auditPager').addEventListener('click', function(e){ const a=e.target.closest('a[data-page]'); if(!a) return; e.preventDefault(); const val=a.getAttribute('data-page'); if(val==='first') auditPage=1; else if(val==='prev') auditPage=Math.max(1,auditPage-1); else if(val==='next') auditPage=Math.min(auditPagesTotal,auditPage+1); else if(val==='last') auditPage=auditPagesTotal; else auditPage=parseInt(val,10); fetchLogs(); });

      $.getJSON('../modules/roles.php', { action: 'list' }, function(rr){ const sel=$('#alRole'); sel.empty(); sel.append('<option value="">All</option>'); (rr.data||[]).forEach(r=> sel.append(`<option value="${r.role_name}">${r.role_name}</option>`)); });
      $.getJSON('../modules/audit.php', { action: 'meta' }, function(m){ const aSel=$('#alAction'); const mSel=$('#alModule'); aSel.empty(); mSel.empty(); aSel.append('<option value="">All</option>'); mSel.append('<option value="">All</option>'); (m.actions||[]).forEach(x=> aSel.append(`<option value="${x}">${x}</option>`)); (m.modules||[]).forEach(x=> mSel.append(`<option value="${x}">${x}</option>`)); });

      $('#alApplyBtn').on('click', function(){ fRole=$('#alRole').val(); fAction=$('#alAction').val(); fModule=$('#alModule').val(); fUser=$('#alUser').val().trim(); fFrom=$('#alFrom').val(); fTo=$('#alTo').val(); auditPage=1; fetchLogs(); });
      $('#alSearchBtn').on('click', function(){ fUser=$('#alUser').val().trim(); auditPage=1; fetchLogs(); });
      $('#alResetBtn').on('click', function(){ $('#alRole').val(''); $('#alAction').val(''); $('#alModule').val(''); $('#alUser').val(''); $('#alFrom').val(''); $('#alTo').val(''); fRole=''; fAction=''; fModule=''; fUser=''; fFrom=''; fTo=''; auditPage=1; fetchLogs(); });

      let lastRows = [];
      function fetchAndStore(){
        $.getJSON('../modules/audit.php', { action: 'list', user: fUser, role: fRole, actionType: fAction, module: fModule, from: fFrom, to: fTo, limit: auditLimit, page: auditPage }, function(resp){ lastRows = resp.data||[]; });
      }
      $('#alExportBtn').on('click', function(){ fetchAndStore(); setTimeout(function(){ const header=['Timestamp','User','Role','Action','Module','Affected Record']; const lines=[header.join(',')]; lastRows.forEach(r=>{ lines.push([r.timestamp||'', r.user_name||r.user_id||'', r.role_name||'', r.action||'', r.module||'', r.affected_record||''].map(x=>`"${String(x).replace(/"/g,'\"')}"`).join(',')); }); const blob=new Blob([lines.join('\n')],{type:'text/csv;charset=utf-8;'}); const url=URL.createObjectURL(blob); const a=document.createElement('a'); a.href=url; a.download='audit_logs.csv'; document.body.appendChild(a); a.click(); document.body.removeChild(a); URL.revokeObjectURL(url); }, 200); });

      fetchLogs();
    });
  </script>
</body>

</html>