<?php
session_start();
include '../db.php'; 

header('Content-Type: application/json');

if (!isset($_SESSION['sellerID'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$sellerID = $_SESSION['sellerID'];

if (!isset($_GET['productID'])) {
    echo json_encode(['error' => 'Product ID not provided']);
    exit;
}

$productID = $_GET['productID'];

// Fetch product details
$sql = "SELECT productID, pName, pDescription, pPrice, imagePath, pCategory 
        FROM products
        WHERE sellerID = ? AND productID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $sellerID, $productID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $product = $result->fetch_assoc();

    // Fetch variants
    $variantSQL = "SELECT variantID, size, stockQuantity 
                   FROM product_variants 
                   WHERE productID = ?";
    $variantStmt = $conn->prepare($variantSQL);
    $variantStmt->bind_param("i", $productID);
    $variantStmt->execute();
    $variantResult = $variantStmt->get_result();

    $variants = [];
    while ($row = $variantResult->fetch_assoc()) {
        $variants[] = $row;
    }

    $product['variants'] = $variants;

    echo json_encode($product);
} else {
    echo json_encode(['error' => 'Product not found']);
}
?>
