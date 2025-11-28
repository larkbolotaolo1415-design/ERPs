<!-- Copy Paste This php block -->
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../init.php';
$pageTitle = "Attendance Log"; // Change to correct title 
$userName = $_SESSION['user_name'] ?? 'User';
?> 

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Attendance Log</title>

  <!-- Bootstrap & Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/dashboard_style.css">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background-color: #f4f6f9;
      margin: 0;
    }

    .header-title {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 25px;
    }

    .header-title h4 {
      color: #0a2a66;
      font-weight: 700;
    }

    .filter-bar {
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
      margin-bottom: 15px;
      align-items: center;
    }

    .btn-export {
      background-color: #0a2a66;
      color: white;
      border: none;
      border-radius: 8px;
    }

    .btn-export:hover {
      background-color: #123a8c;
    }

    .table thead {
      background-color: #0a2a66;
      color: white;
    }

    .modal-header {
      background-color: #0a2a66;
      color: white;
    }

    .modal-body strong {
      color: #0a2a66;
    }

    .spinner-slow {
      animation-duration: 1.5s !important;
    }

    /* Legend Section */
    .legend-box {
      display: flex;
      flex-wrap: wrap;
      gap: 15px;
      margin-bottom: 20px;
      background: #fff;
      border-radius: 10px;
      padding: 10px 15px;
      box-shadow: 0 2px 5px rgba(0,0,0,0.05);
    }

    .legend-item {
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .legend-item span {
      font-size: 14px;
      font-weight: 500;
      color: #333;
    }
  </style>
</head>
<body>
    <?php include __DIR__ . '/../includes/layout/sidebar.php'; ?>
    <?php include __DIR__ . '/../includes/layout/topbar.php'; ?>


  <!-- Content -->
      <div class="main-content p-4 mt-5">
  <div class="content">


    <!-- Filters -->
    <div class="filter-bar">
      <label><strong>From:</strong></label>
      <input type="date" id="fromDate" class="form-control" style="width: 180px;">
      <label><strong>To:</strong></label>
      <input type="date" id="toDate" class="form-control" style="width: 180px;">
      <div class="ms-auto d-flex align-items-center" style="gap:8px;">
        <input type="text" id="searchInput" class="form-control" style="width: 250px;" placeholder="Search">
        <button id="searchBtn" class="btn btn-primary">Search</button>
      </div>
    </div>

    <!-- Legend -->
    <div class="legend-box">
      <div class="legend-item"><span class="badge bg-success">&nbsp;</span><span>Present</span></div>
      <div class="legend-item"><span class="badge bg-danger">&nbsp;</span><span>Absent</span></div>
      <div class="legend-item"><span class="badge bg-warning text-dark">&nbsp;</span><span>Late</span></div>
      <div class="legend-item"><span class="badge bg-info text-dark">&nbsp;</span><span>On Leave</span></div>
      <div class="legend-item"><span class="badge bg-secondary">&nbsp;</span><span>Day Off</span></div>
    </div>

    <!-- Table -->
    <table class="table table-bordered table-hover align-middle text-center">
      <thead>
        <tr>
          <th>#</th>
          <th>Date</th>
          <th>Employee Name</th>
          <th>Time In</th>
          <th>Time Out</th>
          <th>Total Hours</th>
          <th>Status</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody id="attTbody"></tbody>
    </table>
    <div class="d-flex justify-content-between align-items-center mt-3">
      <p id="attPageInfo" class="mb-0 text-secondary"></p>
      <nav aria-label="Page navigation">
        <ul id="attPager" class="pagination mb-0"></ul>
      </nav>
    </div>
  </div>

  <!-- View Modal -->
  <div class="modal fade" id="viewModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title"><i class="bi bi-person-badge"></i> Attendance Details</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body" id="attModalBody"></div>
        <div class="modal-footer">
          <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>
    </div>
<script>
$(function(){
  const currentUserId = <?php echo intval($_SESSION['user_id'] ?? 0); ?>;
  const tbody = document.getElementById('attTbody');
  const pageInfo = document.getElementById('attPageInfo');
  const pager = document.getElementById('attPager');
  const fromDate = document.getElementById('fromDate');
  const toDate = document.getElementById('toDate');
  const searchInput = document.getElementById('searchInput');
  const searchBtn = document.getElementById('searchBtn');
  let rows = [];
  let page = 1;
  const limit = 10;
  let empName = '';
  if (!tbody) return;
  function render(rowsPage, page, pages, baseIndex, name){
    tbody.innerHTML = rowsPage.map((r,i)=>{
      const status = (r.time_in ? ( (r.time_out? 'Present':'On Duty') ) : 'Absent');
      const badgeClass = status==='Present' ? 'bg-success' : (status==='On Duty' ? 'bg-info text-dark' : 'bg-danger');
      return `<tr data-date="${r.date||''}" data-name="${name||''}" data-in="${r.time_in||'-'}" data-out="${r.time_out||'-'}" data-hours="${parseFloat(r.hours_worked||0).toFixed(2)} h" data-status="${status}">
        <td>${baseIndex+i+1}</td>
        <td>${r.date}</td>
        <td>${name||''}</td>
        <td>${r.time_in||'-'}</td>
        <td>${r.time_out||'-'}</td>
        <td>${parseFloat(r.hours_worked||0).toFixed(2)} h</td>
        <td><span class="badge ${badgeClass}">${status}</span></td>
        <td><button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#viewModal"><i class="bi bi-eye"></i></button></td>
      </tr>`;
    }).join('');
    if (pageInfo) pageInfo.textContent = `Showing Page ${page} of ${pages}`;
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
  function paginate(name){
    const total = rows.length;
    const pages = Math.max(1, Math.ceil(total/limit));
    const start = (page-1)*limit;
    render(rows.slice(start, start+limit), page, pages, start, name);
  }
  function setLoading(l){
    const d = !!l;
    [fromDate, toDate, searchInput, searchBtn].forEach(el=>{ if (el) el.disabled = d; });
    if (d) {
      tbody.innerHTML = `<tr><td colspan="8"><div class="d-flex align-items-center justify-content-center" style="gap:8px; padding:16px;"><div class="spinner-border spinner-slow text-primary" role="status"></div><span>Loading...</span></div></td></tr>`;
      if (pageInfo) pageInfo.textContent = 'Loading...';
    }
  }
  function fetchData(){
    setLoading(true);
    $.getJSON('../modules/employees.php', { action: 'list' }, function(re) {
      const me = (re.data||[]).find(r => parseInt(r.user_id||0) === currentUserId);
      const empId = me ? parseInt(me.emp_id) : 0;
      empName = me ? (me.user_name||'') : '';
      const df = fromDate ? fromDate.value : '';
      const dt = toDate ? toDate.value : '';
      $.getJSON('../modules/attendance.php', { action: 'list', emp_id: empId, date_from: df||'', date_to: dt||'' }, function(ra) {
        const term = (searchInput ? (searchInput.value||'') : '').toLowerCase();
        const raw = ra.data || [];
        rows = term ? raw.filter(r => (String(empName).toLowerCase().includes(term) || String(r.date||'').toLowerCase().includes(term))) : raw;
        page = 1;
        paginate(empName);
        setLoading(false);
      }).fail(function(){ setLoading(false); });
    }).fail(function(){ setLoading(false); });
  }
  fetchData();
  if (pager) pager.addEventListener('click', function(e){
    const a = e.target.closest('a.page-link');
    if (!a) return; e.preventDefault();
    const dp = a.getAttribute('data-page');
    const totalPages = Math.max(1, Math.ceil(rows.length/limit));
    if (dp==='prev') page = Math.max(1, page-1);
    else if (dp==='next') page = Math.min(totalPages, page+1);
    else page = parseInt(dp,10)||1;
    paginate(empName);
  });
  if (searchBtn) searchBtn.addEventListener('click', function(){ fetchData(); });
  tbody.addEventListener('click', function(e){
    const btn = e.target.closest('button');
    if (!btn) return;
    const tr = btn.closest('tr');
    const body = document.getElementById('attModalBody');
    if (!tr || !body) return;
    const name = tr.getAttribute('data-name')||'';
    const date = tr.getAttribute('data-date')||'';
    const tin = tr.getAttribute('data-in')||'-';
    const tout = tr.getAttribute('data-out')||'-';
    const hrs = tr.getAttribute('data-hours')||'0 h';
    const st = tr.getAttribute('data-status')||'';
    body.innerHTML = `
      <p><strong>Employee:</strong> ${name}</p>
      <p><strong>Date:</strong> ${date}</p>
      <p><strong>Time In:</strong> ${tin}</p>
      <p><strong>Time Out:</strong> ${tout}</p>
      <p><strong>Total Hours:</strong> ${hrs}</p>
      <p><strong>Status:</strong> ${st}</p>
      <p><strong>Remarks:</strong> </p>`;
  });
});
</script>

</body>
</html>
