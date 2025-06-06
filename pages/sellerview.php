<?php
$pageName = 'buy';
require_once "db.php";
session_start();

// Check if user is logged in
if (!isset($_SESSION['userID'])) {
    header("Location: login.php");
    exit;
}

// Validate seller ID
$sellerID = filter_input(INPUT_GET, 'sellerID', FILTER_VALIDATE_INT);
if (!$sellerID) {
    die("Invalid seller ID.");
}

// Get seller info
$stmt = $conn->prepare("
    SELECT s.sellerID, s.businessName, s.businessDescript, s.imagePath, u.uFirst, u.uLast
    FROM sellers s
    JOIN users u ON u.userID = s.userID
    WHERE s.sellerID = ?
");
$stmt->bind_param("i", $sellerID);
$stmt->execute();
$sellerResult = $stmt->get_result();

if ($sellerResult->num_rows === 0) {
    die("Seller not found.");
}
$seller = $sellerResult->fetch_assoc();

// Get seller's products
$stmt = $conn->prepare("
    SELECT productID, pName, pPrice, imagePath
    FROM products
    WHERE sellerID = ?
");
$stmt->bind_param("i", $sellerID);
$stmt->execute();
$search_result = $stmt->get_result();

// Get seller's reviews
$stmt = $conn->prepare("
    SELECT r.rating, r.rComment, r.reviewDate, u.uFirst, u.uLast, p.pName
    FROM reviews r
    JOIN buyers b ON r.buyerID = b.buyerID
    JOIN users u ON b.userID = u.userID
    JOIN products p ON r.productID = p.productID
    WHERE p.sellerID = ? AND r.rStatus = 'accepted'
    ORDER BY r.reviewDate DESC
");
$stmt->bind_param("i", $sellerID);
$stmt->execute();
$reviewResult = $stmt->get_result();
?>


<!------------------------------------------------------------------------------------------------------------------------>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($seller['businessName']) ?> - Seller Page</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" href="assets/css/theme.css">

    <style>
        .bLogo {
            transition: opacity 0.3s ease; /* Transition for image opacity */
            width: 160px !important;
            height: 160px !important;
            border-radius: 50% !important;
            object-fit: cover;
            object-position: center;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            display: block;
            margin-top: 20px !important;
        }
    </style>

</head>

<body class="hasCatMenu">
    <!--Header-->
    <?php include 'nav.php'; ?>

    <!--Hover menu-->
    <?php include 'hovermenu.php'; ?>
    <?php include 'rotate_warning.php'; ?>

    <!--Seller info-->
    <div class="container my-5 ">

        <div class="text-center mb-4 mt-5">
            <img class="bLogo img-thumbnail mx-auto d-block" src="uploads/<?= htmlspecialchars($seller['imagePath']) ?>" alt="Seller Image"
                style="width: 200px; height: 200px">
            <h2 class="mt-3"><?= htmlspecialchars($seller['businessName']) ?></h2>
            <p>by <?= htmlspecialchars($seller['uFirst']) ?> <?= htmlspecialchars($seller['uLast']) ?></p>
            <p><?= htmlspecialchars($seller['businessDescript']) ?></p>
        </div>


        <hr style="color:rgb(193, 197, 200)">


        <!-- Tabs navigation -->
        <ul class="nav nav-tabs" id="sellerTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <a class="nav-link active" id="items-tab" data-bs-toggle="tab" href="#items" role="tab">Items</a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link" id="reviews-tab" data-bs-toggle="tab" href="#reviews" role="tab">Reviews</a>
            </li>
        </ul>

        <!-- Tabs content -->
        <div class="tab-content mt-3" id="sellerTabsContent">
            <!-- Items Tab -->
            <div class="tab-pane fade show active" id="items" role="tabpanel">
                <?php include 'product_grid.php'; ?>
                
            </div>

            <!-- Reviews Tab -->
            <div class="tab-pane fade" id="reviews" role="tabpanel">
                <h4 class="mb-4">Customer Reviews</h4>
                <div class="row">
                    <?php if ($reviewResult->num_rows > 0): ?>
                        <?php while ($review = $reviewResult->fetch_assoc()): ?>
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card shadow-sm h-100">
                                    <div class="card-body">
                                        <!-- Reviewer's Username and Product Name -->
                                        <h5 class="card-title"><?= htmlspecialchars($review['uFirst']) ?> <?= htmlspecialchars($review['uLast']) ?></h5>
                                        <h6 class="card-subtitle text-muted"><?= htmlspecialchars($review['pName']) ?></h6>
                                        
                                        <!-- Star Rating -->
                                        <div class="mb-2">
                                            <?php
                                            for ($i = 0; $i < 5; $i++) {
                                                if ($i < $review['rating']) {
                                                    echo '<span class="text-warning">&#9733;</span>'; // Filled star
                                                } else {
                                                    echo '<span class="text-muted">&#9733;</span>'; // Empty star
                                                }
                                            }
                                            ?>
                                        </div>

                                        <!-- Review Comment -->
                                        <p class="card-text"><?= htmlspecialchars($review['rComment']) ?></p>
                                    </div>
                                    <div class="card-footer text-muted small">
                                        <span><?= date("F j, Y", strtotime($review['reviewDate'])) ?></span> <!-- Formatted date -->
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="col-12">
                            <p class="text-muted">No customer reviews yet.</p>
                        </div>
                    <?php endif; ?>
                </div>

            </div>
        </div>

    </div>

    <!--footer-->
    <?php include 'footer.php'; ?>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>


</body>

</html>