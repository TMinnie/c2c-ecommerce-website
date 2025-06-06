<?php
session_start();  // Start the session

// Destroy all session variables
session_unset();

// Destroy the session itself
session_destroy();

// Redirect the user to the login page
header("Location: login.php");
exit;
?>
