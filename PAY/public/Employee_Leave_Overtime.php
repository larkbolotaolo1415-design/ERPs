<!-- Copy Paste This php block -->
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../init.php';
$pageTitle = "Employee Leave & Overtime Records"; // Change to correct title 
$userName = $_SESSION['user_name'] ?? 'User';
?> 


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Employee Leave & Overtime Records</title>
  <link rel="stylesheet" href="../assets/css/dashboard_style.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

  <style>
    body {
      background-color: #f8f9fa;
      font-family: "Poppins", sans-serif;
    }
    
    .content {
      margin-left: 250px;
      padding: 30px;
    }
    .card-box {
      background-color: #1665d8;
      color: white;
      border-radius: 12px;
      padding: 20px;
      text-align: center;
    }
    .nav-tabs .nav-link.active {
      background-color: #4c89ff !important;
      color: white !important;
    }
    .nav-tabs .nav-link {
      color: #333;
      border-radius: 6px;
      margin-right: 5px;
    }
    .table {
      border-collapse: separate;
      border-spacing: 0;
      width: 100%;
    }
    .table thead th {
      color: #2563EB;
      border-bottom: 2px solid #2563EB;
      font-weight: 600;
      vertical-align: middle;
      text-align: center;
      padding: 12px 16px;
    }
    .table tbody td {
      padding: 10px 16px;
      vertical-align: middle;
      text-align: center;
      border-bottom: 1px solid #dee2e6;
    }
    .table th, .table td {
      white-space: nowrap;
    }
    .table-striped tbody tr:nth-of-type(odd) {
      background-color: #f9fafb;
    }
    .table tbody tr:nth-child(even) {
      background-color: #F5F6FA;
    }
    .table-scroll {
      max-height: 360px;
      overflow: auto;
    }
    .note {
      font-size: 13px;
      text-align: center; 
      color: #555;
      margin-top: 10px;
    }

    /* Modal form styling */
    .modal-content {
      border-radius: 15px;
      padding: 20px;
    }
    .form-label {
      font-weight: 600;
      color: #1a46d6;
    }
    .btn-cancel {
      border: 1px solid red;
      color: red;
      border-radius: 10px;
      transition: 0.3s;
    }
    .btn-cancel:hover {
      background-color: red;
      color: white;
    }
    .btn-submit {
      background-color: #1a46d6;
      color: white;
      border-radius: 10px;
      transition: 0.3s;
    }
    .btn-submit:hover {
      background-color: #0e2ca0;
    }
  </style>
