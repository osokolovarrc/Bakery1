<?php
session_start(); // Start the session to work with session variables
define('ADMIN_LOGIN', 'wally');
define('ADMIN_PASSWORD', 'mypass');

// Check if user is authenticated
if (!isset($_SERVER['PHP_AUTH_USER']) || !isset($_SERVER['PHP_AUTH_PW'])
    || ($_SERVER['PHP_AUTH_USER'] != ADMIN_LOGIN)
    || ($_SERVER['PHP_AUTH_PW'] != ADMIN_PASSWORD)) {

    // If the user is not authenticated, send a 401 Unauthorized response
    header('HTTP/1.1 401 Unauthorized');
    header('WWW-Authenticate: Basic realm="Our Blog"');
    exit("Access Denied: Username and password required.");
} else {
    $_SESSION['user_id'] = 'admin';
    // If authentication passes, redirect to products.php
    header('Location: products.php');
    exit();
}
?>

