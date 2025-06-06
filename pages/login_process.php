<?php
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows == 1) {
        $user = $res->fetch_assoc();

        if (password_verify($password, $user['uPassword'])) {
            // Login successful
            $_SESSION['userID'] = $user['userID'];
            $_SESSION['uFirst'] = $user['uFirst'];
            $_SESSION['uLast'] = $user['uLast'];
            $_SESSION['role'] = $user['role'];

            // Redirect based on role
            switch ($user['role']) {
                case 'admin':
                    header("Location: admin/dashboard.php");
                    break;
                case 'seller':
                    header("Location: sellerdash.php");
                    break;
                case 'buyer':
                default:
                    header("Location: buyerdash.php");
                    break;
            }
            exit;
        }
    }

    // If credentials fail
    $_SESSION['login-error'] = "Invalid login credentials.";
    header("Location: login.php");
    exit;
}
?>

