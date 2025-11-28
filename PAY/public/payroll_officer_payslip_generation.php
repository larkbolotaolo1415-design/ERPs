<!-- Copy Paste This php block -->
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../init.php';
$pageTitle = "Payslip Generation"; // Change to correct title 
$userName = $_SESSION['user_name'] ?? 'User';
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payroll Officer | Payslip Generation </title>
    <link rel="stylesheet" href="../assets/css/dashboard_style.css">
    <link rel="stylesheet" href="../assets/css/payroll_officer_employee_management_style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        .payslip-preview {
            max-height: 600px;
            overflow: auto;
            border: 1px solid #2563EB;
            padding: 12px;
        }

        .payslip-preview th,
        .payslip-preview td,
        .payslip-preview div,
        .payslip-preview p {
            text-align: left;
        }

        #exportToast {
            position: fixed;
            right: 16px;
            bottom: 16px;
            z-index: 1055;
        }
    </style>
</head>

<body>
    <!-- Place includes at top -->
    <?php include __DIR__ . '/../includes/layout/sidebar.php'; ?>
    <?php include __DIR__ . '/../includes/layout/topbar.php'; ?>

    <div class="main-content p-4 mt-5">
        <div class="d-flex align-items-center mb-3 filter-row" style="gap: 10px;">
            <!-- Period Select -->
            <b>Payroll Period:</b>
            <select id="periodSelect" class="form-select"
                style="width: 240px; background-color: #93C5FD; color: #2563EB; border: none;"></select>

            <!-- Department Dropdown -->
            <select id="departmentFilter" class="form-select"
                style="width: 180px; background-color: #93C5FD; color: #2563EB; border: none;">
                <option value="" selected>Department</option>
            </select>

            <!-- Name Search -->
            <select id="outputFormat" class="form-select"
                style="width: 180px; background-color: #93C5FD; color: #2563EB; border: none;">
                <option value="single" selected>Output: Single PDF (multi-page)</option>
                <option value="zip">Output: Zip of individual PDFs</option>
            </select>

            <button id="applyFilters" class="btn btn-primary me-auto">Apply</button>
            <button id="resetFilters" class="btn btn-outline-secondary">Reset Filters</button>


            <div class="d-flex align-items-center gap-2">
                <input type="text" id="searchInput" class="form-control" placeholder="Search..." style="width: 300px; border-color: #2563EB;">
                <button id="searchButton" class="btn btn-primary fw-semibold" style="background-color: #2563EB; border: none;">Search</button>
                <button id="clearSearchButton" class="btn btn-outline-secondary fw-semibold">Clear</button>
            </div>
        </div>

        <div class="card p-3">
            <h5 class="card-title">Payslip Preview</h5>
            <div id="payslipPreview" class="payslip-preview"></div>
            <div class="mt-3">
                <label class="form-label">Remarks</label>
                <textarea id="remarksInput" class="form-control" rows="4" placeholder="Enter remarks for selected employee"></textarea>
                <button id="saveRemarksBtn" class="btn btn-outline-primary mt-2">Save Remarks</button>
            </div>
            <div class="mt-3 d-flex align-items-center gap-2">
                <input type="text" id="preparedByName" class="form-control" placeholder="Prepared By Name" style="max-width:220px;">
                <input type="text" id="checkedByName" class="form-control" placeholder="Checked By Name" style="max-width:220px;">
                <input type="text" id="approvedByName" class="form-control" placeholder="Approved By Name" style="max-width:220px;">
                <input type="file" id="preparedSig" accept="image/*" class="form-control" style="max-width:220px;">
                <input type="file" id="checkedSig" accept="image/*" class="form-control" style="max-width:220px;">
                <input type="file" id="approvedSig" accept="image/*" class="form-control" style="max-width:220px;">
            </div>
        </div>

        <div class="tab-content" id="employeelistTabsContent"></div>
        <!-- Filters and Search -->
        <div class="tab-pane fade show active" id="employeelist" role="tabpanel" aria-labelledby="employee-list-tab">

            <!-- Bottom Actions and Pagination -->
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div class="d-flex gap-2">
                    <button id="prevEmp" class="btn btn-outline-secondary">Previous</button>
                    <span>
                        <input type="number" id="pageInput" class="form-control d-inline-block" style="width:80px;" min="1" value="1"> / <span id="pageTotal">1</span>
                    </span>
                    <button id="nextEmp" class="btn btn-outline-secondary">Next</button>
                </div>
                <div class="d-flex gap-2 ms-auto">
                    <button id="exportSelected" class="btn btn-outline-primary">Export Current</button>
                    <button id="exportAll" class="btn btn-outline-primary">Export All</button>
                </div>
            </div>
        </div>

        <div id="exportToast" class="toast align-items-center text-bg-primary border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body" id="exportToastText">Preparing export...</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>

        <!-- Chart.js CDN -->
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

        <!-- Reset Filters Script -->
        <script>
            document.addEventListener("DOMContentLoaded", () => {
                const applyBtn = document.querySelector(".btn-accent");
                const filters = document.querySelectorAll(".filter-section select");

                const dateElement = document.querySelector('.current-date');
                const options = {
                    weekday: 'long',
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric'
                };
                dateElement.textContent = new Date().toLocaleDateString(undefined, options);

                const attendanceDonutCtx = document.querySelector('.daily-attendance-chart').getContext('2d');

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
                        layout: {
                            padding: 20
                        },
                        cutout: '80%',
                        plugins: {
                            legend: {
                                display: false
                            },
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
                                font: {
                                    weight: 'bold',
                                    size: 12
                                },
                            }
                        }
                    },
                    plugins: [ChartDataLabels]
                });

                const attendanceTrendCtx = document.getElementById('attendanceTrendChart').getContext('2d');

                const days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
                const onDuty = [35, 40, 38, 41, 39, 25, 15];
                const lateArrivals = [5, 3, 7, 4, 6, 5, 2];
                const absent = [5, 7, 5, 5, 6, 5, 8];

                new Chart(attendanceTrendCtx, {
                    type: 'line',
                    data: {
                        labels: days,
                        datasets: [{
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
                        layout: {
                            padding: 20
                        },
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    boxWidth: 12,
                                    padding: 15,
                                    font: {
                                        size: 11
                                    }
                                }
                            },
                            datalabels: {
                                display: false
                            }
                        },
                        scales: {
                            x: {
                                grid: {
                                    display: false
                                }
                            },
                            y: {
                                ticks: {
                                    callback: v => v
                                }
                            }
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
                    success: function(response) {
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
            $(function() {
                const periodSel = $('#periodSelect');
                const deptSel = $('#departmentFilter');
                const outputSel = $('#outputFormat');
                const searchInput = $('#searchInput');
                const searchBtn = $('#searchButton');
                const clearSearchBtn = $('#clearSearchButton');
                const applyBtn = $('#applyFilters');
                const resetBtn = $('#resetFilters');
                const preview = $('#payslipPreview');
                const remarksInput = $('#remarksInput');
                const saveRemarks = $('#saveRemarksBtn');
                const exportSelectedBtn = $('#exportSelected');
                const exportAllBtn = $('#exportAll');
                const prevBtn = $('#prevEmp');
                const nextBtn = $('#nextEmp');
                const pageInput = $('#pageInput');
                const pageTotal = $('#pageTotal');
                const exportToast = $('#exportToast');
                const exportToastText = $('#exportToastText');
                let orgName = '';
                let orgAddr = '';
                let orgContact = '';
                const preparedSig = $('#preparedSig')[0];
                const checkedSig = $('#checkedSig')[0];
                const approvedSig = $('#approvedSig')[0];
                let preparedSigUrl = '';
                let checkedSigUrl = '';
                let approvedSigUrl = '';

                let allRows = [];
                let filtered = [];
                let currentIdx = 0;
                let selectedIds = new Set();

                function toPeso(n) {
                    return '₱' + Number(n || 0).toLocaleString();
                }

                function periodLabel(p) {
                    return (p.start_date || '') + ' - ' + (p.end_date || '');
                }

                function monthLabel(p) {
                    try {
                        const d = new Date(p.start_date);
                        return d.toLocaleString(undefined, {
                            month: 'long',
                            year: 'numeric'
                        });
                    } catch (e) {
                        return '';
                    }
                }

                function fileToDataUrl(input) {
                    return new Promise(function(resolve) {
                        const f = input?.files?.[0];
                        if (!f) {
                            resolve('');
                            return;
                        }
                        const r = new FileReader();
                        r.onload = function() {
                            resolve(r.result || '');
                        };
                        r.readAsDataURL(f);
                    });
                }

                function populateDepartments() {
                    $.getJSON('../modules/company.php', {
                        action: 'list',
                        resource: 'departments'
                    }, function(rd) {
                        const depts = rd.data || [];
                        deptSel.empty().append('<option value="" selected>Department</option>');
                        depts.forEach(d => deptSel.append(`<option value="${d.dept_id}">${d.dept_name}</option>`));
                    });
                }

                function loadOrgSettings() {
                    $.getJSON('../modules/company.php', {
                        action: 'list',
                        resource: 'org_settings'
                    }, function(r) {
                        const s = r.data || {};
                        orgName = s['Company Name'] || '';
                        orgAddr = s['Company Address'] || '';
                        orgContact = s['Company Contact'] || '';
                    }).fail(function() {
                        orgName = '';
                        orgAddr = '';
                        orgContact = '';
                    });
                }

                function loadPeriods() {
                    $.getJSON('../modules/periods.php', {
                        action: 'list'
                    }, function(rp) {
                        const periods = rp.data || [];
                        periodSel.empty();
                        periods.forEach(p => periodSel.append(`<option value="${p.period_id}">${p.start_date} - ${p.end_date} (${p.status})</option>`));
                        const cur = periods.find(p => p.status === 'processing') || periods.find(p => p.status === 'open') || periods[0];
                        if (cur) {
                            periodSel.val(cur.period_id);
                            loadRows(cur.period_id);
                        }
                    });
                }

                function loadRows(pid) {
                    $.ajax({
                            url: '../modules/payroll.php',
                            method: 'POST',
                            data: {
                                action: 'list',
                                period_id: pid,
                                status: 'approved'
                            },
                            dataType: 'json'
                        })
                        .done(function(r) {
                            allRows = r.data || [];
                            filtered = allRows;
                            selectedIds = new Set(filtered.map(r => r.payroll_id));
                            currentIdx = 0;
                            renderPreview();
                        })
                        .fail(function() {
                            allRows = [];
                            filtered = [];
                            preview.html('<div class="text-danger">Failed to load data</div>');
                        });
                }

                function applyFilters() {
                    const term = (searchInput.val() || '').toLowerCase();
                    const dept = deptSel.val() || '';
                    filtered = allRows.filter(function(r) {
                        const termMatch = term ? (
                            String(r.user_name || '').toLowerCase().includes(term) ||
                            String(r.emp_id || '').toLowerCase().includes(term) ||
                            String(r.dept_name || '').toLowerCase().includes(term) ||
                            String(r.pos_name || '').toLowerCase().includes(term)
                        ) : true;
                        const deptMatch = dept ? String(r.dept_id || '') === String(dept) : true;
                        return termMatch && deptMatch;
                    });
                    selectedIds = new Set(filtered.map(r => r.payroll_id));
                    currentIdx = 0;
                    renderPreview();
                }

                function renderPreview() {
                    if (!filtered.length) {
                        preview.html('<div class="text-muted">No approved payrolls for selected filters.</div>');
                        pageTotal.text('0');
                        pageInput.val('1');
                        prevBtn.prop('disabled', true);
                        nextBtn.prop('disabled', true);
                        return;
                    }
                    const row = filtered[currentIdx];
                    remarksInput.val(row.remarks || '');
                    const ptxt = $('#periodSelect option:selected').text();
                    const m = ptxt.match(/(\d{4}-\d{2}-\d{2})\s-\s(\d{4}-\d{2}-\d{2})/);
                    const period = m ? {
                        start_date: m[1],
                        end_date: m[2]
                    } : {
                        start_date: '',
                        end_date: ''
                    };
                    const payload = {
                        template: 'payslip',
                        return_html: true,
                        company_logo_url: '',
                        company_name: orgName || 'Company',
                        company_address: orgAddr || '',
                        company_contact: orgContact || '',
                        payslip_month_label: monthLabel(period),
                        period_start: period.start_date,
                        period_end: period.end_date,
                        pay_date: period.end_date,
                        emp_name: row.user_name || '',
                        emp_id: row.emp_id || '',
                        dept_name: row.dept_name || '',
                        position_name: row.pos_name || '',
                        basic_pay: Number(row.basic_pay || 0).toLocaleString(),
                        allowances: Number(row.allowances || 0).toLocaleString(),
                        ot_pay: Number(row.ot_pay || 0).toLocaleString(),
                        gross_pay: Number(row.gross_pay || 0).toLocaleString(),
                        tax: Number(row.tax || 0).toLocaleString(),
                        sss: Number(row.sss || 0).toLocaleString(),
                        philhealth: Number(row.philhealth || 0).toLocaleString(),
                        pag_ibig: Number(row.pag_ibig || 0).toLocaleString(),
                        other_deductions: Number(row.other_deductions || 0).toLocaleString(),
                        total_deduction: Number(row.total_deduction || 0).toLocaleString(),
                        net_pay: Number(row.net_pay || 0).toLocaleString(),
                        remarks: (row.remarks || ''),
                        prepared_by_name: $('#preparedByName').val() || '',
                        checked_by_name: $('#checkedByName').val() || '',
                        approved_by_name: $('#approvedByName').val() || '',
                        prepared_sig_url: preparedSigUrl,
                        checked_sig_url: checkedSigUrl,
                        approved_sig_url: approvedSigUrl
                    };
                    $.ajax({
                            url: '../modules/pdf.php',
                            method: 'POST',
                            data: JSON.stringify(payload),
                            processData: false,
                            contentType: 'application/json'
                        })
                        .done(function(html) {
                            preview.html(html);
                        })
                        .fail(function() {
                            preview.html('<div class="text-danger">Preview failed</div>');
                        });
                    const total = filtered.length;
                    pageTotal.text(total);
                    pageInput.val(currentIdx + 1);
                    prevBtn.prop('disabled', currentIdx <= 0);
                    nextBtn.prop('disabled', currentIdx >= total - 1);
                }

                function buildPages(rows) {
                    const ptxt = $('#periodSelect option:selected').text();
                    const m = ptxt.match(/(\d{4}-\d{2}-\d{2})\s-\s(\d{4}-\d{2}-\d{2})/);
                    const period = m ? {
                        start_date: m[1],
                        end_date: m[2]
                    } : {
                        start_date: '',
                        end_date: ''
                    };
                    return rows.map(function(row) {
                        const data = {
                            company_logo_url: '',
                            company_name: orgName || 'Company',
                            company_address: orgAddr || '',
                            company_contact: orgContact || '',
                            payslip_month_label: monthLabel(period),
                            period_start: period.start_date,
                            period_end: period.end_date,
                            pay_date: period.end_date,
                            emp_name: row.user_name || '',
                            emp_id: row.emp_id || '',
                            dept_name: row.dept_name || '',
                            position_name: row.pos_name || '',
                            basic_pay: Number(row.basic_pay || 0).toLocaleString(),
                            allowances: Number(row.allowances || 0).toLocaleString(),
                            ot_pay: Number(row.ot_pay || 0).toLocaleString(),
                            gross_pay: Number(row.gross_pay || 0).toLocaleString(),
                            tax: Number(row.tax || 0).toLocaleString(),
                            sss: Number(row.sss || 0).toLocaleString(),
                            philhealth: Number(row.philhealth || 0).toLocaleString(),
                            pag_ibig: Number(row.pag_ibig || 0).toLocaleString(),
                            other_deductions: Number(row.other_deductions || 0).toLocaleString(),
                            total_deduction: Number(row.total_deduction || 0).toLocaleString(),
                            net_pay: Number(row.net_pay || 0).toLocaleString(),
                            remarks: (row.remarks || ''),
                            prepared_by_name: $('#preparedByName').val() || '',
                            checked_by_name: $('#checkedByName').val() || '',
                            approved_by_name: $('#approvedByName').val() || '',
                            prepared_sig_url: preparedSigUrl,
                            checked_sig_url: checkedSigUrl,
                            approved_sig_url: approvedSigUrl
                        };
                        const fname = `Payslip_${(row.user_name||('Emp_'+row.emp_id)).replace(/\s+/g,'_')}_${period.start_date}_${period.end_date}.pdf`;
                        return {
                            template: 'payslip',
                            data,
                            filename: fname
                        };
                    });
                }

                function showToast(msg) {
                    exportToastText.text(msg || 'Preparing export...');
                    const t = new bootstrap.Toast(exportToast[0]);
                    t.show();
                    return t;
                }

                function hideToast(toast, doneMsg) {
                    if (doneMsg) {
                        exportToastText.text(doneMsg);
                    }
                    setTimeout(function() {
                        toast.hide();
                    }, 3000);
                }

                function exportRows(rows) {
                    const toast = showToast('Preparing export...');
                    const pages = buildPages(rows);
                    const zip = (outputSel.val() === 'zip');
                    const payload = {
                        pages,
                        zip,
                        filename: zip ? `Payslips_${$('#periodSelect option:selected').text().replace(/\s+/g,'_')}.zip` : `Payslips_${$('#periodSelect option:selected').text().replace(/\s+/g,'_')}.pdf`
                    };
                    $.ajax({
                            url: '../modules/pdf.php',
                            method: 'POST',
                            data: JSON.stringify(payload),
                            processData: false,
                            contentType: 'application/json',
                            xhrFields: {
                                responseType: 'blob'
                            }
                        })
                        .done(function(blob) {
                            const url = window.URL.createObjectURL(blob);
                            const a = document.createElement('a');
                            a.href = url;
                            a.download = payload.filename;
                            document.body.appendChild(a);
                            a.click();
                            a.remove();
                            window.URL.revokeObjectURL(url);
                            hideToast(toast, 'Export complete');
                        })
                        .fail(function() {
                            hideToast(toast);
                            alert('Export failed');
                        });
                }

                applyBtn.on('click', applyFilters);
                resetBtn.on('click', function() {
                    deptSel.val('');
                    searchInput.val('');
                    applyFilters();
                });
                searchBtn.on('click', applyFilters);
                clearSearchBtn.on('click', function() {
                    searchInput.val('');
                    applyFilters();
                });
                periodSel.on('change', function() {
                    const pid = $(this).val();
                    loadRows(pid);
                });
                saveRemarks.on('click', function() {
                    const row = filtered[currentIdx];
                    if (!row) return;
                    const txt = remarksInput.val() || '';
                    $.ajax({
                        url: '../modules/payroll.php',
                        method: 'POST',
                        data: {
                            action: 'setRemarks',
                            payroll_id: row.payroll_id,
                            remarks: txt
                        },
                        dataType: 'json'
                    }).done(function() {
                        row.remarks = txt;
                        renderPreview();
                    }).fail(function() {
                        alert('Save failed');
                    });
                });
                exportSelectedBtn.on('click', function() {
                    if (!filtered.length) {
                        alert('No records to export');
                        return;
                    }
                    exportRows([filtered[currentIdx]]);
                });
                exportAllBtn.on('click', function() {
                    exportRows(filtered);
                });
                prevBtn.on('click', function() {
                    if (currentIdx > 0) {
                        currentIdx--;
                        renderPreview();
                    }
                });
                nextBtn.on('click', function() {
                    const total = filtered.length;
                    if (currentIdx < total - 1) {
                        currentIdx++;
                        renderPreview();
                    }
                });
                pageInput.on('keydown', function(e) {
                    if (e.key === 'Enter') {
                        const v = parseInt(pageInput.val(), 10);
                        const total = filtered.length;
                        if (!v || v < 1 || v > total) {
                            alert('Page does not exist');
                            pageInput.val(currentIdx + 1);
                            return;
                        }
                        currentIdx = v - 1;
                        renderPreview();
                    }
                });
                pageInput.on('change', function() {
                    const v = parseInt(pageInput.val(), 10);
                    const total = filtered.length;
                    if (!v || v < 1 || v > total) {
                        alert('Page does not exist');
                        pageInput.val(currentIdx + 1);
                        return;
                    }
                    currentIdx = v - 1;
                    renderPreview();
                });
                $('#preparedSig').on('change', function() {
                    fileToDataUrl($('#preparedSig')[0]).then(function(u) {
                        preparedSigUrl = u;
                        renderPreview();
                    });
                });
                $('#checkedSig').on('change', function() {
                    fileToDataUrl($('#checkedSig')[0]).then(function(u) {
                        checkedSigUrl = u;
                        renderPreview();
                    });
                });
                $('#approvedSig').on('change', function() {
                    fileToDataUrl($('#approvedSig')[0]).then(function(u) {
                        approvedSigUrl = u;
                        renderPreview();
                    });
                });
                $('#preparedByName, #checkedByName, #approvedByName').on('input', function() {
                    renderPreview();
                });
                populateDepartments();
                loadOrgSettings();
                loadPeriods();
            });
        </script>

</body>

</html>