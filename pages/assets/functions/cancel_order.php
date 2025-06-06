<?php

require '../../db.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['orderID'])) {
    $orderID = $_POST['orderID'];
    $redirect = $_POST['redirect'];

    cancelOrder($conn, $orderID);

    header("Location: ../../". $redirect);
    exit();
} else {
    echo "Invalid request.";
}

function cancelOrder($conn, $orderID) {
    
    $conn->autocommit(false); // Start transaction manually

    try {
        // 1. Get current order status
        $stmt = $conn->prepare("SELECT orderStatus FROM orders WHERE orderID = ?");
        $stmt->bind_param("i", $orderID);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();

        if (!$row) {
            throw new Exception("Order not found.");
        }

        $status = strtolower($row['orderStatus']);

        if ($status == 'paid') {
            // Mark order as refunded
            $stmt = $conn->prepare("UPDATE orders SET orderStatus = 'Refunded' WHERE orderID = ?");
            $stmt->bind_param("i", $orderID);
            $stmt->execute();
            $stmt->close();

            // Get the original payment amount
            $stmt = $conn->prepare("SELECT paymentAmount FROM paymentrecords WHERE orderID = ? AND paymentStatus = 'Paid' LIMIT 1");
            $stmt->bind_param("i", $orderID);
            $stmt->execute();
            $result = $stmt->get_result();
            $payment = $result->fetch_assoc();
            $stmt->close();

            if (!$payment) {
                throw new Exception("Original payment record not found.");
            }

            // Insert a new payment record for the refund
            $stmt = $conn->prepare("INSERT INTO paymentrecords (orderID, paymentAmount, paymentStatus, paymentDate) VALUES (?, ?, 'Refunded', NOW())");
            $refundAmount = -1 * floatval($payment['paymentAmount']); // negative amount
            $stmt->bind_param("id", $orderID, $refundAmount);
            $stmt->execute();
            $stmt->close();


            // Get order items
            $stmt = $conn->prepare("SELECT productID, quantity, price FROM orderitems WHERE orderID = ?");
            $stmt->bind_param("i", $orderID);
            $stmt->execute();
            $result = $stmt->get_result();
            $items = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();

            foreach ($items as $item) {
                $productID = $item['productID'];
                $quantity = $item['quantity'];
                $price = $item['price'];

                // Get sellerID
                $stmt = $conn->prepare("SELECT sellerID FROM products WHERE productID = ?");
                $stmt->bind_param("i", $productID);
                $stmt->execute();
                $result = $stmt->get_result();
                $row = $result->fetch_assoc();
                $stmt->close();

                if (!$row) {
                    throw new Exception("Product not found: $productID");
                }

                $sellerID = $row['sellerID'];

                // Subtract from totalSales
                $amount = $quantity * $price;
                $stmt = $conn->prepare("UPDATE sellers SET totalSales = totalSales - ? WHERE sellerID = ?");
                $stmt->bind_param("di", $amount, $sellerID);
                $stmt->execute();
                $stmt->close();
            }
        } elseif ($status === 'pending') {
            // Mark order as cancelled
            $stmt = $conn->prepare("UPDATE orders SET orderStatus = 'Cancelled' WHERE orderID = ?");
            $stmt->bind_param("i", $orderID);
            $stmt->execute();
            $stmt->close();
        } else {
            throw new Exception("Cannot cancel order with status: $status");
        }

        $conn->commit();

    } catch (Exception $e) {
        $conn->rollback();
        echo "❌ Refund failed: " . $e->getMessage();
        exit();
    }

    $conn->autocommit(true);
}
?>
