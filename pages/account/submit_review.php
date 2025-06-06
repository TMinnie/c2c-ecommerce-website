<?php
session_start();
include '../db.php';
error_log("submit_review.php reached");
error_log("orderID: $orderID, productID: $productID, rating: $rating, comment: $comment");


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userID = $_SESSION['userID'];
    $orderID = $_POST['orderID'];
    $productID = $_POST['productID'];
    $rating = $_POST['rating'];
    $comment = $_POST['comment'];

    // Check if user is logged in
    if (!$userID) {
        die("User not logged in.");
    }

    // Get buyerID
    $buyerQuery = $conn->prepare("SELECT buyerID FROM buyers WHERE userID = ?");
    $buyerQuery->bind_param("i", $userID);
    $buyerQuery->execute();
    $buyerResult = $buyerQuery->get_result();

    if ($buyerResult->num_rows === 0) die("Buyer not found.");
    $buyerID = $buyerResult->fetch_assoc()['buyerID'];

    // Debug: Check buyerID
    echo "Buyer ID: $buyerID"; 

    // Insert or update review per product per order
    $stmt = $conn->prepare("
        INSERT INTO reviews (buyerID, orderID, productID, rating, rComment) 
        VALUES (?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE rating = VALUES(rating), rComment = VALUES(rComment)
    ");
    $stmt->bind_param("iiids", $buyerID, $orderID, $productID, $rating, $comment);

    if ($stmt->execute()) {
        echo "Review submitted!";
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>
