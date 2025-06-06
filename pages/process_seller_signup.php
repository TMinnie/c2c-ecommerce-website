<?php
session_start();
include 'db.php';

// Check if user is logged in
    if (!isset($_SESSION['userID'])) {
        $_SESSION['error'] = 'You must be logged in to register as a seller.';
        header('Location: login.php');
        exit;
    }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userID = $_SESSION['userID'];
    $businessName = $_POST['businessName'] ?? '';
    $payDetails = $_POST['payDetails'] ?? '';
    $businessDescript = $_POST['businessDescript'] ?? '';
    $pickupAddress = $_POST['pickupAddress'] ?? '';
    $city = $_POST['city'] ?? '';
    $postalCode = $_POST['postalCode'] ?? '';
    $imagePath = 'placeholder.png';
    $govIDPath = null;
    $proofOfAddressPath = null;

    // Handle business image upload
    if (isset($_FILES['imageFile']) && $_FILES['imageFile']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $fileTmp = $_FILES['imageFile']['tmp_name'];
        $fileName = basename($_FILES['imageFile']['name']);
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png'];

        if (!in_array($fileExt, $allowed)) {
            $message = 'Only JPG, JPEG, and PNG files are allowed for business image.';
            header('Location: sellerdash.php?status=error&message='. urlencode($message));
            exit;
        }

        $newFileName = uniqid('biz_', true) . '.' . $fileExt;
        $targetFile = $uploadDir . $newFileName;

        if (move_uploaded_file($fileTmp, $targetFile)) {
            $imagePath = $newFileName;
        } else {
            $message = 'Failed to upload business image.';
            header('Location: sellerdash.php?status=error&message='. urlencode($message));
            exit;
        }
    }

    // Handle government ID and proof of address uploads (as array)
    if (isset($_FILES['registrationDoc']['name']) && count($_FILES['registrationDoc']['name']) === 2) {
        $allowed = ['jpg', 'jpeg', 'png', 'pdf'];
        $uploadDir = 'uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        for ($i = 0; $i < 2; $i++) {
            $tmpName = $_FILES['registrationDoc']['tmp_name'][$i];
            $fileName = basename($_FILES['registrationDoc']['name'][$i]);
            $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

            if (!in_array($fileExt, $allowed)) {
                $message= 'Only PDF, JPG, JPEG, and PNG files are allowed for documents.';
                header('Location: sellerdash.php?status=error&message='. urlencode($message));
                exit;
            }

            $newFileName = uniqid(($i === 0 ? 'id_' : 'proof_'), true) . '.' . $fileExt;
            $targetPath = $uploadDir . $newFileName;

            if (!move_uploaded_file($tmpName, $targetPath)) {
                $message = 'Failed to upload required documents.';
                header('Location: sellerdash.php?status=error&message='. urlencode($message));
                exit;
            }

            if ($i === 0) {
                $govIDPath = $newFileName;
            } else {
                $proofOfAddressPath = $newFileName;
            }
        }
    } else {
        $message = 'Both ID and Proof of Address documents are required.';
        header('Location: sellerdash.php?status=error&message='. urlencode($message));
        exit;
    }


    // Check if seller already exists
    $check = $conn->prepare("SELECT * FROM sellers WHERE userID = ?");
    $check->bind_param("i", $userID);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows === 0) {
        // No seller found — INSERT
        $stmt = $conn->prepare("INSERT INTO sellers (userID, businessName, businessDescript, payDetails, pickupAddress, city, postalCode, imagePath, status, giid, poa) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending',?,?)");
        $stmt->bind_param("isssssssss", $userID, $businessName, $businessDescript, $payDetails, $pickupAddress, $city, $postalCode, $imagePath, $govIDPath, $proofOfAddressPath );
        $stmt->execute();
        $_SESSION['sellerID'] = $conn->insert_id;
    } else {
        // Seller found — UPDATE (assume resubmission)
        $sellerData = $result->fetch_assoc();
        $sellerID = $sellerData['sellerID'];

        $updateQuery = "UPDATE sellers SET businessName = ?, businessDescript = ?, payDetails = ?, pickupAddress = ?, city = ?, postalCode = ?, giid = ?, poa = ?, status = 'pending'";
        if ($imagePath) {
            $updateQuery .= ", imagePath = ?";
        }
        $updateQuery .= " WHERE sellerID = ?";

        if ($imagePath) {
            $stmt = $conn->prepare($updateQuery);
            $stmt->bind_param("sssssssssi", $businessName, $businessDescript, $payDetails, $pickupAddress, $city, $postalCode, $govIDPath, $proofOfAddressPath, $imagePath, $sellerID );
        } else {
            $stmt = $conn->prepare($updateQuery);
            $stmt->bind_param("ssssssssi", $businessName, $businessDescript, $payDetails, $pickupAddress, $city, $postalCode, $govIDPath, $proofOfAddressPath, $sellerID);
        }

        $_SESSION['sellerID'] = $sellerID;
    }

    if ($stmt->execute()) {
        $message = 'Your seller profile is submitted! Please wait for admin approval.';
    } else {
        $message = 'Something went wrong. Please try again.';
    }

    $stmt->close();
    $conn->close();

    header('Location: sellerdash.php?status=error&message='. urlencode($message));
    exit;
} else {
    $_SESSION['error'] = 'Invalid request.';
    header('Location: buyerdash.php');
    exit;
}