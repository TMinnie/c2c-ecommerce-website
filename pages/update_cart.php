<?php
require_once "db.php";

$productID = $_POST['productID'];
$cartID = $_POST['cartID'];
$quantity = $_POST['quantity'];

// Get size from the cart
$stmt = $conn->prepare("SELECT size FROM cartitems WHERE cartID = ? AND productID = ?");
$stmt->bind_param("ii", $cartID, $productID);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$size = $row['size'];

// Get available stock
$stmt = $conn->prepare("SELECT stockQuantity FROM product_variants WHERE productID = ? AND size = ?");
$stmt->bind_param("is", $productID, $size);
$stmt->execute();
$stockResult = $stmt->get_result();
$stockRow = $stockResult->fetch_assoc();
$maxStock = $stockRow['stockQuantity'];

if ($quantity > $maxStock) {
    echo json_encode(["error" => "Quantity exceeds available stock."]);
    exit;
}

// Update quantity
$stmt = $conn->prepare("UPDATE cartitems SET quantity = ? WHERE productID = ? AND cartID = ?");
$stmt->bind_param("iii", $quantity, $productID, $cartID);
$stmt->execute();

// Fetch updated price and subtotal
$stmt = $conn->prepare("
    SELECT p.pPrice 
    FROM products p
    WHERE p.productID = ?
");
$stmt->bind_param("i", $productID);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

$price = $row['pPrice'];
$subtotal = $price * $quantity;

// Get total cart value
$stmt = $conn->prepare("
    SELECT SUM(p.pPrice * ci.quantity) as total 
    FROM cartitems ci 
    JOIN products p ON ci.productID = p.productID 
    WHERE ci.cartID = ?
");
$stmt->bind_param("i", $cartID);
$stmt->execute();
$result = $stmt->get_result();
$totalRow = $result->fetch_assoc();

echo json_encode([
    'subtotal' => number_format($subtotal, 2),
    'total' => number_format($totalRow['total'], 2)
]);
?>
