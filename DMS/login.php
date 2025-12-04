<?php
/**
 * Login Page
 * Document Management System
 * 
 * This page handles user authentication for admin, doctor, and patient roles.
 * Users are redirected to their respective home pages based on their role.
 */

require_once __DIR__ . '/includes/db_connect.php';

// One-time token login
if (isset($_GET['ott'])) {
    $token = $_GET['ott'];
    try {
        $stmt = $pdo->prepare("SELECT user_id, expires_at, used FROM one_time_tokens WHERE token = ?");
        $stmt->execute([$token]);
        $row = $stmt->fetch();
        if ($row && (int)$row['used'] === 0 && strtotime($row['expires_at']) > time()) {
            $pdo->prepare("UPDATE one_time_tokens SET used = 1 WHERE token = ?")->execute([$token]);
            $uStmt = $pdo->prepare("SELECT id, name, email, role, user_type_id, force_password_change FROM users WHERE id = ?");
            $uStmt->execute([(int)$row['user_id']]);
            $user = $uStmt->fetch();
            if ($user) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['user_type_id'] = isset($user['user_type_id']) ? (int)$user['user_type_id'] : null;
                // Log first login via OTT
                try { $pdo->prepare("INSERT INTO audit_logs (admin_id, action, target_type, target_id, details) VALUES (?,?,?,?,?)")
                    ->execute([$user['id'], 'account_first_login', 'user', $user['id'], null]); } catch (Exception $e) {}
                if (!empty($user['force_password_change']) && (int)$user['force_password_change'] === 1) {
                    header('Location: change_password.php');
                } else {
                    header('Location: dashboard.php');
                }
                exit();
            }
        }
    } catch (Exception $e) {}
}

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

$error_message = '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    if (empty($email) || empty($password)) {
        $error_message = 'Please fill in all fields.';
    } else {
        try {
            // Check if user exists
            $stmt = $pdo->prepare("SELECT id, name, email, password, role, user_type_id, force_password_change FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                // Login successful
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['user_type_id'] = isset($user['user_type_id']) ? (int)$user['user_type_id'] : null;
                if (!empty($user['force_password_change']) && (int)$user['force_password_change'] === 1) {
                    header('Location: change_password.php');
                } else {
                    if ($user['role'] === 'admin') { header('Location: admin/admin_home.php'); } else { header('Location: dashboard.php'); }
                }
                exit();
            } else {
                $error_message = 'Invalid email or password.';
            }
        } catch (PDOException $e) {
            $error_message = 'Database error. Please try again.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Document Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href='https://fonts.googleapis.com/css?family=Poppins' rel='stylesheet'>
    <link href='https://fonts.googleapis.com/css?family=Roboto' rel='stylesheet'>
    <link rel="stylesheet" href="assets/css/root_colors_fonts.css">


        <style>
        body {
            background-color: var(--white);
            font-family: 'Roboto';
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            box-shadow: 0 20px 40px rgba(44, 95, 95, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .login-header {
            background-color: var(--deep-navy);
            padding: 2rem 1rem;
            text-align: center;
            color: var(--white);
            font-family: var(--font-headings);
        }


        .login-header h3 {
            font-weight: 600;
            font-size: 1.5rem;
        }

        .login-header p {
            font-size: 14px;
            opacity: 0.9;
        }

        .card-body {
            padding: 2rem;
        }

        .form-label {
            font-weight: 600;
            color: var(--deep-navy);
        }

        .form-control {
            border-radius: 10px;
            border: 1px solid #D1D5DB;
            padding: 10px 14px;
            transition: all 0.2s ease;
        }

        .form-control:focus {
            border-color: var(--trust-blue);
            box-shadow: 0 0 0 0.2rem rgba(44, 95, 95, 0.25);   
            transform: translateY(-1px);     
        }

        .btn-login {
            background-color: var(--trust-blue);
            border: none;
            border-radius: 10px;
            padding: 14px;
            font-family: var(--font-headings);
            letter-spacing: 1px;
            text-transform: uppercase;
            font-weight: 500;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .btn-login::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }
        .btn-login:hover::before {
            left: 100%;
            background: var(--deep-navy);
        }

        .text-links a {
            color: var(--trust-blue);
            text-decoration: none;
            font-weight: 500;
        }

        .text-links a:hover {
            text-decoration: underline;
        }

        .alert-danger {
            background-color: #FEE2E2;
            border: 1px solid #FCA5A5;
            color: #B91C1C;
            border-radius: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-4">
                <div class="card login-card">
                    <div class="login-header">
                        <h3><i class="fas fa-file-medical-alt me-2"></i>DMS Login</h3>
                        <p class="mb-0">Document Management System</p>
                    </div>
                    <div class="card-body p-4">
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
                            </div>
                            
                            <div class="mb-3">
                                <label for="password" class="form-label">
                                    <i class="fas fa-lock me-2"></i>Password
                                </label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-login">
                                    <i class="fas fa-sign-in-alt me-2"></i>Login
                                </button>
                            </div>
                        </form>
                        
                        <hr class="my-4">
                        
                        <div class="text-center">
                            <p class="mb-2">
                                <a href="forgot_password.php" class="text-decoration-none">
                                    <i class="fas fa-key me-2"></i>Forgot Password?
                                </a>
                            </p>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
