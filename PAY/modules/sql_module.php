<?php
include_once __DIR__ . '/../init.php';


/*

@INSTRUCTIONS

 - Include the sql_module first
 - Then enter the function you want to use

*/

/*

@PARAMS

    1. Query [string] - any query, even with the question mark, as long as string.
    2. Types [string] - specifies the type of the binding variables. (Search documentation for bind types).
    3. Binding Vars [mixed] - any value or variable and as many of it too. (More info on mixed on documentation)

*/

function insert($query, $types, mixed ...$vars)
{
    global $conn;
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        throw new Exception('Prepare failed: ' . $conn->error);
        return false;
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
    $assoc_array = [];
    if ($result->num_rows > 0) {
        $assoc_array = $result->fetch_assoc();
    }
    $stmt->close();
    return $assoc_array;
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
    $assoc_array = [];
    if ($result->num_rows > 0) {
        $assoc_array = $result->fetch_all(MYSQLI_ASSOC);
    }
    $stmt->close();
    return $assoc_array;
}

function update($query, $types, mixed ...$vars)
{
    global $conn;
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        throw new Exception('Prepare failed: ' . $conn->error);
        return false;
    }
    $stmt->bind_param($types, ...$vars);
    $stmt->execute();
    $stmt->close();
    return true;
}

function delete() {}
function does_exist() {}
