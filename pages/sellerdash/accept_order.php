<?php
require_once '../db.php'; // Adjust the path as needed

header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['orderID'])) {
    echo json_encode(["success" => false, "message" => "No order ID provided."]);
    exit;
}

$orderID = intval($data['orderID']);

$conn->begin_transaction();

try {
    // Update the order status
    $updateOrder = $conn->prepare("UPDATE `orders` SET orderStatus = 'Accepted' WHERE orderID = ?");
    $updateOrder->bind_param("i", $orderID);
    $updateOrder->execute();

    if ($updateOrder->affected_rows === 0) {
        throw new Exception("Order update failed or already accepted.");
    }

    // Insert into delivery table
    $insertDelivery = $conn->prepare("INSERT INTO deliveries (orderID, deliveryStatus) VALUES (?, 'pending')");
    $insertDelivery->bind_param("i", $orderID);
    $insertDelivery->execute();

    $conn->commit();
    echo json_encode(["success" => true]);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
