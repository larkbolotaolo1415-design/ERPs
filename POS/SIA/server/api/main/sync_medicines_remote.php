<?php
header("Content-Type: application/json");

require '../../core/connection.php'; // Your DB connection

$remoteUrl = 'http://26.161.108.142/INVENTORY_NEW/Inventory-System-New/php/get_medicines.php';

// Fetch remote JSON
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

// Map remote JSON fields to your local table
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

// Upsert function
function upsert_item($conn, $data) {
    $stmt = $conn->prepare("SELECT medicine_id FROM medicines WHERE medicine_id = ? LIMIT 1");
    $stmt->bind_param('s', $data['medicine_id']);
    $stmt->execute();
    $res = $stmt->get_result();
    $existing = $res->fetch_assoc();
    $stmt->close();

    if ($existing) {
        // UPDATE existing record
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

        $sql = "UPDATE medicines SET ".implode(', ',$fields)." WHERE medicine_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok ? 'updated' : 'error';
    } else {
        // INSERT new record
        $cols = array_keys($data);
        $placeholders = implode(',', array_fill(0,count($cols),'?'));
        $sql = "INSERT INTO medicines (`".implode('`,`',$cols)."`) VALUES ($placeholders)";
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

// Main
$report = ['processed'=>0,'inserted'=>0,'updated'=>0,'errors'=>[]];

$f = fetch_remote($remoteUrl);
if (!$f['ok']) {
    http_response_code(502);
    echo json_encode(['status'=>'error','error'=>$f['error']]);
    exit;
}

$decoded = json_decode($f['body'], true);
if ($decoded === null) {
    http_response_code(502);
    echo json_encode(['status'=>'error','error'=>'invalid_json']);
    exit;
}

// Extract items from "data"
$items = is_array($decoded['data'] ?? null) ? $decoded['data'] : [];
if (empty($items)) {
    echo json_encode(['status'=>'warning','report'=>$report]);
    exit;
}

// Process each item
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

echo json_encode(['status'=>'success','report'=>$report]);
?>
