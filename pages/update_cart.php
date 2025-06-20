<?php
require_once "db.php";

if (!isset($_POST['cartItemID']) || !isset($_POST['quantity'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields.']);
    exit;
}

$cartItemID = (int)$_POST['cartItemID'];
$quantity = (int)$_POST['quantity'];

// Fetch productID, cartID, and size from cartitems
$stmt = $conn->prepare("SELECT productID, cartID, size FROM cartitems WHERE cartItemID = ?");
$stmt->bind_param("i", $cartItemID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Cart item not found.']);
    exit;
}

$row = $result->fetch_assoc();
$productID = $row['productID'];
$cartID = $row['cartID'];
$size = $row['size'];

// Check stock
$stmt = $conn->prepare("SELECT stockQuantity FROM product_variants WHERE productID = ? AND size = ?");
$stmt->bind_param("is", $productID, $size);
$stmt->execute();
$stockResult = $stmt->get_result();

if ($stockResult->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Stock not found.']);
    exit;
}

$stockRow = $stockResult->fetch_assoc();
$maxStock = (int)$stockRow['stockQuantity'];

if ($quantity > $maxStock) {
    echo json_encode(['success' => false, 'message' => 'Quantity exceeds available stock.']);
    exit;
}

// Update quantity in cartitems
$stmt = $conn->prepare("UPDATE cartitems SET quantity = ? WHERE cartItemID = ?");
$stmt->bind_param("ii", $quantity, $cartItemID);
$stmt->execute();

// Fetch price of product
$stmt = $conn->prepare("SELECT pPrice FROM products WHERE productID = ?");
$stmt->bind_param("i", $productID);
$stmt->execute();
$result = $stmt->get_result();
$price = $result->fetch_assoc()['pPrice'];

$subtotal = $price * $quantity;

// Get total cart value
$stmt = $conn->prepare("
    SELECT SUM(p.pPrice * ci.quantity) AS total
    FROM cartitems ci
    JOIN products p ON ci.productID = p.productID
    WHERE ci.cartID = ?
");
$stmt->bind_param("i", $cartID);
$stmt->execute();
$totalResult = $stmt->get_result();
$total = $totalResult->fetch_assoc()['total'];

echo json_encode([
    'success' => true,
    'subtotal' => number_format($subtotal, 2),
    'total' => number_format($total, 2)
]);
?>
