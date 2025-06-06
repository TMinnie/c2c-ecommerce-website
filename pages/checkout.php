<?php $pageName = 'buy'; ?>
<?php
session_start();
require_once "db.php";

// Check if user is logged in
if (!isset($_SESSION['userID'])) {
    header("Location: login.php");
    exit;
}

$userID = $_SESSION['userID'];

// Fetch buyerID and user details
$stmt = $conn->prepare("
    SELECT b.buyerID, u.uFirst, u.uLast, b.shippingAddress1, b.shippingAddress2, 
           b.postalCode, u.email
    FROM buyers b
    JOIN users u ON b.userID = u.userID
    WHERE b.userID = ?
");

$stmt->bind_param("i", $userID);
$stmt->execute();
$buyer = $stmt->get_result()->fetch_assoc();

if (!$buyer) {
    die("Buyer not found.");
}

$buyerID = $buyer['buyerID'];
$uFirst = $buyer['uFirst'];
$uLast = $buyer['uLast'];
$shippingAddress1 = $buyer['shippingAddress1'];
$shippingAddress2 = $buyer['shippingAddress2'];
$postalCode = $buyer['postalCode'];
$email = $buyer['email'];

// Fetch cart items grouped by seller
$stmt = $conn->prepare("
    SELECT p.sellerID, s.businessName, p.pName, p.pPrice, ci.quantity
    FROM cartitems ci
    JOIN products p ON ci.productID = p.productID
    JOIN carts c ON ci.cartID = c.cartID
    JOIN sellers s ON p.sellerID = s.sellerID
    WHERE c.buyerID = ?
");
$stmt->bind_param("i", $buyerID);
$stmt->execute();
$cartItems = $stmt->get_result();

// Group cart items by seller
$sellerOrders = [];
while ($item = $cartItems->fetch_assoc()) {
    $sellerOrders[$item['sellerID']]['businessName'] = $item['businessName'];
    $sellerOrders[$item['sellerID']]['items'][] = $item;
}

//Calculate delivery fee
$baseFee = 40.00;
$perItemFee = 5.00;
$deliveryFee = 0;

// Calculate the delivery fee for each seller
$sellerDeliveryFees = [];

foreach ($sellerOrders as $sellerID => $seller) {
    $stmt = $conn->prepare("
        SELECT SUM(ci.quantity) as itemCount
        FROM cartitems ci
        JOIN carts c ON ci.cartID = c.cartID
        JOIN products p ON ci.productID = p.productID
        WHERE c.buyerID = ? AND p.sellerID = ?
    ");
    $stmt->bind_param("ii", $buyerID, $sellerID);
    $stmt->execute();
    $result = $stmt->get_result();
    $itemCount = $result->fetch_assoc()['itemCount'] ?? 0;

    // Store per-seller delivery fee
    $sellerDeliveryFees[$sellerID] = $baseFee + ($perItemFee * $itemCount);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Checkout</title>
    <!--Bootstrap-->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css">

    <!--Stylesheets-->
    <link rel="stylesheet" href="assets/css/theme.css">
    <link rel="stylesheet" href="assets/css/stylecheckout.css">
</head>

<body>

    <!--Header-->
    <?php include 'nav.php'; ?>

    <!--Checkout-->
    <div class="container my-5">
        <h3 class="mb-4">Checkout Details</h3>

        <form action="create_order.php" method="POST">
            <input type="hidden" name="grandTotal" value="<?= $grandTotal ?>">
            <input type="hidden" name="buyerID" value="<?= $buyerID ?>"
            >
            <!-- Shipping Info -->
            <div class="row">
                <!-- Left Column -->
                <div class="col-md-7 mb-4">
                    <div class="card p-4  shadow-sm ">

                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0">Delivery Address</h5>
                            <a href="account.php?page=address" class="btn btn-outline-secondary btn-sm">Edit Address</a>
                        </div>

                        
                            <div class="mb-3">
                                <label class="form-label">Street Address</label>
                                <input type="text" class="form-control"
                                    value="<?= htmlspecialchars($shippingAddress1); ?>" readonly>
                            </div>

                            <?php if (!empty($shippingAddress2)): ?>
                                <div class="mb-3">
                                    <label class="form-label">City</label>
                                    <input type="text" class="form-control"
                                        value="<?= htmlspecialchars($shippingAddress2); ?>" readonly>
                                </div>
                            <?php endif; ?>

                            <div class="mb-3">
                                <label class="form-label">Postal Code</label>
                                <input type="text" class="form-control" value="<?= htmlspecialchars($postalCode); ?>"
                                    readonly>
                            </div>

                            <br>
                            <h5 class="mb-3">Contact Information</h5>
                            <div class="mb-3">
                                <label class="form-label">Full Name</label>
                                <input type="text" class="form-control"
                                    value="<?= htmlspecialchars($uFirst . ' ' . $uLast); ?>" readonly>
                            </div>

                            <div class="mb-0">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" value="<?= htmlspecialchars($email); ?>"
                                    readonly>
                            </div>
                        
                    </div>

                </div>

                <!-- Right Column -->
                <div class="col-md-5 mb-4">
                    <div class="card p-4  shadow-sm">
                        <h5 class="mb-3">Your Order</h5>
                        <?php
                        $grandTotal = 0;
                        foreach ($sellerOrders as $sellerID => $seller) {
                            $sellerTotal = 0;
                            ?>
                            <input type="hidden" name="sellerIDs[]" value="<?= $sellerID ?>">

                            <h6><?php echo htmlspecialchars($seller['businessName']); ?></h6>
                            <ul class="list-group mb-3">
                                <?php foreach ($seller['items'] as $item):
                                    $subtotal = $item['pPrice'] * $item['quantity'];
                                    $sellerTotal += $subtotal;
                                    ?>
                                    <li class="list-group-item d-flex justify-content-between">
                                        <span class=""><?= $item['pName'] ?> (Qty: <?= $item['quantity'] ?>)</span>
                                        R<?= number_format($item['pPrice'] * $item['quantity'], 2) ?>
                                    </li>
                                <?php endforeach; ?>

                                <?php $sellerDeliveryFee = $sellerDeliveryFees[$sellerID]; ?>
                                <li class="list-group-item d-flex justify-content-between">
                                    <span>Delivery
                                    <i class="bi bi-info-circle ms-1 text-muted" tabindex="0"
                                        data-bs-toggle="popover"
                                        data-bs-trigger="hover"
                                        title="Delivery Fee Explained"
                                        data-bs-content="Delivery = R40 base fee + R5 per item from this seller.">
                                        </i>    
                                    </span>
                                    R<?= number_format($sellerDeliveryFee, 2) ?>
                                </li>

                                <?php
                                $orderTotal = $sellerTotal + $sellerDeliveryFee;
                                ?>

                                <li class="list-group-item d-flex justify-content-between">
                                    <span>Subtotal </span>
                                    R<?php echo number_format($orderTotal, 2); ?>
                                </li>
                            </ul>
                            <?php
                            $grandTotal += $orderTotal;
                        } // End foreach sellers
                        ?>

                        <li class="list-group-item d-flex justify-content-between">
                            <span>Total</span>
                            <strong>R<?php echo number_format($grandTotal, 2); ?></strong>
                        </li>

                        <button type="submit" class="btn btn-orange mt-2 w-100">Continue to Payment</button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'))
            popoverTriggerList.map(function (popoverTriggerEl) {
                return new bootstrap.Popover(popoverTriggerEl)
            });
        });
    </script>


</body>

</html>