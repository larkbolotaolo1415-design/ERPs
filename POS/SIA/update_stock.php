<?php
require '../../core/connection.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['items'])) {
    echo json_encode(['status'=>'error','message'=>'No items received']);
    exit;
}

$connection->begin_transaction();
try {
    $stmt = $connection->prepare("UPDATE medicines SET stock = stock - ? WHERE medicine_name = ?");
    foreach($data['items'] as $item){
        $qty = intval($item['quantity']);
        $name = $item['medicine_name'];
        $stmt->bind_param("is", $qty, $name);
        $stmt->execute();
    }
    $connection->commit();
    echo json_encode(['status'=>'success']);
} catch(Exception $e) {
    $connection->rollback();
    echo json_encode(['status'=>'error','message'=>$e->getMessage()]);
}
?>
