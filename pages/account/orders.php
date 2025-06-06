<?php
include '../db.php';
session_start();

$userID = $_SESSION['userID'] ?? null;
if (!$userID) {
    die("User not logged in.");
}

// Get buyerID
$buyerStmt = $conn->prepare("SELECT buyerID FROM buyers WHERE userID = ?");
$buyerStmt->bind_param("i", $userID);
$buyerStmt->execute();
$buyerResult = $buyerStmt->get_result();

if ($buyerResult->num_rows === 0) {
    die("Buyer not found.");
}
$buyerID = $buyerResult->fetch_assoc()['buyerID'];

// Pagination variables
$limit = 5;
$currentSection = $_GET['page'] ?? 'orders';
$orderPage = isset($_GET['orderPage']) && is_numeric($_GET['orderPage']) ? intval($_GET['orderPage']) : 1;
$offset = ($orderPage - 1) * $limit;

// 1. Get total number of orders (needed for pagination)
$totalQuery = $conn->prepare("SELECT COUNT(*) as total FROM orders WHERE buyerID = ?");
$totalQuery->bind_param("i", $buyerID);
$totalQuery->execute();
$totalResult = $totalQuery->get_result()->fetch_assoc();
$totalOrders = $totalResult['total'];
$totalPages = ceil($totalOrders / $limit);

// 2. Get orders for current page with LIMIT/OFFSET
$orderIDsStmt = $conn->prepare("SELECT orderID FROM orders WHERE buyerID = ? ORDER BY orderDate DESC, orderID DESC LIMIT ? OFFSET ?");
$orderIDsStmt->bind_param("iii", $buyerID, $limit, $offset);
$orderIDsStmt->execute();
$orderIDsResult = $orderIDsStmt->get_result();

$orderIDs = [];
while ($row = $orderIDsResult->fetch_assoc()) {
    $orderIDs[] = $row['orderID'];
}

if (count($orderIDs) > 0) {
    $placeholders = implode(',', array_fill(0, count($orderIDs), '?'));
    $types = str_repeat('i', count($orderIDs));

    $query = "
        SELECT 
            o.orderID,
            o.orderDate,
            o.totalAmount,
            o.deliveryFee,
            o.orderStatus,
            oi.productID,
            p.pName,
            p.imagePath
        FROM orders o
        JOIN orderitems oi ON o.orderID = oi.orderID
        JOIN products p ON oi.productID = p.productID
        WHERE o.orderID IN ($placeholders)
        ORDER BY o.orderDate DESC, o.orderID DESC
    ";

    $stmt = $conn->prepare($query);
    $stmt->bind_param($types, ...$orderIDs);
    $stmt->execute();
    $result = $stmt->get_result();

    $orders = [];
    while ($row = $result->fetch_assoc()) {
        $orderID = $row['orderID'];
        if (!isset($orders[$orderID])) {
            $orders[$orderID] = [
                'orderDate' => $row['orderDate'],
                'totalAmount' => $row['totalAmount'],
                'deliveryFee' => $row['deliveryFee'],
                'orderStatus' => $row['orderStatus'],
                'items' => []
            ];
        }
        $orders[$orderID]['items'][] = [
            'productID' => $row['productID'],
            'pName' => $row['pName'],
            'imagePath' => $row['imagePath']
        ];
    }
} else {
    $orders = [];
}
?>

