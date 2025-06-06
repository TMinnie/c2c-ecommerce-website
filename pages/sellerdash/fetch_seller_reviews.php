<?php
session_start();
require_once '../db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['userID']) || !isset($_SESSION['sellerID'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$sellerID = $_SESSION['sellerID'];

// Filters and sorting options
$sort = $_GET['sort'] ?? 'latest';
$filterRating = $_GET['filterRating'] ?? '';
$filterProduct = $_GET['filterProduct'] ?? '';
$filterDate = $_GET['filterDate'] ?? '';

// Base query with JOINs
$query = "
    SELECT 
        r.reviewID,
        r.rating,
        r.rComment,
        r.reviewDate,
        p.productID,
        p.pName,
        p.imagePath,
        u.uFirst,
        u.uLast
    FROM reviews r
    JOIN products p ON r.productID = p.productID
    JOIN users u ON r.buyerID = u.userID
    WHERE p.sellerID = ?
";

$params = [$sellerID];
$types = "i";

// Apply filters
if ($filterRating !== '') {
    $query .= " AND r.rating = ?";
    $params[] = $filterRating;
    $types .= "i";
}

if ($filterProduct !== '') {
    $query .= " AND p.pName = ?";
    $params[] = $filterProduct;
    $types .= "s";
}

if ($filterDate !== '') {
    $query .= " AND DATE(r.reviewDate) = ?";
    $params[] = $filterDate;
    $types .= "s";
}

// Apply sorting
switch ($sort) {
    case 'rating':
        $query .= " ORDER BY r.rating DESC";
        break;
    case 'product':
        $query .= " ORDER BY p.pName ASC";
        break;
    default:
        $query .= " ORDER BY r.reviewDate DESC";
        break;
}

// Execute query
$stmt = $conn->prepare($query);

if (!$stmt) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $conn->error]);
    exit;
}

$stmt->bind_param($types, ...$params);
$stmt->execute();

$result = $stmt->get_result();
$reviews = [];

while ($row = $result->fetch_assoc()) {
    $reviews[] = $row;
}

echo json_encode($reviews);
?>
