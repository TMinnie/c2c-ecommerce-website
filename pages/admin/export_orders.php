<?php
require_once '../db.php';

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="orders_export.csv"');

// Output UTF-8 BOM for Excel compatibility
echo "\xEF\xBB\xBF";

$output = fopen('php://output', 'w');

// Add column headers
fputcsv($output, ['Order ID', 'Buyer ID', 'Order Date', 'Products Total', 'Order Status', 'Seller ID', 'Delivery Fee', 'Order Total'],';');

// Prepare filters
$whereClauses = [];
$params = [];
$types = '';

if (!empty($_GET['orderStatus'])) {
    $whereClauses[] = "orderStatus = ?";
    $params[] = $_GET['orderStatus'];
    $types .= 's';
}

if (!empty($_GET['orderDate'])) {
    $whereClauses[] = "DATE(orderDate) = ?";
    $params[] = $_GET['orderDate'];
    $types .= 's';
}

if (!empty($_GET['searchType']) && !empty($_GET['searchValue'])) {
    $field = $_GET['searchType'];
    if (in_array($field, ['sellerID', 'buyerID', 'orderID'])) {
        $whereClauses[] = "$field = ?";
        $params[] = $_GET['searchValue'];
        $types .= 'i';
    }
}

$whereSQL = $whereClauses ? 'WHERE ' . implode(' AND ', $whereClauses) : '';
$stmt = $conn->prepare("SELECT * FROM orders $whereSQL ORDER BY orderDate DESC");

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $orderTotal = $row['totalAmount'] + $row['deliveryFee'];
    fputcsv($output, [
        $row['orderID'],
        $row['buyerID'],
        $row['orderDate'],
        number_format($row['totalAmount'], 2),
        $row['orderStatus'],
        $row['sellerID'],
        number_format($row['deliveryFee'], 2),
        number_format($orderTotal, 2)
    ],';');
}

fclose($output);
exit;
?>
