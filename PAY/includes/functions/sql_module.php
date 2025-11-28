<?php

function insert($query, $types, mixed ...$vars)
{
    global $conn;
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        throw new Exception('Prepare failed: ' . $conn->error);
    }
    $stmt->bind_param($types, ...$vars);
    $stmt->execute();
    $stmt->close();
    return true;
}

function select($query, $types = "", mixed ...$vars)
{
    global $conn;
    $stmt = $conn->prepare($query);
    if (!$stmt) throw new Exception('Prepare failed: ' . $conn->error);
    if ($types && !empty($vars)) {
        $stmt->bind_param($types, ...$vars);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $assoc = [];
    if ($result && $result->num_rows > 0) {
        $assoc = $result->fetch_assoc();
    }
    $stmt->close();
    return $assoc;
}

function select_many($query, $types = "", mixed ...$vars)
{
    global $conn;
    $stmt = $conn->prepare($query);
    if (!$stmt) throw new Exception('Prepare failed: ' . $conn->error);
    if ($types && !empty($vars)) {
        $stmt->bind_param($types, ...$vars);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $rows = [];
    if ($result && $result->num_rows > 0) {
        $rows = $result->fetch_all(MYSQLI_ASSOC);
    }
    $stmt->close();
    return $rows;
}

function update($query, $types, mixed ...$vars)
{
    global $conn;
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        throw new Exception('Prepare failed: ' . $conn->error);
    }
    $stmt->bind_param($types, ...$vars);
    $stmt->execute();
    $stmt->close();
    return true;
}

function delete($query, $types, mixed ...$vars)
{
    global $conn;
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        throw new Exception('Prepare failed: ' . $conn->error);
    }
    $stmt->bind_param($types, ...$vars);
    $stmt->execute();
    $stmt->close();
    return true;
}
