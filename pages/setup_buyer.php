<?php
session_start();
require_once "db.php";

// Ensure user is logged in
if (!isset($_SESSION['userID'])) {
    die("Unauthorized access.");
}

$userID = $_SESSION['userID'];

// Collect form inputs safely
$shippingAddress = trim($_POST['shippingAddress'] ?? '');
$city = trim($_POST['city'] ?? '');
$postalCode = trim($_POST['postalCode'] ?? '');
$productID = $_POST['productID'] ?? null; // Optional

// Validate required fields
if (
    $shippingAddress && $city 
) {
    // Insert or update buyer profile
    $stmt = $conn->prepare("
        INSERT INTO buyers (userID, shippingAddress1, shippingAddress2, postalCode)
        VALUES (?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE 
            shippingAddress1 = VALUES(shippingAddress1),
            shippingAddress2 = VALUES(shippingAddress2),
            postalCode = VALUES(postalCode)

    ");
    $stmt->bind_param("isss", $userID, $shippingAddress, $city, $postalCode);
    $stmt->execute();

    // Redirect accordingly
    if ($productID) {
        header("Location: product_view.php?productID=" . urlencode($productID));
    } else {
        header("Location: account.php?page=home&status=success&message=Buyer%20profile%20added%20successfully");
    }
    exit;
} else {
    echo "Please fill in all required fields.";
}
?>
