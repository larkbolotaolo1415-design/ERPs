<!-- Copy Paste This php block -->
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../init.php';
$pageTitle = "Employee Dashboard"; // Change to correct title 
$userName = $_SESSION['user_name'] ?? 'User';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/dashboard_style.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
</head>

<body>
    <?php include __DIR__ . '/../includes/layout/sidebar.php'; ?>
    <?php include __DIR__ . '/../includes/layout/topbar.php'; ?>


    <div class="main-content p-4 mt-5">
        <div class="stats">
            <div class="card">
                <h3>Total Worked Days</h3>
                <p>10</p>
            </div>
            <div class="card">
                <h3>Last Net Pay</h3>
                <p>P12,000.00</p>
            </div>
            <div class="card">
                <h3>Overtime Hours</h3>
                <p class="status">8</p>
            </div>
            <div class="card">
                <h3>Pending Requests</h3>
                <p>2</p>
            </div>
        </div>

        <div class="summary">
            <h2>Payroll Summary</h2>
            <table class="summary-table">
                <tr>
                    <th>Metric</th>
                    <th>Amount</th>
                </tr>
                <tbody id="summaryTbody"></tbody>
            </table>
            <h3 style="margin-top:32px;">Department Breakdown</h3>
            <table class="dept-table">
                <thead>
                    <tr>
                        <th>Department</th>
                        <th>Employees</th>
                        <th>Gross Pay</th>
                        <th>Net Pay</th>
                    </tr>
                </thead>
                <tbody id="deptTbody"></tbody>
            </table>
            <div class="buttons">
                <button id="viewDetailedReportBtn">View Detailed Payroll Report</button>
                <button id="generateCutoffPdfBtn" class="blue">Generate Cutoff Summary PDF</button>
            </div>
        </div>
    </div>
    <script>
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
        $(function() {
            function peso(n) {
                return '₱' + (Number(n || 0)).toLocaleString('en-PH');
            }
            $.getJSON('../modules/payroll.php', {
                action: 'list'
            }, function(r) {
                const rows = r.data || [];
                let gross = 0,
                    ded = 0,
                    net = 0;
                rows.forEach(x => {
                    gross += Number(x.gross_pay || 0);
                    ded += Number(x.total_deduction || 0);
                    net += Number(x.net_pay || 0);
                });
                const avg = rows.length ? Math.round(net / rows.length) : 0;
                $('#summaryTbody').html(`
                    <tr><td>Total Gross Pay</td><td>${peso(gross)}</td></tr>
                    <tr><td>Total Deductions</td><td>${peso(ded)}</td></tr>
                    <tr><td>Total Net Pay</td><td>${peso(net)}</td></tr>
                    <tr><td>Average Net per Employee</td><td>${peso(avg)}</td></tr>
                `);
            });
            const deptBody = document.getElementById('deptTbody');
            $.getJSON('../modules/company.php', {
                action: 'list',
                resource: 'departments'
            }, function(rd) {
                const depts = rd.data || [];
                $.getJSON('../modules/employees.php', {
                    action: 'list',
                    status: 'active'
                }, function(re) {
                    const emps = re.data || [];
                    $.getJSON('../modules/periods.php', {
                        action: 'list'
                    }, function(rp) {
                        const ps = rp.data || [];
                        const cur = ps.find(p => p.status === 'processing') || ps.find(p => p.status === 'open') || ps[0];
                        const pid = cur ? cur.period_id : 0;
                        const renderRows = function(payRows) {
                            const byDept = {};
                            (payRows || []).forEach(pr => {
                                const id = pr.dept_id || 0;
                                if (!byDept[id]) byDept[id] = {
                                    gross: 0,
                                    net: 0
                                };
                                byDept[id].gross += Number(pr.gross_pay || 0);
                                byDept[id].net += Number(pr.net_pay || 0);
                            });
                            if (deptBody) {
                                deptBody.innerHTML = depts.map(d => {
                                    const count = emps.filter(e => parseInt(e.dept_id || 0) === parseInt(d.dept_id || 0)).length;
                                    const sums = byDept[d.dept_id] || {
                                        gross: 0,
                                        net: 0
                                    };
                                    return `<tr><td>${d.dept_name}</td><td>${count}</td><td>${peso(sums.gross)}</td><td>${peso(sums.net)}</td></tr>`;
                                }).join('');
                            }
                        };
                        if (pid) {
                            $.getJSON('../modules/payroll.php', {
                                action: 'list',
                                period_id: pid
                            }, function(pr) {
                                renderRows(pr.data || []);
                            });
                        } else {
                            $.getJSON('../modules/payroll.php', {
                                action: 'list'
                            }, function(pr) {
                                renderRows(pr.data || []);
                            });
                        }
                    });
                });
            });
        });
    </script>
</body>

</html>