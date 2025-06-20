<?php
$pageName = 'buy';
require_once "db.php";
session_start();

// Get the first seasonal tag that has products
$tag_sql = "SELECT DISTINCT seasonalTag FROM products WHERE seasonalTag IS NOT NULL AND status = 'active' LIMIT 1";
$tag_result = $conn->query($tag_sql);
$seasonalTag = '';

if ($tag_result && $tag_result->num_rows > 0) {
    $seasonalTag = $tag_result->fetch_assoc()['seasonalTag'];
}

// Now fetch products for that tag
$sql = "SELECT * FROM products WHERE seasonalTag = ? AND status = 'active'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $seasonalTag);
$stmt->execute();
$search_result = $stmt->get_result();

?>

<!------------------------------------------------------------------------------------------------------------------------->
<!--HTML-->
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($seasonalTag) ?> Products</title>

    <!--Bootstrap-->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css">

    <!--Stylesheets-->
    <link rel="stylesheet" href="assets/css/stylecategory.css">
    <link rel="stylesheet" href="assets/css/theme.css">
</head>

<body class="hasCatMenu">

    <?php include 'nav.php'; ?>
    <?php include 'hovermenu.php'; ?>

    <div class="container py-5">
        <h2 class="text-center my-4">Products for <?= htmlspecialchars($seasonalTag) ?></h2>
        <hr>
    <?php include 'product_grid.php'; ?>
    </div>

    <?php include 'footer.php'; ?>
</body>

</html>