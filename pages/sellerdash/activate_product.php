<?php
require_once '../db.php'; 

header('Content-Type: application/json');

// Get the raw POST data
$data = json_decode(file_get_contents('php://input'), true);

// Check if productID was provided
if (!isset($data['productID'])) {
    echo json_encode(['success' => false, 'error' => 'No product ID provided']);
    exit;
}

$productID = intval($data['productID']);

// Update the product's status to 'active'
$sql = "UPDATE products SET status = 'active' WHERE productID = ?";
$stmt = $conn->prepare($sql);

if ($stmt) {
    $stmt->bind_param("i", $productID);
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to activate product']);
    }
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'error' => 'Database error']);
}

$conn->close();
?>
