<?php
session_start();
include 'db.php';
$pageName = 'sell';

if (!isset($_SESSION['userID'])) {
  header("Location: ../login.php");
  exit;
}

if (isset($_SESSION['sellerID'])) {
  $sellerID = (int) $_SESSION['sellerID']; // Ensure integer for safety
} else {
  header("Location: ../buyerdash.php");
  die("Error: Seller not logged in.");
}

if (isset($_GET['name'])) {
    $businessName = urldecode($_GET['name']);
} else {
    echo "No business name provided.";
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Insights</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/theme.css">
    <link rel="stylesheet" href="assets/css/stylesellerdash.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">


    <style>
      body{
        background-image: url('assets/images/bg.png' ); 
        background-repeat: no-repeat;  
        background-size: cover;;
      }
    </style>
</head>

<body>

<!--Header-->
<?php include 'nav.php'; ?>

<div class="mx-auto card p-4 mb-4 mt-5 shadow-sm rounded-3" style="width: 90%; background-color: #f1f1f1;">
  <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between mb-3">
  <div class="d-flex flex-column flex-sm-row align-items-start align-items-sm-center">
    <h3 class="mb-2 mb-sm-0 me-sm-3">Business Insights</h3>
    <p class="text-muted mb-0"><?php echo htmlspecialchars($businessName) ?></p>
  </div>
  <a href="sellerdash.php" class="mt-2 mt-md-0">Back To Seller Dashboard</a>
</div>

  <!-- Tabs -->
  <div class="container-fluid px-0">
    <ul class="nav nav-tabs mt-3 mb-4">
      <li class="nav-item">
        <a class="nav-link active" id="sales-tab"  data-bs-toggle="tab" href="#sales">Sales</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" id="overview-tab" data-bs-toggle="tab" href="#overview">Orders</a>
      </li>
      
      <li class="nav-item">
        <a class="nav-link" id="products-tab"  data-bs-toggle="tab" href="#products">Products</a>
      </li>
    </ul>
  </div>

  <div class="tab-content">
    <!-- Sales content goes here -->
    <div class="tab-pane fade show active" id="sales">
      <?php require_once 'sellerdash/stats/sales.php' ?>
    </div>
    <!-- Orders content goes here -->
    <div class="tab-pane fade" id="overview">
      <?php require_once 'sellerdash/stats/orders.php' ?>
    </div>
    <!-- Products content goes here -->
    <div class="tab-pane fade" id="products">
      <?php require_once 'sellerdash/stats/products.php' ?>
    </div>
  </div>
</div> 


      <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"></script>


</body>
</html>