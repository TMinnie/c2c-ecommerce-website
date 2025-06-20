<?php
// Sanitize and validate the 'page' parameter to prevent path traversal or inclusion attacks
$page = isset($_GET['page']) ? $_GET['page'] : 'home';

// Whitelist of allowed pages and their corresponding file paths
$allowed_pages = [
    'home' => 'home.php',
    'orders' => 'orders.php',
    'order_details' => 'order_details.php',
    'reviews' => 'reviews.php',
    'profile' => 'profile.php',
    'security' => 'security.php',
    'buyer' => 'buyer_signup.php',
    'seller' => 'seller_signup.php',
    'address' => 'address.php',
    'feedback' => 'submit_feedback.php',
];

// If page requested is in whitelist, include it; else load default
if (array_key_exists($page, $allowed_pages)) {
    include $allowed_pages[$page];
} else {
    // Could also show a 404 error or redirect
    include $allowed_pages['home'];
}
