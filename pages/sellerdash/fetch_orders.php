<?php
session_start();
require_once '../db.php';

header('Content-Type: application/json');

// --- Auth Check ---
if (!isset($_SESSION['userID'], $_SESSION['sellerID'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$sellerID = $_SESSION['sellerID'];
$validRanges = [7, 14, 30, 60, 90, 365];
$rangeDays = (isset($_GET['range']) && in_array((int)$_GET['range'], $validRanges)) ? (int)$_GET['range'] : 30;

$today = new DateTime();
$startDate = $today->sub(new DateInterval("P{$rangeDays}D"))->format('Y-m-d');

// --- Orders with Summary + Unit Count ---
$ordersQuery = "
    SELECT o.orderID, o.buyerID, o.orderDate, o.totalAmount, o.orderStatus,
           u.uFirst, u.uLast, SUM(oi.quantity) AS unitCount
    FROM orders o
    JOIN buyers b ON o.buyerID = b.buyerID
    JOIN users u ON b.userID = u.userID
    JOIN orderitems oi ON o.orderID = oi.orderID
    JOIN products p ON oi.productID = p.productID
    WHERE p.sellerID = ? AND o.orderDate >= ?
    GROUP BY o.orderID
    ORDER BY o.orderDate DESC
";
$stmt = $conn->prepare($ordersQuery);
$stmt->bind_param("is", $sellerID, $startDate);
$stmt->execute();
$result = $stmt->get_result();

$orders = [
    'new' => [],
    'accepted' => [],
    'shipped' => [],
    'completed' => []
];
$orderIDs = [];
$orderData = [];

$statusCounts = [
    'paid' => 0, 'accepted' => 0, 'dispatched' => 0,
    'completed' => 0, 'refunded' => 0, 'cancelled' => 0, 'pending' => 0
];

$OrdersCount = 0;
$UnitsCount = 0;
$totalAmountSum = 0;
$activeStatuses = ['paid', 'accepted', 'dispatched', 'completed'];

while ($row = $result->fetch_assoc()) {
    $orderID = $row['orderID'];
    $status = strtolower($row['orderStatus']);
    $row['unitCount'] = (int)$row['unitCount'];
    $orderIDs[] = $orderID;
    $orderData[$orderID] = $row;

    // Categorize
    switch ($status) {
        case 'paid': $orders['new'][] = $row; break;
        case 'accepted': $orders['accepted'][] = $row; break;
        case 'dispatched': $orders['shipped'][] = $row; break;
        case 'completed':
        case 'cancelled':
        case 'refunded':
        case 'pending':
            $orders['completed'][] = $row;
            break;
    }

    // Status count
    if (isset($statusCounts[$status])) $statusCounts[$status]++;

    // Aggregate
    if (in_array($status, $activeStatuses)) {
        $OrdersCount++;
        $UnitsCount += $row['unitCount'];
        $totalAmountSum += (float)$row['totalAmount'];
    }
}

$averageOrderValue = $OrdersCount > 0 ? round($totalAmountSum / $OrdersCount, 2) : 0;

// --- Fetch Order Items for These Orders ---
$orderItems = [];
if (!empty($orderIDs)) {
    $placeholders = implode(',', array_fill(0, count($orderIDs), '?'));
    $types = str_repeat('i', count($orderIDs));

    $itemQuery = "
        SELECT oi.orderID, oi.productID, p.pName, oi.size, oi.quantity, oi.price
        FROM orderitems oi
        JOIN products p ON oi.productID = p.productID
        WHERE oi.orderID IN ($placeholders)
    ";

    $stmt = $conn->prepare($itemQuery);
    $stmt->bind_param($types, ...$orderIDs);
    $stmt->execute();
    $itemResult = $stmt->get_result();

    while ($item = $itemResult->fetch_assoc()) {
        $orderID = $item['orderID'];
        $orderItems[$orderID][] = $item;
    }

    // Attach items to original order arrays
    foreach (['new', 'accepted', 'shipped', 'completed'] as $group) {
        foreach ($orders[$group] as &$o) {
            $o['items'] = $orderItems[$o['orderID']] ?? [];
        }
    }
}

// --- Unique & Repeat Customers ---
$ucQuery = "
    SELECT COUNT(DISTINCT o.buyerID) AS uniqueCount
    FROM orders o
    JOIN orderitems oi ON o.orderID = oi.orderID
    JOIN products p ON oi.productID = p.productID
    WHERE p.sellerID = ? AND o.orderDate >= ?
";
$stmt = $conn->prepare($ucQuery);
$stmt->bind_param("is", $sellerID, $startDate);
$stmt->execute();
$ucResult = $stmt->get_result();
$uniqueCustomers = $ucResult->fetch_assoc()['uniqueCount'] ?? 0;

$rcQuery = "
    SELECT COUNT(*) AS repeatCount
    FROM (
        SELECT o.buyerID
        FROM orders o
        JOIN orderitems oi ON o.orderID = oi.orderID
        JOIN products p ON oi.productID = p.productID
        WHERE p.sellerID = ? AND o.orderDate >= ?
        GROUP BY o.buyerID
        HAVING COUNT(DISTINCT o.orderID) > 1
    ) AS repeatBuyers
";
$stmt = $conn->prepare($rcQuery);
$stmt->bind_param("is", $sellerID, $startDate);
$stmt->execute();
$repeatCustomers = $stmt->get_result()->fetch_assoc()['repeatCount'] ?? 0;

$repeatPercentage = $uniqueCustomers > 0
    ? round(($repeatCustomers / $uniqueCustomers) * 100, 2)
    : 0;

// --- Fulfillment & Delivery Time ---
$fulfillmentQuery = "
    SELECT AVG(TIMESTAMPDIFF(HOUR, o.orderDate, d.dispatchDate)) AS avgFulfillmentHours,
           AVG(TIMESTAMPDIFF(HOUR, o.orderDate, d.deliveryDate)) AS avgDeliverHours
    FROM orders o
    JOIN deliveries d ON o.orderID = d.orderID
    JOIN orderitems oi ON o.orderID = oi.orderID
    JOIN products p ON oi.productID = p.productID
    WHERE p.sellerID = ? AND o.orderDate >= ? AND d.dispatchDate IS NOT NULL AND d.deliveryDate IS NOT NULL
";
$stmt = $conn->prepare($fulfillmentQuery);
$stmt->bind_param("is", $sellerID, $startDate);
$stmt->execute();
$timing = $stmt->get_result()->fetch_assoc();
$avgFulfillmentHours = round((float)$timing['avgFulfillmentHours'], 2);
$avgDeliverHours = round((float)$timing['avgDeliverHours'], 2);

// --- Orders Per Day ---
$dayQuery = "
    SELECT DATE(o.orderDate) AS orderDay, COUNT(*) AS orderCount
    FROM orders o
    JOIN orderitems oi ON o.orderID = oi.orderID
    JOIN products p ON oi.productID = p.productID
    WHERE p.sellerID = ? AND o.orderDate >= ?
    GROUP BY orderDay
    ORDER BY orderDay ASC
";
$stmt = $conn->prepare($dayQuery);
$stmt->bind_param("is", $sellerID, $startDate);
$stmt->execute();
$dayResult = $stmt->get_result();

$rawData = [];
$start = null;
$end = null;
while ($row = $dayResult->fetch_assoc()) {
    $date = $row['orderDay'];
    $rawData[$date] = (int)$row['orderCount'];
    if (!$start || $date < $start) $start = $date;
    if (!$end || $date > $end) $end = $date;
}

$ordersPerDay = [];
$current = new DateTime($startDate);
$endDateObj = new DateTime($end);
while ($current <= $endDateObj) {
    $d = $current->format('Y-m-d');
    $ordersPerDay[] = [
        'date' => $d,
        'count' => $rawData[$d] ?? 0
    ];
    $current->modify('+1 day');
}

// --- Final JSON Response ---
$response = [
    'orders' => $orders,
    'counts' => $statusCounts,
    'OrdersCount' => $OrdersCount,
    'UnitsCount' => $UnitsCount,
    'aov' => $averageOrderValue,
    'uniqueCustomers' => $uniqueCustomers,
    'repeatPercentage' => $repeatPercentage,
    'dateRangeDays' => $rangeDays,
    'avgFulfillmentHours' => $avgFulfillmentHours,
    'avgDeliverHours' => $avgDeliverHours,
    'ordersPerDay' => $ordersPerDay
];

echo json_encode($response);
?>