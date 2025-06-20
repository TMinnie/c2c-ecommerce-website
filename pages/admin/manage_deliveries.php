<?php
include '../db.php'; // Database connection

// Optional filtering
$where = [];
$params = [];
$types = "";

if (!empty($_GET['searchType']) && !empty($_GET['searchValue'])) {
    $searchType = $_GET['searchType'];
    $searchValue = $_GET['searchValue'];

    if (in_array($searchType, ['deliveryID', 'orderID', 'sellerID', 'buyerID'])) {
        // Ensure the correct table prefix is used for joined fields
        $column = ($searchType == 'sellerID' || $searchType == 'buyerID') ? "o.$searchType" : "d.$searchType";
        $where[] = "$column = ?";
        $params[] = $searchValue;
        $types .= "i";
    }
}

$sql = "SELECT d.*, o.sellerID, o.buyerID 
        FROM deliveries d
        JOIN orders o ON d.orderID = o.orderID";

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
                                        <option value="deliveryID" <?= (isset($_GET['searchType']) && $_GET['searchType'] == 'deliveryID') ? 'selected' : '' ?>>Delivery ID</option>
                                        <option value="orderID" <?= (isset($_GET['searchType']) && $_GET['searchType'] == 'orderID') ? 'selected' : '' ?>>Order ID</option>
                                        <option value="sellerID" <?= (isset($_GET['searchType']) && $_GET['searchType'] == 'sellerID') ? 'selected' : '' ?>>Seller ID</option>
                                        <option value="buyerID" <?= (isset($_GET['searchType']) && $_GET['searchType'] == 'buyerID') ? 'selected' : '' ?>>Buyer ID</option>
                                    </select>
                                    <input type="text" name="searchValue" id="searchValue" class="form-control" placeholder="Enter ID..." value="<?= htmlspecialchars($_GET['searchValue'] ?? '') ?>">
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
                    <button type="button" class="btn btn-secondary w-100" onclick="exportCSV()">Export CSV</button>
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
                        <?php while ($row = $result->fetch_assoc()):
                            $data[] = $row;?>
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

    <script type="module">
        import { downloadCSV } from './exportCSV.js';

        document.addEventListener('DOMContentLoaded', () => {
            const data = <?= json_encode($data ?? []); ?>;

            // Define actual keys in data
            const fields = ['deliveryID', 'orderID', 'deliveryStatus', 'dispatchDate', 'deliveryDate'];

            // Define column headers for export
            const headers = ['Delivery ID', 'Order ID', 'Status', 'Dispatch Date', 'Delivery Date'];

            // Transform data for export
            const formattedData = data.map(row => {
                const formattedRow = {};
                fields.forEach((field, i) => {
                    formattedRow[headers[i]] = row[field] ?? '';
                });
                return formattedRow;
            });

            window.exportCSV = function () {
                downloadCSV(formattedData, headers, "deliveries_export.csv");
            };
        });

    </script>

</body>
</html>
