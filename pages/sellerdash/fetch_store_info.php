<?php
session_start();
include '../db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['sellerID'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$sellerID = $_SESSION['sellerID'];

$sql = "SELECT businessName, pickupAddress, city, payDetails, businessDescript, imagePath
        FROM sellers 
        WHERE sellerID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $sellerID);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    echo json_encode($row);  // Image path should be returned with the extension

} else {
    echo json_encode(['error' => 'Seller not found']);
}
