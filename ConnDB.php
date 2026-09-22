<?php
$host     = getenv('MYSQLHOST')     ?: 'localhost';
$username = getenv('MYSQLUSER')     ?: 'root';
$password = getenv('MYSQLPASSWORD') ?: '';
$dbname   = getenv('MYSQLDATABASE') ?: 'db_northwind';
$port     = getenv('MYSQLPORT')     ?: '3306';

$conn = new mysqli($host, $username, $password, $dbname, $port);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");
?>
