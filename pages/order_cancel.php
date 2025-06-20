<?php $pageName = 'buy'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order Confirmation</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css">
</head>
<body>
<?php include 'nav.php'; ?>

<div class="container my-5" style="height: 450px;">
    <h2>Order Confirmation</h2>
    <hr class="mb-4">
    <div class="alert alert-danger">
        <h5>Your order(s) was unsuccessfull. Please try again.</h5>
    </div>

    <a href="cart.php" class="btn btn-primary  text-center mt-2">Try Again</a>

</div>
<?php include 'footer.php'; ?>
</body>
</html>
