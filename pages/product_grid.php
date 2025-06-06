<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Product Search Results</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom CSS -->
    <style>
        /* Product cards */
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
        }

        .card-img-top {
            border-top-left-radius: 12px;
            border-top-right-radius: 12px;
            object-fit: cover;
            height: 200px;
            aspect-ratio: 1 / 1;
        }

        .card-body {
            padding: 1rem;
            text-align: center;
        }

        .card-title {
            color: #666;
            font-size: 0.95rem;
        }

        .card-text {
            font-size: 1.1rem;
            font-weight: 600;
            color: #333;
        }
    </style>
</head>

<body>
<?php if ($search_result && $search_result->num_rows > 0): ?>
    <div id="productContainer" class="row">
        <?php
        $count = 0;
        while ($row = $search_result->fetch_assoc()):
            $avgRatingQuery = "SELECT AVG(rating) AS avgRating FROM reviews WHERE productID = ? AND rStatus = 'accepted'";
            $avgStmt = $conn->prepare($avgRatingQuery);
            $avgStmt->bind_param("i", $row['productID']);
            $avgStmt->execute();
            $avgResult = $avgStmt->get_result();
            $avgRow = $avgResult->fetch_assoc();
            $averageRating = $avgRow['avgRating'] ? round($avgRow['avgRating'], 1) : null;

            $isHidden = ($count >= 10) ? 'mobile-hidden d-none' : '';
        ?>
            <div class="col-6 col-md-3 mb-4 <?= $isHidden ?>">
                <a href="product_view.php?productID=<?= $row['productID'] ?>" class="text-decoration-none text-dark">
                    <div class="card h-100 shadow-sm hover-shadow">
                        <img src="uploads/<?= htmlspecialchars($row['imagePath']) ?>" class="card-img-top"
                             alt="<?= htmlspecialchars($row['pName']) ?>">
                        <div class="card-body text-start">
                            <p class="card-title"><?= htmlspecialchars($row['pName']) ?></p>
                            <p class="card-text">R<?= number_format($row['pPrice'], 2) ?></p>
                            <?php if ($averageRating): ?>
                                <span class="d-flex align-items-center">
                                    <span class="text-warning">&#9733;</span>
                                    <small class="text-secondary ms-1"><?= $averageRating ?></small>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                </a>
            </div>
        <?php
            $count++;
        endwhile;
        ?>
    </div>

    <?php if ($count > 10): ?>
        <!-- Show More Button (only on mobile) -->
        <div class="text-center mt-3 d-sm-block d-md-none">
            <button id="showMoreBtn" class="btn btn-outline-secondary">Show More</button>
        </div>

        <script>
            document.getElementById('showMoreBtn').addEventListener('click', function () {
                document.querySelectorAll('.mobile-hidden').forEach(function (el) {
                    el.classList.remove('d-none');
                });
                this.style.display = 'none';
            });
        </script>
    <?php endif; ?>

<?php else: ?>
    <div class="alert alert-warning">No products found.</div>
<?php endif; ?>
</body>
</html>
