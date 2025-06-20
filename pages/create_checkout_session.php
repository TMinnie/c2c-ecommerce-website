<?php
require_once __DIR__ . '/../stripe-php-master/init.php';
\Stripe\Stripe::setApiKey('sk_test_51RQOlhP5EnF2nJIgS4zx9eNrZqqolx9sx5JkdhHdeZTXcVkK2O7a95XvdZjvigNmetGtW85o5xpgKdEUSLAde09700vGYaYVtx');

session_start();
require_once 'db.php';

if (!isset($_SESSION['newOrders']) || empty($_SESSION['newOrders'])) {
    die("No orders found.");
}

$newOrders = $_SESSION['newOrders'];
$total = 0;

// Calculate total
foreach ($newOrders as $o) {
    $stmt = $conn->prepare("SELECT deliveryFee, totalAmount FROM orders WHERE orderID = ?");
    $stmt->bind_param("i", $o['orderID']);
    $stmt->execute();
    $orderDetails = $stmt->get_result()->fetch_assoc();
    $total += $orderDetails['deliveryFee'] + $orderDetails['totalAmount'];
}

// Prepare order IDs as query string
$orderIDs = array_column($newOrders, 'orderID');
$orderIDQuery = implode(',', $orderIDs);

// Convert to cents (Stripe uses smallest currency unit)
$amount = intval($total * 100); 

// Create checkout session
$session = \Stripe\Checkout\Session::create([
    //'payment_method_types' => ['card'],
    'line_items' => [[
        'price_data' => [
            'currency' => 'zar',
            'product_data' => [
                'name' => 'Your TukoCart Order',
            ],
            'unit_amount' => $amount,
        ],
        'quantity' => 1,
    ]],
    'mode' => 'payment',
    'success_url' => 'http://localhost/tukocart/pages/order_success.php?orderIDs=' . $orderIDQuery,
    'cancel_url' => 'http://localhost/tukocart/pages/order_cancel.php',
]);

header("Location: " . $session->url);
exit();
