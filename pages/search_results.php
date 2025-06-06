<?php $pageName = 'buy';?>
<?php

session_start();
include "db.php"; 

$query = isset($_GET['query']) ? trim($_GET['query']) : '';

if (empty($query)) {
    header("Location: buyerdash.php"); 
    exit;
}

// Prepared statements to avoid SQL injection
$stmt = $conn->prepare("SELECT * FROM products WHERE (pName LIKE ? OR pDescription LIKE ?) AND status = 'active'");
$searchTerm = '%' . $query . '%';
$stmt->bind_param("ss", $searchTerm, $searchTerm);
$stmt->execute();
$search_result = $stmt->get_result();

?>

<!------------------------------------------------------------------------------------------------------------------------->
<!--HTML-->
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Search Results</title>

    <!--Bootstrap-->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css">

    <!--Stylesheets-->
    <link rel="stylesheet" href="assets/css/theme.css">

</head>

<body class="hasCatMenu">
    <!--Header-->
    <?php include 'nav.php'; ?>

    <!--Hover menu-->
    <?php include 'hovermenu.php'; ?>


    <!--Search Results-->
    <div class="container py-5">
        <h2 class="text-center my-4">Search Results for: <em><?= htmlspecialchars($query) ?></em></h2>
        <hr>
        <?php include 'product_grid.php'; ?>
        
    </div>

    <!--Footer-->
    <?php include 'footer.php'; ?>

</body>
</html>