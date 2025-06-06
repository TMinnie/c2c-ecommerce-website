<?php
session_start();
require_once "db.php";

if (!isset($_SESSION['userID'])) {
    die("Please log in to continue.");
}

$userID = $_SESSION['userID'];

// Fetch buyer ID
$buyerID = fetchBuyerID($userID);
if (!$buyerID) die("Buyer not found.");

// Begin transaction
$conn->begin_transaction();

try {
    if (calculateCartTotal($buyerID) <= 0) {
        throw new Exception("Your cart is empty.");
    }

    $sellers = getUniqueSellersInCart($buyerID);
     if (empty($sellers)) {
    throw new Exception("No sellers found in your cart.");
}

    $newOrders = [];

    foreach ($sellers as $sellerID) {
        $sellerTotal = calculateSellerTotal($buyerID, $sellerID);
        if ($sellerTotal <= 0) continue;

        $itemCount = getItemCountForSeller($buyerID, $sellerID);
        $deliveryFee = 40.00 + (5.00 * $itemCount);

        $orderID = createOrder($buyerID, $sellerID, $sellerTotal, $deliveryFee);
        insertOrderItems($orderID, $buyerID, $sellerID);

        $newOrders[] = [
            'orderID' => $orderID,
            'sellerID' => $sellerID,
            'amount' => $sellerTotal
        ];
    }

    $conn->commit();
    $_SESSION['newOrders'] = $newOrders;
    header("Location: payment.php");
    exit();

} catch (Exception $e) {
    $conn->rollback();
    die("Checkout failed: " . $e->getMessage());
}

// --- FUNCTIONS ---

function fetchBuyerID($userID) {
    global $conn;
    $stmt = $conn->prepare("SELECT buyerID FROM buyers WHERE userID = ?");
    $stmt->bind_param("i", $userID);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc()['buyerID'] ?? null;
}

function calculateCartTotal($buyerID) {
    global $conn;
    $stmt = $conn->prepare("
        SELECT SUM(p.pPrice * ci.quantity) AS total
        FROM cartitems ci
        JOIN carts c ON ci.cartID = c.cartID
        JOIN products p ON ci.productID = p.productID
        WHERE c.buyerID = ?
    ");
    $stmt->bind_param("i", $buyerID);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc()['total'] ?? 0;
}

function calculateSellerTotal($buyerID, $sellerID) {
    global $conn;
    $stmt = $conn->prepare("
        SELECT SUM(p.pPrice * ci.quantity) AS total
        FROM cartitems ci
        JOIN carts c ON ci.cartID = c.cartID
        JOIN products p ON ci.productID = p.productID
        WHERE c.buyerID = ? AND p.sellerID = ?
    ");
    $stmt->bind_param("ii", $buyerID, $sellerID);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc()['total'] ?? 0;
}

function getUniqueSellersInCart($buyerID) {
    global $conn;
    $stmt = $conn->prepare("
        SELECT DISTINCT p.sellerID
        FROM cartitems ci
        JOIN carts c ON ci.cartID = c.cartID
        JOIN products p ON ci.productID = p.productID
        WHERE c.buyerID = ? AND p.status = 'active'
    ");
    $stmt->bind_param("i", $buyerID);
    $stmt->execute();
    $result = $stmt->get_result();

    $sellerIDs = [];
    while ($row = $result->fetch_assoc()) {
        $sellerIDs[] = $row['sellerID'];
    }
    return $sellerIDs;
}


function getItemCountForSeller($buyerID, $sellerID) {
    global $conn;
    $stmt = $conn->prepare("
        SELECT SUM(ci.quantity) AS itemCount
        FROM cartitems ci
        JOIN carts c ON ci.cartID = c.cartID
        JOIN products p ON ci.productID = p.productID
        WHERE c.buyerID = ? AND p.sellerID = ?
    ");
    $stmt->bind_param("ii", $buyerID, $sellerID);
    $stmt->execute();
    return (int)($stmt->get_result()->fetch_assoc()['itemCount'] ?? 0);
}

function createOrder($buyerID, $sellerID, $totalAmount, $deliveryFee) {
    global $conn;
    $stmt = $conn->prepare("
        INSERT INTO orders (buyerID, sellerID, orderDate, totalAmount, orderStatus, deliveryFee)
        VALUES (?, ?, NOW(), ?, 'Pending', ?)
    ");
    $stmt->bind_param("iidd", $buyerID, $sellerID, $totalAmount, $deliveryFee);
    if (!$stmt->execute()) {
        throw new Exception("Failed to create order for seller $sellerID: " . $stmt->error);
    }
    return $conn->insert_id;
}

function insertOrderItems($orderID, $buyerID, $sellerID) {
    global $conn;
   $stmt = $conn->prepare("
        INSERT INTO orderitems (orderID, productID, size, quantity, price)
        SELECT ?, ci.productID, ci.size, ci.quantity, p.pPrice
        FROM cartitems ci
        JOIN carts c ON ci.cartID = c.cartID
        JOIN products p ON ci.productID = p.productID
        WHERE c.buyerID = ? AND p.sellerID = ?
    ");

    $stmt->bind_param("iii", $orderID, $buyerID, $sellerID);
    if (!$stmt->execute()) {
        throw new Exception("Failed to insert order items for seller $sellerID: " . $stmt->error);
    }
}
?>
