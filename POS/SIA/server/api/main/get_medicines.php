<?php
require '../../core/connection.php';

header('Content-Type: application/json');

/**
 * get_medicines.php
 * - If called normally, returns local medicines as JSON.
 * - If called with `remote_url=...`, will fetch remote JSON from that URL,
 *   upsert received records into local `medicines` table, then return local list.
 */

function fetch_remote_json($url) {
    if (function_exists('curl_version')) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
        curl_setopt($ch, CURLOPT_TIMEOUT, 8);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $resp = curl_exec($ch);
        $err = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($resp === false) {
            error_log("get_medicines: curl error: $err");
            return null;
        }
        if ($httpCode < 200 || $httpCode >= 300) {
            error_log("get_medicines: remote returned HTTP $httpCode for $url");
            return null;
        }
        return $resp;
    }

    // fallback to file_get_contents
    $ctx = stream_context_create(['http' => ['timeout' => 8]]);
    $resp = @file_get_contents($url, false, $ctx);
    if ($resp === false) {
        error_log("get_medicines: file_get_contents failed for $url");
        return null;
    }
    return $resp;
}

function get_table_columns($conn, $table) {
    $dbRow = $conn->query('SELECT DATABASE()');
    $dbName = $dbRow ? $dbRow->fetch_row()[0] : null;
    if (!$dbName) return [];
    $sql = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = '" . $conn->real_escape_string($dbName) . "' AND TABLE_NAME = '" . $conn->real_escape_string($table) . "'";
    $res = $conn->query($sql);
    $cols = [];
    if ($res) {
        while ($r = $res->fetch_assoc()) $cols[] = $r['COLUMN_NAME'];
    }
    return $cols;
}

function upsert_medicine_mysqli($conn, $table, $item, $tableCols) {
    // choose match key
    if (isset($item['medicine_id'])) {
        $matchCol = 'medicine_id'; $matchVal = $item['medicine_id'];
    } elseif (isset($item['id'])) {
        $matchCol = 'id'; $matchVal = $item['id'];
    } elseif (isset($item['medicine_name'])) {
        $matchCol = 'medicine_name'; $matchVal = $item['medicine_name'];
    } else {
        return ['ok'=>false,'msg'=>'no-match-key'];
    }

    // intersect with table columns
    $cols = [];
    $vals = [];
    foreach ($item as $k => $v) {
        if (in_array($k, $tableCols)) { $cols[] = $k; $vals[] = $v; }
    }
    if (empty($cols)) return ['ok'=>false,'msg'=>'no-columns'];

    // find existing
    $sqlCheck = "SELECT id FROM {$table} WHERE {$matchCol} = ? LIMIT 1";
    $stmt = $conn->prepare($sqlCheck);
    if (!$stmt) return ['ok'=>false,'msg'=>'prepare-check-failed','error'=>$conn->error];
    $stmt->bind_param('s', $matchVal);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res->fetch_assoc();
    $stmt->close();

    if ($row) {
        // update
        $setParts = [];
        foreach ($cols as $c) $setParts[] = "{$c} = ?";
        $setSql = implode(', ', $setParts);
        $sql = "UPDATE {$table} SET {$setSql} WHERE id = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) return ['ok'=>false,'msg'=>'prepare-update-failed','error'=>$conn->error];
        // bind params
        $types = str_repeat('s', count($vals)) . 'i';
        $params = array_merge(array_map('strval', $vals), [ (int)$row['id'] ]);
        $stmt->bind_param($types, ...$params);
        $ok = $stmt->execute();
        $err = $stmt->error;
        $stmt->close();
        return ['ok'=>$ok,'action'=>'update','id'=>$row['id'],'error'=>$err];
    } else {
        // insert
        $colList = implode(', ', $cols);
        $placeholders = implode(', ', array_fill(0, count($cols), '?'));
        $sql = "INSERT INTO {$table} ({$colList}) VALUES ({$placeholders})";
        $stmt = $conn->prepare($sql);
        if (!$stmt) return ['ok'=>false,'msg'=>'prepare-insert-failed','error'=>$conn->error];
        $types = str_repeat('s', count($vals));
        $params = array_map('strval', $vals);
        $stmt->bind_param($types, ...$params);
        $ok = $stmt->execute();
        $id = $conn->insert_id;
        $err = $stmt->error;
        $stmt->close();
        return ['ok'=>$ok,'action'=>'insert','id'=>$id,'error'=>$err];
    }
}

try {
    // if remote_url is provided, fetch and upsert remote data first
    if (!empty($_GET['remote_url'])) {
        $remoteUrl = $_GET['remote_url'];
        if (!empty($_GET['medicine_id'])) {
            $sep = (strpos($remoteUrl, '?') === false) ? '?' : '&';
            $remoteUrl .= $sep . 'medicine_id=' . urlencode($_GET['medicine_id']);
        }
        $raw = fetch_remote_json($remoteUrl);
        if ($raw !== null) {
            $decoded = json_decode($raw, true);
            // accept either {status:..., data: [...] } or direct array
            if (is_array($decoded)) {
                if (isset($decoded['status']) && isset($decoded['data']) && is_array($decoded['data'])) {
                    $items = $decoded['data'];
                } else {
                    $items = $decoded;
                }

                if (!empty($items)) {
                    $tableCols = get_table_columns($connection, 'medicines');
                    foreach ($items as $it) {
                        if (!is_array($it)) continue;
                        $res = upsert_medicine_mysqli($connection, 'medicines', $it, $tableCols);
                        if (!$res['ok']) {
                            error_log('get_medicines upsert error: ' . json_encode($res));
                        }
                    }
                }
            } else {
                error_log('get_medicines: remote returned non-json or unexpected payload');
            }
        }
    }

    // finally, return local medicines
    $query = $connection->prepare("SELECT * FROM medicines ORDER BY medicine_name ASC");
    $query->execute();
    $result = $query->get_result();

    $medicines = [];
    while ($row = $result->fetch_assoc()) {
        $medicines[] = $row;
    }

    echo json_encode([
        "status" => "success",
        "data" => $medicines
    ]);

} catch (Exception $e) {
    error_log('get_medicines exception: ' . $e->getMessage());
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
}

?>
