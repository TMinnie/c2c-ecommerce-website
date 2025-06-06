<?php $pageName = 'out'; ?>
<?php require_once "db.php";

// Get Random Products
$sql = "SELECT productID, pName, pPrice, imagePath FROM products ORDER BY RAND() LIMIT 4";
$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();
$featuredProducts = [];

while ($row = $result->fetch_assoc()) {
    $featuredProducts[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TukoCart</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/styleindex.css" />
    <link rel="stylesheet" href="assets/css/navigation.css" />
    <link rel="stylesheet" href="assets/css/theme.css" />

    <style>
        .card:hover {
            transform: scale(1.02);
            transition: transform 0.3s ease;
        }

        .hero-banner {
            background: linear-gradient(rgba(255, 255, 255, 0.3), rgba(255, 255, 255, 0.3)),
                url('assets/images/hero-bg.jpg') center/cover no-repeat;
        }

        .btn-get-started {
            font-size: 1.1rem;
            padding: 0.75rem 2rem;
            border-radius: 30px;
        }

        i {
            color: #ffc107;
        }

        .card-img-top {
            object-fit: cover;
            height: 200px;
        }
    </style>
</head>

<!-- Navigation -->
<?php include 'nav.php'; ?>

<body >

    <!-- Hero Banner -->
    <section class="hero-banner text-center py-5 text-dark ">
        <div class="container">
            <h1 class="display-4 fw-bold mt-5">Welcome to TukoCart</h1>
            <p class="lead mb-5">Buy and sell anything, anytime, from anyone.</p>

            <!-- How It Works -->
            <div class="container text-center">
                <div class="row g-4">
                    <div class="col-md-4">
                        <i class="fa fa-user-plus fa-2x mb-2"></i>
                        <h6>Sign Up</h6>
                        <p>Create your account as a buyer or seller.</p>
                    </div>
                    <div class="col-md-4">
                        <i class="fa fa-box-open fa-2x mb-2"></i>
                        <h6>List Your Products</h6>
                        <p>Add items with images, prices, and descriptions.</p>
                    </div>
                    <div class="col-md-4">
                        <i class="fa fa-credit-card fa-2x mb-2"></i>
                        <h6>Sell & Buy Safely</h6>
                        <p>Manage orders and make secure transactions.</p>
                    </div>
                </div>
            </div>
            <a href="login.php" class="btn-get-started btn btn-warning mt-3">Get Started</a>
        </div>
    </section>

    <!-- Featured Products -->
    <section class="container my-5">
        <div class="row g-4">
            <?php foreach ($featuredProducts as $product): ?>
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <div class="card h-100 shadow-sm">
                        <img src="uploads/<?= htmlspecialchars($product['imagePath']) ?>" class="card-img-top"
                            alt="<?= htmlspecialchars($product['pName']) ?>">
                        <div class="card-body">
                            <p class="fw-bold mb-1"><?= htmlspecialchars($product['pName']) ?></p>
                            <p class="text-muted">R<?= number_format($product['pPrice'], 2) ?></p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="content">
        <div class="block-container">
            <div class="imgblock">
                <img src="assets/images/promo1.jpg" class="promo-img" alt="promo1">
            </div>
            <div class="block">
                <h2>About us</h2>
                <p>TukoTrade is a proudly South African e-commerce initiative built to empower the informal economy. We
                    are
                    creating a digital marketplace where everyday entrepreneurs — from street vendors and crafters to
                    home-based
                    sellers — can trade safely, reach more customers, and grow their businesses.
                    South Africa’s informal sector is rich with potential but often excluded from the benefits of
                    digital
                    commerce. TukoTrade was born out of the belief that technology should be accessible to everyone —
                    not
                    just
                    big businesses.</p>
            </div>
        </div>

        <div class="block-container">
            <div class="block">
                <h2>What we provide</h2>
                <p>Our platform is designed to provide informal traders with what they need most:</p>
                <ul>
                    <li>Broader market access</li>
                    <li>Secure digital payments</li>
                    <li>Built-in delivery support</li>
                    <li>Tools to build trust and reputation</li>
                </ul>
            </div>
            <div class="imgblock">
                <img src="assets/images/promo2.jpg" class="promo-img" alt="promo1">
            </div>
        </div>

        <div class="block-container">
            <div class="imgblock">
                <img src="assets/images/promo4.jpg" class="promo-img" alt="promo1">
            </div>
            <div class="block">
                <h2>Our Aim</h2>
                <p>By offering a mobile-first, low-data platform tailored for grassroots entrepreneurs, we aim to bridge
                    the
                    digital divide and bring informal sellers into the online economy — on their own terms.</p>
                <p>At TukoTrade, we believe in local power. When informal traders thrive, so do communities. Our mission
                    is
                    simple:
                    To create a digital space where small businesses can grow, compete, and shine.</p>
            </div>
        </div>
        <h2 style="font-weight: 100; color: #f1f1f1;  text-align-last: center;">Welcome to the trade revolution. Welcome to TukoTrade.</h2>
    </section>


    <!-- Footer -->
    <?php include 'footer.php'; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>