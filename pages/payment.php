<?php $pageName = 'buy'; ?>
<?php
session_start();
require_once "db.php";

if (!isset($_SESSION['newOrders']) || empty($_SESSION['newOrders'])) {
    die("No orders found or session expired.");
}

$newOrders = $_SESSION['newOrders'];
$total = 0;

// Use the first order to fetch buyer info
$firstOrderID = $newOrders[0]['orderID'];

$stmt = $conn->prepare("
    SELECT o.*, b.*, u.uFirst, u.uLast, u.email
    FROM orders o
    JOIN buyers b ON o.buyerID = b.buyerID
    JOIN users u ON b.userID = u.userID
    WHERE o.orderID = ?
");
$stmt->bind_param("i", $firstOrderID);
$stmt->execute();
$buyer = $stmt->get_result()->fetch_assoc();

if (!$buyer) {
    die("Buyer not found.");
}

$totalItems = 0;
$totalSubtotal = 0;
$totalDelivery = 0;

foreach ($newOrders as &$order) {
    $orderID = $order['orderID'];

    // Get seller business name
    $stmt = $conn->prepare("SELECT businessName FROM sellers WHERE sellerID = ?");
    $stmt->bind_param("i", $order['sellerID']);
    $stmt->execute();
    $seller = $stmt->get_result()->fetch_assoc();
    $order['businessName'] = $seller['businessName'];

    // Fetch order details (items, subtotal, deliveryFee)
    $stmt = $conn->prepare("
        SELECT oi.*, p.pName
        FROM orderitems oi
        JOIN products p ON oi.productID = p.productID
        WHERE oi.orderID = ?
    ");
    $stmt->bind_param("i", $orderID);
    $stmt->execute();
    $order['items'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    // Get delivery fee and subtotal for this order
    $stmt = $conn->prepare("SELECT deliveryFee, totalAmount FROM orders WHERE orderID = ?");
    $stmt->bind_param("i", $orderID);
    $stmt->execute();
    $orderDetails = $stmt->get_result()->fetch_assoc();

    $order['deliveryFee'] = $orderDetails['deliveryFee'];
    $order['subtotal'] = $orderDetails['totalAmount'];
    $order['finalAmount'] = $order['subtotal'] + $order['deliveryFee'];

    // Tally totals
    $total += $order['finalAmount'];
    $totalSubtotal += $order['subtotal'];
    $totalDelivery += $order['deliveryFee'];

    foreach ($order['items'] as $item) {
        $totalItems += $item['quantity'];
    }
}
?>


<!--------------------------------------------------------------------------------------------------------------------------->

<!DOCTYPE svg PUBLIC '-//W3C//DTD SVG 1.1//EN' 'http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd'>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Checkout</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/theme.css">
    <link rel="stylesheet" href="assets/css/stylecheckout.css">
</head>

<body>
    <?php include 'nav.php'; ?>

    <div class="container my-5">
        <h2 class="mb-4">Confirm Order</h2>

        <!--Left-->
        <div class="row">
            <div class="col-md-8 mb-4">
                <!-- Delivery Address -->
                <div class="card p-4 shadow-sm mb-4">
                    <h5 class="mb-3">Delivery Address</h5>
                    <p class="mb-2">
                        <?= htmlspecialchars($buyer['shippingAddress1']) ?><br>
                        <?php if (!empty($buyer['shippingAddress2'])): ?>
                            <?= htmlspecialchars($buyer['shippingAddress2']) ?><br>
                        <?php endif; ?>
                        <?= htmlspecialchars($buyer['postalCode']) ?>
                    </p>
                    <small class="text-muted">
                        <p class="mb-1"><?= htmlspecialchars($buyer['uFirst'] ?? '') ?>
                            <?= htmlspecialchars($buyer['uLast'] ?? '') ?></p>
                        <p class="mb-0"><?= htmlspecialchars($buyer['email'] ?? '') ?></p>
                    </small>
                </div>

                <!-- Order Summary -->
                <div class="card p-4 shadow-sm">
                    <h5 class="mb-3">Order Summary</h5>
                    <ul class="list-group mb-3">
                        <?php foreach ($newOrders as $o): ?>
                            <!-- Order Card -->
                            <li class="list-group-item p-3 mb-2 border rounded shadow-sm bg-light">
                                <div class="d-flex justify-content-between mb-3">
                                    <span class="fw-bold">Order #<?= $o['orderID'] ?></span>
                                    <span class="text-muted"><?= $o['businessName'] ?></span>
                                </div>

                                <!-- Order Items -->
                                <ul class="list-group mt-3">
                                    <?php foreach ($o['items'] as $item): ?>
                                        <li class="list-group-item d-flex justify-content-between">
                                            <span class=""><?= $item['pName'] ?> (Qty: <?= $item['quantity'] ?>)</span>
                                            R<?= number_format($item['price'] * $item['quantity'], 2) ?>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                                <br>

                                <div class="d-flex justify-content-between">
                                    <strong>Subtotal: </strong>
                                    <span class="text-muted">R<?= number_format($o['subtotal'], 2) ?></span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <strong>Delivery: </strong>
                                    <span class="text-muted">R<?= number_format($o['deliveryFee'], 2) ?></span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <strong>Order Total: </strong>
                                    <span class="text-muted">R<?= number_format($o['finalAmount'], 2) ?></span>
                                </div>

                            </li>
                        <?php endforeach; ?>
                        <li class="list-group-item d-flex justify-content-between bg-light p-3 mt-3">
                            <span>Total</span>
                            <strong class="fs-4">R<?= number_format($total, 2) ?></strong>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card p-4 shadow-sm">
                <form action="create_checkout_session.php" method="POST">
                    <?php foreach ($newOrders as $o): ?>
                        <input type="hidden" name="orderIDs[]" value="<?= $o['orderID'] ?>">
                    <?php endforeach; ?>

                    <ul class="list-group mb-3">
                        <li class="d-flex justify-content-between">
                            <span><?= $totalItems ?> Item(s):</span>
                            <p>R<?= number_format($totalSubtotal, 2) ?></p>
                        </li>

                        <li class="d-flex justify-content-between">
                            <span>Delivery:</span>
                            <p>R<?= number_format($totalDelivery, 2) ?></p>
                        </li>
                        <hr>
                        <li class="d-flex justify-content-between">
                            <span>To Pay:</span>
                            <p class="text-success fw-bold">R<?= number_format($total, 2) ?></p>
                        </li>
                    </ul>

                    <button type="submit" class="btn btn-orange w-100 mb-3">Proceed to Secure Payment</button>
                    <p class="text-muted text-center"><i class="fa-solid fa-lock"></i> Secure Checkout</p>
                </form>
            </div>
            </div>

        </div>
    </div>

    <!------------------------------------------------------------------------------------------------------------------------------->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>