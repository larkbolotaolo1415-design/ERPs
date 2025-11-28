<?php
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "POS";

$connection = new mysqli($host, $user, $pass, $dbname);

if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}
?>
