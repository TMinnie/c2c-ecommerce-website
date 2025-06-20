<?php $pageName = 'out'; ?>
<?php 
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once "pages/db.php"; 

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
    <link rel="stylesheet" href="pages/assets/css/styleindex.css"/>
    <link rel="stylesheet" href="pages/assets/css/navigation.css"/>
    <link rel="stylesheet" href="pages/assets/css/theme.css"/>

    <style>
        body {
            background-image: url('pages/assets/images/homeback.jpg');
            background-repeat: no-repeat;
            background-size: cover;
            background-attachment: fixed;
        }

        .button {
            font-size: 1.1rem;
            padding: 0.75rem 2rem;
            border-radius: 30px;
            background-color: #fc8c06;
            margin-top: 3rem;
            color: #fff;
            text-decoration: none;
        }

        i {
            color: #fc8c06;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.6);
        }

        h1, h6, p {
            color: #fff;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.7);
        }

        .info-card {
            background-color: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(8px);
            border-radius: 20px;
            padding: 1.5rem;
            box-shadow: 0 4px 20px rgba(0,0,0,0.3);
            height: 100%;
        }


        .info-card h6, .info-card p {
            color: #fff;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.6);
        }

        .hero-banner {
            transform: scale(0.9);
            transform-origin: top center;
        }

    </style>
</head>

<!-- Navigation -->
<?php include 'pages/nav.php'; ?>

<body>

    <!-- Hero Banner -->
    <section class="hero-banner text-center py-5 text-white">
        <div class="container">
            <h1 class="display-4 fw-bold mt-4">Welcome to TukoCart</h1>
            <p class="lead mb-5">Buy and sell anything, anytime, from anyone.</p>

            <!-- How It Works -->
            <div class="container text-center">
                <div class="row g-4 mb-4 justify-content-center">
                    <div class="col-md-4">
                        <div class="info-card">
                            <i class="fa fa-user-plus fa-2x mb-2"></i>
                            <h6 class="fw-bold mt-2">Sign Up</h6>
                            <p>Create your account as a buyer or seller.</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="info-card">
                            <i class="fa fa-box-open fa-2x mb-2"></i>
                            <h6 class="fw-bold mt-2">List Your Products</h6>
                            <p>Add items with images, prices, and descriptions.</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="info-card">
                            <i class="fa fa-credit-card fa-2x mb-2"></i>
                            <h6 class="fw-bold mt-2">Sell & Buy Safely</h6>
                            <p>Manage orders and make secure transactions.</p>
                        </div>
                    </div>
                </div>
            </div>

            <a href="pages/login.php" class="button btn-get-started mt-3">Get Started</a>
        </div>
    </section>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
