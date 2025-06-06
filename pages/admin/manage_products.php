<?php
session_start();
if (!isset($_SESSION['userID']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}
include '../db.php';

// Handle product status toggle
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['productID'])) {
    $productID = intval($_POST['productID']);
    $status = $_POST['status'];

    // Get current status
    $statusQ = $conn->prepare("SELECT status FROM products WHERE productID = ?");
    $statusQ->bind_param("i", $productID);
    $statusQ->execute();
    $result = $statusQ->get_result();
    $product = $result->fetch_assoc();

    if ($product) {
        $newStatus = ($product['status'] === 'active') ? 'inactive' : 'active';
        $updateQ = $conn->prepare("UPDATE products SET status = ? WHERE productID = ?");
        $updateQ->bind_param("si", $newStatus, $productID);
        $updateQ->execute();
    }

    header("Location: manage_products.php?status=" . urlencode($_POST['status']));
    exit;
}


function getTotalProductCount($conn, $status = null, $category = null, $search = '')
{
    $whereClauses = [];
    $params = [];
    $types = '';

    if (!empty($status) && $status !== 'all') {
        $whereClauses[] = "status = ?";
        $params[] = $status;
        $types .= 's';
    }

    if (!empty($category)) {
        $whereClauses[] = "pCategory = ?";
        $params[] = $category;
        $types .= 'i';
    }

    if (!empty($search)) {
        $whereClauses[] = "(pName LIKE ? OR pDescription LIKE ? OR seasonalTag LIKE ? OR CAST(productID AS CHAR) LIKE ? OR CAST(sellerID AS CHAR) LIKE ?)";
        $searchParam = '%' . $search . '%';
        array_push($params, $searchParam, $searchParam, $searchParam, $searchParam, $searchParam);
        $types .= 'sssss';
    }

    $whereSQL = $whereClauses ? 'WHERE ' . implode(' AND ', $whereClauses) : '';
    $sql = "SELECT COUNT(*) as total FROM products $whereSQL";

    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row['total'];
}

//---------------------------------------------------------------------------------------------------------------------------------------------->
// Function to render the reviews table based on status
// Capture filters
    $status = $_GET['status']??'all';
    $category = $_GET['category'] ?? '';
    $search = $_GET['search'] ?? '';

    
    $perPage = 10;
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;

    $totalRecords = getTotalProductCount($conn, $status, $category, $search);
    $totalPages = ceil($totalRecords / $perPage);

