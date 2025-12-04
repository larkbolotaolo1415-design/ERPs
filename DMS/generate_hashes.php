<?php
// Password hash generator for demo users
// Run this script to get the correct password hashes

echo "Password hashes for demo users:\n\n";

echo "Password '12345678' hash: " . password_hash('12345678', PASSWORD_DEFAULT) . "\n";
echo "Password 'patient123' hash: " . password_hash('patient123', PASSWORD_DEFAULT) . "\n";

echo "\nSQL UPDATE statements:\n\n";
echo "-- Update admin password to 12345678\n";
echo "UPDATE users SET password = '" . password_hash('12345678', PASSWORD_DEFAULT) . "' WHERE email = 'admin@dms.com';\n\n";

echo "-- Update doctor password to 12345678\n";
echo "UPDATE users SET password = '" . password_hash('12345678', PASSWORD_DEFAULT) . "' WHERE email = 'doctor@dms.com';\n\n";

echo "-- Update patient password to patient123\n";
echo "UPDATE users SET password = '" . password_hash('patient123', PASSWORD_DEFAULT) . "' WHERE email = 'patient@dms.com';\n";
?>
