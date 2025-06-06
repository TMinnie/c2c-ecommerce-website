<?php
session_start();
require '../db.php'; 

header('Content-Type: application/json');

if ($_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$range = isset($_GET['range']) ? (int)$_GET['range'] : 30;

$sqlOrders = "
  SELECT COUNT(DISTINCT o.orderID) AS total_orders
  FROM orders o
  WHERE o.orderDate >= CURDATE() - INTERVAL ? DAY
";

$stmt = $conn->prepare($sqlOrders);
$stmt->bind_param("i", $range);
$stmt->execute();
$orderResult = $stmt->get_result();
$orderRow = $orderResult->fetch_assoc();

$totalOrders = (int)($orderRow['total_orders'] ?? 0);

$sqlBuyers = "
  SELECT COUNT(DISTINCT o.buyerID) AS total_buyers
  FROM orders o
  WHERE o.orderDate >= CURDATE() - INTERVAL ? DAY
";

$stmt = $conn->prepare($sqlBuyers);
$stmt->bind_param("i", $range);
$stmt->execute();
$buyerResult = $stmt->get_result();
$buyerRow = $buyerResult->fetch_assoc();

$totalBuyers = (int)($buyerRow['total_buyers'] ?? 0);


$sqlTopProducts = "
  SELECT p.pName, SUM(oi.quantity) AS total_sold
  FROM orderitems oi
  JOIN products p ON oi.productID = p.productID
  JOIN orders o ON oi.orderID = o.orderID
  WHERE o.orderDate >= CURDATE() - INTERVAL ? DAY
  GROUP BY p.productID
  ORDER BY total_sold DESC
  LIMIT 3
";

$stmt = $conn->prepare($sqlTopProducts);
$stmt->bind_param("i", $range);
$stmt->execute();
$topProductsResult = $stmt->get_result();

$topProducts = [];
while ($row = $topProductsResult->fetch_assoc()) {
    $topProducts[] = [
        'product' => $row['pName'],
        'quantity' => (int)$row['total_sold']
    ];
}

// === SALES PER DAY (no duplicate orders) ===
$sql = "
  SELECT DATE(pr.paymentDate) as payment_day, SUM(pr.paymentAmount) as total
  FROM paymentRecords pr
  JOIN (
      SELECT MAX(paymentID) as latest_payment_id
      FROM paymentRecords
      WHERE paymentStatus = 'Paid'
      GROUP BY orderID
  ) latest ON pr.paymentID = latest.latest_payment_id
  WHERE pr.orderID IN (
      SELECT DISTINCT o.orderID
      FROM orders o
      JOIN orderitems oi ON o.orderID = oi.orderID
      JOIN products p ON oi.productID = p.productID
  )
  AND pr.paymentDate >= CURDATE() - INTERVAL ? DAY
  GROUP BY payment_day
  ORDER BY payment_day ASC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $range);
$stmt->execute();
$result = $stmt->get_result();

$labels = [];
$sales = [];

$start = new DateTime(date('Y-m-d', strtotime("-$range days")));
$end = new DateTime();
$end->modify('+1 day');

$salesData = [];
while ($row = $result->fetch_assoc()) {
    $salesData[$row['payment_day']] = round($row['total'], 2);
}

$period = new DatePeriod($start, new DateInterval('P1D'), $end);
foreach ($period as $date) {
    $day = $date->format('Y-m-d');
    $labels[] = $day;
    $sales[] = $salesData[$day] ?? 0;
}

// === GROSS SALES (total for period) ===
$sqlGross = "
  SELECT SUM(pr.paymentAmount) AS gross_total
  FROM paymentRecords pr
  JOIN (
      SELECT MAX(paymentID) AS latest_payment_id
      FROM paymentRecords
      WHERE paymentStatus = 'Paid'
      GROUP BY orderID
  ) latest ON pr.paymentID = latest.latest_payment_id
  WHERE pr.orderID IN (
      SELECT DISTINCT o.orderID
      FROM orders o
      JOIN orderitems oi ON o.orderID = oi.orderID
      JOIN products p ON oi.productID = p.productID
  )
  AND pr.paymentDate >= CURDATE() - INTERVAL ? DAY
";

