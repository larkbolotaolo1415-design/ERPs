<?php
header("Content-Type: application/json");

require '../../core/connection.php';

$remoteUrl = 'http://26.161.108.142/Inventory-System-New-main/php/get_medicines.php';

function fetch_remote($url) {
    if (function_exists('curl_version')) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $resp = curl_exec($ch);
        $err = curl_error($ch);
        $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($resp === false || $http < 200 || $http >= 300) {
            return ['ok'=>false, 'error'=>$err ?: "HTTP $http"];
        }
        return ['ok'=>true, 'body'=>$resp];
    }
    $resp = @file_get_contents($url);
    if ($resp === false) return ['ok'=>false,'error'=>'file_get_contents_failed'];
    return ['ok'=>true,'body'=>$resp];
}

function map_remote_to_local($item) {
    return [
        'medicine_id'    => $item['id'] ?? null,
        'medicine_group' => $item['category'] ?? 'Uncategorized',
        'medicine_name'  => $item['name'] ?? null,
        'generic_name'   => $item['generic_name'] ?? null,
        'dosage'         => $item['dosage'] ?? null,
        'form'           => $item['form'] ?? null,
        'stock'          => isset($item['quantity']) ? (int)$item['quantity'] : 0,
        'price'          => isset($item['price']) ? (float)$item['price'] : 0.00
    ];
}

function upsert_item($conn, $data) {
    $stmt = $conn->prepare("SELECT medicine_id FROM medicines WHERE medicine_id = ? LIMIT 1");
    $stmt->bind_param('s', $data['medicine_id']);
    $stmt->execute();
    $res = $stmt->get_result();
    $existing = $res->fetch_assoc();
    $stmt->close();

    if ($existing) {
        $fields = [];
        $params = [];
        $types = '';
        foreach ($data as $k => $v) {
            if ($k == 'medicine_id') continue;
            $fields[] = "`$k` = ?";
            $params[] = $v;
            $types .= is_numeric($v) ? 'd' : 's';
        }
        $params[] = $data['medicine_id'];
        $types .= 's';

        $sql = "UPDATE medicines SET ".implode(', ', $fields)." WHERE medicine_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok ? 'updated' : 'error';

    } else {
        $cols = array_keys($data);
        $placeholders = implode(',', array_fill(0, count($cols), '?'));
        $sql = "INSERT INTO medicines (`".implode('`,`', $cols)."`) VALUES ($placeholders)";
        $stmt = $conn->prepare($sql);
        $types = '';
        $params = [];
        foreach ($data as $v) {
            $types .= is_numeric($v) ? 'd' : 's';
            $params[] = $v;
        }
        $stmt->bind_param($types, ...$params);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok ? 'inserted' : 'error';
    }
}

$report = ['processed'=>0,'inserted'=>0,'updated'=>0,'errors'=>[]];

$f = fetch_remote($remoteUrl);
if (!$f['ok']) {
    http_response_code(502);
    $errorMsg = $f['error'] ?? 'Unknown error';
    // Provide more descriptive error messages
    if (strpos($errorMsg, 'HTTP 502') !== false || strpos($errorMsg, '502') !== false) {
        $errorMsg = 'Bad Gateway: Unable to connect to inventory system';
    } elseif (strpos($errorMsg, 'timeout') !== false || strpos($errorMsg, 'TIMEOUT') !== false) {
        $errorMsg = 'Connection timeout: Inventory system did not respond in time';
    } elseif (strpos($errorMsg, 'Connection refused') !== false || strpos($errorMsg, 'refused') !== false) {
        $errorMsg = 'Connection refused: Inventory system is not available';
    }
    echo json_encode(['status'=>'error','error'=>$errorMsg, 'message'=>'Failed to sync medicines from inventory system']);
    exit;
}

$decoded = json_decode($f['body'], true);
if ($decoded === null) {
    http_response_code(502);
    echo json_encode([
        'status'=>'error',
        'error'=>'Invalid response from inventory system',
        'message'=>'The inventory system returned invalid data. Please contact support.'
    ]);
    exit;
}

$items = is_array($decoded['data'] ?? null) ? $decoded['data'] : [];
if (empty($items)) {
    echo json_encode(['status'=>'warning','report'=>$report]);
    exit;
}

foreach ($items as $item) {
    if (!is_array($item)) continue;
    $report['processed']++;
    $mapped = map_remote_to_local($item);

    if (empty($mapped['medicine_id'])) {
        $report['errors'][] = 'Missing medicine_id';
        continue;
    }

    $res = upsert_item($connection, $mapped);
    if ($res === 'inserted') $report['inserted']++;
    elseif ($res === 'updated') $report['updated']++;
    else $report['errors'][] = $mapped['medicine_id'];
}

/* -------------------------------
   REMOVE LOCAL ITEMS NOT IN REMOTE
-------------------------------- */
$remote_ids = array_map(fn($x) => $x['id'], $items);

if (!empty($remote_ids)) {
    $placeholders = implode(',', array_fill(0, count($remote_ids), '?'));
    $sql = "DELETE FROM medicines WHERE medicine_id NOT IN ($placeholders)";
    $stmt = $connection->prepare($sql);

    $types = str_repeat('s', count($remote_ids));
    $stmt->bind_param($types, ...$remote_ids);

    $stmt->execute();
    $stmt->close();
}

echo json_encode(['status'=>'success','report'=>$report]);
?>
