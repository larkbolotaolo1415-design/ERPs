<?php
// CHECK IF SESSION EXIST

require_once __DIR__ . '/../init.php';
require_once MODULES_PATH . '/login.php';

if (isset($_COOKIE['session_id']) && $_COOKIE["session_id"] != "") {
	// QUERY
	$stmt = $conn->prepare("SELECT * FROM sessions WHERE session_id = ? LIMIT 1");
	$stmt->bind_param("s", $_COOKIE['session_id']);
	$stmt->execute();
	$qrd_session_id = $stmt->get_result();
	if ($qrd_session_id->num_rows > 0) {
		$row = $qrd_session_id->fetch_assoc();

		$stmt = $conn->prepare("SELECT * FROM user_table WHERE user_id = ?");
		if (!$stmt) throw new Exception('Prepare failed: ' . $conn->error);
		$stmt->bind_param("s", $row['user_id']);
		$stmt->execute();
		$result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $user_roww = $result->fetch_assoc();
            $role_name = '';
            $stmtRole = $conn->prepare("SELECT role_name FROM roles_table WHERE role_id = ? LIMIT 1");
            if ($stmtRole) { $stmtRole->bind_param("i", $user_roww['role_id']); $stmtRole->execute(); $resRole = $stmtRole->get_result(); if ($resRole && $resRole->num_rows>0) { $role_name = $resRole->fetch_assoc()['role_name']; } }
            if ($role_name === 'Admin') header("Location: admin_dashboard.php");
            else if ($role_name === 'Payroll Officer') header("Location: payroll_officer_dashboard.php");
            else if ($role_name === 'Manager') header("Location: manager_dashboard.php");
            else if ($role_name === 'Employee') header("Location: employee_dashboard.php");
        }
	} else {
		header("Location: login_page.php");
	}
	$session_id_row = $qrd_session_id->fetch_assoc();
}