</head>
<body>
    <?php include __DIR__ . '/../includes/layout/sidebar.php'; ?>
    <?php include __DIR__ . '/../includes/layout/topbar.php'; ?>

    <div class="main-content p-4 mt-5">


  <div class="d-flex justify-content-between align-items-center mt-3 mb-4">
    <select class="form-select w-auto">
      <option>Year</option>
    </select>
    <div>
      <button class="btn btn-outline-primary">Export CSV/PDF</button>
      <button class="btn btn-primary">Create Leave Request</button>
      <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#overtimeModal">Create Overtime Request</button>
    </div>
  </div>

  <div class="row text-center mb-4">
    <div class="col-md-3"><div class="card-box"><h6>Vacation Leave Balance:</h6><h3 id="vlBal">0</h3></div></div>
    <div class="col-md-3"><div class="card-box"><h6>Sick Leave Balance:</h6><h3 id="slBal">0</h3></div></div>
    <div class="col-md-3"><div class="card-box"><h6>Miscellaneous Leave Credits:</h6><h3 id="mlBal">0</h3></div></div>
    <div class="col-md-3"><div class="card-box"><h6>Total Overtime This Month:</h6><h3 id="otMonth">0 Hours</h3></div></div>
  </div>

  <ul class="nav nav-tabs" id="recordTabs">
    <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#leave">Leave</a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#overtime">Overtime</a></li>
  </ul>

  <div class="tab-content bg-light p-4 rounded-bottom shadow-sm">
    <!-- Leave Tab -->
    <div class="tab-pane fade show active" id="leave">
      <div class="d-flex flex-wrap align-items-end gap-2 mt-3">
        <div>
          <label class="form-label">Leave Type</label>
          <select id="leaveTypeFilter" class="form-select">
            <option value="all">All</option>
          </select>
        </div>
        <div>
          <label class="form-label">Duration Min</label>
          <input type="number" id="leaveDurMin" class="form-control" placeholder="Days">
        </div>
        <div>
          <label class="form-label">Duration Max</label>
          <input type="number" id="leaveDurMax" class="form-control" placeholder="Days">
        </div>
        <div>
          <label class="form-label">Status</label>
          <select id="leaveStatusFilter" class="form-select">
            <option value="all">All</option>
            <option value="pending">Pending</option>
            <option value="approved">Approved</option>
            <option value="rejected">Rejected</option>
          </select>
        </div>
        <div class="flex-grow-1">
          <label class="form-label">Remarks</label>
          <input type="text" id="leaveRemarkFilter" class="form-control" placeholder="Search remarks">
        </div>
        <div class="ms-auto d-flex gap-2">
          <button id="leaveResetBtn" class="btn btn-outline-primary">Reset</button>
          <button id="leaveApplyBtn" class="btn btn-primary">Apply</button>
        </div>
      </div>
      <div class="table-responsive table-scroll">
        <table class="table table-striped align-middle mt-3">
          <thead>
            <tr>
              <th>Date</th>
              <th>Leave Type</th>
              <th>Duration</th>
              <th>Status</th>
              <th>Remarks</th>
            </tr>
          </thead>
          <tbody></tbody>
        </table>
      </div>
    </div>

    <!-- Overtime Tab -->
    <div class="tab-pane fade" id="overtime">
      <div class="d-flex flex-wrap align-items-end gap-2 mt-3">
        <div>
          <label class="form-label">Hours Min</label>
          <input type="number" id="otDurMin" class="form-control" placeholder="Hours">
        </div>
        <div>
          <label class="form-label">Hours Max</label>
          <input type="number" id="otDurMax" class="form-control" placeholder="Hours">
        </div>
        <div>
          <label class="form-label">Status</label>
          <select id="otStatusFilter" class="form-select">
            <option value="all">All</option>
            <option value="pending">Pending</option>
            <option value="approved">Approved</option>
            <option value="rejected">Rejected</option>
          </select>
        </div>
        <div class="flex-grow-1">
          <label class="form-label">Remarks</label>
          <input type="text" id="otRemarkFilter" class="form-control" placeholder="Search remarks">
        </div>
        <div class="ms-auto d-flex gap-2">
          <button id="otResetBtn" class="btn btn-outline-primary">Reset</button>
          <button id="otApplyBtn" class="btn btn-primary">Apply</button>
        </div>
      </div>
      <div class="table-responsive table-scroll">
        <table class="table table-striped align-middle mt-3">
          <thead>
            <tr>
              <th>Date</th>
              <th>Hours</th>
              <th>OT Type</th>
              <th>Status</th>
              <th>Remarks</th>
            </tr>
          </thead>
          <tbody></tbody>
        </table>
      </div>
    </div>
  </div>

  <p class="note">Note: Leave balances reset annually as per company policy. Overtime is credited after payroll approval.</p>
</div>

<!-- MODAL: Create Overtime Request -->
<div class="modal fade" id="overtimeModal" tabindex="-1" aria-labelledby="overtimeModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header border-0">
        <h5 class="modal-title fw-bold" id="overtimeModalLabel">OVERTIME REQUEST FORM</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <form method="POST" action="submit_overtime.php" id="overtimeForm">
          <div class="row mb-3">
            <div class="col-md-4">
              <label for="ot_date" class="form-label">Date</label>
              <input type="date" name="ot_date" id="ot_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
            </div>
            <div class="col-md-4">
              <label for="ot_from" class="form-label">Time From</label>
              <input type="time" name="ot_from" id="ot_from" class="form-control" required>
            </div>
            <div class="col-md-4">
              <label for="ot_to" class="form-label">Time To</label>
              <input type="time" name="ot_to" id="ot_to" class="form-control" required>
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-md-4">
              <label for="ot_rate" class="form-label">Hourly Rate (₱)</label>
              <input type="number" step="0.01" min="0" name="ot_rate" id="ot_rate" class="form-control" placeholder="0.00">
            </div>
          </div>

          <div class="mb-3">
            <label for="ot_reason" class="form-label">Reason</label>
            <textarea name="ot_reason" id="ot_reason" class="form-control" rows="5" placeholder="Put your reason here....." required></textarea>
          </div>

          <div class="d-flex justify-content-between">
            <button type="button" class="btn btn-cancel" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-submit">Submit</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- Optional: jQuery AJAX submit (if you don't want page reload) -->
