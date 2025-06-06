<?php

// Get buyer info
$stmt = $conn->prepare("SELECT buyerID FROM buyers WHERE userID = ?");
$stmt->bind_param("i", $userID);
$stmt->execute();
$buyerData = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$buyerData) {
    die("Buyer not found.");
}

$buyerID = $buyerData['buyerID'];

    try {
        $sellerTotals = [];

        foreach ($orderIDs as $orderID) {
            // Validate order
            $stmt = $conn->prepare("
                SELECT totalAmount, deliveryFee, orderStatus 
                FROM orders 
                WHERE orderID = ? AND buyerID = ?
            ");
            $stmt->bind_param("ii", $orderID, $buyerID);
            $stmt->execute();
            $order = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if (!$order) {
                throw new Exception("Order $orderID not found or does not belong to you.");
            }

            if ($order['orderStatus'] === 'Paid') {
                continue;
            }

            // Mark as paid
            $stmt = $conn->prepare("UPDATE orders SET orderStatus = 'Paid' WHERE orderID = ?");
            $stmt->bind_param("i", $orderID);
            $stmt->execute();
            $stmt->close();

            // Update stock and track seller totals
            $stmt = $conn->prepare("
                SELECT oi.productID, oi.quantity, oi.price, p.sellerID 
                FROM orderitems oi
                JOIN products p ON oi.productID = p.productID
                WHERE oi.orderID = ?
            ");
            $stmt->bind_param("i", $orderID);
            $stmt->execute();
            $items = $stmt->get_result();

            while ($item = $items->fetch_assoc()) {
                $productID = $item['productID'];
                $quantity = $item['quantity'];
                $sellerID = $item['sellerID'];
                $itemTotal = $item['price'] * $quantity;

                // Reduce stock
                $updateStock = $conn->prepare("
                    UPDATE products 
                    SET stockQuantity = stockQuantity - ? 
                    WHERE productID = ? AND stockQuantity >= ?
                ");
                $updateStock->bind_param("iii", $quantity, $productID, $quantity);
                $updateStock->execute();

                if ($updateStock->affected_rows === 0) {
                    throw new Exception("Insufficient stock for product ID: $productID");
                }
                $updateStock->close();

                // Tally seller sales
                if (!isset($sellerTotals[$sellerID])) {
                    $sellerTotals[$sellerID] = 0;
                }
                $sellerTotals[$sellerID] += $itemTotal;
            }
            $stmt->close();

            // Payment record
            $deliveryFee = $order['deliveryFee'] ?? 0;
            $paidAmount = $order['totalAmount'] + $deliveryFee;

            $stmt = $conn->prepare("
                INSERT INTO paymentrecords (orderID, paymentDate, paymentStatus, paymentAmount)
                VALUES (?, NOW(), 'Paid', ?)
            ");
            $stmt->bind_param("id", $orderID, $paidAmount);
            $stmt->execute();
            $stmt->close();
        }

        // Update sellers' total sales
        foreach ($sellerTotals as $sellerID => $amount) {
            $stmt = $conn->prepare("UPDATE sellers SET totalSales = totalSales + ? WHERE sellerID = ?");
            $stmt->bind_param("di", $amount, $sellerID);
            $stmt->execute();
            $stmt->close();
        }

        // Clear cart BEFORE redirect
        clearBuyerCart($buyerID);

        $conn->commit();
        $ids = implode(',', $orderIDs);
        exit();

    } catch (Exception $e) {
        $conn->rollback();
        die("Checkout failed: " . htmlspecialchars($e->getMessage()));
    }

// Function: clear cart
function clearBuyerCart($buyerID) {
    global $conn;
    $stmt = $conn->prepare("
        DELETE ci FROM cartitems ci
        JOIN carts c ON ci.cartID = c.cartID
        WHERE c.buyerID = ?
    ");
    $stmt->bind_param("i", $buyerID);
    $stmt->execute();
    $stmt->close();
}
?>
