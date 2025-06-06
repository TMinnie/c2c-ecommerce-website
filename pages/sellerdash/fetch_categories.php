<?php
include '../db.php'; // or your connection logic

$result = $conn->query("SELECT * FROM categories WHERE parentID IS NOT NULL ORDER BY parentID ASC");
$categories = [];

while ($row = $result->fetch_assoc()) {
    $categories[] = $row;
}

echo json_encode($categories);
?>