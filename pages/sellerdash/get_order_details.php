<?php
session_start();
header('Content-Type: application/json');
include '../db.php';

$orderID = $_GET['orderID'] ?? null;
$sellerID = $_SESSION['sellerID'] ?? null;

if (!$orderID || !$sellerID) {
    echo json_encode(['error' => 'Invalid order or seller session']);
    exit;
}

// Fetch general order info (only once)
$sqlOrder = "SELECT 
                o.orderID,
                o.orderStatus,
                o.totalAmount,
                o.deliveryFee,
                CONCAT(u.uFirst, ' ', u.uLast) AS buyerName,
                CONCAT(b.shippingAddress1, ', ', b.shippingAddress2, ', ', b.postalCode) AS shippingAddress
            FROM orders o
            JOIN buyers b ON o.buyerID = b.buyerID
            JOIN users u ON b.userID = u.userID
            WHERE o.orderID = ? AND o.sellerID = ?";

$stmtOrder = $conn->prepare($sqlOrder);
$stmtOrder->bind_param("ii", $orderID, $sellerID);
$stmtOrder->execute();
$resultOrder = $stmtOrder->get_result();

if ($resultOrder->num_rows === 0) {
    echo json_encode(['error' => 'No matching order found']);
    exit;
}

$order = $resultOrder->fetch_assoc();

// Fetch all items in the order belonging to this seller
$sqlItems = "SELECT 
                p.productID,
                p.pName,
                oi.quantity,
                oi.price,
                (oi.quantity * oi.price) AS subtotal
            FROM orderitems oi
            JOIN products p ON oi.productID = p.productID
            WHERE oi.orderID = ? AND p.sellerID = ?";

$stmtItems = $conn->prepare($sqlItems);
$stmtItems->bind_param("ii", $orderID, $sellerID);
$stmtItems->execute();
$resultItems = $stmtItems->get_result();

$items = [];
while ($row = $resultItems->fetch_assoc()) {
    $items[] = $row;
}

// Final response
$response = [
    'orderID' => $order['orderID'],
    'orderStatus' => $order['orderStatus'],
    'buyerName' => $order['buyerName'],
    'shippingAddress' => $order['shippingAddress'],
    'totalAmount' => $order['totalAmount'],
    'deliveryFee' => $order['deliveryFee'],
    'grandTotal' => $order['totalAmount'] + $order['deliveryFee'],
    'items' => $items
];

echo json_encode($response);
?>
