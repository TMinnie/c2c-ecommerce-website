<?php
session_start();
if (!isset($_SESSION['userID']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}
include '../db.php';

// Handle approve or reject actions (unchanged)...
// [Your original POST action handling code stays unchanged here]

//---------------------------------------------------------------------------------------------------------------------------------------------->
// Capture filters
$searchValue = $_GET['searchValue'] ?? '';
$searchType = $_GET['searchType'] ?? '';
$rating = $_GET['rating'] ?? '';
$rStatus = $_GET['rStatus'] ?? '';

// Pagination setup
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Build dynamic SQL
$whereClauses = [];
$params = [];
$types = '';

if (!empty($rating)) {
    $whereClauses[] = "rating = ?";
    $params[] = $rating;
    $types .= 'i';
}

if (!empty($rStatus)) {
    $whereClauses[] = "rStatus = ?";
    $params[] = $rStatus;
    $types .= 's';
}

if (!empty($searchValue) && !empty($searchType)) {
    $whereClauses[] = "$searchType = ?";
    $params[] = $searchValue;
    $types .= 'i';
}

$whereSQL = $whereClauses ? 'WHERE ' . implode(' AND ', $whereClauses) : '';

// Query for total count
$countSQL = "SELECT COUNT(*) as total FROM reviews $whereSQL";
$countStmt = $conn->prepare($countSQL);
if (!empty($params)) {
    $countStmt->bind_param($types, ...$params);
}
$countStmt->execute();
$countResult = $countStmt->get_result();
$totalRows = $countResult->fetch_assoc()['total'];
$totalPages = ceil($totalRows / $limit);

// Final paginated query
$sql = "SELECT * FROM reviews $whereSQL ORDER BY reviewDate DESC LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);

// Bind dynamic + pagination params
$fullTypes = $types . 'ii';
$fullParams = [...$params, $limit, $offset];
$stmt->bind_param($fullTypes, ...$fullParams);
$stmt->execute();
$result = $stmt->get_result();
?>


<!---------------------------------------------------------------------------------------------------------------------------------------------->
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>

    <!--Bootstrap-->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css">

    <!--Stylesheets-->
    <link rel="stylesheet" href="../assets/css/theme.css">
    <link rel="stylesheet" href="../assets/css/styleaccount.css">
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
                <div class="container mt-4">
                    <h3 class="mb-4">Manage Reviews</h3>
                    <hr>

                    <?php if (isset($_SESSION['success'])): ?>
                        <div class="alert alert-success"><?= $_SESSION['success'] ?></div>
                        <?php unset($_SESSION['success']); ?>
                    <?php endif; ?>

                    <div class="card p-3 mb-4 shadow-sm rounded-3">
                        <form method="GET" class="">
                            <div class="row g-3 align-items-end">
                                <!-- Status Filter -->
                                <div class="col-md-2">
                                    <?php $currentStatus = $_GET['rStatus'] ?? 'pending'; ?>
                                    <select id="rStatus" name="rStatus" class="form-select"
                                        onchange="this.form.submit()">
                                        <option value="" <?= ($currentStatus === '') ? 'selected' : '' ?>>All</option>
                                        <option value="pending" <?= ($currentStatus === 'pending') ? 'selected' : '' ?>>
                                            Pending</option>
                                        <option value="accepted" <?= ($currentStatus === 'accepted') ? 'selected' : '' ?>>
                                            Approved</option>
                                        <option value="rejected" <?= ($currentStatus === 'rejected') ? 'selected' : '' ?>>
                                            Rejected</option>
                                    </select>
                                </div>

                                <!-- ID Search -->
                                <div class="col-md-4">
                                    <div class="input-group">
                                        <select name="searchType" id="searchType" class="form-select">
                                            <option value="sellerID">Seller ID</option>
                                            <option value="buyerID">Buyer ID</option>
                                            <option value="productID">Product ID</option>
                                            <option value="orderID">Order ID</option>
                                            <option value="reviewID">Review ID</option>
                                        </select>
                                        <input type="text" name="searchValue" id="searchValue" class="form-control"
                                            placeholder="Enter ID...">
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <select name="rating" class="form-select">
                                        <option value="">Rating</option>
                                        <option value="1">1 Star</option>
                                        <option value="2">2 Star</option>
                                        <option value="3">3 Star</option>
                                        <option value="4">4 Star</option>
                                        <option value="5">5 Star</option>
                                    </select>
                                </div>

                                <div class="col-md-3 d-flex align-items-end">
                                    <button type="submit" class="btn btn-outline-secondary w-100">Apply Filters</button>
                                </div>
                            </div>
                        </form>
                    </div>


                    <table class="table table-bordered table-hover mt-3">
                        <thead class="table-light">
                            <tr>
                                <th>Review ID</th>
                                <th>Buyer ID</th>
                                <th>Product ID</th>
                                <th>Order ID</th>
                                <th>Rating</th>
                                <th>Comment</th>
                                <th>Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $result->fetch_assoc()) { ?>
                                <tr>
                                    <td><?= $row['reviewID'] ?></td>
                                    <td>
                                        <a href="manage_buyers.php?buyerID=<?= $row['buyerID'] ?>">
                                            <?= $row['buyerID'] ?>
                                        </a>
                                    </td>
                                    <td>
                                        <a href="manage_products.php?productID=<?= $row['productID'] ?>">
                                            <?= $row['productID'] ?>
                                        </a>
                                    </td>
                                    <td>
                                        <a href="manage_orders.php?orderID=<?= $row['orderID'] ?>">
                                            <?= $row['orderID'] ?>
                                        </a>
                                    </td>
                                    <td><?= $row['rating'] ?> / 5</td>
                                    <td><?= htmlspecialchars($row['rComment']) ?></td>
                                    <td><?= $row['reviewDate'] ?></td>
                                    <td>
                                        <?php if ($row['rStatus'] === 'pending') { ?>
                                            <form method="POST" action="manage_reviews.php" style="display:inline;">
                                                <input type="hidden" name="reviewID" value="<?= $row['reviewID'] ?>">
                                                <button class="btn btn-success btn-sm" name="action" value="accepted"
                                                    onclick="return confirm('Are you sure you want to accept this review?');">Accept</button>
                                            </form>
                                            <form method="POST" action="manage_reviews.php" style="display:inline;">
                                                <input type="hidden" name="reviewID" value="<?= $row['reviewID'] ?>">
                                                <button class="btn btn-danger btn-sm" name="action" value="rejected">Reject</button>
                                            </form>
                                        <?php } elseif ($row['rStatus'] === 'accepted') { ?>
                                            <form method="POST" action="manage_reviews.php" style="display:inline;">
                                                <input type="hidden" name="reviewID" value="<?= $row['reviewID'] ?>">
                                                <button class="btn btn-danger btn-sm" name="action" value="rejected">Reject</button>
                                            </form>
                                        <?php } elseif ($row['rStatus'] === 'rejected') { ?>
                                            <form method="POST" action="manage_reviews.php" style="display:inline;">
                                                <input type="hidden" name="reviewID" value="<?= $row['reviewID'] ?>">
                                                <button class="btn btn-success btn-sm" name="action" value="accepted"
                                                    onclick="return confirm('Are you sure you want to accept this review?');">Accept</button>
                                            </form>
                                        <?php } ?>
                                    </td>
                                </tr>
                            <?php } ?>


                        </tbody>
                    </table>
<!-- Pagination -->
<nav>
    <ul class="pagination justify-content-center">
        <?php
        $queryParams = $_GET;
        for ($i = 1; $i <= $totalPages; $i++):
            $queryParams['page'] = $i;
            $link = '?' . http_build_query($queryParams);
        ?>
            <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                <a class="page-link" href="<?= $link ?>"><?= $i ?></a>
            </li>
        <?php endfor; ?>
    </ul>
</nav>



                </div>

            </div>
        </div>
    </div>


    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>


</body>

</html>