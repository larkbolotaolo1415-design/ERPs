<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
header('Content-Type: application/json');

try {
    require '../../core/connection.php';
    
    // Check connection
    if (!$connection || $connection->connect_error) {
        throw new Exception('Database connection failed');
    }
    
    // Query to get all invoices with all columns
    $query = "SELECT 
                invoice_id, 
                invoice_number, 
                patient_id, 
                employee_id, 
                subtotal, 
                discount_amount, 
                tax_amount, 
                total_amount, 
                payment_method, 
                date_created 
              FROM invoices 
              ORDER BY date_created DESC 
              LIMIT 100";
    
    $result = $connection->query($query);
    
    if (!$result) {
        throw new Exception("Query error: " . $connection->error);
    }
    
    $invoices = [];
    
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $invoices[] = [
                'invoice_id' => (int)$row['invoice_id'],
                'invoice_number' => $row['invoice_number'],
                'patient_id' => $row['patient_id'],
                'employee_id' => $row['employee_id'],
                'subtotal' => (float)$row['subtotal'],
                'discount_amount' => (float)$row['discount_amount'],
                'tax_amount' => (float)$row['tax_amount'],
                'total_amount' => (float)$row['total_amount'],
                'payment_method' => $row['payment_method'],
                'date_created' => $row['date_created']
            ];
        }
    }
    
    echo json_encode([
        'status' => 'success',
        'data' => $invoices
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>
