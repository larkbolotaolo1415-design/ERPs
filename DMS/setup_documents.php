<?php
/**
 * Document Management Setup Script (file-path storage)
 * Verifies documents table and ensures uploads/documents directory exists.
 */

echo "<h2>Document Management System Setup (File-Path Storage)</h2>";

require_once __DIR__ . '/includes/db_connect.php';

try {
    // Check if documents table exists (table structure is now in document_management_system.sql)
    // This script now only verifies the table exists and reports status
    
    // Check if table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'documents'");
    $tableExists = $stmt->rowCount() > 0;
    
    if ($tableExists) {
        echo "<p style='color: green;'>✓ Documents table exists (structure defined in document_management_system.sql)</p>";

        // Check if table is empty
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM documents");
        $count = (int)$stmt->fetch()['count'];
        echo "<p style='color: blue;'>✓ Documents table ready — {$count} document(s)</p>";

        // Ensure uploads/documents directory exists for file-path storage
        $baseDir = __DIR__;
        $uploadDir = $baseDir . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'documents';
        if (!is_dir($uploadDir)) {
            if (@mkdir($uploadDir, 0775, true)) {
                echo "<p style='color: green;'>✓ Created uploads/documents/ directory for file storage.</p>";
            } else {
                echo "<p style='color: red;'>✗ Failed to create uploads/documents/ directory. Please create it manually.</p>";
            }
        } else {
            echo "<p style='color: green;'>✓ uploads/documents/ directory already exists.</p>";
        }
    } else {
        echo "<p style='color: red;'>✗ Documents table not found. Please import document_management_system.sql first.</p>";
        echo "<p style='color: orange;'>⚠ Run the master SQL file (document_management_system.sql) to create all database tables.</p>";
    }

} catch (PDOException $e) {
    echo "<p style='color: red;'>✗ Database error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "<hr><h3>Setup Complete!</h3>";
echo "<div style='background: #f0f0f0; padding: 15px; border-radius: 5px;'>";
echo "<p><strong>Next Steps:</strong></p>";
echo "<ul>";
echo "<li>Ensure <code>uploads/documents/</code> is writable by the web server user.</li>";
echo "<li>Adjust <code>upload_max_filesize</code> and <code>post_max_size</code> in php.ini if needed</li>";
echo "<li>Test uploads via admin and patient upload forms</li>";
echo "</ul></div>";
echo "<hr><p style='color: green; font-weight: bold;'>Document Management System (file-path storage) ready to use!</p>";
?>
