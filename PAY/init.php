<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// define('INCLUDES_PATH', BASE_PATH . '/includes');
// define('FUNCTIONS_PATH', BASE_PATH . '/includes/functions');
// define('CONFIG_PATH', BASE_PATH . '/config');
// define('MODULES_PATH', BASE_PATH . '/modules');
// define('PUBLIC_PATH', BASE_PATH . '/public');

require_once __DIR__ . '/config/paths.php';
require_once CONFIG_PATH . '/config.php';
require_once INCLUDES_PATH . '/db_connect.php';

// INCLUDE ALL PHP FILE IN FUNCTIONS
$functionFiles = glob(INCLUDES_PATH . '/functions/*.php');
foreach ($functionFiles as $file) {
    require_once $file;
}
