<?php
session_start();
include '../db.php';

$userID = $_SESSION['userID'];
if (!$userID) {
    die("User not logged in.");
}

// Get buyerID
$buyerQuery = $conn->prepare("SELECT buyerID FROM buyers WHERE userID = ?");
$buyerQuery->bind_param("i", $userID);
$buyerQuery->execute();
$buyerResult = $buyerQuery->get_result();

if ($buyerResult->num_rows === 0) die("Buyer not found.");
$buyerID = $buyerResult->fetch_assoc()['buyerID'];


// Fetch products from completed orders not yet reviewed
$query = "
    SELECT o.orderID, o.orderDate, p.productID, p.pName, p.pDescription, p.imagePath
    FROM orders o
    JOIN orderitems oi ON o.orderID = oi.orderID
    JOIN products p ON oi.productID = p.productID
    LEFT JOIN reviews r 
        ON r.orderID = o.orderID 
        AND r.productID = p.productID 
        AND r.buyerID = ?
    WHERE o.buyerID = ? AND o.orderStatus = 'completed' AND r.reviewID IS NULL
    ORDER BY o.orderDate DESC
";

$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $buyerID, $buyerID);
$stmt->execute();
$result = $stmt->get_result();

// Fetch previous reviews by this buyer
$historyQuery = "
    SELECT r.orderID, r.productID, r.rating, r.rComment, r.reviewDate, r.rStatus, p.pName, p.imagePath
    FROM reviews r
    JOIN products p ON r.productID = p.productID
    WHERE r.buyerID = ?
    ORDER BY r.reviewDate DESC
";
$historyStmt = $conn->prepare($historyQuery);
$historyStmt->bind_param("i", $buyerID);
$historyStmt->execute();
$historyResult = $historyStmt->get_result();

// Handle delete request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_review'])) {
    $orderID = $_POST['orderID'];
    $productID = $_POST['productID'];

    $deleteStmt = $conn->prepare("DELETE FROM reviews WHERE buyerID = ? AND orderID = ? AND productID = ?");
    $deleteStmt->bind_param("iii", $buyerID, $orderID, $productID);
    $deleteStmt->execute();
    if ($deleteStmt->affected_rows > 0) {
        $message = "Review deleted successfully!";
    } else {
        $message = "No matching review found to delete.";
    }
    header("Location: ../account.php?page=reviews&status=success&message=". urlencode($message)); // Redirect to the profile page after update
    exit();

} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Handle review submission
    $orderID = $_POST['orderID'];
    $productID = $_POST['productID'];
    $rating = $_POST['rating'];
    $comment = $_POST['rComment'];

    // Insert or update review per product per order
    $stmt = $conn->prepare("
        INSERT INTO reviews (buyerID, orderID, productID, rating, rComment, reviewDate) 
        VALUES (?, ?, ?, ?, ?, NOW())
        ON DUPLICATE KEY UPDATE reviewDate = NOW(), rating = VALUES(rating), rComment = VALUES(rComment)
    ");
    $stmt->bind_param("iiids", $buyerID, $orderID, $productID, $rating, $comment);
    $stmt->execute();

    $message = "Review submitted!";

    header("Location: ../account.php?page=reviews&status=success&message=". urlencode($message)); 
    exit();
}

?>

