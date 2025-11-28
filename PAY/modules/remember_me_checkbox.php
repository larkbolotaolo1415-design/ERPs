<?php

require_once __DIR__ . '/../init.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_COOKIE['remember_me_is_checked'])) {
        if ($_COOKIE['remember_me_is_checked'] == 1) {
            echo json_encode(['is_remembered' => 1]);
            exit;
        } else {
            echo json_encode(['is_remembered' => 0]);
            exit;
        }
    } else {
        echo json_encode(['is_remembered' => 0]);
        exit;
    }

    echo json_encode(['is_remembered' => 0]);
}
