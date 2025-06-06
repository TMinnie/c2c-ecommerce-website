<?php
session_start();
include '../db.php';

$sellerID = $_SESSION['sellerID'];

$response = [
    'totalOrders' => 0,
    'weeklyOrders' => 0,
    'monthlyOrders' => 0,
    'averageOrderValue' => 0
];

// Total Orders
$stmt = $conn->prepare("SELECT COUNT(*) FROM orders WHERE sellerID = ?");
$stmt->bind_param("i", $sellerID);
$stmt->execute();
$stmt->bind_result($response['totalOrders']);
$stmt->fetch();
$stmt->close();

// Orders in Last 7 Days
$stmt = $conn->prepare("SELECT COUNT(*) FROM orders WHERE sellerID = ? AND orderDate >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
$stmt->bind_param("i", $sellerID);
$stmt->execute();
$stmt->bind_result($response['weeklyOrders']);
$stmt->fetch();
$stmt->close();

// Orders in Last 30 Days
$stmt = $conn->prepare("SELECT COUNT(*) FROM orders WHERE sellerID = ? AND orderDate >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
$stmt->bind_param("i", $sellerID);
$stmt->execute();
$stmt->bind_result($response['monthlyOrders']);
$stmt->fetch();
$stmt->close();

// Average Order Value
$stmt = $conn->prepare("SELECT AVG(totalAmount) FROM orders WHERE sellerID = ?");
$stmt->bind_param("i", $sellerID);
$stmt->execute();
$stmt->bind_result($response['averageOrderValue']);
$stmt->fetch();
$stmt->close();

$response['averageOrderValue'] = number_format((float)$response['averageOrderValue'], 2, '.', '');

echo json_encode($response);
?>
