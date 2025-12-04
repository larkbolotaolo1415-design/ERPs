<?php
/**
 * Logout Page
 * Document Management System
 * 
 * This page destroys the user session and redirects to the login page.
 * Clears all session data for security.
 */

require_once __DIR__ . '/includes/db_connect.php';

// Destroy all session data
session_unset();
session_destroy();

// Redirect to login page
header('Location: login.php');
exit();
?>
