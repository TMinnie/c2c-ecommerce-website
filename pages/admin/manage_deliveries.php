<?php
include '../db.php'; // Database connection

// Optional filtering
$where = [];
$params = [];
$types = "";

if (!empty($_GET['deliveryID'])) {
    $where[] = "deliveryID = ?";
    $params[] = $_GET['deliveryID'];
    $types .= "i";
}
if (!empty($_GET['orderID'])) {
    $where[] = "orderID = ?";
    $params[] = $_GET['orderID'];
    $types .= "i";
}
if (!empty($_GET['deliveryStatus'])) {
    $where[] = "deliveryStatus = ?";
    $params[] = $_GET['deliveryStatus'];
    $types .= "s";
}

$sql = "SELECT * FROM deliveries";
if ($where) {
    $sql .= " WHERE " . implode(" AND ", $where);
}
$sql .= " ORDER BY deliveryDate DESC";

$stmt = $conn->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
?>
<!-------------------------------------------------------------------------------------------------------------------------------->
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
                <h3 class="mb-4">Manage Deliveries</h3>
            <hr>

            <!-- Search Form -->
             <div class="card p-3 mb-4 shadow-sm rounded-3">
            <form method="GET" class="row g-3">

                <div class="col-md-4">
                                <div class="input-group">
                                    <select name="searchType" class="form-select">
                                        <option value="productID">Delivery ID</option>
                                        <option value="orderID">Order ID</option>
                                    </select>
                                    <input type="text" name="searchValue" id="searchValue" class="form-control" placeholder="Enter ID...">
                                </div>
                            </div>
                <div class="col-md-3">
                    <select name="deliveryStatus" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="Pending" <?= (isset($_GET['deliveryStatus']) && $_GET['deliveryStatus'] == 'Pending') ? 'selected' : '' ?>>Pending</option>
                        <option value="Dispatched" <?= (isset($_GET['deliveryStatus']) && $_GET['deliveryStatus'] == 'Dispatched') ? 'selected' : '' ?>>Dispatched</option>
                        <option value="Delivered" <?= (isset($_GET['deliveryStatus']) && $_GET['deliveryStatus'] == 'Delivered') ? 'selected' : '' ?>>Delivered</option>
                        <option value="Cancelled" <?= (isset($_GET['deliveryStatus']) && $_GET['deliveryStatus'] == 'Cancelled') ? 'selected' : '' ?>>Cancelled</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-outline-secondary w-100">Apply Filters</button>
                </div>
                <a href="export_orders.php?<?= http_build_query($_GET) ?>" class="btn btn-secondary w-100">Download CSV</a>

            </form>
            </div>

            <!-- Deliveries Table -->
            <table class="table table-bordered align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Delivery ID</th>
                        <th>Order ID</th>
                        <th>Status</th>
                        <th>Dispatch Date</th>
                        <th>Delivery Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= $row['deliveryID']; ?></td>
                                <td>
                                    <a href="manage_orders.php?orderID=<?= $row['orderID'] ?>">
                                        <?= $row['orderID'] ?>
                                    </a>
                                </td>
                                <td><?= $row['deliveryStatus']; ?></td>
                                <td><?= $row['dispatchDate'] ? $row['dispatchDate'] : '-'; ?></td>
                                <td><?= $row['deliveryDate'] ? $row['deliveryDate'] : '-'; ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="text-center">No deliveries found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
                 
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>


</body>
</html>
