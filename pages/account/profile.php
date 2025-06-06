<?php
session_start();
include '../db.php';  // Make sure this path is correct to include your DB connection file

// Check if the user is logged in
if (!isset($_SESSION['userID'])) {
    header("Location: login.php");
    exit();
}

$userID = $_SESSION['userID'];

// Fetch user data from the database
$userQuery = $conn->prepare("SELECT * FROM users WHERE userID = ?");
$userQuery->bind_param("i", $userID);
$userQuery->execute();
$userResult = $userQuery->get_result();

// Check if the user exists in the database
if ($userResult->num_rows === 0) {
    die("User not found.");
}

$user = $userResult->fetch_assoc();

// Handle profile update (e.g., update email or password)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newEmail = $_POST['email'];
    $fname = $_POST['fname'];
    $lname = $_POST['lname'];

    if (filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
        $updateQuery = $conn->prepare("UPDATE users SET uFirst = ?, uLast = ?, email = ? WHERE userID = ?");
        $updateQuery->bind_param("sssi", $fname, $lname, $newEmail, $userID);
        $updateQuery->execute();

        $message = ($updateQuery->affected_rows > 0) 
            ? "Profile updated successfully!" 
            : "No changes were made.";
    } else {
        $message = "Invalid email format.";
    }

    header("Location: ../account.php?page=profile&status=success&message=". urlencode($message)); // Redirect to the profile page after update
    exit();
}
?>

<!------------------------------------------------------------------------------------------------------------------------------------------------------->

<div class="card shadow-sm mt-4 mb-5" style="background-color: #F1F1F1;">
                <div class="card-body mb-0">
<div class="container my-4">
    <h3 class="mb-4">Personal Details</h3>
    <hr>

    <div class="card">
        <div class="card-body">

        <h5>Edit profile</h5>            
            <hr>
            <form method="POST" action="account/profile.php">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="fname" class="form-label">First Name</label>
                        <input type="text" class="form-control" id="fname" name="fname" value="<?= htmlspecialchars($user['uFirst']) ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label for="lname" class="form-label">Last Name</label>
                        <input type="text" class="form-control" id="lname" name="lname" value="<?= htmlspecialchars($user['uLast']) ?>" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                </div>
                
                <button type="submit" class="btn btn-primary">Update Profile</button>
            </form>
        </div>
    </div>
</div>
</div>
</div>