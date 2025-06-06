<?php
$pageName = 'buy';

session_start();
include 'db.php';

$userID = $_SESSION['userID'] ?? null;

$isSeller = false;

if (isset($_SESSION['userID'])) {
    $checkSeller = $conn->prepare("SELECT sellerID FROM sellers WHERE userID = ?");
    $checkSeller->bind_param("i", $_SESSION['userID']);
    $checkSeller->execute();
    $sellerResult = $checkSeller->get_result();
    $isSeller = $sellerResult->num_rows > 0;
    
    if ($isSeller) {
        $_SESSION['sellerID'] = $sellerResult->fetch_assoc()['sellerID'];  // Set session
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>

    <!--bootstrap-->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-SgOJa3DmI69IUzQ2PVdRZhwQ+dy64/BUtbMJw1MZ8t5HZApcHrRKUc4W0kG879m7" crossorigin="anonymous">
    <!--frontawesome-->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <!--stylesheet-->
    <link rel="stylesheet" href="assets/css/stylehome.css" />
    <link rel="stylesheet" href="assets/css/theme.css" />


</head>

<body>
    <!--Header-->
    <?php include 'nav.php'; ?>

    <!--Options-->
    <section class="options-container">
        <div class="row equal-heigt-cards">
            <div class="col-sm-6 mb-3 mb-sm-0">
                <div class="card h-100">
                    <a href="buyerdash.php" class="stretched-link text-reset">
                        <img class="card-img" src="assets/images/promo5.jpg" alt="shop">
                        <div class="card-body">
                            <h2 class="card-title">Shop as a Buyer</h2>
                            <p class="card-text">Browse listings, manage your orders, and explore new products.</p>
                        </div>
                    </a>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="card h-100">
                    <a href="#" class="stretched-link text-reset" onclick="handleSellerClick(event)">
                        <img class="card-img" src="assets/images/promo2.jpg" alt="sell">
                        <div class="card-body">
                            <h2 class="card-title">Manage as a Seller</h2>
                            <p class="card-text">Add new products, view your listings, and handle customer orders with
                                ease.</p>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </section>


    <!--Seller sign up-->
    <div class="modal fade" id="sellerSignupModal" tabindex="-1" aria-labelledby="sellerSignupLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">

            <form class="modal-content" action="process_seller_signup.php" method="POST" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title" id="sellerSignupLabel">Become a Seller</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="userID" value="<?php echo $_SESSION['userID'] ?? ''; ?>">

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Business Name</label>
                            <input type="text" class="form-control" name="businessName" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Bank Account Number</label>
                            <input type="text" class="form-control" name="payDetails" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Business Description</label>
                        <textarea class="form-control" name="businessDescript" rows="3" required></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Pickup Address</label>
                            <input type="text" class="form-control" name="pickupAddress" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">City</label>
                            <input type="text" class="form-control" name="city">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Upload Business Image</label>
                        <input type="file" class="form-control" name="imageFile" accept=".jpeg, .jpg, .png">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Submit & Become Seller</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>


    <!--Footer-->
    <?php include 'footer.php'; ?>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-k6d4wzSIapyDyv1kpU366/PK5hCdSbCRGRCMv+eplOQJWyd1fbcAu9OCUj5zNLiq"
        crossorigin="anonymous"></script>

    <!--Fontawesome JS-->
    <script defer src="https://use.fontawesome.com/releases/v5.15.4/js/all.js"
        integrity="sha384-rOA1PnstxnOBLzCLMcre8ybwbTmemjzdNlILg8O7z1lUkLXozs4DHonlDtnE7fpc"
        crossorigin="anonymous"></script>


    <!--Javascript-->
    <script>
        function handleSellerClick(e) {
            e.preventDefault();

            const isSeller = <?php echo $isSeller ? 'true' : 'false'; ?>;

            if (isSeller) {
                window.location.href = "sellerdash.php";
            } else {
                const modal = new bootstrap.Modal(document.getElementById('sellerSignupModal'));
                modal.show();
            }
        }
    </script>

</body>

</html>