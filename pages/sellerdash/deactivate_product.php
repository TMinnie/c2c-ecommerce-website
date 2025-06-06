<?php
header('Content-Type: application/json');

session_start();
include '../db.php';

$data = json_decode(file_get_contents('php://input'), true);
$productID = $data['productID'];

if (!$productID) {
    echo json_encode(['success' => false, 'error' => 'Missing product ID']);
    exit;
}

try {
    $conn->begin_transaction();

    // 1. Deactivate the product
    $stmt1 = $conn->prepare("UPDATE products SET status = 'inactive' WHERE productID = ?");
    $stmt1->bind_param("i", $productID);
    $stmt1->execute();

    // 2. Remove product from all carts
    $stmt2 = $conn->prepare("DELETE FROM cartitems WHERE productID = ?");
    $stmt2->bind_param("i", $productID);
    $stmt2->execute();

    $conn->commit();

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
?>