// SCAN FOR SESSION ID
if (isset($_COOKIE['remember_me'])) {
	// GET THE USER ID, FROM DB
	// QUERY USER ID FROM THE R_SESSION'S USER ID COLUMN TO THE USER_ROLES TABLE IN THE DATABASE
	// USE LOGIN.PHP TO LOGIN FROM THE BROWSER

	$r_session_id = $_COOKIE['remember_me'];

	$stmt = $conn->prepare("SELECT * FROM remember_sessions WHERE r_session_id = ?");
	if (!$stmt) throw new Exception('Prepare failed: ' . $conn->error);
	$stmt->bind_param("s", $r_session_id);
	$stmt->execute();
	$result = $stmt->get_result();

	// IF R_SESSION FOUND
	if ($result->num_rows > 0) {
		$row =  $result->fetch_assoc();

		// QUERY USER DETAILS
		$stmt = $conn->prepare("SELECT * FROM user_table WHERE user_id = ?");
		if (!$stmt) throw new Exception('Prepare failed: ' . $conn->error);
		$stmt->bind_param("s", $row['user_id']);
		$stmt->execute();
		$user_result = $stmt->get_result();

		// IF USER WITH R_SESSION ID EXISTS
		if ($user_result->num_rows > 0) {
			$user_row = $user_result->fetch_assoc();
			$user_email = trim($user_row['user_email']);
			$user_pass = trim($user_row['password']);
			// LOGIN TO WEBPAGE
			if (isset($_COOKIE['remember_me_is_checked'])) {
				if ($_COOKIE['remember_me_is_checked'] == 1) {
					login($user_email, $user_pass, true);
				} else {
					login($user_email, $user_pass, false);
				}
			}
		}
	}
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Login and Forgot Password</title>
	<link rel="stylesheet" href="../assets/css/login_style.css">
	<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
</head>

<body>
	<div class="container">
		<!-- Login Forms -->
		<div class="form-card" id="loginForm">
			<a href="landing_page.php" class="back-link">&lt; Back</a>
			<h2>LOGIN</h2>
			<p class="subtitle">Welcome to our Payroll System</p>
			<label>Email</label>
			<input type="email" id="loginEmail" placeholder="Enter your email">
			<label>Password</label>
			<input type="password" id="loginPassword" placeholder="Enter password">
			<div class="options">
				<label><input type="checkbox" id="remember_me">Remember me</label>
				<a href="#" id="forgotLink">Forgot Password?</a>
			</div>
			<div class="buttons">
				<button id="signInBtn">Sign in</button>
			</div>
			<div class="message success" id="successMsg">Login Successful!</div>
			<div class="message fail" id="failMsg">Invalid Email or Password!</div>
		</div>

		<!-- Forgot Password - Email -->
		<div class="form-card forgot hidden" id="forgotFormEmail">
			<a href="#" class="back-link" id="backToLogin1">&lt; Back</a>
			<h2>FORGOT PASSWORD</h2>
			<p class="subtitle">An OTP will be sent to your email</p>
			<label>Email</label>
			<input type="email" placeholder="Enter your email">
			<button id="toOtpForm">Reset Password</button>
		</div>

		<!-- Forgot Password - OTP Verification-->
		<div class="form-card forgot hidden" id="forgotFormOtp">
			<a href="#" class="back-link" id="backToEmail">&lt; Back</a>
			<h2>FORGOT PASSWORD</h2>
			<p class="subtitle">An OTP will be sent to your email</p>
			<label>OTP</label>
			<input type="text" placeholder="Enter OTP Number">
			<button id="toNewPasswordForm">Reset Password</button>
			<input type="hidden" id="resetUserId" value="">
		</div>

		<!-- Forgot Password - Input New Password-->
		<div class="form-card forgot hidden" id="forgotFormNewPassword">
			<a href="#" class="back-link" id="backToOtp">&lt; Back</a>
			<h2>FORGOT PASSWORD</h2>
			<p class="subtitle">An OTP will be sent to your email</p>
			<label>New Password</label>
			<input type="password" placeholder="Enter new password">
			<label>Confirm Password</label>
			<input type="password" placeholder="Confirm new password">
			<button id="confirmResetBtn">Confirm Password Reset</button>
		</div>
	</div>

	<!-- Google Sign in -->
	<div class="modal" id="googleModal">
		<div class="modal-content">
			<h3>Sign in with Google</h3>
			<p>This is a sample modal popup (no actual Google function yet).</p>
			<button id="closeGoogleModal">Close</button>
		</div>
	</div>

	<!-- Password ResetuccessL -->
	<div class="modal" id="resetSuccessModal">
		<div class="modal-content">
			<h3>Success!</h3>
			<p>Password has been reset successfully.</p>
			<button id="closeResetSuccessModal">OK</button>
		</div>
	</div>

	<script>
		$(document).ready(function() {
			$("#forgotLink").click(function() {
				$("#loginForm").hide();
				$("#forgotFormEmail").show();
			});

			$("#backToLogin1").click(function() {
				$("#forgotFormEmail").hide();
				$("#loginForm").show();
			});

			// VERIFY IF REMEMBER BOX IS CHECKED
			$.ajax({
				url: "../modules/remember_me_checkbox.php",
				type: "POST",
				dataType: "json",
				success: function(response) {
					if (response['is_remembered'] == 1) {
						$("#remember_me").prop('checked', true);
					} else {
						$("#remember_me").prop('checked', false);
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

		// OTP FORM 
		$("#toOtpForm").click(function() {
			const email = $("#forgotFormEmail input").val().trim();
			if (email === "") return alert("Enter your email.");

			$.post("/Payroll%20System/SIA-Payroll-System-Modules/PHP-Backend/send_otp.php", {
				email: email
			}, function(res) {
				if (res.status === "success") {
					alert("OTP sent to your email!");
					$("#forgotFormEmail").hide();
					$("#forgotFormOtp").show();
				} else alert(res.message);
			}, "json");
		});

		// BACK TO EMAIL
		$("#backToEmail").click(function() {
			$("#forgotFormOtp").hide();
			$("#forgotFormEmail").show();
		});

		// TO NEW PASS FORM
		$("#toNewPasswordForm").click(function() {
			// EMAIL & OTP DATA
			const email = $("#forgotFormEmail input").val().trim();
			const otp = $("#forgotFormOtp input").val().trim();

			if (otp === "") return alert("Enter OTP.");

			// POST verify_otp.php
			$.post("/Payroll%20System/SIA-Payroll-System-Modules/PHP-Backend/Verify_otp.php", {
				email: email,
				otp: otp
			}, function(res) {
				if (res.status === "success") {
					$("#resetUserId").val(res.user_id);
					$("#forgotFormOtp").hide();
					$("#forgotFormNewPassword").show();
				} else {
					alert(res.message);
				}
			}, "json");

		});

		$("#backToOtp").click(function() {
			$("#forgotFormNewPassword").hide();
			$("#forgotFormOtp").show();
		});

		$("#googleSignInBtn").click(function() {
			$("#googleModal").css("display", "flex");
		});

		$("#closeGoogleModal").click(function() {
			$("#googleModal").hide();
		});

		$("#confirmResetBtn").click(function() {
			const user_id = $("#resetUserId").val();
			const newPass = $("#forgotFormNewPassword input[type='password']").eq(0).val().trim();
			const confirmPass = $("#forgotFormNewPassword input[type='password']").eq(1).val().trim();
			if (!newPass || !confirmPass) return alert("Please fill in both password fields.");
			if (newPass !== confirmPass) return alert("Passwords do not match.");
			$.post("/Payroll%20System/SIA-Payroll-System-Modules/PHP-Backend/reset_password.php", {
				user_id: user_id,
				password: newPass
			}, function(res) {
				if (res.status === "success") {
					$("#resetSuccessModal").css("display", "flex");
				} else {
					alert(res.message);
				}
			}, "json");
		});

		$("#closeResetSuccessModal").click(function() {
			$("#resetSuccessModal").hide();
			$("#forgotFormNewPassword").hide();
			$("#loginForm").show();
		});

		// SIGN IN BTN
		$("#signInBtn").click(() => {
			login();
		});

		// TO LOGIN.PHP
		function login() {
			const email = $("#loginEmail").val().trim();
			const pass = $("#loginPassword").val().trim();
			const remember_me = $("#remember_me").is(':checked') ? 1 : 0;

			$(".message").hide();

			if (email === "" || pass === "") {
				$("#failMsg").text("Please fill in all fields.").fadeIn().delay(2000).fadeOut();
				return;
			}

			$.ajax({
				url: "../modules/login.php",
				type: "POST",
				data: {
					email: email,
					password: pass,
					remember_me: remember_me
				},
				dataType: "json",
				success: function(response) {
					if (response.status === "success") {
                        $("#successMsg").fadeIn().delay(1000).fadeOut(function() {
                            switch (response.user_role) {
                                case "Admin":
                                    window.location.href = "admin_dashboard.php";
                                    break;
                                case "Payroll Officer":
                                    window.location.href = "payroll_officer_dashboard.php";
                                    break;
                                case "Manager":
                                    window.location.href = "manager_dashboard.php";
                                    break;
                                case "Employee":
                                    window.location.href = "employee_dashboard.php";
                                    break;

                                default:
                                    window.location.href = "login_page.php";
                            }
                        });
					} else {
						$("#failMsg").text(response.message).fadeIn().delay(2000).fadeOut();
						console.log(response.message)
					}
				},
				error: (xhr, status, error) => {
					$("#failMsg").text("Server error. Please try again.").fadeIn().delay(2000).fadeOut();
					console.log(xhr)
					console.log(status)
					console.log(error)
				}
			});
		}
	</script>
</body>

</html>