<script>
      $(function(){
    const currentUserId = <?php echo intval($_SESSION['user_id'] ?? 0); ?>;
    const leaveTbody = document.querySelector('#leave tbody');
    const otTbody = document.querySelector('#overtime tbody');
    let leaveData = [];
    let otData = [];
    $.getJSON('../modules/employees.php', { action: 'list' }, function(re) {
      const me = (re.data||[]).find(r => parseInt(r.user_id||0) === currentUserId);
      const empUserId = me ? parseInt(me.user_id) : 0;
      const empRecordId = me ? parseInt(me.emp_id) : 0;
      $.getJSON('../modules/requests.php', { action: 'list', type: 'leave', emp_id: empUserId }, function(rl) {
        leaveData = rl.data || [];
        function populateLeaveTypes() {
          const sel = document.getElementById('leaveTypeFilter');
          if (!sel) return;
          const types = Array.from(new Set((leaveData||[]).map(r => r.leave_type).filter(Boolean)));
          sel.innerHTML = '<option value="all">All</option>' + types.map(t => `<option value="${t}">${t}</option>`).join('');
        }
        function getLeaveFilters() {
          const type = ($('#leaveTypeFilter').val()||'all');
          const min = parseInt($('#leaveDurMin').val());
          const max = parseInt($('#leaveDurMax').val());
          const status = ($('#leaveStatusFilter').val()||'all');
          const remark = String($('#leaveRemarkFilter').val()||'').toLowerCase();
          return { type, min: isNaN(min)?null:min, max: isNaN(max)?null:max, status, remark };
        }
        function applyLeaveFilters(rows) {
          const f = getLeaveFilters();
          return (rows||[]).filter(r => {
            const typeOk = f.type==='all' || String(r.leave_type||'').toLowerCase() === String(f.type||'').toLowerCase();
            const dur = parseInt(r.days||0);
            const minOk = f.min===null || dur >= f.min;
            const maxOk = f.max===null || dur <= f.max;
            const statusOk = f.status==='all' || String(r.status||'').toLowerCase() === String(f.status||'').toLowerCase();
            const remOk = f.remark==='' || String(r.pay_types||'').toLowerCase().includes(f.remark);
            return typeOk && minOk && maxOk && statusOk && remOk;
          });
        }
        function renderLeave() {
          if (!leaveTbody) return;
          const rows = applyLeaveFilters(leaveData);
          leaveTbody.innerHTML = '';
          let vl=0, sl=0, ml=0;
          rows.forEach(r => {
            const tr = document.createElement('tr');
            tr.innerHTML = `<td>${r.date_from} - ${r.date_to}</td><td>${r.leave_type}</td><td>${r.days}</td><td>${r.status}</td><td>${r.pay_types||''}</td>`;
            leaveTbody.appendChild(tr);
            if (r.status==='approved') {
              if ((r.leave_type||'').toLowerCase().includes('vac')) vl += parseInt(r.days||0);
              else if ((r.leave_type||'').toLowerCase().includes('sick')) sl += parseInt(r.days||0);
              else ml += parseInt(r.days||0);
            }
          });
          const vlEl = document.getElementById('vlBal');
          const slEl = document.getElementById('slBal');
          const mlEl = document.getElementById('mlBal');
          if (vlEl) vlEl.textContent = Math.max(0, 12 - vl);
          if (slEl) slEl.textContent = Math.max(0, 14 - sl);
          if (mlEl) mlEl.textContent = Math.max(0, 7 - ml);
        }
        populateLeaveTypes();
        renderLeave();
        $('#leaveApplyBtn').on('click', renderLeave);
        $('#leaveResetBtn').on('click', function(){
          $('#leaveTypeFilter').val('all');
          $('#leaveDurMin').val('');
          $('#leaveDurMax').val('');
          $('#leaveStatusFilter').val('all');
          $('#leaveRemarkFilter').val('');
          renderLeave();
        });
        $('#leaveTypeFilter,#leaveDurMin,#leaveDurMax,#leaveStatusFilter,#leaveRemarkFilter').on('change keyup', function(){ renderLeave(); });
      });
        $.getJSON('../modules/requests.php', { action: 'list', type: 'overtime', emp_id: empUserId }, function(ro) {
          otData = ro.data || [];
          function getOtFilters() {
            const min = parseInt($('#otDurMin').val());
            const max = parseInt($('#otDurMax').val());
            const status = ($('#otStatusFilter').val()||'all');
            const remark = String($('#otRemarkFilter').val()||'').toLowerCase();
            return { min: isNaN(min)?null:min, max: isNaN(max)?null:max, status, remark };
          }
          function applyOtFilters(rows) {
            const f = getOtFilters();
            return (rows||[]).filter(r => {
              const hours = parseInt(r.hours||0);
              const minOk = f.min===null || hours >= f.min;
              const maxOk = f.max===null || hours <= f.max;
              const statusOk = f.status==='all' || String(r.status||'').toLowerCase() === String(f.status||'').toLowerCase();
              const comp = (r.computed_amount!==undefined && r.computed_amount!==null) ? String(r.computed_amount) : '';
              const remOk = f.remark==='' || comp.toLowerCase().includes(f.remark);
              return minOk && maxOk && statusOk && remOk;
            });
          }
          function renderOt() {
            if (!otTbody) return;
            const rows = applyOtFilters(otData);
            otTbody.innerHTML = '';
            let hoursMonth = 0;
            const monthStr = new Date().toISOString().slice(0,7);
            rows.forEach(r => {
              const tr = document.createElement('tr');
              const dateLabel = r.date ? r.date : `${r.date_from||''} - ${r.date_to||''}`;
              const hoursLabel = (r.hours!==undefined && r.hours!==null) ? r.hours : '-';
              const compLabel = (r.computed_amount!==undefined && r.computed_amount!==null) ? r.computed_amount : '-';
              tr.innerHTML = `<td>${dateLabel}</td><td>${hoursLabel}</td><td>${r.rate||'-'}</td><td>${r.status||'-'}</td><td>${compLabel}</td>`;
              otTbody.appendChild(tr);
              const dateForMonth = r.date || r.date_from || '';
              if (dateForMonth.slice(0,7) === monthStr && r.status==='approved') hoursMonth += parseInt(r.hours||0);
            });
            const otEl = document.getElementById('otMonth');
            if (otEl) otEl.textContent = hoursMonth + ' Hours';
          }
          renderOt();
          $('#otApplyBtn').on('click', renderOt);
          $('#otResetBtn').on('click', function(){
            $('#otDurMin').val('');
            $('#otDurMax').val('');
            $('#otStatusFilter').val('all');
            $('#otRemarkFilter').val('');
            renderOt();
          });
          $('#otDurMin,#otDurMax,#otStatusFilter,#otRemarkFilter').on('change keyup', function(){ renderOt(); });
        });
        $("#overtimeForm").submit(function(e){
          e.preventDefault();
          if (!empUserId) { alert('No user profile found for your account.'); return; }
          const from = ($('#ot_from').val()||'');
          const to = ($('#ot_to').val()||'');
          const date = ($('#ot_date').val()||'');
          function toMin(s){ const parts = String(s).split(':'); const h = parseInt(parts[0]||'0',10); const m = parseInt(parts[1]||'0',10); return h*60 + m; }
          let diff = toMin(to) - toMin(from); if (diff < 0) diff += 24*60;
          const hours = Math.max(0, Math.ceil(diff / 60));
          let rate = parseFloat($('#ot_rate').val());
          if (!isFinite(rate)) rate = 0;
          if (!date || !from || !to || hours <= 0) { alert('Please fill date, time from/to, and ensure hours > 0.'); return; }
          const payload = { action: 'request', type: 'overtime', emp_id: empUserId, date: date, rate };
          const btn = $(this).find('button[type="submit"]');
          btn.prop('disabled', true).text('Submitting...');
          $.post('../modules/requests.php', payload, function(response){
            if (response && response.status === 'success') {
              alert('Overtime request submitted successfully!');
            } else {
              alert(response && response.message ? response.message : 'Failed to submit overtime request');
            }
            $("#overtimeModal").modal("hide");
            $("#overtimeForm")[0].reset();
            $.getJSON('../modules/requests.php', { action: 'list', type: 'overtime', emp_id: empUserId }, function(ro2){
              const rows2 = ro2.data || [];
              otTbody.innerHTML = '';
              let hoursMonth2 = 0;
              const monthStr2 = new Date().toISOString().slice(0,7);
              rows2.forEach(r => {
                const tr = document.createElement('tr');
                const dateLabel = r.date ? r.date : `${r.date_from||''} - ${r.date_to||''}`;
                const hoursLabel = (r.hours!==undefined && r.hours!==null) ? r.hours : '-';
                const compLabel = (r.computed_amount!==undefined && r.computed_amount!==null) ? r.computed_amount : '-';
                tr.innerHTML = `<td>${dateLabel}</td><td>${hoursLabel}</td><td>${r.rate||'-'}</td><td>${r.status||'-'}</td><td>${compLabel}</td>`;
                otTbody.appendChild(tr);
                const dateForMonth2 = r.date || r.date_from || '';
                if (dateForMonth2.slice(0,7) === monthStr2 && r.status==='approved') hoursMonth2 += parseInt(r.hours||0);
              });
              document.getElementById('otMonth').textContent = hoursMonth2 + ' Hours';
            });
          }, 'json').fail(function(xhr){ alert('Request failed: ' + (xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Network/Server error')); }).always(function(){ btn.prop('disabled', false).text('Submit'); });
        });
      });
  });
</script>

</body>
</html>
