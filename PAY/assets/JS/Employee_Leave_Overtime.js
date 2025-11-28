$(function(){
  $('#sidebarToggle').on('click', function(){
    $('.sidebar').toggleClass('d-none');
  });

  const currentUserId = window.CURRENT_USER_ID || 0;
  const leaveTbody = document.querySelector('#leave tbody');
  const otTbody = document.querySelector('#overtime tbody');

  $.getJSON('../modules/employees.php', { action: 'list', user_id: currentUserId }, function(re){
    const me = (re.data||[])[0];
    const empId = me ? parseInt(me.emp_id) : 0;
    $.getJSON('../modules/requests.php', { action: 'list', type: 'leave', emp_id: empId }, function(rl){
      const rows = rl.data || [];
      if (leaveTbody) leaveTbody.innerHTML = '';
      let vl=0, sl=0, ml=0;
      rows.forEach(function(r){
        const tr = document.createElement('tr');
        tr.innerHTML = `<td>${r.date_from} - ${r.date_to}</td><td>${r.leave_type}</td><td>${r.days}</td><td>${r.status}</td><td>${r.pay_types||''}</td>`;
        if (leaveTbody) leaveTbody.appendChild(tr);
        if (r.status==='approved') {
          const lt = (r.leave_type||'').toLowerCase();
          if (lt.includes('vac')) vl += parseInt(r.days||0);
          else if (lt.includes('sick')) sl += parseInt(r.days||0);
          else ml += parseInt(r.days||0);
        }
      });
      const vlEl = document.getElementById('vlBal');
      const slEl = document.getElementById('slBal');
      const mlEl = document.getElementById('mlBal');
      if (vlEl) vlEl.textContent = Math.max(0, 12 - vl);
      if (slEl) slEl.textContent = Math.max(0, 14 - sl);
      if (mlEl) mlEl.textContent = Math.max(0, 7 - ml);
    });
    $.getJSON('../modules/requests.php', { action: 'list', type: 'overtime', emp_id: empId }, function(ro){
      const rows = ro.data || [];
      if (otTbody) otTbody.innerHTML = '';
      let hoursMonth = 0;
      const monthStr = new Date().toISOString().slice(0,7);
      rows.forEach(function(r){
        const tr = document.createElement('tr');
        tr.innerHTML = `<td>${r.date_from} - ${r.date_to}</td><td>${r.hours}</td><td>${r.rate}</td><td>${r.status}</td><td>${r.computed_amount}</td>`;
        if (otTbody) otTbody.appendChild(tr);
        if ((r.date_from||'').slice(0,7) === monthStr && r.status==='approved') hoursMonth += parseInt(r.hours||0);
      });
      const otEl = document.getElementById('otMonth');
      if (otEl) otEl.textContent = hoursMonth + ' Hours';
    });

    $('#overtimeForm').on('submit', function(e){
      e.preventDefault();
      const d = $('#ot_date').val();
      const tf = $('#ot_from').val();
      const tt = $('#ot_to').val();
      function toMinutes(t){ var a=t.split(':'); return (parseInt(a[0]||0)*60) + parseInt(a[1]||0); }
      const diffMin = Math.max(0, toMinutes(tt) - toMinutes(tf));
      const hours = Math.round(diffMin/60);
      const payload = { action: 'request', type: 'overtime', emp_id: empId, date_from: d, date_to: d, hours: hours, rate: 0 };
      $.post('../modules/requests.php', payload, function(){
        const modalEl = document.getElementById('overtimeModal');
        const modalInst = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
        modalInst.hide();
        $('#overtimeForm')[0].reset();
        $.getJSON('../modules/requests.php', { action: 'list', type: 'overtime', emp_id: empId }, function(rn){
          const rows = rn.data || [];
          if (otTbody) otTbody.innerHTML = '';
          rows.forEach(function(r){
            const tr = document.createElement('tr');
            tr.innerHTML = `<td>${r.date_from} - ${r.date_to}</td><td>${r.hours}</td><td>${r.rate}</td><td>${r.status}</td><td>${r.computed_amount}</td>`;
            if (otTbody) otTbody.appendChild(tr);
          });
        });
      }, 'json');
    });
  });
});
