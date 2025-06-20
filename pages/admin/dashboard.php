<?php
include '../db.php';
session_start();
if (!isset($_SESSION['userID']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$page = isset($_GET['page']) ? $_GET['page'] : 'profile';

// USER STATS
$totalUsers = $conn->query("SELECT COUNT(*) AS total FROM users")->fetch_assoc()['total'];
$userRoles = $conn->query("SELECT role, COUNT(*) AS count FROM users GROUP BY role");
$userStatus = $conn->query("SELECT status, COUNT(*) AS count FROM users GROUP BY status");

// PRODUCT & REVIEW STATS
$activeProducts = $conn->query("SELECT COUNT(*) AS count FROM products WHERE status = 'active'")->fetch_assoc()['count'];
$totalReviews = $conn->query("SELECT COUNT(*) AS count FROM reviews")->fetch_assoc()['count'];

// ORDER & SALES STATS
$totalOrders = $conn->query("SELECT COUNT(*) AS count FROM orders")->fetch_assoc()['count'];
$totalSales = $conn->query("SELECT SUM(totalAmount) AS total FROM orders WHERE orderStatus = 'Completed'OR orderStatus = 'Dispatched'")->fetch_assoc()['total'];
$orderStatus = $conn->query("SELECT orderStatus, COUNT(*) AS count FROM orders GROUP BY orderStatus");

// PENDING SECTION
$pendingSellers = $conn->query("SELECT COUNT(*) AS count FROM sellers WHERE status = 'pending'")->fetch_assoc()['count'];
$pendingReviews = $conn->query("SELECT COUNT(*) AS count FROM reviews WHERE rStatus = 'pending'")->fetch_assoc()['count'];
$pendingFeedback = $conn->query("SELECT COUNT(*) AS count FROM feedback WHERE status = 'pending'")->fetch_assoc()['count'];

// TOP LISTS
$topProducts = $conn->query("SELECT p.pName, AVG(r.rating) AS avgRating FROM products p JOIN reviews r ON p.productID = r.productID GROUP BY p.productID ORDER BY avgRating DESC LIMIT 5");
$topSellers = $conn->query("SELECT businessName, totalSales as totalRevenue FROM sellers  GROUP BY sellerID ORDER BY totalRevenue DESC LIMIT 5");
$topCategories = $conn->query("SELECT c.name, COUNT(p.productID) AS productCount FROM categories c JOIN products p ON c.categoryID = p.pCategory GROUP BY c.categoryID ORDER BY productCount DESC LIMIT 3");

?>
<!------------------------------------------------------------------------------------------------------------------------------>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>

    <!--Bootstrap-->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!--Stylesheets-->
    <link rel="stylesheet" href="../assets/css/theme.css">
    <link rel="stylesheet" href="../assets/css/styleaccount.css">

    <style>
        .card {
            border-radius: 15px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            background-color: #fff;
            transition: transform 0.2s ease-in-out;
        }

        .section {
            margin-bottom: 15px;
            font-size: 1.4rem;
            font-weight: bold;
        }

        .section-title {
            margin-bottom: 15px;
            font-size: 1.4rem;
            font-weight: bold;
            transition: transform 0.3s ease;
        }

        .section-title:hover {
            transform: translateX(8px); /* Moves it 8px to the right */
        }

        .stats-value {
            font-size: 1.5rem;
            font-weight: bold;
        }

        .badge {
            font-size: 0.8rem;
        }

        .top-list li {
            padding: 4px 0;
            border-bottom: 1px solid #eee;
        }

        .top-list li:last-child {
            border-bottom: none;
        }

    </style>
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
                <div class="row g-4">

                    <div class="col-md-8 ">

                        <!-- Section 1: Pending Items -->
                        <div class="section">Pending Approvals</div> 
                        <div class="card p-3 mb-4">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <div class="card p-3 text-center">
                                        <div>Sellers</div>
                                        <div class="stats-value text-warning" id="pendingSellers">0</div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card p-3 text-center">
                                        <div>Reviews</div>
                                        <div class="stats-value text-warning" id="pendingReviews">0</div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card p-3 text-center">
                                        <div>Feedback</div>
                                        <div class="stats-value text-warning" id="pendingFeedback">0</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Section 2: User Stats -->
                        <a href="stat_users.php" class="title-link"><div class="section-title">User Statistics <i class="fa-solid fa-caret-right text-muted"></i></div> </a>
                        <div class="card p-3 mb-4">
                            <div class="row g-3">
                                <div class="col-md-6 mb-3">
                                    <div class="card p-3 text-center">
                                        <div>Total Users</div>
                                        <div class="stats-value" id="totalUsers"><?= $totalUsers ?></div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="card p-3 text-center">
                                        <div>Sellers</div>
                                        <div class="stats-value" id="totalSellers"> </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="card p-3 text-center">
                                        <div>Active Users</div>
                                        <div class="stats-value text-success" id="activeUsers">0</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card p-3 text-center">
                                        <div>Inactive Users</div>
                                        <div class="stats-value text-danger" id="inactiveUsers">0</div>
                                    </div>
                                </div>
                            </div>
                        </div>


                        <!-- Section 3: Product Stats -->
                        <div class="section">Product & Review Statistics</div> 
                        <div class="card p-3 mb-4">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="card p-3 text-center">
                                        <div>Active Products</div>
                                        <div class="stats-value" id="activeProducts">0</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card p-3 text-center">
                                        <div>Total Reviews</div>
                                        <div class="stats-value" id="totalReviews">0</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Section 4: Order and Sales Stats -->
                        <div class="section">Orders & Sales</div>
                        <div class="row mb-4">
                            <div class="col-md-5">
                                <div class="card p-3 text-center">
                                    <div>Total Sales Value</div>
                                    <div class="stats-value text-success" id="totalSales">R0.00</div>
                                </div>
                            </div>
                        
                            <div class="col-md-7">
                                <div class="card p-4">
                                    <table class="table table-bordered text-center mb-0">
                                        <thead class="table-light">
                                            <tr><th colspan="2">📦 Order Breakdown</th></tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td scope="row">Total Orders</td>
                                                <td class="stats-value" id="totalOrders">0</td>
                                            </tr>
                                            <tr>
                                                <td scope="row">Pending Orders</td>
                                                <td class="stats-value text-warning" id="pendingOrders">0</td>
                                            </tr>
                                            <tr>
                                                <td scope="row">Paid Orders</td>
                                                <td class="stats-value text-warning" id="paidOrders">0</td>
                                            </tr>
                                            <tr>
                                                <td scope="row">Dispatched Orders</td>
                                                <td class="stats-value text-success" id="dispatchedOrders">0</td>
                                            </tr>
                                            <tr>
                                                <td scope="row">Completed Orders</td>
                                                <td class="stats-value text-success" id="completedOrders">0</td>
                                            </tr>
                                            <tr>
                                                <td scope="row">Cancelled Orders</td>
                                                <td class="stats-value text-danger" id="cancelledOrders">0</td>
                                            </tr>
                                            <tr>
                                                <td scope="row">Refunded Orders</td>
                                                <td class="stats-value text-danger" id="refundedOrders">0</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        </div>

                        
                    

                    <div class="col-md-4">
                        <!-- Section 5: Top Lists -->
                        <div class="section">Top Lists</div>

                        <div class="card p-3 mb-4">
                            <h6 class="mb-4 fw-bold">Top Products (by Rating)</h6>
                            <ol class="list-unstyled top-list" id="topProducts">
                                <li>Loading...</li>
                            </ol>
                        </div>

                        <div class="card p-3 mb-4">
                            <h6 class="mb-4 fw-bold">Top Sellers (by Sales)</h6>
                            <ul class="list-unstyled top-list" id="topSellers">
                                <li>Loading...</li>
                            </ul>
                        </div>


                        <div class="card p-3 mb-4">
                            <h6 class="mb-4 fw-bold">Top Categories (by Popularity)</h6>
                            <ul class="list-unstyled top-list" id="topCategories">
                                <li>Loading...</li>
                            </ul>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <div id="auth-button"></div>
<div id="view-selector"></div>
<div id="chart-container"></div>


    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>


    <script>
        // === PHP-to-JS Injection ===
        const userRoles = <?= json_encode(mysqli_fetch_all($userRoles, MYSQLI_ASSOC)) ?>;
        const userStatus = <?= json_encode(mysqli_fetch_all($userStatus, MYSQLI_ASSOC)) ?>;
        const orderStatus = <?= json_encode(mysqli_fetch_all($orderStatus, MYSQLI_ASSOC)) ?>;
        const topProducts = <?= json_encode(mysqli_fetch_all($topProducts, MYSQLI_ASSOC)) ?>;
        const topSellers = <?= json_encode(mysqli_fetch_all($topSellers, MYSQLI_ASSOC)) ?>;
        const topCategories = <?= json_encode(mysqli_fetch_all($topCategories, MYSQLI_ASSOC)) ?>;

        // === User Role Counts ===
        let sellers = 0;
        userRoles.forEach(role => {
            if (role.role === "seller") sellers = role.count;
        });
        document.getElementById("totalSellers").innerText = sellers;

        // === User Status Counts ===
        let active = 0, inactive = 0;
        userStatus.forEach(stat => {
            if (stat.status === "active") active = stat.count;
            if (stat.status === "inactive") inactive = stat.count;
        });
        document.getElementById("activeUsers").innerText = active;
        document.getElementById("inactiveUsers").innerText = inactive;

        // === Product & Review Counts ===
        document.getElementById("activeProducts").innerText = "<?= $activeProducts ?>";
        document.getElementById("totalReviews").innerText = "<?= $totalReviews ?>";

        // === Order & Sales Stats ===
        let pending = 0, paid= 0, dispatched = 0, completed = 0, cancelled = 0, refunded = 0;
        orderStatus.forEach(stat => {
            if (stat.orderStatus === "Pending") pending = stat.count;
            if (stat.orderStatus === "Paid") paid = stat.count;
            if (stat.orderStatus === "Dispatched") dispatched = stat.count;
            if (stat.orderStatus === "Completed") completed = stat.count;
            if (stat.orderStatus === "Cancelled") cancelled = stat.count;
            if (stat.orderStatus === "Refunded") refunded = stat.count;
        });
        document.getElementById("pendingOrders").innerText = pending;
        document.getElementById("paidOrders").innerText = paid;
        document.getElementById("dispatchedOrders").innerText = dispatched;
        document.getElementById("completedOrders").innerText = completed;
        document.getElementById("cancelledOrders").innerText = cancelled;
        document.getElementById("refundedOrders").innerText = refunded;
        document.getElementById("totalOrders").innerText = "<?= $totalOrders ?>";
        document.getElementById("totalSales").innerText = "R<?= number_format((float) $totalSales, 2, '.', ',') ?>";

        // === Pending Items ===
        document.getElementById("pendingSellers").innerText = "<?= $pendingSellers ?>";
        document.getElementById("pendingReviews").innerText = "<?= $pendingReviews ?>";
        document.getElementById("pendingFeedback").innerText = "<?= $pendingFeedback ?>";

        // === Top Products ===
        let tpHTML = '';
        topProducts.forEach(p => {
            tpHTML += `<li>${p.pName} - <span class="text-warning">&#9733;</span> ${parseFloat(p.avgRating).toFixed(1)}</li>`;
        });
        document.getElementById("topProducts").innerHTML = tpHTML;

        // === Top Sellers ===
        let tsHTML = '';
        topSellers.forEach(s => {
            tsHTML += `<li><strong>${s.businessName}</strong> - R${parseFloat(s.totalRevenue).toFixed(2)}</li>`;
        });
        document.getElementById("topSellers").innerHTML = tsHTML;

        // === Top Categories ===
        let tcHTML = '';
        topCategories.forEach(c => {
            tcHTML += `<li><strong>${c.name}</strong> - ${c.productCount} products</li>`;
        });
        document.getElementById("topCategories").innerHTML = tcHTML;
    </script>

</body>

</html>