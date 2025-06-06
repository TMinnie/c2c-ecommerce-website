<?php
// order_details.php
include '../db.php';
session_start();

if (!isset($_SESSION['userID'])) {
    die("User not logged in.");
}
$userID = $_SESSION['userID'];

$orderID = isset($_GET['orderID']) ? intval($_GET['orderID']) : 0;
if ($orderID <= 0) {
    die("Invalid order ID.");
}

// Step 1: Get buyerID linked to user
$buyerStmt = $conn->prepare("SELECT buyerID FROM buyers WHERE userID = ?");
$buyerStmt->bind_param("i", $userID);
$buyerStmt->execute();
$buyerResult = $buyerStmt->get_result();
if ($buyerResult->num_rows === 0) {
    die("Buyer not found.");
}
$buyerID = $buyerResult->fetch_assoc()['buyerID'];

// Step 2: Verify the order belongs to the buyer
$orderStmt = $conn->prepare("SELECT * FROM orders WHERE orderID = ? AND buyerID = ?");
$orderStmt->bind_param("ii", $orderID, $buyerID);
$orderStmt->execute();
$orderResult = $orderStmt->get_result();
if ($orderResult->num_rows === 0) {
    die("Order not found or access denied.");
}
$order = $orderResult->fetch_assoc();

// Step 3: Get order details (buyer name, address, ordersummary(totalitems, deliveryfee, totalamount))
$orderDetailsQuery = "
    SELECT 
        o.orderID,
        CONCAT(u.uFirst, ' ', u.uLast) AS buyerName,
        b.shippingAddress1,
        b.shippingAddress2,
        b.postalCode,
        COUNT(oi.productID) AS totalItems,
        o.totalAmount,
        o.deliveryFee,
        d.dispatchDate, 
        d.deliveryDate
    FROM orders o
    JOIN buyers b ON o.buyerID = b.buyerID
    JOIN users u ON b.userID = u.userID
    JOIN orderitems oi ON o.orderID = oi.orderID
    LEFT JOIN deliveries d ON o.orderID = d.orderID
    WHERE o.orderID = ?
    GROUP BY 
        o.orderID, buyerName, b.shippingAddress1, b.shippingAddress2, b.postalCode, o.deliveryFee, o.totalAmount, d.dispatchDate
";

$orderDetailsStmt = $conn->prepare($orderDetailsQuery);
$orderDetailsStmt->bind_param("i", $orderID);
$orderDetailsStmt->execute();
$orderDetailsResult = $orderDetailsStmt->get_result();
$orderDetails = $orderDetailsResult->fetch_assoc();

// Step 4: Get products in the order
$itemQuery = "
    SELECT oi.productID, oi.quantity, oi.price, p.pName, p.imagePath
    FROM orderitems oi
    JOIN products p ON oi.productID = p.productID
    WHERE oi.orderID = ?
";
$itemStmt = $conn->prepare($itemQuery);
$itemStmt->bind_param("i", $orderID);
$itemStmt->execute();
$items = $itemStmt->get_result();

// Step 5: Get tracking status

$steps = ['Pending', 'Order Paid', 'Order Accepted', 'Order Dispatched', 'Order Delivered'];
$statusMap = [
    'Pending' => 'Pending',
    'Paid' => 'Order Paid',
    'Accepted' => 'Order Accepted',
    'Dispatched' => 'Order Dispatched',
    'Completed' => 'Order Delivered'
];

$dbStatus = $order['orderStatus'];
$orderStatus = $statusMap[$dbStatus] ?? 'Pending'; // fallback to Pending

