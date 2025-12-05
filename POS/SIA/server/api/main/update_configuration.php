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
    $stmtConfig = $connection->prepare("UPDATE admin_configuration 
        SET tax_percentage=?, pwd_discount=?, senior_discount=?, 
        minimum_stock_for_shortage=?, minimum_medicine_count_for_warning=?, 
        minimum_medicine_count_for_critical=? 
        WHERE id=1");

    $stmtConfig->bind_param("dddddd", $tax, $pwd, $senior, $stock, $warning, $critical);
    $stmtConfig->execute();

    // If medicine price is provided, update it locally
    if (isset($_POST['medicine_id']) && isset($_POST['price'])) {
        $medicineId = $_POST['medicine_id'];
        $price = $_POST['price'];

        if (!is_numeric($price) || $price <= 0) {
            throw new Exception("Invalid price value.");
        }

        // Local DB update
        $stmtPrice = $connection->prepare("UPDATE medicines SET price=? WHERE medicine_id=?");
        $stmtPrice->bind_param("ds", $price, $medicineId);
        $stmtPrice->execute();

        // ================================
        // 🔥 SYNC PRICE TO REMOTE SERVER
        // ================================
        $remoteUrl = "http://26.161.108.142/INVENTORY_NEW/Inventory-System-New/php/update_price.php";

        $postData = http_build_query([
            "medicine_id" => $medicineId,
            "price" => $price
        ]);

        $ch = curl_init($remoteUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $remoteResponse = curl_exec($ch);
        $curlError = curl_error($ch);
        curl_close($ch);

        // Optional: Validate remote response
        if ($remoteResponse === false) {
            throw new Exception("Remote sync failed: " . $curlError);
        }
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
