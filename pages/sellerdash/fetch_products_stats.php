<?php
session_start();
require_once '../db.php';

if (!isset($_SESSION['sellerID'])) {
    http_response_code(401);
    echo json_encode(['error'=>'Unauthorized']);
    exit;
}
$sellerID = (int)$_SESSION['sellerID'];

// 1. Total Products
$totalProducts = $conn
  ->query("SELECT COUNT(*) AS cnt
           FROM products
           WHERE sellerID = $sellerID")
  ->fetch_assoc()['cnt'];

// 2. Total Reviews (via join)
$totalReviews = $conn
  ->query("SELECT COUNT(*) AS cnt
           FROM reviews r
           JOIN products p ON r.productID = p.productID
           WHERE p.sellerID = $sellerID")
  ->fetch_assoc()['cnt'];

// 3. Average Store Rating
$avgRating = $conn
  ->query("SELECT AVG(r.rating) AS avgRating
           FROM reviews r
           JOIN products p ON r.productID = p.productID
           WHERE p.sellerID = $sellerID")
  ->fetch_assoc()['avgRating'];
$avgRating = $avgRating ? round($avgRating,1) : 0;

// 4. Review Sentiment 

$sentiment = $conn->query("
    SELECT 
        SUM(r.rating >= 4) AS positive, 
        COUNT(*) AS total 
    FROM reviews r 
    JOIN products p ON r.productID = p.productID 
    WHERE p.sellerID = $sellerID
")->fetch_assoc();

$sentimentPct = $sentiment['total'] ? round($sentiment['positive'] / $sentiment['total'] * 100, 0) : 0;

// Label assignment
if ($sentimentPct >= 90) {
    $sentimentLabel = "Overwhelmingly Positive";
} elseif ($sentimentPct >= 75) {
    $sentimentLabel = "Very Positive";
} elseif ($sentimentPct >= 60) {
    $sentimentLabel = "Positive";
} elseif ($sentimentPct >= 40) {
    $sentimentLabel = "Mixed";
} elseif ($sentimentPct >= 25) {
    $sentimentLabel = "Negative";
} else {
    $sentimentLabel = "Overwhelmingly Negative";
}

// 5. Top / Least Selling Products
function fetchList($conn, $sellerID, $order, $limit=3) {
    $stmt = $conn->prepare("
        SELECT 
            p.productID,
            p.pName,
            p.pPrice,
            COALESCE(SUM(oi.quantity), 0) AS totalQuantitySold
        FROM 
            products p
        LEFT JOIN 
            orderitems oi ON p.productID = oi.productID
        WHERE 
            p.sellerID = ?
        GROUP BY 
            p.productID, p.pName, p.pPrice
        ORDER BY 
            totalQuantitySold $order
        LIMIT $limit
    ");
    $stmt->bind_param("i", $sellerID);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}


$topSelling   = fetchList($conn,$sellerID,'DESC');
$leastSelling = fetchList($conn,$sellerID,'ASC');

// 6. Top / Lowest Rated Products (avg per product)
function fetchRated($conn, $sellerID, $order, $limit=3) {
  $stmt = $conn->prepare("
    SELECT p.pName, ROUND(AVG(r.rating),1) AS avgRating
    FROM products p
    JOIN reviews r ON r.productID = p.productID
    WHERE p.sellerID = ?
    GROUP BY p.productID
    ORDER BY avgRating $order
    LIMIT $limit
  ");
  $stmt->bind_param("i", $sellerID);
  $stmt->execute();
  return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

$topRated    = fetchRated($conn,$sellerID,'DESC');
$lowestRated = fetchRated($conn,$sellerID,'ASC');

// 7. Full Product Summary
$summaryQuery = $conn->prepare("
    SELECT 
        p.productID,
        p.pName,
        p.pPrice,
        COALESCE(SUM(oi.quantity), 0) AS totalQuantitySold,
        ROUND(AVG(r.rating), 1) AS avgRating
    FROM products p
    LEFT JOIN orderitems oi ON p.productID = oi.productID
    LEFT JOIN reviews r ON p.productID = r.productID
    WHERE p.sellerID = ?
    GROUP BY p.productID, p.pName, p.pPrice
    ORDER BY totalQuantitySold DESC
");
$summaryQuery->bind_param("i", $sellerID);
$summaryQuery->execute();
$summaryProducts = $summaryQuery->get_result()->fetch_all(MYSQLI_ASSOC);


// 8. Output JSON
header('Content-Type: application/json');
echo json_encode([
  'totalProducts'    => (int)$totalProducts,
  'totalReviews'     => (int)$totalReviews,
  'storeRating'      => $avgRating,
  'sentimentLabel'   => $sentimentLabel,
  'topSelling'       => $topSelling,
  'leastSelling'     => $leastSelling,
  'topRated'         => $topRated,
  'lowestRated'      => $lowestRated,
  'summaryProducts'  => $summaryProducts
]);

