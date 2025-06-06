<?php
session_start();
header('Content-Type: application/json');
require_once '../db.php';

// Check if the seller is logged in
$input = json_decode(file_get_contents("php://input"), true);
$sellerID = $_SESSION['sellerID'] ?? null;
$orderID = $input['orderID'] ?? null;

if (!$orderID || !$sellerID) {
    echo json_encode(['error' => 'Missing order ID or seller session']);
    exit;
}

// Check if the order belongs to the seller
$sqlCheck = "SELECT 1 FROM products p
             JOIN orderitems oi ON p.productID = oi.productID
             JOIN orders o ON oi.orderID = o.orderID
             WHERE o.orderID = ? AND p.sellerID = ?";

$stmtCheck = $conn->prepare($sqlCheck);
$stmtCheck->bind_param("ii", $orderID, $sellerID);
$stmtCheck->execute();
$resultCheck = $stmtCheck->get_result();

if ($resultCheck->num_rows === 0) {
    echo json_encode(['error' => 'This order does not belong to the seller']);
    exit;
}

// Update delivery status, dispatchDate, and deliveryDate
$sqlUpdate = "UPDATE deliveries 
              SET dispatchDate = NOW(), 
                  deliveryDate = DATE_ADD(NOW(), INTERVAL 2 DAY),
                  deliveryStatus = 'shipped' 
              WHERE orderID = ? AND deliveryStatus = 'pending'";

$stmtUpdate = $conn->prepare($sqlUpdate);
$stmtUpdate->bind_param("i", $orderID);
$stmtUpdate->execute();

if ($stmtUpdate->affected_rows > 0) {
    // Also update the order status
    $sqlOrderUpdate = "UPDATE orders 
                       SET orderStatus = 'Dispatched' 
                       WHERE orderID = ?";
    $stmtOrderUpdate = $conn->prepare($sqlOrderUpdate);
    $stmtOrderUpdate->bind_param("i", $orderID);
    $stmtOrderUpdate->execute();

    echo json_encode(['success' => 'Order marked as shipped']);
} else {
    echo json_encode(['error' => 'Failed to update delivery status']);
}
?>
