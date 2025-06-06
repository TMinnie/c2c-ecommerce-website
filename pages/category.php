<?php
$pageName = 'buy';
require_once "db.php";
session_start();

// Validate categoryID
$categoryID = filter_input(INPUT_GET, 'categoryID', FILTER_VALIDATE_INT);
if (!$categoryID) {
    die("Invalid category ID.");
}

// Fetch category name
$catStmt = $conn->prepare("SELECT name FROM categories WHERE categoryID = ?");
$catStmt->bind_param("i", $categoryID);
$catStmt->execute();
$catResult = $catStmt->get_result();

if ($catResult->num_rows === 0) {
    die("Category not found.");
}
$category = $catResult->fetch_assoc();

// Get child category IDs (including the current one)
$subcategories = [$categoryID];
$childStmt = $conn->prepare("SELECT categoryID FROM categories WHERE parentID = ?");
$childStmt->bind_param("i", $categoryID);
$childStmt->execute();
$childResult = $childStmt->get_result();

while ($row = $childResult->fetch_assoc()) {
    $subcategories[] = $row['categoryID'];
}

// Prepare placeholders and types
$placeholders = implode(',', array_fill(0, count($subcategories), '?'));
$types = str_repeat('i', count($subcategories));

// Fetch products in category and subcategories
$sql = "SELECT * FROM products WHERE pCategory IN ($placeholders) AND status = 'active'";
$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$subcategories);
$stmt->execute();
$search_result = $stmt->get_result();
?>

<!------------------------------------------------------------------------------------------------------------------------->
<!--HTML-->
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($category['name']) ?> Products</title>

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
        <h2 class="text-center my-4">Products in <?= htmlspecialchars($category['name']) ?></h2>
        <hr>
    <?php include 'product_grid.php'; ?>
    </div>

    <?php include 'footer.php'; ?>
</body>

</html>