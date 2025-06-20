<?php
include '../db.php'; // database connection

$where = [];
$params = [];
$types = "";

$searchType = $_GET['searchType'] ?? '';
$searchValue = $_GET['searchValue'] ?? '';

if (!empty($searchType) && !empty($searchValue)) {
    if (in_array($searchType, ['paymentID', 'orderID'])) {
        $where[] = "p.$searchType = ?";
        $params[] = $searchValue;
        $types .= "i";
    } elseif (in_array($searchType, ['sellerID', 'buyerID'])) {
        $where[] = "o.$searchType = ?";
        $params[] = $searchValue;
        $types .= "i";
    }
}

if (!empty($_GET['paymentDate'])) {
    $where[] = "DATE(p.paymentDate) = ?";
    $params[] = $_GET['paymentDate'];
    $types .= "s";
}

// Prepare the SQL query with dynamic WHERE conditions
$sql = "SELECT p.*, o.sellerID, o.buyerID 
        FROM paymentrecords p 
        JOIN orders o ON p.orderID = o.orderID";

if ($where) {
    $sql .= " WHERE " . implode(" AND ", $where);
}
$sql .= " ORDER BY p.paymentDate DESC";


$stmt = $conn->prepare($sql);
if ($params) {
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
                  <h3 class="mb-4">Manage Transactions</h3>
                <hr>

                <!--filter result-->
                <div class="card p-3 mb-4 shadow-sm rounded-3">
                <form method="GET" class="row g-3">
         
                    <div class="col-md-5">
                        <div class="input-group">
                            <select name="searchType" class="form-select">
                                <option value="paymentID">Payment ID</option>
                                <option value="orderID">Order ID</option>
                                <option value="sellerID">Seller ID</option>
                                <option value="buyerID">Buyer ID</option>
                            </select>
                            <input type="text" name="searchValue" class="form-control" placeholder="Enter ID...">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <input type="date" name="paymentDate" class="form-control" placeholder="Search by Payment Date" value="<?= isset($_GET['paymentDate']) ? htmlspecialchars($_GET['paymentDate']) : '' ?>">
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-outline-secondary w-100">Apply Filters</button>
                    </div>
                     <div class="">
                        <button type="button" class="btn btn-secondary w-100" onclick="exportCSV()">Export CSV</button>
                    </div>

                </form>
            </div>

                    <table class="table table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Payment ID</th>
                                <th>Order ID</th>
                                <th>Payment Date</th>
                                <th>Status</th>
                                <th>Amount</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php if ($result->num_rows > 0): ?>
                                <?php while ($row = $result->fetch_assoc()) {
                                    $data[] = $row; ?>
                                    <tr>
                                        <td><?= $row['paymentID']; ?></td>
                                        <td><a href="manage_orders.php?orderID=<?= $row['orderID']; ?>"><?= $row['orderID']; ?></a></td>
                                        <td><?= $row['paymentDate']; ?></td>
                                        <td><?= $row['paymentStatus']; ?></td>
                                        <td>R <?= number_format($row['paymentAmount'], 2); ?></td>
                                    </tr>
                                <?php } ?>
                            <?php else: ?>
                                <tr><td colspan="7" class="text-center">No transactions found.</td></tr>
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

    // Assign to window inside DOMContentLoaded so it's globally available
    document.addEventListener('DOMContentLoaded', () => {
        const data = <?= json_encode($data); ?>;
        const headers = ['paymentID', 'orderID', 'sellerID', 'buyerID', 'paymentDate', 'paymentStatus', 'paymentAmount'];

        window.exportCSV = function () {
            downloadCSV(data, headers, "transactions_export.csv");
        };
    });
</script>



</body>
</html>
