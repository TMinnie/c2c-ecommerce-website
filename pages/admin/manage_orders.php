<?php
session_start();
if (!isset($_SESSION['userID']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}
require_once '../db.php';

// Capture filters
$orderID = $_GET['orderID']??'';
$orderStatusFilter = $_GET['orderStatus'] ?? '';
$orderDateFilter = $_GET['orderDate'] ?? '';
$searchType = $_GET['searchType'] ?? '';
$searchValue = $_GET['searchValue'] ?? '';

// Build dynamic SQL
$whereClauses = [];
$params = [];
$types = '';

// Filter: Order ID
if (!empty($orderID)) {
    $whereClauses[] = "orderID = ?";
    $params[] = $orderID;
    $types .= 'i';
}

// Filter: Order Status
if (!empty($orderStatusFilter)) {
    $whereClauses[] = "orderStatus = ?";
    $params[] = $orderStatusFilter;
    $types .= 's';
}

// Filter: Order Date
if (!empty($orderDateFilter)) {
    $whereClauses[] = "DATE(orderDate) = ?";
    $params[] = $orderDateFilter;
    $types .= 's';
}

// Filter: Seller ID, Buyer ID, or Order ID
if (!empty($searchValue) && in_array($searchType, ['sellerID', 'buyerID', 'orderID'])) {
    $whereClauses[] = "$searchType = ?";
    $params[] = $searchValue;
    $types .= 'i';
}

// Final SQL
$whereSQL = $whereClauses ? 'WHERE ' . implode(' AND ', $whereClauses) : '';
$sql = "SELECT * FROM orders $whereSQL ORDER BY orderDate DESC";
$stmt = $conn->prepare($sql);

// Bind params if there are any
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();
?>


<!-- HTML OUTPUT ------------------------------------------------------------------------------------------------------------------->
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>

    <!--Bootstrap-->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css">

    <!--Stylesheets-->
    <link rel="stylesheet" href="../assets/css/theme.css">
</head>

<body>
    <!-- Navigation -->
    <?php include 'admin_nav.php'; ?>

    <div class="d-flex">
        <!-- Sidebar with links -->
        <?php include 'sidebar.php'; ?>

        <!-- Content Area for Dynamic Content -->
        <div class="content-wrapper">
            <div id="dynamic-content" class="mt-4">
                <h3 class="mb-4">Manage Orders</h3>
                <hr>

                <div class="card p-3 mb-4 shadow-sm rounded-3">
                <!-- Filters -->
                <form class="row g-3 mb-2" method="GET" action="">
                    <div class="col-md-2">
                        <select name="orderStatus" id="orderStatus" class="form-select">
                            <option value="">All Statuses</option>
                            <option value="pending" <?= $orderStatusFilter == 'pending' ? 'selected' : '' ?>>Pending</option>
                            <option value="paid" <?= $orderStatusFilter == 'paid' ? 'selected' : '' ?>>Paid</option>
                            <option value="accepted" <?= $orderStatusFilter == 'accepted' ? 'selected' : '' ?>>Accepted
                            </option>
                            <option value="shipped" <?= $orderStatusFilter == 'shipped' ? 'selected' : '' ?>>Shipped
                            </option>
                            <option value="completed" <?= $orderStatusFilter == 'completed' ? 'selected' : '' ?>>Completed
                            </option>
                            <option value="cancelled" <?= $orderStatusFilter == 'cancelled' ? 'selected' : '' ?>>Cancelled
                            </option>
                            <option value="refunded" <?= $orderStatusFilter == 'refunded' ? 'selected' : '' ?>>Refunded
                            </option>
                        </select>
                    </div>

                      <div class="col-md-4">
                        <div class="input-group">
                            <select name="searchType" class="form-select">
                                <option value="sellerID">Seller ID</option>
                                <option value="buyerID">Buyer ID</option>
                                <option value="orderID">Order ID</option>
                            </select>
                            <input type="text" name="searchValue" class="form-control" placeholder="Enter ID...">
                        </div>
                    </div>


                    <div class="col-md-3">
                        <input type="date" name="orderDate" id="orderDate"
                            value="<?= htmlspecialchars($orderDateSearch) ?>" class="form-control">
                    </div>

                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-outline-secondary w-100">Apply Filters</button>
                    </div>
                    
                     <a href="export_orders.php?<?= http_build_query($_GET) ?>" class="btn btn-secondary w-100">Download CSV</a>
                </form>
                </div>
                <!-- Orders Table -->
                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Order ID</th>
                                <th>Buyer ID</th>
                                <th>Order Date</th>
                                <th>Products Total</th>
                                <th>Order Status</th>
                                <th>Seller ID</th>
                                <th>Delivery Fee</th>
                                <th>Order Total</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result->num_rows > 0): ?>

                                <?php while ($order = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td>
                                            <a href="#" class="order-details-link" data-order-id="<?= $order['orderID'] ?>" data-bs-toggle="modal" data-bs-target="#orderDetailsModal">
                                                <?= $order['orderID'] ?>
                                            </a>
                                        </td>
                                        <td>
                                            <a href="manage_buyers.php?buyerID=<?= $order['buyerID'] ?>">
                                                <?= $order['buyerID'] ?>
                                            </a>
                                        </td>
                                        <td><?= $order['orderDate'] ?></td>
                                        <td>R<?= number_format($order['totalAmount'], 2) ?></td>
                                        <td><?= ucwords($order['orderStatus']) ?></td>
                                        <td>
                                            <a href="manage_sellers.php?sellerID=<?= $order['sellerID'] ?>">
                                                <?= $order['sellerID'] ?>
                                            </a>
                                        </td>
                                        <td>R<?= number_format($order['deliveryFee'], 2) ?></td>
                                        <td>
                                            R<?= number_format($order['deliveryFee'] + $order['totalAmount'], 2) ?>
                                        </td>
                                        <td>
                                            <?php if ($order['orderStatus']== 'Pending'||$order['orderStatus']== 'Paid'): ?>
                                                <!-- Show Cancel button -->
                                                <form method="post" action="../assets/functions/cancel_order.php">
                                                    <input type="hidden" name="orderID" value="<?= $order['orderID'] ?>">
                                                    <input type="hidden" name="redirect" value="admin/manage_orders.php">
                                                    <button type="submit" class="btn btn-sm btn-danger">Cancel</button>
                                                </form>

                                            <?php else: ?>
                                                <button type="submit" class="btn btn-sm btn-secondary" disabled>Cancel</button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="9" class="text-center">No orders found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>

    <!-- Order items Modal -->
    <div class="modal fade" id="orderDetailsModal" tabindex="-1" aria-labelledby="orderDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">Order Details</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <div id="order-items-content">
            <!-- Order items will be loaded here -->
            </div>
        </div>
        </div>
    </div>
    </div>



    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!--JavaScript to Load Order Items via AJAX-->
    <script>
    document.addEventListener("DOMContentLoaded", function () {
        const links = document.querySelectorAll('.order-details-link');
        links.forEach(link => {
            link.addEventListener('click', function (e) {
                e.preventDefault();
                const orderID = this.getAttribute('data-order-id');

                fetch(`fetch_order_items.php?orderID=${orderID}`)
                    .then(response => response.text())
                    .then(data => {
                        document.getElementById('order-items-content').innerHTML = data;
                    })
                    .catch(error => {
                        console.error('Error fetching order items:', error);
                        document.getElementById('order-items-content').innerHTML = "<p>Error loading order details.</p>";
                    });
            });
        });
    });
    </script>

    
</body>

</html>