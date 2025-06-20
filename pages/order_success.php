<?php $pageName = 'buy'; ?>
<?php
require_once __DIR__ . '/../stripe-php-master/init.php';
\Stripe\Stripe::setApiKey('sk_test_51RQOlhP5EnF2nJIgS4zx9eNrZqqolx9sx5JkdhHdeZTXcVkK2O7a95XvdZjvigNmetGtW85o5xpgKdEUSLAde09700vGYaYVtx');

session_start();
require_once "db.php";

// Check if user is logged in
if (!isset($_SESSION['userID'])) {
    header("Location: login.php");
    exit;
}

$userID = $_SESSION['userID'];

if (!isset($_GET['orderIDs'])) {
    die("Order ID(s) missing.");
}

$orderIDs = array_map('intval', explode(',', $_GET['orderIDs']));

// Get buyer info
$stmt = $conn->prepare("SELECT buyerID FROM buyers WHERE userID = ?");
$stmt->bind_param("i", $userID);
$stmt->execute();
$buyerData = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$buyerData) {
    die("Buyer not found.");
}

$buyerID = $buyerData['buyerID'];

try {
    $conn->begin_transaction();

    $sellerTotals = [];

    foreach ($orderIDs as $orderID) {
        // Validate order
        $stmt = $conn->prepare("SELECT totalAmount, deliveryFee, orderStatus FROM orders WHERE orderID = ? AND buyerID = ?");
        $stmt->bind_param("ii", $orderID, $buyerID);
        $stmt->execute();
        $order = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$order) {
            throw new Exception("Order $orderID not found or does not belong to you.");
        }

        if ($order['orderStatus'] === 'Paid') {
            continue;
        }

        // Mark order as paid
        $stmt = $conn->prepare("UPDATE orders SET orderStatus = 'Paid' WHERE orderID = ?");
        $stmt->bind_param("i", $orderID);
        $stmt->execute();
        $stmt->close();

        // Get order items
        $stmt = $conn->prepare("
            SELECT 
                oi.productID, 
                oi.quantity, 
                oi.price, 
                oi.size, 
                p.sellerID, 
                pv.variantID 
            FROM orderitems oi
            JOIN products p ON oi.productID = p.productID
            JOIN product_variants pv ON oi.productID = pv.productID AND oi.size = pv.size
            WHERE oi.orderID = ?
        ");
        $stmt->bind_param("i", $orderID);
        $stmt->execute();
        $items = $stmt->get_result();


        while ($item = $items->fetch_assoc()) {
            $variantID = $item['variantID'];
            $productID = $item['productID'];
            $quantity = $item['quantity'];
            $sellerID = $item['sellerID'];
            $itemTotal = $item['price'] * $quantity;

            // Reduce stock for the specific variant
            $updateStock = $conn->prepare("
                UPDATE product_variants 
                SET stockQuantity = stockQuantity - ? 
                WHERE variantID = ? AND stockQuantity >= ?
            ");
            $updateStock->bind_param("iii", $quantity, $variantID, $quantity);

            $updateStock->execute();

            if ($updateStock->affected_rows === 0) {
                throw new Exception("Insufficient stock for product ID: $productID");
            }
            $updateStock->close();

            if (!isset($sellerTotals[$sellerID])) {
                $sellerTotals[$sellerID] = 0;
            }
            $sellerTotals[$sellerID] += $itemTotal;
        }
        $stmt->close();

        // Insert payment record
        $deliveryFee = $order['deliveryFee'] ?? 0;
        $paidAmount = $order['totalAmount'] + $deliveryFee;

        $stmt = $conn->prepare("
            INSERT INTO paymentrecords (orderID, paymentDate, paymentStatus, paymentAmount)
            VALUES (?, NOW(), 'Paid', ?)
        ");
        $stmt->bind_param("id", $orderID, $paidAmount);
        $stmt->execute();
        $stmt->close();
    }

    // Update seller sales
    foreach ($sellerTotals as $sellerID => $amount) {
        $stmt = $conn->prepare("UPDATE sellers SET totalSales = totalSales + ? WHERE sellerID = ?");
        $stmt->bind_param("di", $amount, $sellerID);
        $stmt->execute();
        $stmt->close();
    }

    // Clear cart
    clearBuyerCart($buyerID);

    // COMMIT all changes
    $conn->commit();

} catch (Exception $e) {
    $conn->rollback();
    die("Checkout failed: " . htmlspecialchars($e->getMessage()));
}

// Clear cart function
function clearBuyerCart($buyerID) {
    global $conn;
    $stmt = $conn->prepare("
        DELETE ci FROM cartitems ci
        JOIN carts c ON ci.cartID = c.cartID
        WHERE c.buyerID = ?
    ");
    $stmt->bind_param("i", $buyerID);
    $stmt->execute();
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order Confirmation</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css">
</head>
<body>
<?php include 'nav.php'; ?>

<div class="container my-5">
    <h2>Order Confirmation</h2>
    <hr class="mb-4">
    <div class="alert alert-success">
        <h5>Your order(s) have been successfully placed!</h5>
    </div>

    <?php foreach ($orderIDs as $orderID): ?>
        <?php
        $stmt = $conn->prepare("
            SELECT o.orderID, o.orderDate, o.totalAmount, o.deliveryFee, o.orderStatus, 
                   u.uFirst, u.uLast, u.email, 
                   b.shippingAddress1, b.shippingAddress2, b.postalCode,
                   s.businessName
            FROM orders o
            JOIN buyers b ON o.buyerID = b.buyerID
            JOIN users u ON b.userID = u.userID
            JOIN sellers s ON o.sellerID = s.sellerID
            WHERE o.orderID = ?
        ");
        $stmt->bind_param("i", $orderID);
        $stmt->execute();
        $order = $stmt->get_result()->fetch_assoc();

        if (!$order) {
            echo "<div class='alert alert-danger'>Order ID #$orderID not found.</div>";
            continue;
        }

        $stmt = $conn->prepare("
            SELECT oi.productID, p.pName, oi.quantity, oi.price
            FROM orderitems oi
            JOIN products p ON oi.productID = p.productID
            WHERE oi.orderID = ?
        ");
        $stmt->bind_param("i", $orderID);
        $stmt->execute();
        $orderItems = $stmt->get_result();

        $stmt = $conn->prepare("SELECT paymentStatus FROM paymentrecords WHERE orderID = ?");
        $stmt->bind_param("i", $orderID);
        $stmt->execute();
        $paymentStatus = $stmt->get_result()->fetch_assoc()['paymentStatus'] ?? 'Unknown';
        ?>

        <div class="card mb-4">
            <div class="card-header">
                <strong>Order ID: #<?php echo htmlspecialchars($order['orderID']); ?></strong> |
                <?php echo htmlspecialchars($order['businessName']); ?> |
                <?php echo htmlspecialchars($order['orderDate']); ?> |
                Status: <?php echo htmlspecialchars($paymentStatus); ?>
            </div>
            <div class="card-body">
                <ul class="list-group mb-3">
                    <?php while ($item = $orderItems->fetch_assoc()): ?>
                        <li class="list-group-item d-flex justify-content-between">
                            <span><?php echo htmlspecialchars($item['pName']) . " x" . $item['quantity']; ?></span>
                            <strong>R<?php echo number_format($item['price'] * $item['quantity'], 2); ?></strong>
                        </li>
                    <?php endwhile; ?>
                </ul>

                <?php
                $deliveryFee = $order['deliveryFee'] ?? 0;
                $grandTotal = $order['totalAmount'] + $deliveryFee;
                ?>
                <p><strong>Subtotal:</strong> R<?php echo number_format($order['totalAmount'], 2); ?></p>
                <p><strong>Delivery Fee:</strong> R<?php echo number_format($deliveryFee, 2); ?></p>
                <p><strong>Grand Total:</strong> R<?php echo number_format($grandTotal, 2); ?></p>
            </div>
        </div>
    <?php endforeach; ?>

    <a href="buyerdash.php" class="btn btn-primary d-flex text-center">Continue Shopping</a>

</div>
<?php include 'footer.php'; ?>
</body>
</html>
