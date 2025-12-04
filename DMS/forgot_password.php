<?php
/**
 * Forgot Password Page
 * Document Management System
 * 
 * This page allows users to request a password reset link via email.
 * Uses PHPMailer to send reset tokens with 30-minute expiration.
 */

require_once __DIR__ . '/includes/db_connect.php';
require_once __DIR__ . '/includes/phpmailer_config.php';

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

// Handle forgot password form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    
    if (empty($email)) {
        $error_message = 'Please enter your email address.';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = 'Please enter a valid email address.';
    } else {
        try {
            // Check if user exists
            $stmt = $pdo->prepare("SELECT id, name FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user) {
                // Generate reset token
                $token = bin2hex(random_bytes(32));
                $expires_at = date('Y-m-d H:i:s', strtotime('+30 minutes'));
                
                // Store reset token in database
                $stmt = $pdo->prepare("INSERT INTO password_reset (email, token, expires_at) VALUES (?, ?, ?)");
                $stmt->execute([$email, $token, $expires_at]);
                
                // Send email using PHPMailer
                if (sendPasswordResetEmail($email, $user['name'], $token)) {
                    $success_message = 'Password reset link has been sent to your email address.';
                } else {
                    $error_message = 'Failed to send email. Please try again later.';
                }
            } else {
                $success_message = 'If an account with that email exists, a password reset link has been sent.';
            }
        } catch (PDOException $e) {
            $error_message = 'An error occurred. Please try again later.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Document Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href='https://fonts.googleapis.com/css?family=Poppins' rel='stylesheet'>
    <link href='https://fonts.googleapis.com/css?family=Roboto' rel='stylesheet'>
    <link rel="stylesheet" href="assets/css/root_colors_fonts.css">
    
    <style>
        :root {
            --primary-color: #2563EB;
            --secondary-color: #1E3A8A;
            --accent-color: #DC2626;
            --success-color: #10B981;
            --warning-color: #FBBF24;
            --dark-text: #2C3E50;
            --light-text: #6C757D;
        }
        
        body {
            background: var(--white);
            min-height: 100vh;
            display: flex;
            align-items: center;
            font-family: 'Roboto';
        }
        h3, h6 {
            font-family: 'Poppins';
        }
        /* TIDY: Additional styling for consistency */
        .forgot-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            box-shadow: 0 20px 40px rgba(44, 95, 95, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        .forgot-header {
            background: var(--secondary-color);
            color: white;
            padding: 2.5rem;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        .form-control {
            border: 2px solid #E9ECEF;
            border-radius: 10px;
            padding: 12px 16px;
            font-size: 16px;
            transition: all 0.3s ease;
        }
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(44, 95, 95, 0.25);
            transform: translateY(-1px);
        }
        .form-label {
            font-weight: 600;
            color: var(--deep-navy);
        }
        .btn-reset {
            background: var(--primary-color);
            font-family: 'Poppins';
            border: none;
            padding: 14px;
            letter-spacing: 1px;
            border-radius: 10px;
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
            background: var(--secondary-color);
        }
        .alert {
            border-radius: 10px;
            border: none;
            padding: 12px 16px;
        }
        .alert-danger {
            background: linear-gradient(135deg, #FFE6E6 0%, #FFCCCC 100%);
            color: #721C24;
        }
        .alert-success {
            background: linear-gradient(135deg, #E6F7E6 0%, #CCF2CC 100%);
            color: #155724;
        }
        .text-decoration-none {
            color: var(--primary-color);
            font-weight: 500;
            transition: color 0.3s ease;
        }
        .text-decoration-none:hover {
            color: var(--secondary-color);
        }
        .bg-light {
            background: linear-gradient(135deg, #F8F9FA 0%, #E9ECEF 100%) !important;
            border-radius: 10px;
        }
        .text-muted {
            color: var(--light-text) !important;
        }
        /* UI Improvement: Better card body padding */
        .card-body {
            padding: 2rem;
        }
        .form-control {
            border-radius: 8px;
            border: 1px solid var(--light-gray-2);
            padding: 0.75rem 1rem;
            transition: all 0.2s ease;
        }
        .form-control:focus {
            border-color: var(--trust-blue);
            box-shadow: 0 0 0 0.2rem rgba(37, 99, 235, 0.15);
            outline: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-4">
                <div class="card forgot-card">
                    <div class="forgot-header">
                        <h3><i class="fas fa-key me-2"></i>Forgot Password</h3>
                        <p class="mb-0">Enter your email to reset your password</p>
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
                        
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="email" class="form-label">
                                    <i class="fas fa-envelope me-2"></i>Email Address
                                </label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" 
                                       required>
                                <div class="form-text">Enter the email address associated with your account.</div>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-reset">
                                    <i class="fas fa-paper-plane me-2"></i>SEND RESET LINK
                                </button>
                            </div>
                        </form>
                        
                        <hr class="my-4">
                        
                        <div class="text-center">
                            <p class="mb-2">
                                <a href="login.php" class="text-decoration-none">
                                    <i class="fas fa-arrow-left me-2"></i>Back to Login
                                </a>
                            </p>
                        </div>
                        
                        <!-- Email Configuration Note -->
                        <div class="mt-4 p-3 bg-light rounded">
                            <h6 class="text-muted mb-2"><i class="fas fa-info-circle me-2"></i>Email Configuration:</h6>
                            <small class="text-muted">
                                To enable email functionality, configure your mail server settings in the PHP configuration or use PHPMailer with SMTP credentials.
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
