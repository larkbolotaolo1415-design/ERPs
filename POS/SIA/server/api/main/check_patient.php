<?php
require '../../core/connection.php';

$patient_id = trim($_POST['patient_id'] ?? $_GET['patient_id'] ?? '');


if (!$patient_id) {
    echo json_encode(['status'=>'error','message'=>'Patient ID is required']);
    exit;
}

$stmt = $connection->prepare("SELECT * FROM admitted_patients WHERE patient_id = ? AND status = 'admitted'");
$stmt->bind_param("s", $patient_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo json_encode(['status'=>'success','data'=>$result->fetch_assoc()]);
} else {
    echo json_encode(['status'=>'error','message'=>'Patient not found or not admitted']);
}
?>
