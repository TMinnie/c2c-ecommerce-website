<?php
session_start();
include '../db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['sellerID'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$sellerID = $_SESSION['sellerID'];

$businessName = $_POST['businessName'] ?? '';
$pickupAddress = $_POST['pickupAddress'] ?? '';
$city = $_POST['city'] ?? '';
$payDetails = $_POST['payDetails'] ?? '';
$businessDescript = $_POST['businessDescript'] ?? '';

$imagePath = ''; // will store updated or existing image

// Fetch current imagePath
$sql = "SELECT imagePath FROM sellers WHERE sellerID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $sellerID);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $imagePath = $row['imagePath'];
}

// Handle image upload
if (isset($_FILES['imageFile']) && $_FILES['imageFile']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = 'uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $fileTmp = $_FILES['imageFile']['tmp_name'];
        $fileName = basename($_FILES['imageFile']['name']);
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));  // Get the file extension
        $allowed = ['jpg', 'jpeg', 'png'];

        if (!in_array($fileExt, $allowed)) {
            echo json_encode(['error' => 'Only JPG, JPEG, and PNG files are allowed.']);
            exit;       
        }

        // Generate new filename WITH extension
        $newFileName = uniqid('s_', true) . '.' . $fileExt;
        $targetFile = $uploadDir . $newFileName;

        if (move_uploaded_file($fileTmp, $targetFile)) {
            $imagePath = $newFileName;  // Save the filename with the extension
        } else {
            echo json_encode(['error' => 'Failed to upload image.']);
            exit;
        }
}

// Update seller info
$sql = "UPDATE sellers 
        SET businessName = ?, pickupAddress = ?, city = ?, payDetails = ?, businessDescript = ?, imagePath = ? 
        WHERE sellerID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssssssi", $businessName, $pickupAddress, $city, $payDetails, $businessDescript, $imagePath, $sellerID);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['error' => 'Failed to update seller info']);
}
?>