function renderProductsTable($conn, $status = null, $productID = null, $search = '', $category = null, $page = 1, $perPage = 10)
{
    // Build dynamic SQL
    $whereClauses = [];
    $params = [];
    $types = '';

    // Filters (same as before)
    if (!empty($status) && $status !== 'all') {
        $whereClauses[] = "status = ?";
        $params[] = $status;
        $types .= 's';
    }

    if (!empty($category)) {
        $whereClauses[] = "pCategory = ?";
        $params[] = $category;
        $types .= 'i';
    }

    if (!empty($search)) {
        $whereClauses[] = "(pName LIKE ? OR pDescription LIKE ? OR seasonalTag LIKE ? OR CAST(productID AS CHAR) LIKE ? OR CAST(sellerID AS CHAR) LIKE ?)";
        $searchParam = '%' . $search . '%';
        array_push($params, $searchParam, $searchParam, $searchParam, $searchParam, $searchParam);
        $types .= 'sssss';
    }

    $whereSQL = $whereClauses ? 'WHERE ' . implode(' AND ', $whereClauses) : '';

    // Pagination calculations
    $offset = ($page - 1) * $perPage;
    $sql = "SELECT * FROM products $whereSQL ORDER BY createdAt DESC LIMIT ? OFFSET ?";
    
    // Add LIMIT/OFFSET params
    $params[] = $perPage;
    $params[] = $offset;
    $types .= 'ii';

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();

    $html = '';

    while ($row = $result->fetch_assoc()) {
    $descModalID = 'descModal' . $row['productID'];

    // Fetch size and stock from product_variants
    $variantStmt = $conn->prepare("SELECT size, stockQuantity FROM product_variants WHERE productID = ?");
    $variantStmt->bind_param("i", $row['productID']);
    $variantStmt->execute();
    $variantResult = $variantStmt->get_result();

    $variantInfo = '';
    while ($variantRow = $variantResult->fetch_assoc()) {
        $variantInfo .= htmlspecialchars($variantRow['size']) . ' (' . intval($variantRow['stockQuantity']) . '), ';
    }
    $variantInfo = rtrim($variantInfo, ', ');

    $html .= '<tr>
        <td>
            <a href="#" class="product-details-link" data-product-id="'. $row['productID'] . '" 
            data-bs-toggle="modal" data-bs-target="#productDetailsModal">'. $row['productID'] . '</a>
        </td>
        <td>
            <a href="manage_sellers.php?sellerID=' . $row['sellerID'] . '">' . $row['sellerID'] . '</a>
        </td>
       <td>
             ' .htmlspecialchars($row['pName']) . '
        </td>
        <td>R' . number_format($row['pPrice'], 2) . '</td>
        <td>' . $variantInfo . '</td> 
        <td>' . date('Y-m-d', strtotime($row['createdAt'])) . '</td>
        <td>' . htmlspecialchars($row['seasonalTag']) . '</td>
        
        <td>
            <form method="POST" action="manage_products.php" style="display:inline;">
                <input type="hidden" name="productID" value="' . $row['productID'] . '">
                <input type="hidden" name="status" value="' . $row['status'] . '">';

    if ($row['status'] === 'active') {
        $html .= '<button class="btn btn-warning btn-sm"
                    onclick="return confirm(\'Are you sure you want to deactivate this product?\');">
                    Deactivate
                </button>';
    } else {
        $html .= '<button class="btn btn-success btn-sm"
                    onclick="return confirm(\'Reactivate this product?\');">
                    Activate
                </button>';
    }

    $html .= '</form>
        </td>
    </tr>';

    $variantStmt->close(); // Close inside the loop since we reuse prepare each time
}

    return $html;
}
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
                    <h3 class="mb-4">Manage Products</h3>
                    <hr>

                    <?php if (isset($_SESSION['success'])): ?>
                        <div class="alert alert-success"><?= $_SESSION['success'] ?></div>
                        <?php unset($_SESSION['success']); ?>
                    <?php endif; ?>

                    <!-- Filter and Search Controls -->
                    <div class="card p-3 mb-4 shadow-sm rounded-3">
                        <form method="GET" class="row g-3 align-items-center">
                            <div class="col-4">
                                <label for="status" class="col-form-label fw-semibold">Filter by Status:</label>
                                                  
                            <?php
                            $productID = isset($_GET['productID']) ? intval($_GET['productID']) : null;
                            ?>
                            <select id="status" name="status" class="form-select w-auto d-inline-block"
                                onchange="this.form.submit()">
                                <option value="all" <?= $status === 'all' ? 'selected' : '' ?>>All</option>
                                <option value="active" <?= $status === 'active' ? 'selected' : '' ?>>Active</option>
                                <option value="inactive" <?= $status === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                                <option value="pending" <?= $status === 'pending' ? 'selected' : '' ?>>Pending</option>
                            </select>
                            </div>
                            <div class="col-6">
                                <label for="status" class="col-form-label fw-semibold">Filter by Category:</label>
                                               
                            <select id="category" name="category" class="form-select w-auto d-inline-block"
                                onchange="this.form.submit()">
                                <option value="" <?= empty($category) ? 'selected' : '' ?>>None</option>
                                    <?php
                                    $result = $conn->query("SELECT * FROM categories WHERE parentID IS NOT NULL ORDER BY name");
                                    while ($row = $result->fetch_assoc()):
                                        $selected = (isset($edit['parentID']) && $edit['parentID'] == $row['categoryID']) ? 'selected' : '';
                                        ?>
                                        <option value="<?= $row['categoryID'] ?>" <?= $category == $row['categoryID'] ? 'selected' : '' ?>> <?= htmlspecialchars($row['name']) ?></option>
                                    <?php endwhile; ?>
                            </select>
                            </div>

                            <div class="d-flex">
                                    <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" class="form-control me-2" placeholder="Search by name, ID, Tag...">
                                    <button type="submit" class="btn btn-outline-secondary">Search</button>
                            </div>

                        </form>
                    </div>

                    <table class="table table-bordered table-hover mt-3">
                        <thead class="table-light">
                            <tr>
                                <th>Product ID</th>
                                <th>Seller ID</th>
                                <th>Name</th>
                                <th>Price</th>
                                <th>Variants</th>
                                <th>Date Added</th>
                                <th>Seasonal Tag</th>
                                <th>Status</th>
                            </tr>

                        </thead>
                        <tbody>
                            <?= renderProductsTable($conn, $status, $productID, $search, $category, $page, $perPage); ?>

                        </tbody>

                    </table>

                    <nav aria-label="Page navigation">
                    <ul class="pagination justify-content-center mt-4">
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                            <a class="page-link" href="?status=<?= urlencode($status) ?>&category=<?= urlencode($category) ?>&search=<?= urlencode($search) ?>&page=<?= $i ?>">
                            <?= $i ?>
                            </a>
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