<?php
/**
 * Reset Password Page
 * Document Management System
 * 
 * This page allows users to reset their password using a token from email.
 * Tokens expire after 30 minutes for security.
 */

require_once __DIR__ . '/includes/db_connect.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    $role = $_SESSION['user_role'];
    switch ($role) {
        case 'admin':
            header('Location: admin/admin_home.php');
            break;
        case 'doctor':
            header('Location: doctor/doctor_home.php');
            break;
        case 'patient':
            header('Location: patient/patient_home.php');
            break;
    }
    exit();
}

$success_message = '';
$error_message = '';
$token_valid = false;
$token = '';

// Check if token is provided
if (!isset($_GET['token']) || empty($_GET['token'])) {
    $error_message = 'Invalid or missing reset token.';
} else {
    $token = $_GET['token'];

    try {
        // Check if token exists and is not expired
        $stmt = $pdo->prepare("SELECT email FROM password_reset WHERE token = ? AND expires_at > NOW()");
        $stmt->execute([$token]);
        $reset_record = $stmt->fetch();
        
        if ($reset_record) {
            $token_valid = true;
        } else {
            $error_message = 'Invalid or expired reset token. Please request a new password reset.';
        }
    } catch (PDOException $e) {
        $error_message = 'An error occurred. Please try again later.';
    }
}

// Handle password reset form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $token_valid) {
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    if (empty($password) || empty($confirm_password)) {
        $error_message = 'Please fill in all fields.';
} elseif (strlen($password) < 6) {
    $error_message = 'Password must be at least 6 characters long.';
} elseif ($password !== $confirm_password) {
        $error_message = 'Passwords do not match.';
    } else {
        try {
            // Get email from reset token
            $stmt = $pdo->prepare("SELECT email FROM password_reset WHERE token = ? AND expires_at > NOW()");
            $stmt->execute([$token]);
            $reset_record = $stmt->fetch();
            
            if ($reset_record) {
                $email = $reset_record['email'];
                
                // Update user password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = ?");
                $stmt->execute([$hashed_password, $email]);
                
                // Delete used reset token
                $stmt = $pdo->prepare("DELETE FROM password_reset WHERE token = ?");
                $stmt->execute([$token]);
                
                $success_message = 'Password has been reset successfully! You can now login with your new password.';
                $token_valid = false; // Hide form after successful reset
            } else {
                $error_message = 'Invalid or expired reset token.';
            }
        } catch (PDOException $e) {
            $error_message = 'Failed to reset password. Please try again.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Document Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #F9FAFB;
            font-family: 'Roboto';
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .reset-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        }
        .reset-header {
            background-color: #1E3A8A;
            padding: 2rem 1rem;
            text-align: center;
            color: #FFFFFF;
            font-family: 'Poppins', sans-serif;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .btn-reset {
            background-color: #2563EB;
            border: none;
            border-radius: 10px;
            padding: 14px;
            font-family: 'Poppins';
            letter-spacing: 1px;
            text-transform: uppercase;
            font-weight: 500;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        .btn-reset::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }
        .btn-reset:hover::before {
            left: 100%;
            background: #1E3A8A;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-4">
                <div class="card reset-card">
                    <div class="reset-header">
                        <h3><i class="fas fa-lock me-2"></i>Reset Password</h3>
                        <p class="mb-0">Enter your new password</p>
                    </div>
                    <div class="card-body p-4">
                        <?php if ($success_message): ?>
                            <div class="alert alert-success" role="alert">
                                <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($success_message); ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($error_message): ?>
                            <div class="alert alert-danger" role="alert">
                                <i class="fas fa-exclamation-triangle me-2"></i><?php echo htmlspecialchars($error_message); ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($token_valid): ?>
                            <form method="POST" action="">
                                <div class="mb-3">
                                    <label for="password" class="form-label">
                                        <i class="fas fa-lock me-2"></i>New Password
                                    </label>
                                    <input type="password" class="form-control" id="password" name="password" 
                                           minlength="6" required>
                                    <div class="form-text">Password must be at least 6 characters long.</div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="confirm_password" class="form-label">
                                        <i class="fas fa-lock me-2"></i>Confirm New Password
                                    </label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                                           minlength="6" required>
                                </div>
                                
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary btn-reset">
                                        <i class="fas fa-save me-2"></i>Reset Password
                                    </button>
                                </div>
                            </form>
                        <?php endif; ?>
                        
                        <hr class="my-4">
                        
                        <div class="text-center">
                            <p class="mb-0">
                                <a href="login.php" class="text-decoration-none">
                                    <i class="fas fa-arrow-left me-2"></i>Back to Login
                                </a>
                            </p>
                        </div>
                        
                        <!-- Security Note -->
                        <div class="mt-4 p-3 bg-light rounded">
                            <h6 class="text-muted mb-2"><i class="fas fa-shield-alt me-2"></i>Security Note:</h6>
                            <small class="text-muted">
                                • Reset tokens expire after 30 minutes<br>
                                • Each token can only be used once<br>
                                • Choose a strong password for better security
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Password confirmation validation
        document.getElementById('confirm_password').addEventListener('input', function() {
            const password = document.getElementById('password').value;
            const confirmPassword = this.value;
            
            if (password !== confirmPassword) {
                this.setCustomValidity('Passwords do not match');
            } else {
                this.setCustomValidity('');
            }
        });
    </script>
</body>
</html>
