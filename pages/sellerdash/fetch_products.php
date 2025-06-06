
<?php
session_start();
include '../db.php'; 

header('Content-Type: application/json');

if (!isset($_SESSION['sellerID'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$sellerID = $_SESSION['sellerID'];

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$status = isset($_GET['status']) ? $_GET['status'] : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : '';

$sql = "SELECT * FROM products WHERE sellerID = ?";  // include your own sellerID check
$params = [$sellerID];

if (!empty($search) && trim($search) !== '') {
    $sql .= " AND (pName LIKE ? OR pDescription LIKE ?)";
    $searchTerm = '%' . $search . '%';
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

if ($status !== '') {
  $sql .= " AND status = ?";
  $params[] = $status;
}

switch ($sort) {
  case 'price_asc': $sql .= " ORDER BY pPrice ASC"; break;
  case 'price_desc': $sql .= " ORDER BY pPrice DESC"; break;
  case 'created_asc': $sql .= " ORDER BY createdAt ASC"; break;
  case 'created_desc': $sql .= " ORDER BY createdAt DESC"; break;
  case 'stock_asc': $sql .= " ORDER BY stockQuantity ASC"; break;
  case 'stock_desc': $sql .= " ORDER BY stockQuantity DESC"; break;
  default: $sql .= " ORDER BY pName ASC"; break;
}

$stmt = $conn->prepare($sql);
if ($stmt === false) {
    echo json_encode(['error' => 'Prepare failed: ' . $conn->error]);
    exit;
}

// Dynamically bind parameters
$types = str_repeat('s', count($params));
$stmt->bind_param($types, ...$params);

$stmt->execute();
$result = $stmt->get_result();

$products = [];
while ($row = $result->fetch_assoc()) {
    $products[] = $row;
}
$stmt->close();

foreach ($products as &$product) {
  $productID = $product['productID'];

  $stmt = $conn->prepare("SELECT variantID, size, stockQuantity FROM product_variants WHERE productID = ?");
  $stmt->bind_param("i", $productID);
  $stmt->execute();
      $variants = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
  $stmt->close();
  $product['variants'] = $variants;
}

echo json_encode(
$products,
);

?>


