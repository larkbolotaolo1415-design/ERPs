<!-- Copy Paste This php block -->
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../init.php';
$pageTitle = "Payroll History View"; // Change to correct title 
$userName = $_SESSION['user_name'] ?? 'User';
?>  

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Payroll History View</title>
    <link rel="stylesheet" href="../assets/css/dashboard_style.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background-color: #f8f9fc;
    }

    .content {
      margin-left: 230px;
      padding: 25px;
    }
    .summary-card {
      background-color: #2563eb;
      color: white;
      border-radius: 10px;
      text-align: center;
      padding: 20px;
      font-size: 18px;
      font-weight: 600;
    }
    .filter-box {
      background: white;
      padding: 20px;
      border-radius: 10px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
      margin-bottom: 20px;
    }
    .table thead {
      background-color: #2563eb;
      color: white;
    }
    .pagination .page-link {
      color: #2563eb;
    }
    .page-link.active {
      background-color: #2563eb;
      color: white;
      border: none;
    }
  </style>
</head>
<body>


  <!-- Main Content -->
    <!-- Place includes at top -->
    <?php include __DIR__ . '/../includes/layout/sidebar.php'; ?>
    <?php include __DIR__ . '/../includes/layout/topbar.php'; ?>

   <div class="main-content p-4 mt-5">
    <div class="row g-3">
      <!-- Filter -->
      <div class="col-md-6">
        <div class="filter-box">
          <h6 class="fw-bold mb-3 text-primary">Filter Payroll History</h6>
          <form>
            <div class="mb-3">
              <label class="form-label">Filter Type:</label>
              <select class="form-select">
                <option selected>Date Range</option>
                <option>Month</option>
                <option>Year</option>
              </select>
            </div>
            <div class="mb-3 d-flex align-items-center">
              <input type="date" id="filterStart" class="form-control me-2">
              <span class="me-2">to</span>
              <input type="date" id="filterEnd" class="form-control">
            </div>
            <div class="d-flex gap-2">
              <button type="reset" id="filterReset" class="btn btn-outline-primary w-50">Reset Filter</button>
              <button type="button" id="filterApply" class="btn btn-primary w-50">Apply Filter</button>
            </div>
          </form>
        </div>
      </div>

      <!-- Summary Cards -->
      <div class="col-md-6">
        <div class="summary-card mb-3">
          Total Earnings (Year-to-Date) <br>
          <h3>₱254,800.00</h3>
        </div>
        <div class="summary-card mb-3">
          Total Deductions <br>
          <h3>₱48,250.00</h3>
        </div>
        <div class="summary-card">
          Average Net Pay <br>
          <h3>₱17,250.00</h3>
        </div>
      </div>
    </div>

    <!-- Payroll Table -->
    <div class="mt-4">
      <table class="table table-bordered align-middle">
        <thead>
          <tr>
            <th>Period</th>
            <th>Start Date</th>
            <th>End Date</th>
            <th>Gross Pay</th>
            <th>Deductions</th>
            <th>Net Pay</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody id="payHistTbody"></tbody>
      </table>

      <!-- Pagination -->
      <nav class="d-flex justify-content-center mt-3">
        <ul class="pagination" id="pagination"></ul>
      </nav>
    </div>

  <!-- Payslip Modal -->
  <div class="modal fade" id="payslipModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title">Payslip Details</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <p><b>Employee:</b> <span id="psEmployee"></span></p>
          <p><b>Period:</b> <span id="psPeriod"></span></p>
          <hr>
          <p><b>Gross Pay:</b> ₱<span id="psGross"></span></p>
          <p><b>Deductions:</b> ₱<span id="psDed"></span></p>
          <p><b>Net Pay:</b> ₱<span id="psNet"></span></p>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
