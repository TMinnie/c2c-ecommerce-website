<?php
session_start();
require_once "db.php";

if (isset($_GET['cartItemID'])) {
    $cartItemID = $_GET['cartItemID'];

    $stmt = $conn->prepare("DELETE FROM cartitems WHERE cartItemID = ?");
    $stmt->bind_param("i", $cartItemID);
    $stmt->execute();
}

header("Location: cart.php");
exit;
