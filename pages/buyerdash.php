<?php $pageName = 'buy'; 
require_once "db.php";
session_start();

//Subcategories from db
$randomSubcategories = [];

$sql = "
    SELECT c1.categoryID, c1.name, c1.imagePath
FROM categories c1
JOIN (
    SELECT categoryID
    FROM categories
    WHERE parentID IS NULL
    ORDER BY name ASC
    LIMIT 6
) c2 ON c1.parentID = c2.categoryID
WHERE c1.categoryID = (
    SELECT MIN(c3.categoryID)
    FROM categories c3
    WHERE c3.parentID = c2.categoryID
);
";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $randomSubcategories[] = [
            "categoryID"=> $row["categoryID"],
            "imagePath" => $row["imagePath"],
            "name" => $row["name"]
        ];
    }
}

//Sesonal products from db
$sql = "
SELECT productID, pName, imagePath, seasonalTag 
FROM products 
WHERE seasonalTag IS NOT NULL
AND status = 'active'
LIMIT 6
";

$stmt = $conn->prepare($sql);
$stmt->execute();

$result = $stmt->get_result();
$featuredProducts = [];

while ($row = $result->fetch_assoc()) {
    $featuredProducts[] = $row;
}

//Top sellers from db
$topSellers = [];

$sql = "
    SELECT u.uFirst, u.uLast, s.sellerID, s.businessName, s.imagePath
    FROM sellers s
    JOIN users u ON u.userID = s.userID
    ORDER BY s.totalSales DESC
    LIMIT 6
";

$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $topSellers[] = [
            'uFirst' => $row['uFirst'],
            'uLast' => $row['uLast'],
            'sellerID' => $row['sellerID'],
            'businessName' => $row['businessName'],
            'imagePath' => $row['imagePath']
        ];
    }
}

?>

<!------------------------------------------------------------------------------------------------------------------------------>
<!--HTML-->
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop</title>

    <!--bootstrap-->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-SgOJa3DmI69IUzQ2PVdRZhwQ+dy64/BUtbMJw1MZ8t5HZApcHrRKUc4W0kG879m7" crossorigin="anonymous">
    <!--frontawesome-->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <!--stylesheet-->
    <link rel="stylesheet" href="assets/css/theme.css" />
    <link rel="stylesheet" href="assets/css/stylebuyerdash.css" />

</head>
<body class="hasCatMenu">
<!--Header-->
<?php include 'nav.php'; ?>

<!--Hover menu-->
<?php include 'hovermenu.php'; ?>

<?php if (isset($_SESSION['cart_msg'])): ?>
  <script>
    alert("<?php echo $_SESSION['cart_msg']; ?>");
  </script>
  <?php unset($_SESSION['cart_msg']); ?>
<?php endif; ?>


    <!--Top categories-->
    <section id="popular-categories" class="content my-5">
        <h2>Shop Popular Categories</h2>
        <p class="text-light">These categories are trending for a reason—dive in and explore.</p>
        <div class="row text-center g-4">
            <?php foreach ($randomSubcategories as $category): ?>
                <div  class="category-link col-6 col-sm-4 col-md-3 col-lg-2">
                <a href="category.php?categoryID=<?= $category['categoryID'] ?>">
                    <div class="category">
                        <img class="category-img img-fluid" src="assets/images/<?php echo $category['imagePath']; ?>">
                        <p class="category-label"><?php echo $category['name']; ?></p>
                    </div>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!--Editor's pick-->
    <?php if (!empty($featuredProducts)): ?>
    <section id="editors-pick" class="content container-fluid my-5">
        <div class="row g-4 align-items-center text-center text-md-start">
            <div class="col-md-6 col-12 text-center">
                    <h4>Editor's Picks</h4>
                    <h3><?php echo !empty($featuredProducts) ? htmlspecialchars($featuredProducts[0]['seasonalTag']) . ' Gifts' : 'Seasonal Gifts'; ?></h3>
                    <button class="custom-button" onclick="window.location.href='seasonalview.php'">Shop more unique finds</button>
            </div>
            <?php foreach ($featuredProducts as $product): ?>
                <div class="pick col-6 col-md-3">
                    <a href="product_view.php?productID=<?php echo $product['productID']; ?>">
                        <img class="picks-img img-fluid" 
                            src="uploads/<?php echo $product['imagePath']; ?>" 
                            alt="<?php echo $product['pName']; ?>" 
                            style="width: 180px; height: 180px; object-fit: cover;" />
                        <p class="category-label small text-center"><?php echo $product['pName']; ?></p>
                    </a>

                </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

    <!--Top sellers-->
    <section id="popular-sellers" class="content container-fluid my-5">

        <h2>Support Our Top Local Sellers</h2>
        <p  class="text-light">The people have spoken—these sellers are top-tier.</p>
        <div class="row text-center g-3">
            <?php foreach ($topSellers as $seller): ?>
                <div class="col-6 col-sm-4 col-md-3 col-lg-2 text-center">
                <a href="sellerview.php?sellerID=<?= urlencode($seller['sellerID']) ?>" >
                    <div class="category">
                        <img class="category-img img-fluid" src="uploads/<?php echo $seller['imagePath']; ?>"
                            alt="<?php echo $seller['uFirst']; ?><?php echo $seller['uLast']; ?>" />
                        <p class="category-label"><?php echo $seller['uFirst']; ?> <?php echo $seller['uLast']; ?></p>
                        <p class="category-desc"><?php echo $seller['businessName']; ?></p>
                    </div>
                </a>
            </div>
            <?php endforeach; ?>

        </div>
    </section>


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

</body>
</html>