<?php
require_once '../db.php';
session_start();

$sellerID = $_SESSION['sellerID'] ?? null;

if (!$sellerID) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$lowStockProducts = [];
$outOfStockProducts = [];
$newOrders = 0;

// Get low stock variants (stockQuantity < 5 and > 0)
$stmt = $conn->prepare("
    SELECT p.pName, v.size, v.stockQuantity
    FROM products p
    JOIN product_variants v ON p.productID = v.productID
    WHERE p.sellerID = ? AND v.stockQuantity < 5 AND v.stockQuantity > 0
");
$stmt->bind_param("i", $sellerID);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $lowStockProducts[] = "{$row['pName']} - Size: {$row['size']} ({$row['stockQuantity']})";
}
$stmt->close();

// Get out-of-stock variants (stockQuantity = 0)
$stmt = $conn->prepare("
    SELECT p.pName, v.size
    FROM products p
    JOIN product_variants v ON p.productID = v.productID
    WHERE p.sellerID = ? AND v.stockQuantity = 0
");
$stmt->bind_param("i", $sellerID);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $outOfStockProducts[] = "{$row['pName']} - Size: {$row['size']}";
}
$stmt->close();

// Count new orders (distinct orderID where seller's variant was ordered and orderStatus = 'Paid')
$stmt2 = $conn->prepare("
    SELECT COUNT(DISTINCT o.orderID)
        FROM orders o
        JOIN orderitems oi ON o.orderID = oi.orderID
        JOIN products p ON oi.productID = p.productID
        WHERE p.sellerID = ? AND o.orderStatus = 'Paid'
");
$stmt2->bind_param("i", $sellerID);
$stmt2->execute();
$stmt2->bind_result($newOrders);
$stmt2->fetch();
$stmt2->close();

echo json_encode([
    'lowStockProducts' => $lowStockProducts,
    'outOfStockProducts' => $outOfStockProducts,
    'newOrders' => $newOrders
]);
?>
