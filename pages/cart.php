<?php $pageName = 'buy';

session_start();
require_once "db.php";

if (!isset($_SESSION['userID'])) {
    header("Location: login.php");
    exit();
}

$userID = $_SESSION['userID'];

// Get buyerID
$stmt = $conn->prepare("SELECT buyerID FROM buyers WHERE userID = ?");
$stmt->bind_param("i", $userID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    header("Location: account.php?page=buyer");
    exit;
}

$buyer = $result->fetch_assoc();
$buyerID = $buyer['buyerID'];

// Get cartID
$stmt = $conn->prepare("SELECT cartID FROM carts WHERE buyerID = ?");
$stmt->bind_param("i", $buyerID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    $_SESSION['cart_msg'] = "No cart exists yet.";
        header("Location: buyerdash.php");
        exit();
}

$cart = $result->fetch_assoc();
$cartID = $cart['cartID'];

// Fetch cart items
$stmt = $conn->prepare("
    SELECT 
        ci.cartItemID,
        ci.productID,
        ci.quantity,
        ci.size,
        p.pName,
        p.pPrice,
        p.imagePath,
        COALESCE(pv.stockQuantity, 0) AS stockQuantity
    FROM cartitems ci
    JOIN products p ON ci.productID = p.productID
    LEFT JOIN product_variants pv ON ci.productID = pv.productID AND ci.size = pv.size
    WHERE ci.cartID = ?
");

$stmt->bind_param("i", $cartID);
$stmt->execute();
$cartItemsResult = $stmt->get_result();


$isCartEmpty = ($cartItemsResult->num_rows == 0);
if ($isCartEmpty) {
    echo "<div class='container mt-5'><h3>Your cart is empty.</h3>";
    echo "<a href='buyerdash.php' class='btn btn-custom'>Continue shopping</a>";
    echo "</div>";
}

?>

<!------------------------------------------------------------------------------------------------------------------------->
<!--HTML-->
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Your Cart</title>
    
    <!--Bootstrap-->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css">

    <!--Stylesheets-->
    <link rel="stylesheet" href="assets/css/stylecart.css">
    <link rel="stylesheet" href="assets/css/theme.css">
    

</head>

<body class="hasCatMenu">
<!--Header-->
<?php include 'nav.php'; ?>

<!--Hover menu-->
<?php include 'hovermenu.php'; ?>

<!--Cart-->
<div class="container mt-5" style="margin-bottom: 150px;">
    <h3 class="mb-2">Shopping Cart</h3>
    <hr>
    <input type="hidden" name="cartID" value="<?php echo $cartID; ?>">
    
    <div class="table-responsive">
        <table class="table table-bordered align-middle text-center cart-table d-none d-md-table">

            <thead class="table-light">
                <tr>
                    <th style="width: 10%;">Product</th>
                    <th style="width: 25%;">Name</th>
                    <th style="width: 10%;">Size</th>
                    <th style="width: 15%;">Price</th>
                    <th style="width: 10%;">Quantity</th>
                    <th style="width: 15%;">Subtotal</th>
                    <th>Remove</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $total = 0;

                while ($row = $cartItemsResult->fetch_assoc()) {
                    $productID = $row['productID'];
                    $cartItemID = $row['cartItemID'];
                    $name = htmlspecialchars($row['pName']);
                    $price = $row['pPrice'];
                    $quantity = $row['quantity'];
                    $subtotal = $price * $quantity;
                    $total += $subtotal;
                    $size = htmlspecialchars($row['size']);
                    ?>
                    <tr>
                        <td><a href="#" data-bs-toggle="modal" data-bs-target="#imgModal<?php echo $productID; ?>">
                            <img src="uploads/<?php echo $row['imagePath']; ?>" width="80" class="img-thumbnail">
                        </a></td>
                        <td class="text-start">
                            <a style="color: black; text-decoration: underline;" href="product_view.php?productID=<?php echo $productID; ?>"><?php echo $name;?></a>
                        </td>
                        <td><?php echo $size; ?></td>
                        <td>R<?php echo number_format($price, 2); ?></td>
                        <td>
                            <input type="number" 
                                class="form-control mx-auto quantity-input" 
                                data-cartitemid="<?php echo $cartItemID; ?>" 
                                value="<?php echo $quantity; ?>" 
                                min="1"
                                max="<?php echo $row['stockQuantity']; ?>">

                        </td>
                        <td>R<?php echo number_format($subtotal, 2); ?></td>
                        <td>
                            <a href="remove_from_cart.php?cartItemID=<?php echo $cartItemID; ?>" class="btn btn-sm btn-danger">
                                <i class="fas fa-trash text-white ml-1" aria-hidden="true"></i>
                            </a>
                        </td>
                    </tr>

                    <!-- Modal for each product -->
                    <div class="modal fade" id="imgModal<?php echo $productID; ?>" tabindex="-1" aria-labelledby="imgModalLabel<?php echo $productID; ?>" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered modal-lg custom-modal-height">
                            <div class="modal-content">
                                <div class="modal-body text-center">
                                    <img src="uploads/<?php echo $row['imagePath']; ?>" class="img-fluid rounded custom-img" alt="<?php echo $name; ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                <?php } ?>
                </tbody>

            <tfoot>
                <tr>
                    <th colspan="5" class="text-end">
                        Total: (<?php echo $cartItemsResult->num_rows . " item" . ($cartItemsResult->num_rows != 1 ? "s" : ""); ?>)
                    </th>
                    <th colspan="2">R<?php echo number_format($total, 2); ?></th>
                </tr>
            </tfoot>
        </table>


    <?php
    // Clone the result set for mobile (reset pointer)
    $cartItemsResult->data_seek(0);
    while ($row = $cartItemsResult->fetch_assoc()) {
        $productID = $row['productID'];
        $cartItemID = $row['cartItemID'];
        $name = htmlspecialchars($row['pName']);
        $price = $row['pPrice'];
        $quantity = $row['quantity'];
        $subtotal = $price * $quantity;
        $size = htmlspecialchars($row['size']);
    ?>
        <div class="cart-item-mobile d-md-none">
            <div class="square-image-wrapper d-flex" >
                <img src="uploads/<?php echo $row['imagePath']; ?>" alt="<?php echo $name; ?>" class="img-fluid product-image">
                <div class="ms-3">
                    <p><strong><?php echo $name; ?></strong></p>
                    <p>Size: <?php echo $size; ?></p>
                    <p>Price: R<?php echo number_format($price, 2); ?></p>
                </div>
            </div>
            <div class="cart-item-info">
                <p>Quantity: 
                    <input type="number" 
                        class="form-control mx-auto quantity-input" 
                        data-cartitemid="<?php echo $cartItemID; ?>" 
                        value="<?php echo $quantity; ?>" 
                        min="1"
                        max="<?php echo $row['stockQuantity']; ?>">
                </p>

                <p>Subtotal: R<?php echo number_format($subtotal, 2); ?></p>

                <a href="remove_from_cart.php?cartItemID=<?php echo $cartItemID; ?>" 
                class="btn btn-sm btn-danger mt-2">
                    Remove
                </a>
            </div>
        </div>
    <?php } ?>
    </div>

<div class="mt-4 d-md-none text-end">
    <strong>Total: R<?php echo number_format($total, 2); ?></strong>
</div>


    <div class="d-flex justify-content-between mt-4">
    <a href="checkout.php" class="btn btn-success<?php echo $isCartEmpty ? ' disabled' : ''; ?>" 
       <?php echo $isCartEmpty ? 'tabindex="-1" aria-disabled="true"' : ''; ?>>
       Proceed to Checkout
    </a>
</div>
</div>

<!--Footer-->
<?php include 'footer.php'; ?>

<!--JS-->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
$(".quantity-input").on("change", function (event) {
    event.preventDefault(); // 🔒 Stop the form-like behavior

    const input = $(this);
    const cartItemID = input.data("cartitemid");
    const quantity = parseInt(input.val());
    const max = parseInt(input.attr("max"));

    if (quantity > max) {
        alert("Quantity cannot exceed available stock.");
        input.val(max);
        return;
    }

    $.ajax({
        url: "update_cart.php",
        method: "POST",
        dataType: "json",
        data: {
            cartItemID: cartItemID,
            quantity: quantity
        },
        success: function (response) {
            if (response.success) {
                // 🔄 Update the UI without refreshing
                const row = input.closest("tr");
                row.find("td:nth-child(6)").text("R" + response.subtotal);
                $("tfoot th:last").text("R" + response.total);
            } else {
                alert(response.message);
            }
        },
        error: function () {
            alert("Something went wrong. Try again.");
        }
    });
});



</script>

<!--Bootstrap JS-->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
