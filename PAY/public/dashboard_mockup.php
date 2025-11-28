<?php
require_once __DIR__ . '/../init.php';

?>
<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Hospital Payroll Dashboard</title>
	<link rel="stylesheet" href="../assets/css/dashboard_style.css">
	<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
</head>

<body>
	<div class="sidebar">
		<div class="logo">Hospital Name</div>
		<ul>
			<li>Dashboard</li>
			<li>User Management</li>
			<li>Role Management</li>
			<li>Company Settings</li>
			<li>System Configuration</li>
			<li>Data Backup & Restore</li>
			<li>Audit Logs</li>
			<li>Summary Report</li>
			<li>Security Controls</li>
			<li id="logout-btn">Logout</li>
		</ul>
	</div>

	<div class="main-content">
		<div class="stats">
			<div class="card">
				<h3>Total Employees</h3>
				<p>128</p>
			</div>
			<div class="card">
				<h3>Departments</h3>
				<p>8</p>
			</div>
			<div class="card">
				<h3>Current Payroll Status</h3>
				<p class="status">processing...</p>
			</div>
			<div class="card">
				<h3>Pending Approvals</h3>
				<p>12</p>
			</div>
		</div>

		<div class="summary">
			<h2>Payroll Summary</h2>
			<table class="summary-table">
				<tr>
					<th>Metric</th>
					<th>Amount</th>
				</tr>
				<tr>
					<td>Total Gross Pay</td>
					<td>₱1,240,500.00</td>
				</tr>
				<tr>
					<td>Total Deductions</td>
					<td>₱145,800.00</td>
				</tr>
				<tr>
					<td>Total Net Pay</td>
					<td>₱1,094,700.00</td>
				</tr>
				<tr>
					<td>Average Net per Employee</td>
					<td>₱8,554.69</td>
				</tr>
			</table>
			<h3 style="margin-top:32px;">Department Breakdown</h3>
			<table class="dept-table">
				<tr>
					<th>Department</th>
					<th>Employees</th>
					<th>Gross Pay</th>
					<th>Net Pay</th>
				</tr>
				<tr>
					<td>Emergency</td>
					<td>24</td>
					<td>₱240,000.00</td>
					<td>₱210,000.00</td>
				</tr>
				<tr>
					<td>Pediatrics</td>
					<td>16</td>
					<td>₱160,000.00</td>
					<td>₱140,000.00</td>
				</tr>
				<tr>
					<td>Radiology</td>
					<td>12</td>
					<td>₱120,000.00</td>
					<td>₱105,000.00</td>
				</tr>
				<tr>
					<td>Admin</td>
					<td>8</td>
					<td>₱80,000.00</td>
					<td>₱70,000.00</td>
				</tr>
				<tr>
					<td>Other</td>
					<td>68</td>
					<td>₱640,500.00</td>
					<td>₱569,700.00</td>
				</tr>
			</table>
			<div class="buttons">
				<button>View Detailed Payroll Report</button>
				<button class="blue">Generate Cutoff Summary PDF</button>
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
	</script>
</body>

</html>