<!-- HTML Section -->
<div class="card shadow-sm mt-4 mb-5" style="background-color: #F1F1F1;">
    <div class="card-body mb-0">
        <div class="container my-4">
            <h3 class="mb-4">Orders</h3>
            <hr>
            <div class="row g-3">
                <?php if (!empty($orders)): ?>
                    <?php foreach ($orders as $orderID => $order): ?>
                        <div class="col-md-12 col-lg-12">
                            <div class="card h-100 shadow-sm">
                                <div class="card-body d-flex flex-column shadow-sm p-4">
                                    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">

                                        <h5 class="mb-0">
                                            Order #<?= $orderID ?> |
                                            Ordered <?= date('Y-m-d', strtotime($order['orderDate'])) ?> |
                                            <span class="badge 
                                                <?= ($order['orderStatus'] == 'shipped' || $order['orderStatus'] == 'completed') 
                                                    ? 'bg-success' 
                                                    : (($order['orderStatus'] == 'pending') ? 'bg-warning text-dark' : 'bg-secondary') ?>">
                                                <?= ucfirst($order['orderStatus']) ?>
                                            </span>
                                        </h5>
                                        <a href="javascript:void(0)" id="view-btn" class=" account-link btn btn-sm btn-primary"
                                           onclick="loadContent('order_details&orderID=<?= $orderID ?>')">
                                            View Order
                                        </a>
                                    </div>
                                    <hr>
                                    <div class="d-flex flex-wrap gap-3 justify-content-start">
                                        <?php foreach ($order['items'] as $item): ?>
                                            <div class="me-3 text-center mb-2" style="flex: 1 1 100px; max-width: 100px;">

                                                <img src="uploads/<?= htmlspecialchars($item['imagePath']) ?>" alt="<?= htmlspecialchars($item['pName']) ?>"
                                                     style="width: 100px; height: 100px; object-fit: cover;">   
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="alert alert-info" role="alert">No orders found.</div>
                <?php endif; ?>
            </div>

            <!-- Pagination Controls -->
            <?php if ($totalPages > 1): ?>
                <nav aria-label="Page navigation">
                    <ul class="pagination justify-content-center mt-4">
                        <?php if ($orderPage > 1): ?>
                            <li class="page-item">
                                <a href="#" class="page-link" data-page="orders" data-orderpage="<?= $orderPage - 1 ?>">Previous</a>
                            </li>
                        <?php endif; ?>

                        <?php
                        $startPage = max(1, $orderPage - 2);
                        $endPage = min($totalPages, $orderPage + 2);

                        for ($i = $startPage; $i <= $endPage; $i++): ?>
                            <li class="page-item <?= ($i == $orderPage) ? 'active' : '' ?>">
                                <a href="#" class="page-link" data-page="orders" data-orderpage="<?= $i ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>

                        <?php if ($orderPage < $totalPages): ?>
                            <li class="page-item">
                                <a href="#" class="page-link" data-page="orders" data-orderpage="<?= $orderPage + 1 ?>">Next</a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
function loadContent(params) {
    const xhr = new XMLHttpRequest();
    xhr.open('GET', 'load_account.php?' + params, true);
    xhr.onload = function () {
        if (xhr.status === 200) {
            document.getElementById('dynamic-content').innerHTML = xhr.responseText;
        } else {
            console.error('Failed to load content:', xhr.status);
        }
    };
    xhr.send();
}

document.addEventListener('DOMContentLoaded', function () {
    // Load initial content based on URL params (if you want to auto-load on page load)
    const urlParams = new URLSearchParams(window.location.search);
    const page = urlParams.get('page') || 'orders';
    const orderPage = urlParams.get('orderPage') || '1';
    loadContent(`${page}&orderPage=${orderPage}`);

    // Delegate click events for pagination links
    document.body.addEventListener('click', function (e) {
        const target = e.target;
        if (target.tagName === 'A' && target.classList.contains('page-link')) {
            e.preventDefault();

            const page = target.dataset.page || 'orders';
            const orderPage = target.dataset.orderpage || '1';

            loadContent(`${page}&orderPage=${orderPage}`);

            // Update URL without reloading page
            history.pushState(null, '', `?page=${page}&orderPage=${orderPage}`);
        }
    });

    // Handle browser back/forward buttons for AJAX navigation
    window.addEventListener('popstate', function () {
        const urlParams = new URLSearchParams(window.location.search);
        const page = urlParams.get('page') || 'orders';
        const orderPage = urlParams.get('orderPage') || '1';
        loadContent(`${page}&orderPage=${orderPage}`);
    });
});
</script>
