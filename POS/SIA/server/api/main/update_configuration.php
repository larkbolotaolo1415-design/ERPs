<?php
require '../../core/connection.php';
header('Content-Type: application/json');

try {
    // Validate configuration fields
    $tax = $_POST['tax_percentage'] ?? null;
    $pwd = $_POST['pwd_discount'] ?? null;
    $senior = $_POST['senior_discount'] ?? null;
    $stock = $_POST['minimum_stock_for_shortage'] ?? null;
    $warning = $_POST['minimum_medicine_count_for_warning'] ?? null;
    $critical = $_POST['minimum_medicine_count_for_critical'] ?? null;

    if (!$tax || !$pwd || !$senior || !$stock || !$warning || !$critical) {
        throw new Exception("Missing configuration values.");
    }

    // Update admin configuration
    $stmtConfig = $connection->prepare("UPDATE admin_configuration SET tax_percentage=?, pwd_discount=?, senior_discount=?, minimum_stock_for_shortage=?, minimum_medicine_count_for_warning=?, minimum_medicine_count_for_critical=? WHERE id=1");
    $stmtConfig->bind_param("dddddd", $tax, $pwd, $senior, $stock, $warning, $critical);
    $stmtConfig->execute();

    // If medicine price is provided, update it
    if (isset($_POST['medicine_id']) && isset($_POST['price'])) {
        $medicineId = $_POST['medicine_id'];
        $price = $_POST['price'];

        if (!is_numeric($price) || $price <= 0) {
            throw new Exception("Invalid price value.");
        }

        $stmtPrice = $connection->prepare("UPDATE medicines SET price=? WHERE medicine_id=?");
        $stmtPrice->bind_param("ds", $price, $medicineId);
        $stmtPrice->execute();
    }

    echo json_encode([
        "status" => "success",
        "message" => "Configuration and medicine price updated successfully."
    ]);

} catch (Exception $e) {
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
}
?>
