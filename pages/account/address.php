<?php
session_start();
include '../db.php';

if (!isset($_SESSION['userID'])) {
    header("Location: login.php");
    exit();
}

$userID = $_SESSION['userID'];

// Fetch buyer info
$query = $conn->prepare("SELECT shippingAddress1, shippingAddress2, postalCode FROM buyers WHERE userID = ?");
$query->bind_param("i", $userID);
$query->execute();
$result = $query->get_result();

if ($result->num_rows === 0) {
    die("Buyer info not found.");
}

$buyer = $result->fetch_assoc();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $address1 = trim($_POST['address1']);
    $address2 = trim($_POST['address2']);
    $postalCode = trim($_POST['postalCode']);

    if (!empty($address1) && !empty($postalCode)) {
        $update = $conn->prepare("UPDATE buyers SET shippingAddress1 = ?, shippingAddress2 = ?, postalCode = ? WHERE userID = ?");
        $update->bind_param("sssi", $address1, $address2, $postalCode, $userID);
        $update->execute();

        if ($update->affected_rows > 0) {
            $message = "Address updated successfully!";
        } else {
            $message = "No changes were made.";
        }

    } else {
        $message = "Address line 1 and postal code are required.";
    }

    header("Location: ../account.php?page=address&status=success&message=". urlencode($message)); 
        exit();
}
?>

<!------------------------------------------------------------------------------------------------------------------------------------------------------->
<div class="card shadow-sm mt-4 mb-5" style="background-color: #F1F1F1;">
                <div class="card-body mb-0">
<div class="container my-4">
    <h3 class="mb-4">Address Details</h3>
    <hr>

    <div class="card">
        <div class="card-body">
        <h5>Update Address</h5>            
        <hr>
            <form method="POST" action="account/address.php">
                <div class="mb-3">
                    <label for="address1" class="form-label">Street Address</label>
                    <input type="text" class="form-control" id="address1" name="address1"
                        value="<?= htmlspecialchars($buyer['shippingAddress1']) ?>" required>
                </div>
                <div class="mb-3">
                    <label for="address2" class="form-label">City</label>
                    <input type="text" class="form-control" id="address2" name="address2"
                        value="<?= htmlspecialchars($buyer['shippingAddress2']) ?>">
                </div>
                <div class="mb-3">
                    <label for="postalCode" class="form-label">Postal Code</label>
                    <input type="text" class="form-control" id="postalCode" name="postalCode"
                        value="<?= htmlspecialchars($buyer['postalCode']) ?>" required>
                </div>
                <button type="submit" class="btn btn-primary">Update Address</button>
            </form>
        </div>
    </div>
</div>

</div>
</div>