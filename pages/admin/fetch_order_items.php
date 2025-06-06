<?php
require_once '../db.php';

$orderID = $_GET['orderID'] ?? null;

if (!$orderID) {
    echo "<p>Invalid order ID.</p>";
    exit;
}

$stmt = $conn->prepare("
    SELECT p.productID, p.pName, oi.quantity, oi.price, (oi.quantity * oi.price) AS subtotal
    FROM orderitems oi
    JOIN products p ON oi.productID = p.productID
    WHERE oi.orderID = ?
");
$stmt->bind_param("i", $orderID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0): ?>
    <table class='table table-bordered'>
        <thead>
            <tr>
                <th>Product ID</th>
                <th>Product Name</th>
                <th>Quantity</th>
                <th>Price (each)</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($item = $result->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($item['productID']) ?></td>
                <td><?= htmlspecialchars($item['pName']) ?></td>
                <td><?= $item['quantity'] ?></td>
                <td>R<?= number_format($item['price'], 2) ?></td>
                <td>R<?= number_format($item['subtotal'], 2) ?></td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>No items found for this order.</p>
<?php endif; ?>
