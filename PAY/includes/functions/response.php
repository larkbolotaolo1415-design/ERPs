<?php

function json_success($data = [])
{
    header('Content-Type: application/json');
    echo json_encode(array_merge(['status' => 'success'], $data));
    exit;
}

function json_error($message, $code = 400)
{
    header('Content-Type: application/json');
    http_response_code($code);
    echo json_encode(['status' => 'error', 'message' => $message]);
    exit;
}