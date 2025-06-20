<?php
$host = 'localhost';
$db   = 'tukocart'; // your database name
$user = 'root';     // your MySQL username
$pass = '';        
$charset = 'utf8mb4';

// Create connection
$conn = new mysqli($host, $user, $pass, $db);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>