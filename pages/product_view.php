<?php $pageName = 'buy'; ?>

<?php
session_start();
require_once "db.php";

if (!isset($_SESSION['userID'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['productID']) || !is_numeric($_GET['productID'])) {
    die("Invalid product id");
}
$productID = intval($_GET['productID']);

$sql = "
    SELECT 
        p.productID, p.pName, p.pDescription, p.pPrice, p.imagePath,  p.pCategory, 
        p.createdAt, p.seasonalTag, p.sellerID, s.businessName
    FROM products p
    JOIN sellers s ON p.sellerID = s.sellerID
    WHERE p.productID = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $productID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Product not found.");
}

$product = $result->fetch_assoc();

// Get average rating for the product
$avgRatingQuery = "
    SELECT AVG(rating) AS avgRating
    FROM reviews
    WHERE productID = ? AND rStatus = 'accepted'
";

$avgStmt = $conn->prepare($avgRatingQuery);
$avgStmt->bind_param("i", $productID);
$avgStmt->execute();
$avgResult = $avgStmt->get_result();
$avgRow = $avgResult->fetch_assoc();

$averageRating = $avgRow['avgRating'] ? round($avgRow['avgRating'], 1) : null;


// Fetch available sizes and their stock quantities
$sizeQuery = "SELECT size, stockQuantity FROM product_variants WHERE productID = ?";
$sizeStmt = $conn->prepare($sizeQuery);
$sizeStmt->bind_param("i", $productID);
$sizeStmt->execute();
$sizeResult = $sizeStmt->get_result();

$sizes = [];
while ($row = $sizeResult->fetch_assoc()) {
    $sizes[] = $row;
}

// Count total reviews
$countQuery = "SELECT COUNT(*) AS total FROM reviews WHERE productID = ? AND rStatus = 'accepted'";
$stmt = $conn->prepare($countQuery);
$stmt->bind_param("i", $productID);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$totalReviews = $row['total'];

// Count reviews by star rating
$reviewCounts = array_fill(1, 5, 0); // Initialize 1-5 stars to 0

$countByStarsQuery = "SELECT rating, COUNT(*) AS count FROM reviews WHERE productID = ? AND rStatus = 'accepted' GROUP BY rating";
$stmt = $conn->prepare($countByStarsQuery);
$stmt->bind_param("i", $productID);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $reviewCounts[$row['rating']] = $row['count'];
}


//check if user has buyer info
$showBuyerSetupModal = false;
$buyerID = null;

if (isset($_SESSION['userID'])) {
    $userID = $_SESSION['userID'];

    $buyerQuery = "SELECT buyerID FROM buyers WHERE userID = ?";
    $buyerStmt = $conn->prepare($buyerQuery);
    $buyerStmt->bind_param("i", $userID);
    $buyerStmt->execute();
    $buyerResult = $buyerStmt->get_result();

    if ($buyerResult->num_rows === 0) {
        $showBuyerSetupModal = true;
    } else {
        $buyerData = $buyerResult->fetch_assoc();
        $buyerID = $buyerData['buyerID'];
    }
}

$filters = [];
$filterSQL = '';
$bindTypes = '';
$bindValues = [];

if (isset($_GET['filter']) && is_array($_GET['filter'])) {
    $filters = array_filter($_GET['filter'], function ($v) {
        return in_array($v, ['1', '2', '3', '4', '5']);
    });

    if (!empty($filters)) {
        $placeholders = implode(',', array_fill(0, count($filters), '?'));
        $filterSQL = " AND r.rating IN ($placeholders)";
        $bindTypes = str_repeat('i', count($filters));
        $bindValues = array_map('intval', $filters);
    }
}

$reviewQuery = "
    SELECT r.rating, r.rComment, r.reviewDate, u.uFirst, u.uLast
    FROM reviews r
    JOIN buyers b ON r.buyerID = b.buyerID
    JOIN users u ON b.userID = u.userID
    WHERE r.productID = ? AND r.rStatus = 'accepted' $filterSQL
    ORDER BY r.reviewDate DESC
";

$reviewStmt = $conn->prepare($reviewQuery);
$bindTypes = 'i' . $bindTypes;
$bindParams = array_merge([$productID], $bindValues);
$reviewStmt->bind_param($bindTypes, ...$bindParams);
$reviewStmt->execute();
$reviewResult = $reviewStmt->get_result();
?>