<!------------------------------------------------------------------------------------------------------------------------------>
<!-- HTML Section -->
 <div class="card shadow-sm mt-4 mb-5" style="background-color: #F1F1F1;">
    <div class="card-body mb-0">
        <div class="container my-4">
            <h3 class="mb-4">Product Reviews</h3>
            <hr>

            <!-- Tabs -->
            <ul class="nav nav-tabs" id="reviewsTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <a class="nav-link active" id="items-tab" data-bs-toggle="tab" href="#items" role="tab">Review</a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link" id="history-tab" data-bs-toggle="tab" href="#history" role="tab">History</a>
                </li>
            </ul>

            <div class="tab-content mt-3" id="reviewsTabsContent">

                <!-- Items to review -->
                <div class="tab-pane fade show active" id="items" role="tabpanel">
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <div class="row g-3 align-items-center border p-3 mb-4 rounded shadow-sm bg-white">
                                <!-- Image Column -->
                                <div class="col-md-3 text-center">
                                    <div class="square-image-wrapper">
                                    <img src="uploads/<?= htmlspecialchars($row['imagePath']) ?>" alt="Product Image" class="img-fluid rounded product-image">
                                    </div>
                                </div>

                                <!-- Details + Form -->
                                <div class="col-md-9">
                                    <h5 class="mb-1"><?= htmlspecialchars($row['pName']) ?></h5>
                                    <p class="text-muted mb-1">Order #<?= htmlspecialchars($row['orderID']) ?> (<?= date("Y-m-d", strtotime($row['orderDate'])) ?>)</p>
                                    <p class="mb-2"><?= htmlspecialchars($row['pDescription']) ?></p>

                                    <!-- Review Form -->
                                    <form method="POST" action="account/reviews.php">
                                        <input type="hidden" name="orderID" value="<?= $row['orderID'] ?>">
                                        <input type="hidden" name="productID" value="<?= $row['productID'] ?>">

                                        <div class="mb-2">
                                            <label for="rating" class="form-label">Rating</label>
                                            <select class="form-select" name="rating" required>
                                                <option value="1">1 Star</option>
                                                <option value="2">2 Stars</option>
                                                <option value="3">3 Stars</option>
                                                <option value="4">4 Stars</option>
                                                <option value="5">5 Stars</option>
                                            </select>
                                        </div>

                                        <div class="mb-3">
                                            <label for="rComment" class="form-label">Your Review</label>
                                            <textarea class="form-control" name="rComment" rows="3" placeholder="Write your review here..." required></textarea>
                                        </div>

                                        <button type="submit" class="btn btn-primary">Submit Review</button>
                                    </form>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="alert alert-info">No products to review.</div>
                    <?php endif; ?>
                </div>

                <!-- Review History -->
                <div class="tab-pane fade" id="history" role="tabpanel">
                    <?php if ($historyResult->num_rows > 0): ?>
                        <?php while ($row = $historyResult->fetch_assoc()): ?>
                            <div class="row border p-3 mb-4 rounded bg-white shadow-sm align-items-center g-3">
                                <div class="col-md-2 text-center">
                                    <div class="square-image-wrapper">
                                    <img src="uploads/<?= htmlspecialchars($row['imagePath']) ?>" alt="Product Image" class="product-image">
                                    </div>
                                </div>
                                <div class="col-md-9">
                                    <h5><?= htmlspecialchars($row['pName']) ?></h5>

                                    <p class="mb-1 text-warning" style="font-size: 1.3rem;">
                                        <?= str_repeat('★', (int)$row['rating']) . str_repeat('☆', 5 - (int)$row['rating']) ?>
                                    </p>
                                    <p class="text-muted mb-1"><?= htmlspecialchars($row['reviewDate']) ?></p>
                                    <p><?= nl2br(htmlspecialchars($row['rComment'])) ?></p>
                                    <span class="badge 
                                        <?= $row['rStatus'] === 'accepted' ? 'bg-success' : ($row['rStatus'] === 'pending' ? 'bg-warning text-dark' : 'bg-danger') ?>">
                                        <?= ucfirst($row['rStatus']) ?>
                                    </span>
                                </div>
                                <div class="col-md-1 text-end">
                                    <form method="POST" action="account/reviews.php" onsubmit="return confirm('Delete this review?');">
                                        <input type="hidden" name="orderID" value="<?= $row['orderID'] ?>">
                                        <input type="hidden" name="productID" value="<?= $row['productID'] ?>">
                                        <button type="submit" name="delete_review" class="btn btn-sm btn-danger">Delete</button>
                                    </form>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="alert alert-info">No previous reviews found.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>




