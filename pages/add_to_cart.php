<?php
session_start();
require_once "db.php";

// 1. Ensure user is logged in
if (!isset($_SESSION['userID'])) {
    die("Please log in to add items to your cart.");
}

$userID = $_SESSION['userID'];

// 2. Validate input
if (
    !isset($_POST['productID'], $_POST['quantity'], $_POST['size']) ||
    !is_numeric($_POST['productID']) ||
    !is_numeric($_POST['quantity']) ||
    empty($_POST['size'])
) {
    die("Invalid input.");
}

$productID = intval($_POST['productID']);
$quantity = intval($_POST['quantity']);
$size = trim($_POST['size']);

if ($quantity < 1) {
    die("Quantity must be at least 1.");
}

// 3. Fetch buyerID
$stmt = $conn->prepare("SELECT buyerID FROM buyers WHERE userID = ?");
$stmt->bind_param("i", $userID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    // Redirect to profile setup
    $_SESSION['flash_message'] = "Please complete your buyer profile before shopping.";
    header("Location: buyer_setup_model.php");
    exit;
}

$buyer = $result->fetch_assoc();
$buyerID = $buyer['buyerID'];

// 4. Get or create cart
$stmt = $conn->prepare("SELECT cartID FROM carts WHERE buyerID = ?");
$stmt->bind_param("i", $buyerID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    $stmt = $conn->prepare("INSERT INTO carts (buyerID) VALUES (?)");
    $stmt->bind_param("i", $buyerID);
    $stmt->execute();
    $cartID = $stmt->insert_id;
} else {
    $cart = $result->fetch_assoc();
    $cartID = $cart['cartID'];
}

// 5. Check available stock for size
$stmt = $conn->prepare("SELECT stockQuantity FROM product_variants WHERE productID = ? AND size = ?");
$stmt->bind_param("is", $productID, $size);
$stmt->execute();
$stockResult = $stmt->get_result();
$variant = $stockResult->fetch_assoc();

if (!$variant || $variant['stockQuantity'] < $quantity) {
    die("Not enough stock available for size $size.");
}

// 6. Check if item already in cart
$stmt = $conn->prepare("SELECT quantity FROM cartitems WHERE cartID = ? AND productID = ? AND size = ?");
$stmt->bind_param("iis", $cartID, $productID, $size);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $newQuantity = $row['quantity'] + $quantity;

    // Check stock again for new quantity
    if ($newQuantity > $variant['stockQuantity']) {
        die("Cannot add $quantity more. Only " . ($variant['stockQuantity'] - $row['quantity']) . " left in stock for size $size.");
    }

    $stmt = $conn->prepare("UPDATE cartitems SET quantity = ? WHERE cartID = ? AND productID = ? AND size = ?");
    $stmt->bind_param("iiis", $newQuantity, $cartID, $productID, $size);
    $stmt->execute();
} else {
    $stmt = $conn->prepare("INSERT INTO cartitems (cartID, productID, quantity, size) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiis", $cartID, $productID, $quantity, $size);
    $stmt->execute();
}

// 7. Redirect back to product page with success message
$_SESSION['flash_message'] = "Product ($size) successfully added to your cart!";
header("Location: product_view.php?productID=" . urlencode($productID));
exit;
?>
