<?php
include '../db.php'; // database connection

$where = [];
$params = [];
$types = "";

$searchType= $_GET['searchType']??'';
$searchValue= $_GET['searchValue']??'';

// Build filter
if (!empty($searchType)&& !empty($searchValue)) {
    $where[] = "$searchType = ?";
    $params[] = $searchValue;
    $types .= "i";
}

if (!empty($_GET['paymentDate'])) {
    $where[] = "DATE(paymentDate) = ?";
    $params[] = $_GET['paymentDate'];
    $types .= "s";
}

$sql = "SELECT * FROM paymentrecords";
if ($where) {
    $sql .= " WHERE " . implode(" AND ", $where);
}
$sql .= " ORDER BY paymentDate DESC";

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
                     <a href="export_orders.php?<?= http_build_query($_GET) ?>" class="btn btn-secondary w-100">Download CSV</a>
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
                        <?php while ($row = $result->fetch_assoc()) { ?>
                            <tr>
                                <td><?= $row['paymentID']; ?></td>
                                <td>
                                    <a href="manage_orders.php?orderID=<?= $row['orderID'] ?>">
                                        <?= $row['orderID'] ?>
                                    </a>
                                </td>
                                <td><?= $row['paymentDate']; ?></td>
                                <td>
                                        <?= $row['paymentStatus']; ?>
                                </td>
                                <td>R <?= number_format($row['paymentAmount'], 2); ?></td>

                            </tr>
                        <?php } ?>
                        </tbody>
                    </table>

                    
                </div>
            </div>
        </div>


    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>


</body>
</html>