?>
<!---------------------------------------------------------------------------------------------------------------------------->
<!-- HTML Section -->
<div class="card shadow-sm mt-4 mb-5" style="background-color: #F1F1F1;">
    <div class="card-body mb-0">
        <div class="container mt-4">

            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3>Order details</h3>
                <a href="javascript:void(0)" class="account-link btn btn-sm btn-primary"
                    onclick="loadContent('orders&orderID=<?php echo $order['orderID']; ?>')">
                    View All Orders
                </a>
            </div>

            <!--Order details-->
            <div class="card shadow-sm p-4">
                <p class="mb-2">
                    <strong>Order #<?= $orderID ?></strong> |
                    Ordered <?= date('Y-m-d', strtotime($order['orderDate'])) ?> |
                    <?= ucfirst($order['orderStatus']) ?>
                    <?php if ($order['orderStatus'] === 'Dispatched' && !empty($orderDetails['dispatchDate'])): ?>
                        | Dispatched on <?= date('Y-m-d', strtotime($orderDetails['dispatchDate'])) ?>
                    <?php elseif ($order['orderStatus'] === 'Completed' && !empty($orderDetails['deliveryDate'])): ?>
                        | Delivered on <?= date('Y-m-d', strtotime($orderDetails['deliveryDate'])) ?>
                    <?php endif; ?>
                </p>
                <hr>

                <?php $orderTotal = $orderDetails['totalAmount'] + $orderDetails['deliveryFee']; ?>

                <div class="row">
                    <!-- Delivery Address -->
                    <div class="col-12 col-md-4 mb-5">
                        <p class="fw-bold mb-3"><i class="fa-solid fa-address-book me-1"></i>Delivery Address</p>
                        <p class="mb-1"><?= htmlspecialchars($orderDetails['buyerName']) ?></p>
                        <p class="mb-1"><?= htmlspecialchars($orderDetails['shippingAddress1']) ?></p>
                        <?php if (!empty($orderDetails['shippingAddress2'])): ?>
                            <p class="mb-1"><?= htmlspecialchars($orderDetails['shippingAddress2']) ?></p>
                        <?php endif; ?>
                        <p class="mb-1"><?= htmlspecialchars($orderDetails['postalCode']) ?></p>
                    </div>

                    <!-- Delivery + Payment -->
                    <div class="col-12 col-md-4 mb-5">
                        <p class="fw-bold mb-2"><i class="fa-solid fa-truck me-1"></i>Delivery Method</p>
                        <p class="mb-4">Standard Courier</p>

                        <p class="fw-bold mb-2"><i class="fa-regular fa-credit-card me-1"></i>Payment Method</p>
                        <p class="mb-1">Card</p>
                    </div>

                    <!-- Order Summary -->
                    <div class="col-12 col-md-4">
                        <p class="fw-bold mb-3"><i class="fa-solid fa-list-ul me-1"></i>Order Summary</p>
                        <div class="d-flex justify-content-between">
                            <p>Total Items (<?= $orderDetails['totalItems'] ?>):</p>
                            <span>R<?= number_format($orderDetails['totalAmount'], 2) ?></span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <p>Delivery Fee:</p>
                            <span>R<?= number_format($orderDetails['deliveryFee'], 2) ?></span>
                        </div>
                        <div class="d-flex justify-content-between fw-bold mt-4">
                            <span>Total:</span>
                            <span>R<?= number_format($orderTotal, 2) ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <br>

            <!--Order Tracking-->
            <?php if (!in_array($order['orderStatus'], ['Cancelled', 'Refunded'])): ?>
                <div class="card shadow-sm px-4 p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5>Track your order</h5>
                        <?php if (in_array($order['orderStatus'], ['Pending', 'Paid'])): ?>
                            <form method="post" action="assets/functions/cancel_order.php">
                                <input type="hidden" name="orderID" value="<?= $order['orderID'] ?>">
                                <input type="hidden" name="redirect" value="account.php?page=orders ?>">
                                <button type="submit" class="btn btn-sm btn-danger">Cancel</button>
                            </form>
                        <?php else: ?>
                            <button type="submit" class="btn btn-sm btn-secondary" disabled>Cancel</button>
                        <?php endif; ?>
                    </div>
                    <ul class="tracking-steps">
                        <?php foreach ($steps as $index => $step): ?>
                            <?php
                            $statusClass = '';
                            if ($step == $orderStatus) {
                                $statusClass = 'current';
                            } elseif (array_search($step, $steps) < array_search($orderStatus, $steps)) {
                                $statusClass = 'completed';
                            }

                            // Date display logic
                            $dateDisplay = '';
                            if ($step === 'Order Dispatched' && !empty($orderDetails['dispatchDate'])) {
                                $dateDisplay = date('Y-m-d', strtotime($orderDetails['dispatchDate']));
                            }
                            if ($step === 'Order Delivered' && !empty($orderDetails['deliveryDate'])) {
                                $dateDisplay = date('Y-m-d', strtotime($orderDetails['deliveryDate']));
                            }
                            ?>
                            <li class="<?= $statusClass ?>">
                                <div class="step-wrapper">
                                    <span class="icon">
                                        <?= ($statusClass == 'completed' || $statusClass == 'current') ? '&#10003;' : '' ?>
                                    </span>
                                    <span class="text"><?= $step ?></span>
                                    <?php if ($dateDisplay): ?>
                                        <div class="text-muted small"><?= $dateDisplay ?></div>
                                    <?php endif; ?>

                                    <?php if ($index < count($steps) - 1): ?>
                                        <div class="progress-line <?= (array_search($step, $steps) < array_search($orderStatus, $steps)) ? 'filled' : '' ?>"></div>
                                    <?php endif; ?>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>

                </div>
                <br>
            <?php endif; ?>

            <!--Order items-->
            <div class="row">
                <?php while ($item = $items->fetch_assoc()): ?>
                    <div class="col-12 mb-3">
                        <div class="card p-2 d-flex flex-column flex-md-row align-items-start align-items-md-center">
                            <div class="square-image-wrapper">
                                <img src="uploads/<?= $item['imagePath'] ?>" alt="<?= $item['pName'] ?>" class="product-image">
                            </div>
                            <div>
                                <h6 class="fw-bold mb-1"><?= $item['pName'] ?></h6>
                                <p class="mb-1">Quantity: <?= $item['quantity'] ?></p>
                                <p class="mb-0">R<?= number_format($item['price'], 2) ?></p>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>

        </div>
    </div>
</div>