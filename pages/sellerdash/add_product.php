<?php
session_start();
header('Content-Type: application/json');


include '../db.php';

if (!isset($_SESSION['userID'])) {
    header("Location: ../login.php");
    exit;
}

if (!isset($_SESSION['sellerID'])) {
  echo json_encode(['error' => 'Unauthorized']);
  exit;
}

$sellerID = $_SESSION['sellerID'];

$pName = htmlspecialchars($_POST['pName']);
$pDescription = htmlspecialchars($_POST['pDescription']);
$pPrice = floatval($_POST['pPrice']);
$pCategory = htmlspecialchars($_POST['pCategory']);

// Handle file upload
$imagePath = '';
if (isset($_FILES['imagePath']) && $_FILES['imagePath']['error'] === UPLOAD_ERR_OK) {
    $imageTmpPath = $_FILES['imagePath']['tmp_name'];
    $imageName = $_FILES['imagePath']['name'] ?? 'placeholder.png';

    // Set the path where the image will be stored
    $uploadsDir = '../uploads/';
    $imagePath = $uploadsDir . basename($imageName);

    if (move_uploaded_file($imageTmpPath, $imagePath)) {
        // File uploaded successfully
    } else {
        echo json_encode(['error' => 'Error uploading image.']);
        exit;
    }
} else {
    echo json_encode(['error' => 'No image uploaded or error occurred.']);
    exit;
}

// Insert product into database
$sql = "INSERT INTO products (sellerID, pName, pDescription, pPrice, imagePath, pCategory) 
        VALUES (?, ?, ?, ?, ?,  ?)";
$stmt = $conn->prepare($sql);

if ($stmt) {
    $stmt->bind_param("issdss", $sellerID, $pName, $pDescription, $pPrice, $imageName,  $pCategory);

    if ($stmt->execute()) {

        $productID = $stmt->insert_id;
        // Handle variants
        $sizes = isset($_POST['sizes']) ? $_POST['sizes'] : [];
$quantities = isset($_POST['quantities']) ? $_POST['quantities'] : [];

$variantStmt = $conn->prepare("INSERT INTO product_variants (productID, size, stockQuantity) VALUES (?, ?, ?)");

if (!$variantStmt) {
    echo json_encode(['error' => 'Failed to prepare variant insert.']);
    exit;
}

// Check if valid variants submitted
if (!empty($sizes) && !empty($quantities) && count($sizes) === count($quantities)) {
    $validVariantInserted = false;
    for ($i = 0; $i < count($sizes); $i++) {
        $size = trim($sizes[$i]);
        $qty = intval($quantities[$i]);

        if ($size !== "" && $qty >= 0) {
            $variantStmt->bind_param("isi", $productID, $size, $qty);
            $variantStmt->execute();
            $validVariantInserted = true;
        }
    }

    // If no valid variant inserted, insert default
    if (!$validVariantInserted) {
        $defaultSize = "One Size";
        $defaultQty = 0;
        $variantStmt->bind_param("isi", $productID, $defaultSize, $defaultQty);
        $variantStmt->execute();
    }
} else {
    // No variants submitted or arrays don't match, insert default
    $defaultSize = "One Size";
    $defaultQty = 0;
    $variantStmt->bind_param("isi", $productID, $defaultSize, $defaultQty);
    $variantStmt->execute();
}
        echo json_encode(['success' => true]);
        exit;

    } else {
        echo json_encode(['error' => 'Error adding product.']);
    }
} else {
    echo json_encode(['error' => 'Failed to prepare statement.']);
}



?>
