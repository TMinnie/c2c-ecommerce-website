<?php
session_start();
include '../db.php';

if (!isset($_SESSION['userID'])) {
    header("Location: login.php");
    exit();
}

$userID = $_SESSION['userID'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newPassword = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];

    if (!empty($newPassword)) {
        if ($newPassword === $confirmPassword) {
            $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);

            $updateQuery = $conn->prepare("UPDATE users SET uPassword = ? WHERE userID = ?");
            $updateQuery->bind_param("si", $hashedPassword, $userID);
            $updateQuery->execute();

            $message = ($updateQuery->affected_rows > 0)
                ? "Password updated successfully!" 
                : "No changes were made.";
        } else {
            $message = "Passwords do not match.";
        }
    } else {
        $message = "Password cannot be empty.";
    }

    header("Location: ../account.php?page=security&status=success&message=". urlencode($message)); // Redirect to the profile page after update
    exit();
}
?>

<!-- security.php HTML -->
<!------------------------------------------------------------------------------------------------------------------------------------------------------->
<div class="card shadow-sm mt-4 mb-5" style="background-color: #F1F1F1;">
                <div class="card-body mb-0">
<div class="container my-4">
    <h3 class="mb-4">Security Settings</h3>
    <hr>

    <div class="card">
        <div class="card-body">
        <h5>Change Password</h5>            
        <hr>
            <form method="POST" action="account/security.php">
                <div class="mb-3">
                    <input type="password" class="form-control" id="password" name="password" placeholder="New Password" required>
                </div>
                <div class="mb-3">
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Confirm New Password" required>
                </div>
                <button type="submit" class="btn btn-primary">Update Password</button>
            </form>
        </div>
    </div>
</div>
</div>
</div>