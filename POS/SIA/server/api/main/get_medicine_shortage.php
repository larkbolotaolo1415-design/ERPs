<?php
require '../../core/connection.php';

try {
    // Get the configured minimum stock for shortage
    $configQuery = $connection->query("SELECT minimum_stock_for_shortage FROM admin_configuration LIMIT 1");
    $config = $configQuery->fetch_assoc();
    $minStock = $config['minimum_stock_for_shortage'] ?? 100; // fallback to 100 if not found

    // Count medicines below the minimum stock
    $query = $connection->prepare("SELECT COUNT(*) AS shortage FROM medicines WHERE stock < ?");
    $query->bind_param("i", $minStock);
    $query->execute();
    $result = $query->get_result();
    $row = $result->fetch_assoc();

    echo $row['shortage'];
} catch (Exception $e) {
    echo "0"; // fallback if error occurs
}
?>
