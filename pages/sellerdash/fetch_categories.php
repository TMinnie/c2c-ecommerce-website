<?php
session_start();
include '../db.php';

header('Content-Type: application/json');

    echo json_encode(['error' => 'Unauthorized']);
?>