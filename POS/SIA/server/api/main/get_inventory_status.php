<?php
require '../../core/connection.php';

try {
    // Fetch configuration thresholds
    $configQuery = $connection->query("
        SELECT
            minimum_stock_for_shortage,
            minimum_medicine_count_for_warning,
            minimum_medicine_count_for_critical
        FROM admin_configuration
        LIMIT 1
    ");
    $config = $configQuery->fetch_assoc();

    $minStock = $config['minimum_stock_for_shortage'] ?? 100;
    $warningPercent = $config['minimum_medicine_count_for_warning'] ?? 50; // in %
    $criticalPercent = $config['minimum_medicine_count_for_critical'] ?? 25; // in %

    // Count total medicines
    $totalQuery = $connection->query("SELECT COUNT(*) AS total FROM medicines");
    $total = $totalQuery->fetch_assoc()['total'] ?? 0;

    // Count medicines above minimum stock
    $aboveQuery = $connection->prepare("SELECT COUNT(*) AS above_min FROM medicines WHERE stock >= ?");
    $aboveQuery->bind_param("i", $minStock);
    $aboveQuery->execute();
    $result = $aboveQuery->get_result();
    $aboveMin = $result->fetch_assoc()['above_min'] ?? 0;

    // Count medicines below minimum stock
    $lowStock = $total - $aboveMin;

    // Calculate percentage of medicines above minimum stock
    $percentageAbove = $total > 0 ? ($aboveMin / $total) * 100 : 0;

    // Determine inventory status based on thresholds
    if ($percentageAbove < $criticalPercent) {
        $status = "Critical";
    } elseif ($percentageAbove < $warningPercent) {
        $status = "Warning";
    } else {
        $status = "Good";
    }

    echo json_encode([
        "status" => $status,
        "total" => $total,
        "low_stock" => $lowStock,
        "percentage_above" => round($percentageAbove, 2)
    ]);

} catch (Exception $e) {
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
}
?>
