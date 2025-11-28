<?php

require_once __DIR__ . '/../init.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Start session if not already started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $ses_id = $_COOKIE['session_id'] ?? $_SESSION['session_id'] ?? null;
    if ($ses_id) {
        logout_session($ses_id);
    }

    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    $_SESSION = [];
    session_unset();
    session_destroy();

    setcookie(session_name(), "", time() - 3600, "/");
    setcookie("session_id", "", time() - 3600, "/");
    setcookie("remember_me", "", time() - 3600, "/");
    setcookie("remember_me_is_checked", "", time() - 3600, "/");

    if (isset($conn) && isset($_COOKIE['remember_me']) && $_COOKIE['remember_me'] !== '') {
        $stmt = $conn->prepare('UPDATE remember_sessions SET r_session_status=\'ended\' WHERE r_session_id=?');
        if ($stmt) {
            $stmt->bind_param('s', $_COOKIE['remember_me']);
            $stmt->execute();
        }
    }

    json_success(['message' => 'Logged out successfully']);
} else {
    json_error('Invalid request method');
}
