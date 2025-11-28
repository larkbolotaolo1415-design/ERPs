<!-- Copy Paste This php block -->
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../init.php';
$pageTitle = "Period Management"; // Change to correct title 
$userName = $_SESSION['user_name'] ?? 'User';
?>     


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payroll Officer | Period Management</title>
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
        <div class="container-fluid mt-4">
            <!-- Status Legend + Buttons -->
            <div class="card border shadow-sm mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start flex-wrap">
                        <!-- Legends -->
                        <div class="flex-grow-1">
                            <h6 class="fw-bold text-primary mb-3 text-start">Status Indicator Legend</h6>
                            <ul class="list-unstyled mb-0 small text-start">
                                <li class="mb-2"><i
                                        class="bi bi-circle-fill text-success me-2"></i><strong>OPEN</strong> → Payroll
                                    period available for data entry and computation</li>
                                <li class="mb-2"><i
                                        class="bi bi-circle-fill text-primary me-2"></i><strong>PROCESSING</strong> →
                                    Payroll being computed or under review</li>
                                <li class="mb-2"><i
                                        class="bi bi-circle-fill text-danger me-2"></i><strong>LOCKED</strong> → Payroll
                                    finalized; no further changes allowed</li>
                                <li><i class="bi bi-circle-fill text-secondary me-2"></i><strong>ARCHIVED</strong> →
                                    Payroll period archived but can be reopened if necessary</li>
                            </ul>
                        </div>

                        <!-- Buttons -->
                        <div class="d-flex flex-column align-items-start gap-2 ms-auto mt-3 mt-md-0"
                            style="width: 230px;">
                            <button class="btn btn-primary fw-semibold w-100"><i class="bi bi-plus-lg"></i> Add New
                                Cutoff Period</button>
                            <button class="btn btn-outline-primary fw-semibold w-100"><i
                                    class="bi bi-bar-chart-line"></i> View Period Summary</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payroll Period Table -->
            <div class="card border-0 shadow-sm">
                <div class="table-responsive">
                    <table class="table align-middle mb-0 text-start">
                        <thead class="bg-light text-primary fw-semibold">
                            <tr>
                                <th>Period ID</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="fw-semibold">PRD-2025-01</td>
                                <td>01/01/2025</td>
                                <td>01/15/2025</td>
                                <td><span class="text-success fw-semibold"><i
                                            class="bi bi-circle-fill me-1"></i>OPEN</span></td>
                                <td>
                                    <a href="#" class="text-warning text-decoration-none me-3"><i
                                            class="bi bi-lock"></i> Lock</a>
                                    <a href="#" class="text-secondary text-decoration-none me-3">Archive Period</a>
                                    <a href="#" class="text-primary text-decoration-none">View Details</a>
                                </td>
                            </tr>
                            <tr>
                                <td class="fw-semibold">PRD-2025-02</td>
                                <td>01/16/2025</td>
                                <td>01/31/2025</td>
                                <td><span class="text-primary fw-semibold"><i
                                            class="bi bi-circle-fill me-1"></i>PROCESSING</span></td>
                                <td>
                                    <a href="#" class="text-warning text-decoration-none me-3"><i
                                            class="bi bi-lock"></i> Lock</a>
                                    <a href="#" class="text-secondary text-decoration-none me-3">Archive Period</a>
                                    <a href="#" class="text-primary text-decoration-none">View Details</a>
                                </td>
                            </tr>
                            <tr>
                                <td class="fw-semibold">PRD-2025-03</td>
                                <td>02/01/2025</td>
                                <td>02/15/2025</td>
                                <td><span class="text-danger fw-semibold"><i
                                            class="bi bi-circle-fill me-1"></i>LOCKED</span></td>
                                <td>
                                    <a href="#" class="text-success text-decoration-none me-3"><i
                                            class="bi bi-unlock"></i> Unlock</a>
                                    <a href="#" class="text-primary text-decoration-none me-3">Reopen Period</a>
                                    <a href="#" class="text-primary text-decoration-none">View Details</a>
                                </td>
                            </tr>
                            <tr>
                                <td class="fw-semibold">PRD-2025-04</td>
                                <td>02/16/2025</td>
                                <td>02/29/2025</td>
                                <td><span class="text-secondary fw-semibold"><i
                                            class="bi bi-circle-fill me-1"></i>ARCHIVED</span></td>
                                <td>
                                    <span class="text-muted me-3">-</span>
                                    <a href="#" class="text-primary text-decoration-none me-3">Reopen Period</a>
                                    <a href="#" class="text-primary text-decoration-none">View Details</a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-end p-4">
                    <ul class="pagination mb-0">
                        <li class="page-item"><a class="page-link text-primary" href="#">First</a></li>
                        <li class="page-item"><a class="page-link text-primary" href="#">
                                << /a>
                        </li>
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

                // Employment Status Donut
                const empStatusCtx = document.querySelector('.employment-status-donut').getContext('2d');

                new Chart(empStatusCtx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Active', 'On Leave', 'Inactive'],
                        datasets: [{
                            data: [60, 20, 10],
                            backgroundColor: ['#0d6efd', '#ffc107', '#dc3545'],
                            borderWidth: 0                     // keeps it super-clean
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        layout: { padding: 20 },
                        cutout: '80%',                        // ← thin ring
                        plugins: {
                            legend: { display: false },
                            datalabels: {
                                color: '#000',                // black text
                                anchor: 'end',                // stick the label to the outside edge
                                align: 'end',                // …and keep going
                                offset: 4,                    // small nudge away from the segment
                                formatter: (v, ctx) => {
                                    const sum = ctx.chart.data.datasets[0].data.reduce((a, b) => a + b, 0);
                                    const pct = (v * 100 / sum).toFixed(0) + '%';
                                    return ctx.chart.data.labels[ctx.dataIndex] + ' ' + pct;
                                },
                                font: { weight: 'bold', size: 12 }
                            }
                        }
                    },
                    plugins: [ChartDataLabels]
                });

                /* ----  Monthly Gross vs Net Pay Line Chart  ---- */
                const payCtx = document.getElementById('payTrendChart').getContext('2d');

                const months = ['Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                const gross = [6200, 6350, 6400, 6300, 6500, 6600, 6700, 6800, 6750, 6900, 7000];
                const net = [4800, 4920, 4960, 4890, 5030, 5100, 5180, 5250, 5210, 5340, 5400];

                new Chart(payCtx, {
                    type: 'line',
                    data: {
                        labels: months,
                        datasets: [
                            {
                                label: 'Gross Pay',
                                data: gross,
                                borderColor: '#0d6efd',
                                backgroundColor: 'rgba(13,110,253,.1)',
                                tension: 0.3,
                                fill: false
                            },
                            {
                                label: 'Net Pay',
                                data: net,
                                borderColor: '#198754',
                                backgroundColor: 'rgba(25,135,84,.1)',
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
                            y: { ticks: { callback: v => '$' + v.toLocaleString() } }
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
            // Populate periods from backend
            $(function() {
                const tbody = document.querySelector('table.table tbody');
                if (!tbody) return;
                $.getJSON('../modules/periods.php', { action: 'list' }, function(resp) {
                    if (resp.status !== 'success') return;
                    const rows = resp.data || [];
                    tbody.innerHTML = '';
                    rows.forEach(r => {
                        const tr = document.createElement('tr');
                        tr.innerHTML = `
                            <td class=\"fw-semibold\">${r.period_id}</td>
                            <td>${r.start_date}</td>
                            <td>${r.end_date}</td>
                            <td><span class=\"fw-semibold\">${r.status.toUpperCase()}</span></td>
                            <td>
                                <a href=\"#\" class=\"text-warning text-decoration-none me-3\" data-action=\"locked\" data-id=\"${r.period_id}\"><i class=\"bi bi-lock\"></i> Lock</a>
                                <a href=\"#\" class=\"text-primary text-decoration-none\" data-action=\"processing\" data-id=\"${r.period_id}\">Set Processing</a>
                            </td>`;
                        tbody.appendChild(tr);
                    });
                    tbody.addEventListener('click', function(e) {
                        const a = e.target.closest('a[data-id]');
                        if (!a) return;
                        e.preventDefault();
                        const pid = parseInt(a.getAttribute('data-id'), 10);
                        const status = a.getAttribute('data-action');
                        $.post('../modules/periods.php', { action: 'setStatus', period_id: pid, status }, function(res) {
                            if (res.status === 'success') location.reload();
                        }, 'json');
                    });
                });
            });
        </script>
</body>

</html>