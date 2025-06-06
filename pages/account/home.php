<?php
require_once "../db.php";
session_start();

if (!isset($_SESSION['userID'])) {
    header("Location: login.php");
    exit;
}

$userID = $_SESSION['userID'];
$userFirstName = htmlspecialchars($_SESSION['uFirst']) ?? 'User';

$isBuyer = false;
$isSeller = false;

$checkBuyer = $conn->prepare("SELECT buyerID FROM buyers WHERE userID = ?");
$checkBuyer->bind_param("i", $userID);
$checkBuyer->execute();
$buyerResult = $checkBuyer->get_result();
$isBuyer = $buyerResult->num_rows > 0;

$checkSeller = $conn->prepare("SELECT sellerID FROM sellers WHERE userID = ? AND status = 'accepted'");
$checkSeller->bind_param("i", $userID);
$checkSeller->execute();
$sellerResult = $checkSeller->get_result();
$isSeller = $sellerResult->num_rows > 0;
?>

<style>
    .uniform-btn {
    width: 100%;
    max-width: 160px;
    display: inline-block;
    white-space: normal; /* allow wrapping */
    word-break: break-word; /* wrap long words if needed */
    text-align: center;
    padding: 6px 12px;
    border-radius: 5px;
}

.card-body .link-primary {
    display: inline-block;
    margin: 4px 0;
}

/* Optional: style for mobile */
@media (max-width: 576px) {
    .uniform-btn {
        max-width: 100%; /* full width in small screens */
    }
}
</style>

<div class="home container py-3">
    <div class="mb-4 text-left">
        <h1 class="text-white">Hi <?= $userFirstName ?>!</h1>
    </div>

    <div class="row g-4">
        <!-- Account Settings -->
        <div class="col-md-6 col-lg-4">
            <div class="card border-0 shadow h-100">
                <div class="card-body text-center">
                    <div class="mb-3 fs-2 text-primary">⚙️</div>
                    <h5 class="card-title mb-3">Account Settings</h5>
                    <a href="account.php?page=profile" class="link-primary  mb-2 uniform-btn">Update Profile</a>
                    <a href="account.php?page=security" class="link-primary mb-2 uniform-btn">Change Password</a>
                </div>
            </div>
        </div>

        <!-- Buyer Panel or Prompt -->
        <div class="col-md-6 col-lg-4">
            <div class="card border-0 shadow h-100">
                <div class="card-body text-center">
                    <div class="mb-3 fs-2 text-success">🛒</div>
                    <h5 class="card-title mb-3"><?= $isBuyer ? 'Buyer Dashboard' : 'Become a Buyer' ?></h5>

                    <a href="account.php?page=<?= $isBuyer ? 'orders' : 'buyer' ?>" class="link-primary  mb-2 uniform-btn">
                        <?= $isBuyer ? 'View Orders' : 'Become a Buyer' ?>
                    </a>

                    <?php if ($isBuyer): ?>
                        <a href="account.php?page=address" class="link-primary mb-2 uniform-btn">Manage Address</a>
                        <a href="account.php?page=reviews" class="link-primary mb-2 uniform-btn">Review Products</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Seller Panel or Prompt -->
        <div class="col-md-6 col-lg-4">
            <div class="card border-0 shadow h-100">
                <div class="card-body text-center">
                    <div class="mb-3 fs-2 text-warning">📦</div>
                    <h5 class="card-title mb-3"><?= $isSeller ? 'Seller Dashboard' : 'Become a Seller' ?></h5>
                    <a href="<?= $isSeller ? 'sellerdash.php' : 'account.php?page=seller' ?>" class="link-primary uniform-btn">
                        <?= $isSeller ? 'Go to Dashboard' : 'Become a Seller' ?>
                    </a>
                </div>
            </div>
        </div>

    </div>
</div>