<!----------------------------------------------------------------------------------------------------------------------->

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>
        <?php echo htmlspecialchars($product['pName']); ?>
    </title>

    <!--Bootstrap-->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css">

    <!--Stylesheets-->
    <link rel="stylesheet" href="assets/css/styleproduct.css">
    <link rel="stylesheet" href="assets/css/theme.css">

</head>

<body class="hasCatMenu">

    <!--Header-->
    <?php include 'nav.php'; ?>

    <!--Hover menu-->
    <?php include 'hovermenu.php'; ?>

    <div class="product-container container my-4">
<?php if (isset($_SESSION['flash_message'])): ?>
        <div class="alert alert-success"><?= $_SESSION['flash_message']; unset($_SESSION['flash_message']); ?></div>
    <?php endif; ?>

        <div class="row d-flex">
            <div class="col-md-8 d-flex">
                <div id="product-card" class="card p-3 shadow-sm flex-grow-1">
                    <div class="row mt-3">
                        <div class="col-md-5" >
                            <div class="square-image-wrapper">
                                <!-- Product Image -->
                                <img src="uploads/<?php echo htmlspecialchars($product['imagePath']); ?>"
                                alt="<?php echo htmlspecialchars($product['pName']); ?>"
                                class="img-fluid product-image">
                            </div>

                        </div>
                        <div class="col-md-7">
                            
                            <!-- Product Details -->
                            <div class="product-details">
                                <h4><?php echo htmlspecialchars($product['pName']); ?></h4>

                                <p class="text-muted d-flex align-items-center gap-2">
                                    <a href="sellerview.php?sellerID=<?php echo $product['sellerID']; ?>"
                                        class="text-secondary">
                                        <?php echo htmlspecialchars($product['businessName']); ?>
                                    </a>
                                <p>

                                    <?php if ($averageRating): ?>
                                        <span class="d-flex align-items-center text-warning">
                                            <?php
                                            for ($i = 1; $i <= 5; $i++) {
                                                echo $i <= round($averageRating) ? "&#9733;" : "<span class='text-muted'>&#9733;</span>";
                                            }
                                            ?>

                                        </span>
                                    <?php else: ?>
                                        <small class="text-muted">(No reviews yet)</small>
                                    <?php endif; ?>

                                <p class="mt-3"><?php echo nl2br(htmlspecialchars($product['pDescription'])); ?></p>

                            </div>
                        </div>
                    </div>
                </div>
            </div>


            <div class="col-md-4">
                <div class="card p-3 shadow-sm h-100">
                    <h4 class="product-price">R <?php echo number_format($product['pPrice'], 2); ?></h4>

                    <div class="d-flex justify-content-between">
                        <p class="text-muted">Estimated Delivery</p>
                        <p class="text-muted"><?php echo date('j F Y', strtotime('+2 days')); ?></p>
                    </div>
                    <hr>

                    <?php if (count($sizes) > 0): ?>
                        <form id="addToCartForm" action="add_to_cart.php" method="POST">
                            <input type="hidden" name="productID" value="<?php echo $product['productID']; ?>">

                            <!-- Size Selector -->
                            <div class="mb-3">
                                <label for="sizeSelect"><strong>Size:</strong></label>
                                <select class="form-select" name="size" id="sizeSelect" required>
                                    <option value="" disabled selected>Select size</option>
                                    <?php foreach ($sizes as $s): ?>
                                        <option value="<?php echo $s['size']; ?>"
                                            data-stock="<?php echo $s['stockQuantity']; ?>">
                                            <?php echo htmlspecialchars($s['size']); ?> (<?php echo $s['stockQuantity']; ?>
                                            available)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Quantity Input -->
                            <div class="mb-3">
                                <label for="quantityInput"><strong>Quantity:</strong></label>
                                <input type="number" name="quantity" id="quantityInput" min="1" class="form-control"
                                    style="width:100px;" disabled>
                            </div>

                            <!-- Add to Cart Button -->
                            <?php if ($showBuyerSetupModal): ?>
                                <!-- Trigger Modal Instead -->
                                <button type="button" class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#buyerSetupModal">
                                    <span><i class="fa-solid fa-cart-plus "> </i> Add to Cart</span>
                                </button>
                            <?php else: ?>
                                <!-- Normal Add to Cart Button -->
                                <button type="submit" class="btn btn-primary w-100" id="addToCartBtn" disabled>
                                    <span><i class="fa-solid fa-cart-plus"> </i> Add to Cart</span>
                                </button>
                            <?php endif; ?>

                        </form>
                    <?php else: ?>
                        <p class="text-danger">Out of stock for all sizes.</p>
                    <?php endif; ?>
                </div>

            </div>
        </div>


        <div class="reviews-section mt-5 mb-5">
            <h4 class="mb-4">Customer Reviews</h4>
            <div class="row">
                <!-- Left: Summary -->
                <div class="col-md-4 mb-4">
                    <div class="card p-3 shadow-sm">
                        <?php if ($averageRating !== null): ?>
                            <h2 class="mb-1"><?php echo number_format($averageRating, 1); ?> <span class="text-warning">&#9733;</span></h2>
                        <?php else: ?>
                            <h3 class="mb-3">No ratings yet</h3>
                        <?php endif; ?>
                       <!-- <h2 class="mb-1"><?php echo number_format($averageRating, 1); ?> <span
                                class="text-warning">&#9733;</span></h2>-->
                        <p class="text-muted"><?php echo $totalReviews; ?> Reviews</p>

                        <?php for ($i = 5; $i >= 1; $i--): ?>
                            <div class="d-flex align-items-center mb-1">
                                <div style="width: 30px;"><p><?php echo $i; ?> <span class="text-warning">&#9733;</span></p></div>
                                <div class="progress flex-grow-1 me-2" style="height: 8px;">
                                    <div class="progress-bar bg-<?php echo $i == 5 ? 'success' : ($i == 4 ? 'warning' : ($i == 1 ? 'danger' : 'secondary')); ?>"
                                        style="width: <?php echo ($reviewCounts[$i] / max($totalReviews, 1)) * 100; ?>%">
                                    </div>
                                </div>
                                <span><?php echo $reviewCounts[$i]; ?></span>
                            </div>
                        <?php endfor; ?>

                        <div class="mt-4 border p-2">
                            <strong>Filter by Ratings</strong>
                           <form method="GET" id="filterForm">
                            <input type="hidden" name="productID" value="<?php echo $productID; ?>">
                            <?php for ($i = 5; $i >= 1; $i--): ?>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="filter[]"
                                        value="<?php echo $i; ?>" id="star<?php echo $i; ?>"
                                        <?php echo in_array((string)$i, $filters) ? 'checked' : ''; ?>
                                        onchange="document.getElementById('filterForm').submit();">
                                    <label class="form-check-label" for="star<?php echo $i; ?>">
                                        <?php echo $i; ?> <span class="text-warning">&#9733;</span>
                                    </label>
                                </div>
                            <?php endfor; ?>
                        </form>

                        </div>
                    </div>
                </div>

                <!-- Right: Reviews -->
                <div class="col-md-8">
                    <?php if ($reviewResult->num_rows > 0): ?>
                        <?php while ($review = $reviewResult->fetch_assoc()): ?>
                            <div class="card mb-3 shadow-sm p-3">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <strong><?php echo htmlspecialchars($review['uFirst'] . ' ' . $review['uLast']); ?></strong>
                                    <small class="text-muted"><?php echo date('F j, Y', strtotime($review['reviewDate'])); ?></small>
                                </div>
                                <div class="text-warning mb-2">
                                    <?php
                                    for ($i = 1; $i <= 5; $i++) {
                                        echo $i <= $review['rating'] ? "&#9733;" : "<span class='text-muted'>&#9733;</span>";
                                    }
                                    ?>
                                </div>
                                <p><?php echo nl2br(htmlspecialchars($review['rComment'])); ?></p>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p class="text-muted">No reviews match the selected filters.</p>
                    <?php endif; ?>
                </div>

            </div>
        </div>

    </div>

    <?php include 'footer.php'; ?>

    <?php include 'buyer_setup_model.php'; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const sizeSelect = document.getElementById('sizeSelect');
            const quantityInput = document.getElementById('quantityInput');
            const addToCartBtn = document.getElementById('addToCartBtn');

            sizeSelect.addEventListener('change', () => {
                const selectedOption = sizeSelect.options[sizeSelect.selectedIndex];
                const stock = parseInt(selectedOption.getAttribute('data-stock'));

                if (!isNaN(stock) && stock > 0) {
                    quantityInput.disabled = false;
                    quantityInput.max = stock;
                    quantityInput.value = 1;
                    addToCartBtn.disabled = false;
                } else {
                    quantityInput.disabled = true;
                    quantityInput.value = '';
                    addToCartBtn.disabled = true;
                }
            });
        });
    </script>

</body>

</html>