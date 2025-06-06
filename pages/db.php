<?php
$host = 'sql302.infinityfree.com';
$db   = 'if0_39173099_tukocart'; // your database name
$user = 'if0_39173099';     // your MySQL username
$pass = '';        
$charset = 'utf8mb4';

// Create connection
$conn = new mysqli($host, $user, $pass, $db);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>