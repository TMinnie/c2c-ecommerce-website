<?php
require '../db.php'; // adjust path as needed
header('Content-Type: application/json');

$range = isset($_GET['range']) ? intval($_GET['range']) : 30;

// Initialize arrays
$labels = [];
$users = [];
$buyers = [];
$sellers = [];

$today = new DateTime();
$interval = new DateInterval("P1D");
$startDate = (clone $today)->sub(new DateInterval("P{$range}D"));
$period = new DatePeriod($startDate, $interval, $today->modify('+1 day'));

foreach ($period as $date) {
    $formatted = $date->format("Y-m-d");
    $labels[] = $formatted;

    // --- Cumulative Users ---
    $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE regDate <= ?");
    $stmt->bind_param("s", $formatted);
    $stmt->execute();
    $stmt->bind_result($userCount);
    $stmt->fetch();
    $users[] = $userCount ?: 0;
    $stmt->close();

    // --- Cumulative Buyers ---
    $stmt = $conn->prepare("SELECT COUNT(*) FROM buyers WHERE created_at <= ?");
    $stmt->bind_param("s", $formatted);
    $stmt->execute();
    $stmt->bind_result($buyerCount);
    $stmt->fetch();
    $buyers[] = $buyerCount ?: 0;
    $stmt->close();

    // --- Cumulative Sellers ---
    $stmt = $conn->prepare("SELECT COUNT(*) FROM sellers WHERE createdAt <= ?");
    $stmt->bind_param("s", $formatted);
    $stmt->execute();
    $stmt->bind_result($sellerCount);
    $stmt->fetch();
    $sellers[] = $sellerCount ?: 0;
    $stmt->close();
}

// Total active users
$stmt = $conn->query("SELECT COUNT(*) AS total_users FROM users WHERE status = 'active'");
$totalUsers = $stmt->fetch_assoc()['total_users'];

// Total buyers
$stmt = $conn->query("SELECT COUNT(*) AS total_buyers FROM buyers");
$totalBuyers = $stmt->fetch_assoc()['total_buyers'];

// Total sellers
$stmt = $conn->query("SELECT COUNT(*) AS total_sellers FROM sellers");
$totalSellers = $stmt->fetch_assoc()['total_sellers'];

$buyerRatio = $totalUsers > 0 ? round(($totalBuyers / $totalUsers) * 100, 2) : 0;
$sellerRatio = $totalUsers > 0 ? round(($totalSellers / $totalUsers) * 100, 2) : 0;

// Rejected sellers
$stmt = $conn->query("SELECT COUNT(*) AS rejected FROM sellers WHERE status = 'rejected'");
$rejectedSellers = $stmt->fetch_assoc()['rejected'];

// Inactive buyers
$stmt = $conn->query("
    SELECT COUNT(*) AS inactive_buyers
    FROM buyers b
    LEFT JOIN (
        SELECT buyerID, MAX(orderDate) AS last_order
        FROM orders
        GROUP BY buyerID
    ) o ON b.buyerID = o.buyerID
    WHERE o.buyerID IS NULL OR o.last_order < DATE_SUB(CURDATE(), INTERVAL 1 YEAR)
");
$inactiveBuyers = $stmt->fetch_assoc()['inactive_buyers'];

// Array to store stats
$stats = [];

// 1. Total Buyers
$sql = "SELECT COUNT(*) as count FROM buyers 
        JOIN users ON buyers.userID = users.userID 
        WHERE users.status = 'active'";
$stats['total_buyers'] = $conn->query($sql)->fetch_assoc()['count'];

// 2. Total Sellers
$sql = "SELECT COUNT(*) as count FROM sellers ";
$stats['total_sellers'] = $conn->query($sql)->fetch_assoc()['count'];


// 3. Active Sellers (has at least one active product)
$sql = "SELECT COUNT(DISTINCT s.sellerID) as count 
        FROM sellers s 
        JOIN products p ON s.sellerID = p.sellerID 
        WHERE p.status = 'active'";
$stats['active_sellers'] = $conn->query($sql)->fetch_assoc()['count'];

// 4. Inactive Sellers (has no active products)
$sql = "SELECT COUNT(*) as count 
        FROM sellers s 
        WHERE s.sellerID NOT IN (
            SELECT DISTINCT sellerID FROM products WHERE status = 'active'
        )";
$stats['inactive_sellers'] = $conn->query($sql)->fetch_assoc()['count'];


// 6. Pending Sellers
$sql = "SELECT COUNT(*) as count FROM sellers WHERE status = 'pending'";
$stats['pending_sellers'] = $conn->query($sql)->fetch_assoc()['count'];

// 7. Total (buyers + sellers)
$sql = "SELECT 
            (SELECT COUNT(*) FROM buyers) + 
            (SELECT COUNT(*) FROM sellers) as total";
$stats['total'] = $conn->query($sql)->fetch_assoc()['total'];

    
$totalUsers = $stats['total'];
$totalBuyers = $stats['total_buyers'];
$activeSellers = $stats['active_sellers'];
$inactiveSellers = $stats['inactive_sellers'];
$pendingSellers = $stats['pending_sellers'];

echo json_encode([
    "labels" => $labels,
    "users" => $users,
    "buyers" => $buyers,
    "sellers" => $sellers,
    "total_users" => $totalUsers,
    "total_buyers" => $buyerRatio,
    "total_sellers" => $sellerRatio,
    "rejected_sellers" => $rejectedSellers,
    "inactive_buyers" => $inactiveBuyers,
    "stats" => [
        "t_buyers" => $totalBuyers,
        "t_sellers" => $totalSellers,
        "active_sellers" => $activeSellers,
        "inactive_sellers" => $inactiveSellers,
        "pending_sellers" => $pendingSellers,
        "t_users" => $totalUsers
    ]
]);
?>
