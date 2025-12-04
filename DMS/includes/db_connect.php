<?php
/**
 * Database Connection File
 * Document Management System
 * 
 * This file establishes a connection to the MySQL database using XAMPP.
 * Include this file in any page that needs database access.
 * 
 * Configuration:
 * - Host: localhost
 * - Username: root
 * - Password: (empty)
 * - Database: document_management_system
 */

// Database configuration
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'document_management_system';

// Create connection
try {
    $pdo = new PDO("mysql:host=$host;dbname=$database;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    // Set timezone for both PHP and MySQL to ensure consistency
    date_default_timezone_set('Asia/Manila');
    $pdo->exec("SET time_zone = '+08:00'");
    
    // Connection successful - no output needed
} catch(PDOException $e) {
    // Handle connection error
    die("Connection failed: " . $e->getMessage());
}

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