$stmt = $conn->prepare($sqlGross);
$stmt->bind_param("i", $range);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$grossSales = (float)($row['gross_total'] ?? 0.0);

// === BEST DAY/TIME ===
$sql = "
  SELECT 
    DATE_FORMAT(o.orderDate, '%a') AS best_day,
    DATE_FORMAT(o.orderDate, '%H:%i') AS best_time,
    SUM(o.totalAmount) AS total_sales
  FROM orders o
  WHERE o.orderID IN (
    SELECT DISTINCT o.orderID
    FROM orders o
    JOIN orderitems oi ON o.orderID = oi.orderID
    JOIN products p ON oi.productID = p.productID
  )
  GROUP BY best_day, best_time
  ORDER BY total_sales DESC
  LIMIT 1
";

$stmt = $conn->prepare($sql);
$stmt->execute();
$bestResult = $stmt->get_result();
$bestTimeRow = $bestResult->fetch_assoc();

$bestDay = $bestTimeRow['best_day'] ?? 'N/A';
$bestTime = $bestTimeRow['best_time'] ?? 'N/A';

// === REFUNDS ===
$sqlRefunds = "
  SELECT COUNT(DISTINCT pr.paymentID) AS refund_count, 
         SUM(pr.paymentAmount) AS refund_total
  FROM paymentRecords pr
  WHERE pr.paymentStatus = 'Refunded'
    AND pr.orderID IN (
      SELECT DISTINCT o.orderID
      FROM orders o
      JOIN orderitems oi ON o.orderID = oi.orderID
      JOIN products p ON oi.productID = p.productID
    )
    AND pr.paymentDate >= CURDATE() - INTERVAL ? DAY
";

$stmt = $conn->prepare($sqlRefunds);
$stmt->bind_param("i", $range);
$stmt->execute();
$refundResult = $stmt->get_result();
$refundRow = $refundResult->fetch_assoc();

$refundCount = (int)($refundRow['refund_count'] ?? 0);
$refundTotal = (float)($refundRow['refund_total'] ?? 0.0);

$balance = $grossSales - $refundTotal;


// Charges count and total
$sqlCharges = "
  SELECT COUNT(DISTINCT pr.paymentID) AS charge_count, 
         SUM(pr.paymentAmount) AS charge_total
  FROM paymentRecords pr
  JOIN orders o ON pr.orderID = o.orderID
  JOIN orderitems oi ON o.orderID = oi.orderID
  JOIN products p ON oi.productID = p.productID
  WHERE pr.paymentStatus = 'Paid'
    AND pr.paymentDate >= CURDATE() - INTERVAL ? DAY
";

$stmt = $conn->prepare($sqlCharges);
$stmt->bind_param("i",  $range);
$stmt->execute();
$chargeResult = $stmt->get_result();
$chargeRow = $chargeResult->fetch_assoc();

$chargeCount = (int)($chargeRow['charge_count'] ?? 0);
$chargeTotal = (float)($chargeRow['charge_total'] ?? 0.0);

$aov = ($totalOrders > 0) ? round($grossSales / $totalOrders, 2) : 0;

$sqlTotalUnits = "
  SELECT SUM(oi.quantity) AS total_units
  FROM orderitems oi
  JOIN orders o ON oi.orderID = o.orderID
  WHERE o.orderDate >= CURDATE() - INTERVAL ? DAY
";

$stmt = $conn->prepare($sqlTotalUnits);
$stmt->bind_param("i", $range);
$stmt->execute();
$unitResult = $stmt->get_result();
$unitRow = $unitResult->fetch_assoc();

$totalUnits = (int)($unitRow['total_units'] ?? 0);


echo json_encode([
  "labels" => $labels,
  "sales" => $sales,
  "gross" => $grossSales,
  "aov" => $aov, 
  "refunds" => [
    "count" => $refundCount,
    "total" => $refundTotal
  ],
  "charges" => [
    "count" => $chargeCount,
    "total" => $chargeTotal
  ],
  "balance" => $balance,
  "best_day" => $bestDay,
  "best_time" => $bestTime,
  "orders" => $totalOrders,
  "units" => $totalUnits,
  "buyers" => $totalBuyers,
  "top_products" => $topProducts
]);

exit;
?>
