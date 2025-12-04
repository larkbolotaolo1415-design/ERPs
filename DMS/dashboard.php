<?php
require_once __DIR__ . '/includes/db_connect.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Ensure user_type_id is in session; derive if missing
if (!isset($_SESSION['user_type_id'])) {
    $stmt = $pdo->prepare('SELECT user_type_id FROM users WHERE id = ?');
    $stmt->execute([(int)$_SESSION['user_id']]);
    $row = $stmt->fetch();
    $_SESSION['user_type_id'] = $row ? (int)$row['user_type_id'] : null;
}

// Enforce password change if required
$fpStmt = $pdo->prepare('SELECT force_password_change FROM users WHERE id = ?');
$fpStmt->execute([(int)$_SESSION['user_id']]);
$fp = $fpStmt->fetch();
if ($fp && (int)$fp['force_password_change'] === 1) {
    header('Location: change_password.php');
    exit();
}

$userId = (int)$_SESSION['user_id'];
$userName = $_SESSION['user_name'] ?? '';
$userTypeId = (int)($_SESSION['user_type_id'] ?? 0);
include __DIR__ . '/dashboard_template.php';
?>
