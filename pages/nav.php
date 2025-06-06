<?php
$cartCount = 0;

if (isset($_SESSION['userID'])) {
    require_once "db.php";

    $userID = $_SESSION['userID'];

    // Get buyerID
    $stmt = $conn->prepare("SELECT buyerID FROM buyers WHERE userID = ?");
    $stmt->bind_param("i", $userID);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $buyerID = $row['buyerID'];

        // Get cartID
        $stmt = $conn->prepare("SELECT cartID FROM carts WHERE buyerID = ?");
        $stmt->bind_param("i", $buyerID);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            $cartID = $row['cartID'];

            // Get total quantity from cartitems
            $stmt = $conn->prepare("SELECT SUM(quantity) AS totalItems FROM cartitems WHERE cartID = ?");
            $stmt->bind_param("i", $cartID);
            $stmt->execute();
            $result = $stmt->get_result();
            $data = $result->fetch_assoc();

            $cartCount = $data['totalItems'] ?? 0;
        }
    }

    $conn->query("
    UPDATE orders o
    JOIN deliveries d ON o.orderID = d.orderID
    SET o.orderStatus = 'Completed', d.deliveryStatus = 'delivered'
    WHERE d.deliveryStatus = 'shipped' AND d.deliveryDate < NOW()
");

}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nav</title>

    <!--bootstrap-->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-SgOJa3DmI69IUzQ2PVdRZhwQ+dy64/BUtbMJw1MZ8t5HZApcHrRKUc4W0kG879m7" crossorigin="anonymous">

    <!--fontawesome-->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <!--stylesheet-->
    <link rel="stylesheet" href="assets/css/navigation.css" />
    <link rel="stylesheet" href="assets/css/theme.css" />

</head>

<!--Navigation-->
<nav class="navbar navbar-expand-lg  py-0 fixed-top">
    <div class="container-fluid ">
        <!-- Logo -->
        <a class="navbar-brand" href="buyerdash.php">
            <img src="assets/images/logoVertical.jpg" class="logo-img img-fluid" alt="Logo" style="max-height: 60px;">

        </a>

        <!-- Toggler Button -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent"
            aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Collapse Content -->
        <div class="collapse navbar-collapse justify-content-lg-end" id="navbarSupportedContent">

            <?php if ($pageName === 'buy'): ?>
                <form class="d-flex mx-auto my-2 my-lg-0" style="max-width: 500px;" action="search_results.php" method="GET"
                    role="search">
                    <input class="form-control me-2" type="search" name="query" placeholder="Search products..."
                        aria-label="Search">
                    <button class="btn btn-outline-secondary" type="submit"><i class="fas fa-search"></i></button>
                </form>
            <?php elseif ($pageName === 'sell'): ?>
                <!-- Invisible form for alignment -->
                <div class="d-none d-lg-block mx-auto" style="width: 500px;"></div>
            <?php endif; ?>

            <!-- Right Nav Items -->
            <ul class="navbar-nav d-flex flex-column flex-lg-row align-items-center gap-2 text-center">
                <?php if ($pageName === 'buy' || $pageName === 'sell'): ?>
                    <li class="nav-item"><a class="nav-link" href="buyerdash.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="account.php">My Account</a></li>
                    <li class="nav-item"><a class="nav-link" href="sellerdash.php">Sell</a></li>
                    <li class="nav-item position-relative">
                        <a class="cart-badge" href="cart.php">
                            <i id="icon" class="fas fa-shopping-cart"></i>
                            <?php if ($cartCount > 0): ?>
                                <span class=""><?php echo $cartCount; ?></span>
                            <?php endif; ?>
                        </a>
                    </li>

                    <li class="nav-item"><a class="nav-link" href="logout.php"><i id="icon"
                                class="fas fa-sign-out-alt"></i></a></li>

                <?php elseif ($pageName === 'out'): ?>
                    <li class="nav-item"><a class="nav-link" href="login.php">Get Started</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-kQtW33rZJAHjgefvhyyzcGFgZsQsPsHFpWY/bP0xM4Yb4LrfJ1zG3hKQFWCytR5p" crossorigin="anonymous">
</script>