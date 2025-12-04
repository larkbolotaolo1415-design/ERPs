<?php
// Minimal Google callback stub: expects provider to send validated email
require_once __DIR__ . '/includes/db_connect.php';

$email = isset($_GET['email']) ? trim($_GET['email']) : '';
if ($email === '') {
    header('Location: login.php');
    exit();
}

$stmt = $pdo->prepare('SELECT id, name, email, role, user_type_id, force_password_change FROM users WHERE email = ?');
$stmt->execute([$email]);
$user = $stmt->fetch();
if (!$user) { header('Location: login.php'); exit(); }

$_SESSION['user_id'] = $user['id'];
$_SESSION['user_name'] = $user['name'];
$_SESSION['user_email'] = $user['email'];
$_SESSION['user_role'] = $user['role'];
$_SESSION['user_type_id'] = isset($user['user_type_id']) ? (int)$user['user_type_id'] : null;

if (!empty($user['force_password_change']) && (int)$user['force_password_change'] === 1) {
    // Log first login via Google
    try {
        $pdo->prepare('INSERT INTO audit_logs (admin_id, action, target_type, target_id, details) VALUES (?,?,?,?,?)')
            ->execute([$user['id'], 'account_first_login', 'user', $user['id'], 'google']);
    } catch (Exception $e) {
        // TIDY: Silent failure for audit log
    }
    header('Location: change_password.php');
    exit();
}

if ($user['role'] === 'admin') {
    header('Location: admin/admin_home.php');
} else {
    header('Location: dashboard.php');
}
exit();
?>

