<?php
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $uFirst = trim($_POST['uFirst']); 
    $uLast = trim($_POST['uLast']);  
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];
    $hashed = password_hash($password, PASSWORD_DEFAULT);
    
    $_SESSION['signup-data'] = [
        'uFirst' => $_POST['uFirst'],
        'uLast' => $_POST['uLast'],
        'email' => $_POST['email']
        ];

    // Check if email exists
    $check = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows > 0) {
        $_SESSION['signup-error'] = "Email already registered.";
        header("Location: login.php");
        exit;
    }
    else{

        // Check if password and confirm password match 
        if (empty($password) || empty($confirm)) {
            $_SESSION['signup-error'] = "Please fill in all fields.";
            header("Location: login.php");
            exit;
        }
        elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/', $password)) {
            $_SESSION['signup-error'] = "Password must be at least 8 characters long and include uppercase, lowercase, number, and special character.";
            header("Location: login.php");
            exit;
        }
        elseif ($password !== $confirm) {
        $_SESSION['signup-error'] = "Passwords do not match.";
        header("Location: login.php");
        exit;
        }
    }

    $stmt = $conn->prepare("INSERT INTO users (uFirst, uLast, email, uPassword) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $uFirst, $uLast, $email, $hashed);

    if ($stmt->execute()) {
        $_SESSION['userID'] = $conn->insert_id;
        $_SESSION['uFirst'] = $uFirst;
        $_SESSION['uLast'] = $uLast;
        unset($_SESSION['signup-data']);
        header("Location: buyerdash.php");
        exit;
    } else {
        $_SESSION['signup-error'] = "Something went wrong.";
        header("Location: login.php");
    }
}
?>
