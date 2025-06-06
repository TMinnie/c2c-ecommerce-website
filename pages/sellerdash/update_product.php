<?php
session_start();
include '../db.php';

header('Content-Type: application/json');
ob_clean();

if (!isset($_SESSION['userID'])) {
    header("Location: ../login.php");
    exit;
}

if (!isset($_SESSION['sellerID'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$sellerID = $_SESSION['sellerID'];

// Validate required fields
$requiredFields = ['productID', 'pName', 'pDescription', 'pPrice'];
foreach ($requiredFields as $field) {
    if (!isset($_POST[$field])) {
        echo json_encode(['error' => "Missing field: $field"]);
        exit;
    }
}

// Sanitize inputs
$productID = (int)$_POST['productID'];
$pName = trim($_POST['pName']);
$pDescription = trim($_POST['pDescription']);
$pPrice = (float)$_POST['pPrice'];
$pCategory = !empty($_POST['pCategory']) ? $_POST['pCategory'] : null;

// Fetch current product details
$sql = "SELECT imagePath FROM products WHERE productID = ? AND sellerID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $productID, $sellerID);
$stmt->execute();
$result = $stmt->get_result();

if (!$row = $result->fetch_assoc()) {
    echo json_encode(['error' => 'Product not found.']);
    exit;
}

$imagePath = $row['imagePath']; // default to existing

// Handle file upload if provided
if (isset($_FILES['imagePath']) && $_FILES['imagePath']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = '../uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $fileTmpPath = $_FILES['imagePath']['tmp_name'];
    $fileName = basename($_FILES['imagePath']['name']);
    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    $allowedExts = ['jpg', 'jpeg', 'png', 'gif'];

    if (!in_array($fileExt, $allowedExts)) {
        echo json_encode(['error' => 'Invalid image format.']);
        exit;
    }

    $newFileName = uniqid('img_', true) . '.' . $fileExt;
    $destPath = $uploadDir . $newFileName;

    if (move_uploaded_file($fileTmpPath, $destPath)) {
        $imagePath = $newFileName;
    } else {
        echo json_encode(['error' => 'Error moving uploaded file.']);
        exit;
    }
}

// Update product details
$sql = "UPDATE products 
        SET pName = ?, pDescription = ?, pPrice = ?, pCategory = ?, imagePath = ? 
        WHERE productID = ? AND sellerID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssdisii", $pName, $pDescription, $pPrice, $pCategory, $imagePath, $productID, $sellerID);

if (!$stmt->execute()) {
    echo json_encode(['error' => 'Failed to update product.']);
    exit;
}

// Update product variants (stock quantities)
if (isset($_POST['variantIDs'], $_POST['stockQuantities'])) {
    $variantIDs = $_POST['variantIDs'];
    $stockQuantities = $_POST['stockQuantities'];

    for ($i = 0; $i < count($variantIDs); $i++) {
        $variantID = (int)$variantIDs[$i];
        $stockQuantity = (int)$stockQuantities[$i];

        $updateVariantSQL = "UPDATE product_variants 
                             SET stockQuantity = ? 
                             WHERE variantID = ? AND productID = ?";
        $variantStmt = $conn->prepare($updateVariantSQL);
        $variantStmt->bind_param("iii", $stockQuantity, $variantID, $productID);
        $variantStmt->execute();
    }
}

echo json_encode(['success' => true]);
?>