$(function() {
  const tbody = document.getElementById('payHistTbody');
  const pager = document.getElementById('pagination');
  let allRows = [];
  let filtered = [];
  let page = 1;
  const pageSize = 10;

  function renderTable() {
    if (!tbody) return;
    tbody.innerHTML = '';
    const start = (page - 1) * pageSize;
    const slice = filtered.slice(start, start + pageSize);
    slice.forEach(r => {
      const gross = parseInt(r.gross_pay || 0);
      const ded = parseInt(r.total_deduction || 0);
      const net = parseInt(r.net_pay || 0);
      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td>${r.payroll_period_id}</td>
        <td>${r.start_date}</td>
        <td>${r.end_date}</td>
        <td>₱${gross.toLocaleString()}</td>
        <td>₱${ded.toLocaleString()}</td>
        <td>₱${net.toLocaleString()}</td>
        <td><a href="#" class="text-primary view-payslip" data-id="${r.payroll_id}">View Payslip</a></td>`;
      tbody.appendChild(tr);
    });
  }

  function renderPagination() {
    if (!pager) return;
    const pageCount = Math.max(1, Math.ceil(filtered.length / pageSize));
    if (page > pageCount) page = pageCount;
    const items = [];
    items.push(`<li class="page-item ${page===1?'disabled':''}"><a class="page-link" data-act="first" href="#">&laquo;</a></li>`);
    items.push(`<li class="page-item ${page===1?'disabled':''}"><a class="page-link" data-act="prev" href="#">&lsaquo;</a></li>`);
    const start = Math.max(1, page - 2);
    const end = Math.min(pageCount, page + 2);
    for (let i = start; i <= end; i++) {
      items.push(`<li class="page-item"><a class="page-link ${i===page?'active':''}" data-page="${i}" href="#">${i}</a></li>`);
    }
    items.push(`<li class="page-item ${page===pageCount?'disabled':''}"><a class="page-link" data-act="next" href="#">&rsaquo;</a></li>`);
    items.push(`<li class="page-item ${page===pageCount?'disabled':''}"><a class="page-link" data-act="last" href="#">&raquo;</a></li>`);
    pager.innerHTML = items.join('');
  }

  function updateCards(rows) {
    const cards = document.querySelectorAll('.summary-card h3');
    let totalGross = 0, totalDed = 0, totalNet = 0;
    rows.forEach(r => { totalGross += parseInt(r.gross_pay||0); totalDed += parseInt(r.total_deduction||0); totalNet += parseInt(r.net_pay||0); });
    if (cards[0]) cards[0].textContent = '₱' + totalGross.toLocaleString();
    if (cards[1]) cards[1].textContent = '₱' + totalDed.toLocaleString();
    if (cards[2]) cards[2].textContent = '₱' + Math.round(totalNet / Math.max(1, rows.length)).toLocaleString();
  }

  function applyFilter() {
    const fs = $('#filterStart').val() || '';
    const fe = $('#filterEnd').val() || '';
    filtered = (allRows||[]).filter(r => {
      const s = r.start_date || '';
      const e = r.end_date || '';
      const startOk = !fs || s >= fs;
      const endOk = !fe || e <= fe;
      return startOk && endOk;
    });
    page = 1;
    updateCards(filtered);
    renderTable();
    renderPagination();
  }

  // Render initial pagination so controls are visible before data loads
  renderTable();
  renderPagination();

  $.getJSON('../modules/payslip.php', { action: 'history' }, function(resp) {
    if (resp.status !== 'success') return;
    allRows = resp.data || [];
    filtered = allRows.slice();
    updateCards(allRows);
    renderTable();
    renderPagination();
  });

  document.addEventListener('click', function(e){
    const a = e.target.closest('#pagination a.page-link');
    if (!a) return;
    e.preventDefault();
    const act = a.getAttribute('data-act');
    const pageAttr = a.getAttribute('data-page');
    const pageCount = Math.max(1, Math.ceil(filtered.length / pageSize));
    if (pageAttr) page = parseInt(pageAttr, 10);
    else if (act === 'first') page = 1;
    else if (act === 'prev') page = Math.max(1, page - 1);
    else if (act === 'next') page = Math.min(pageCount, page + 1);
    else if (act === 'last') page = pageCount;
    renderTable();
    renderPagination();
  });

  $('#filterApply').on('click', applyFilter);
  $('#filterReset').on('click', function(){
    $('#filterStart').val('');
    $('#filterEnd').val('');
    filtered = allRows.slice();
    page = 1;
    updateCards(allRows);
    renderTable();
    renderPagination();
  });

  document.addEventListener('click', function(e){
    const link = e.target.closest('a.view-payslip');
    if (!link) return;
    e.preventDefault();
    const id = parseInt(link.getAttribute('data-id'), 10);
    if (!id) return;
    $.getJSON('../modules/payslip.php', { action: 'detail', payroll_id: id }, function(resp){
      if (resp.status !== 'success') return;
      const d = resp.data || {};
      const set = (sel, val) => { const el = document.querySelector(sel); if (el) el.textContent = val || ''; };
      set('#psEmployee', d.user_name || '');
      set('#psPeriod', (d.start_date||'') + ' - ' + (d.end_date||''));
      set('#psGross', parseInt(d.gross_pay||0).toLocaleString());
      set('#psDed', parseInt(d.total_deduction||0).toLocaleString());
      set('#psNet', parseInt(d.net_pay||0).toLocaleString());
      const modalEl = document.getElementById('payslipModal');
      const modalInst = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
      modalInst.show();
    });
  });
});
</script>

</body>
</html>
