<?php
header('Content-Type: application/json');
ini_set('display_errors', 1);
error_reporting(E_ALL);

require '../db.php';
session_start();

if (!isset($_SESSION['sellerID'])) {
    echo json_encode([]);
    exit;
}
$sellerID = $_SESSION['sellerID'];

$dateFilter = $_GET['date'] ?? 'all';
$statusFilter = $_GET['status'] ?? '';
$buyerFilter = $_GET['buyer'] ?? '';
$sort = $_GET['sort'] ?? 'latest';

// Build dynamic WHERE clause
$conditions = ["p.sellerID = ?"];
$params = [$sellerID];
$types = "i";

if ($statusFilter) {
    $conditions[] = "pr.paymentStatus = ?";
    $params[] = $statusFilter;
    $types .= "s";
}

if ($buyerFilter) {
    $conditions[] = "(u.firstName LIKE ? OR u.lastName LIKE ?)";
    $params[] = "%$buyerFilter%";
    $params[] = "%$buyerFilter%";
    $types .= "ss";
}

if ($dateFilter == 'today') {
    $conditions[] = "DATE(pr.paymentDate) = CURDATE()";
}
if ($dateFilter == 'this_week') {
    $conditions[] = "YEARWEEK(pr.paymentDate, 1) = YEARWEEK(CURDATE(), 1)";
}
if ($dateFilter == 'this_month') {
    $conditions[] = "MONTH(pr.paymentDate) = MONTH(CURDATE()) AND YEAR(pr.paymentDate) = YEAR(CURDATE())";
}
if ($dateFilter == 'custom') {
    $start = $_GET['start'] ?? '';
    $end = $_GET['end'] ?? '';
    if ($start && $end) {
        $conditions[] = "DATE(pr.paymentDate) BETWEEN ? AND ?";
        $params[] = $start;
        $params[] = $end;
        $types .= "ss";
    }
}

// Sorting logic
$orderBy = "pr.paymentDate DESC";
if ($sort == 'amount_asc') $orderBy = "pr.paymentAmount ASC";
if ($sort == 'amount_desc') $orderBy = "pr.paymentAmount DESC";
if ($sort == 'units_asc') $orderBy = "unitCount ASC";
if ($sort == 'units_desc') $orderBy = "unitCount DESC";

$where = implode(" AND ", $conditions);

$sql = "SELECT pr.paymentID, pr.orderID, pr.paymentAmount, pr.paymentDate, pr.paymentStatus,
        SUM(oi.quantity) AS unitCount, GROUP_CONCAT(DISTINCT CONCAT(u.uFirst, ' ', u.uLast)) AS buyerNames
        FROM paymentrecords pr
        JOIN orders o ON pr.orderID = o.orderID
        JOIN orderitems oi ON o.orderID = oi.orderID
        JOIN products p ON oi.productID = p.productID
        JOIN users u ON o.buyerID = u.userID
        WHERE $where
        GROUP BY pr.paymentID, pr.orderID, pr.paymentAmount, pr.paymentDate, pr.paymentStatus
        ORDER BY $orderBy";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

$earnings = [];
while ($row = $result->fetch_assoc()) {
    $earnings[] = $row;
}
echo json_encode($earnings);

?>
