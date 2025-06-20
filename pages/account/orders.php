<?php
include '../db.php';
session_start();

$userID = $_SESSION['userID'] ?? null;
if (!$userID) {
    die("User not logged in.");
}

// Get buyerID
$buyerStmt = $conn->prepare("SELECT buyerID FROM buyers WHERE userID = ?");
$buyerStmt->bind_param("i", $userID);
$buyerStmt->execute();
$buyerResult = $buyerStmt->get_result();

if ($buyerResult->num_rows === 0) {
    die("Buyer not found.");
}
$buyerID = $buyerResult->fetch_assoc()['buyerID'];

// Get all order IDs for this buyer
$orderIDsStmt = $conn->prepare("SELECT orderID FROM orders WHERE buyerID = ? ORDER BY orderDate DESC, orderID DESC");
$orderIDsStmt->bind_param("i", $buyerID);
$orderIDsStmt->execute();
$orderIDsResult = $orderIDsStmt->get_result();

$orderIDs = [];
while ($row = $orderIDsResult->fetch_assoc()) {
    $orderIDs[] = $row['orderID'];
}

if (count($orderIDs) > 0) {
    $placeholders = implode(',', array_fill(0, count($orderIDs), '?'));
    $types = str_repeat('i', count($orderIDs));

    $query = "
        SELECT 
            o.orderID,
            o.orderDate,
            o.totalAmount,
            o.deliveryFee,
            o.orderStatus,
            oi.productID,
            p.pName,
            p.imagePath
        FROM orders o
        JOIN orderitems oi ON o.orderID = oi.orderID
        JOIN products p ON oi.productID = p.productID
        WHERE o.orderID IN ($placeholders)
        ORDER BY o.orderDate DESC, o.orderID DESC
    ";

    $stmt = $conn->prepare($query);
    $stmt_params = [];
    $stmt_params[] = &$types;
    foreach ($orderIDs as $key => $id) {
        $stmt_params[] = &$orderIDs[$key];
    }
    call_user_func_array([$stmt, 'bind_param'], $stmt_params);
    $stmt->execute();
    $result = $stmt->get_result();

    $orders = [];
    while ($row = $result->fetch_assoc()) {
        $orderID = $row['orderID'];
        if (!isset($orders[$orderID])) {
            $orders[$orderID] = [
                'orderDate' => $row['orderDate'],
                'totalAmount' => $row['totalAmount'],
                'deliveryFee' => $row['deliveryFee'],
                'orderStatus' => $row['orderStatus'],
                'items' => []
            ];
        }
        $orders[$orderID]['items'][] = [
            'productID' => $row['productID'],
            'pName' => $row['pName'],
            'imagePath' => $row['imagePath']
        ];
    }
} else {
    $orders = [];
}
?>

<!-- HTML output -->
<div class="card shadow-sm mt-4 mb-5" style="background-color: #F1F1F1;">
    <div class="card-body mb-0">
        <div class="container my-4">
            <h3 class="mb-4">Orders</h3>
            <hr>
            <div class="row g-3" id="orders-container">
                <?php 
                if (!empty($orders)) {
                    foreach ($orders as $orderID => $order): ?>
                        <div class="col-md-12 col-lg-12">
                            <div class="card h-100 shadow-sm mb-3">
                                <div class="card-body d-flex flex-column shadow-sm p-4">
                                    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
                                        <h5 class="mb-0">
                                            Order #<?= $orderID ?> |
                                            Ordered <?= date('Y-m-d', strtotime($order['orderDate'])) ?> |
                                            <span class="badge 
                                                <?= ($order['orderStatus'] == 'shipped' || $order['orderStatus'] == 'completed') 
                                                    ? 'bg-success' 
                                                    : (($order['orderStatus'] == 'pending') ? 'bg-warning text-dark' : 'bg-secondary') ?>">
                                                <?= ucfirst($order['orderStatus']) ?>
                                            </span>
                                        </h5>
                                        <a href="javascript:void(0)" class="account-link btn btn-sm btn-primary"
                                           onclick="loadContent('order_details&orderID=<?= $orderID ?>')">
                                            View Order
                                        </a>
                                    </div>
                                    <hr>
                                    <div class="d-flex flex-wrap gap-3 justify-content-start">
                                        <?php foreach ($order['items'] as $item): ?>
                                            <div class="me-3 text-center mb-2" style="flex: 1 1 100px; max-width: 100px;">
                                                <img src="uploads/<?= htmlspecialchars($item['imagePath']) ?>" alt="<?= htmlspecialchars($item['pName']) ?>"
                                                     style="width: 100px; height: 100px; object-fit: cover;">   
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach;
                } else {
                    echo '<div class="alert alert-info" role="alert">No orders found.</div>';
                }
                ?>
            </div>
        </div>
    </div>
</div>
