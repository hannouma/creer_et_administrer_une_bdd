<?php
require_once '../src/User.php';
require_once '../src/Movie.php';
require_once '../src/Session.php';

// Start the session with secure settings
session_start([
    'cookie_lifetime' => 86400, // 24 hours session lifetime
    'cookie_secure'   => true,  // Requires HTTPS
    'cookie_httponly' => true,  // Prevents client-side scripts from accessing cookies
    'use_strict_mode' => true   // Regenerates session ID on every request
]);

// Check if the user is logged in
if (isset($_SESSION['loggedInUser'])) {
    $loggedInUser = $_SESSION['loggedInUser'];
    $roles = $loggedInUser->getRoles();

    // Check if the user is an admin or a complex user
    if (in_array('ROLE_ADMIN', $roles) || in_array('COMPLEX_USER', $roles)) {
        // Redirect both admin and complex user to the moviesSessionsManaging.php page
        header('Location: moviesSessionsManaging.php');
        exit();
    } else {
        // Redirect to welcome.php  for users without required roles
        header('Location: welcome.php');
        exit();
    }
} else {
    // Redirect to login.php if not logged in
    header('Location: login.php');
    exit();
}
?>
