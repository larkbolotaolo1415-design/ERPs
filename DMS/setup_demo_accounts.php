<?php
/**
 * Database Check and Demo Account Creator
 * This script will check your database and create demo accounts if needed
 */

require_once __DIR__ . '/includes/db_connect.php';

echo "<h2>Database Check and Demo Account Setup</h2>";

try {
    // Check if users table exists and what users are in it
    $stmt = $pdo->query("SELECT id, name, email, role FROM users ORDER BY role");
    $users = $stmt->fetchAll();
    
    echo "<h3>Current Users in Database:</h3>";
    if (empty($users)) {
        echo "<p style='color: red;'>No users found in database!</p>";
    } else {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th></tr>";
        foreach ($users as $user) {
            echo "<tr>";
            echo "<td>" . $user['id'] . "</td>";
            echo "<td>" . htmlspecialchars($user['name']) . "</td>";
            echo "<td>" . htmlspecialchars($user['email']) . "</td>";
            echo "<td>" . htmlspecialchars($user['role']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    echo "<hr>";
    
    // Check for specific demo accounts
    $demo_accounts = [
        'admin@dms.com' => 'admin',
        'doctor@dms.com' => 'doctor', 
        'patient@dms.com' => 'patient'
    ];
    
    echo "<h3>Demo Account Status:</h3>";
    foreach ($demo_accounts as $email => $role) {
        $stmt = $pdo->prepare("SELECT id, name, role FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user) {
            echo "<p style='color: green;'>✓ {$role} account exists: {$email}</p>";
        } else {
            echo "<p style='color: red;'>✗ {$role} account missing: {$email}</p>";
        }
    }
    
    echo "<hr>";
    
    // Create missing demo accounts
    echo "<h3>Creating Missing Demo Accounts:</h3>";
    
    $demo_data = [
        [
            'name' => 'System Administrator',
            'email' => 'admin@dms.com',
            'password' => password_hash('12345678', PASSWORD_DEFAULT),
            'role' => 'admin'
        ],
        [
            'name' => 'Dr. John Smith',
            'email' => 'doctor@dms.com', 
            'password' => password_hash('12345678', PASSWORD_DEFAULT),
            'role' => 'doctor'
        ],
        [
            'name' => 'Jane Doe',
            'email' => 'patient@dms.com',
            'password' => password_hash('patient123', PASSWORD_DEFAULT),
            'role' => 'patient'
        ]
    ];
    
    foreach ($demo_data as $account) {
        // Check if account already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$account['email']]);
        
        if ($stmt->fetch()) {
            // Update existing account password
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = ?");
            $stmt->execute([$account['password'], $account['email']]);
            echo "<p style='color: blue;'>Updated password for: {$account['email']}</p>";
        } else {
            // Create new account
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
            $stmt->execute([$account['name'], $account['email'], $account['password'], $account['role']]);
            echo "<p style='color: green;'>Created account: {$account['email']}</p>";
        }
    }
    
    echo "<hr>";
    echo "<h3>Final Demo Credentials:</h3>";
    echo "<div style='background: #f0f0f0; padding: 15px; border-radius: 5px;'>";
    echo "<p><strong>Admin:</strong> admin@dms.com / 12345678</p>";
    echo "<p><strong>Doctor:</strong> doctor@dms.com / 12345678</p>";
    echo "<p><strong>Patient:</strong> patient@dms.com / patient123</p>";
    echo "</div>";
    
    echo "<hr>";
    echo "<p style='color: green; font-weight: bold;'>Setup complete! You can now try logging in with the demo credentials above.</p>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>Database error: " . $e->getMessage() . "</p>";
}
?>
