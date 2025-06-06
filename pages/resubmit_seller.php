<?php
session_start();
require 'db.php'; // adjust path as needed

$userID = $_SESSION['userID']; // assuming user is logged in and userID is stored in session

$stmt = $conn->prepare("UPDATE sellers SET status = 'resubmit' WHERE userID = ?");
$stmt->bind_param("i", $userID);
$stmt->execute();
$stmt->close();

// Redirect to seller registration form
header("Location: sellerdash.php"); // change to your actual form path
exit;
