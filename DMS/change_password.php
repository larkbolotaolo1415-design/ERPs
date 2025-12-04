<?php
require_once __DIR__ . '/includes/db_connect.php';
if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit(); }

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    if (strlen($new) < 8) {
        $msg = 'Password must be at least 8 characters.';
    } elseif ($new !== $confirm) {
        $msg = 'Passwords do not match.';
    } else {
        $hash = password_hash($new, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare('UPDATE users SET password = ?, password_hash = ?, force_password_change = 0 WHERE id = ?');
        $stmt->execute([$hash, $hash, (int)$_SESSION['user_id']]);
        // Log change
        try {
            $pdo->prepare('INSERT INTO audit_logs (admin_id, action, target_type, target_id, details) VALUES (?,?,?,?,?)')
                ->execute([(int)$_SESSION['user_id'], 'change_password', 'user', (int)$_SESSION['user_id'], null]);
        } catch (Exception $e) {}
        header('Location: dashboard.php');
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Change Password - Document Management System</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
  <link href='https://fonts.googleapis.com/css?family=Poppins:600,700|Roboto:400,500&display=swap' rel='stylesheet'>
  <link rel="stylesheet" href="assets/css/root_colors_fonts.css">
  <style>
    /* UI Improvement: Enhanced change password page styling */
    body {
      background-color: var(--light-gray);
      font-family: var(--font-body);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .password-card {
      background: var(--white);
      border-radius: 12px;
      box-shadow: 0 4px 16px rgba(0,0,0,0.1);
      border: none;
      max-width: 500px;
      width: 100%;
    }
    .password-header {
      background: var(--trust-blue);
      color: var(--white);
      padding: 1.5rem 2rem;
      border-radius: 12px 12px 0 0;
      text-align: center;
    }
    .password-header h3 {
      font-family: var(--font-headings);
      font-weight: 600;
      margin: 0;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 0.75rem;
    }
    .card-body {
      padding: 2rem;
    }
    .form-label {
      font-weight: 600;
      color: var(--dark-gray);
      margin-bottom: 0.5rem;
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
    .form-text {
      font-size: 0.875rem;
      color: var(--dark-gray);
      opacity: 0.7;
    }
    .btn-primary {
      background: var(--trust-blue);
      border: none;
      border-radius: 8px;
      padding: 0.75rem 1.5rem;
      font-weight: 500;
      transition: all 0.3s ease;
    }
    .btn-primary:hover {
      background: var(--deep-navy);
      transform: translateY(-2px);
      box-shadow: 0 4px 8px rgba(0,0,0,0.15);
    }
    .alert-danger {
      background-color: rgba(220, 38, 38, 0.1);
      color: var(--red);
      border: 1px solid rgba(220, 38, 38, 0.2);
      border-left: 4px solid var(--red);
      border-radius: 8px;
      padding: 1rem 1.25rem;
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-md-6 col-lg-5">
        <div class="card password-card">
          <div class="password-header">
            <h3><i class="fas fa-key"></i>Change Password</h3>
          </div>
          <div class="card-body">
            <?php if ($msg): ?>
              <div class="alert alert-danger mb-4">
                <i class="fas fa-exclamation-triangle"></i>
                <?php echo htmlspecialchars($msg); ?>
              </div>
            <?php endif; ?>
            <form method="POST">
              <div class="mb-4">
                <label class="form-label">New Password <span class="text-danger">*</span></label>
                <input type="password" class="form-control" name="new_password" required minlength="8">
                <div class="form-text">Password must be at least 8 characters long</div>
              </div>
              <div class="mb-4">
                <label class="form-label">Confirm Password <span class="text-danger">*</span></label>
                <input type="password" class="form-control" name="confirm_password" required minlength="8">
                <div class="form-text">Please re-enter your new password</div>
              </div>
              <div class="d-grid">
                <button class="btn btn-primary" type="submit">
                  <i class="fas fa-save me-2"></i>Update Password
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>


