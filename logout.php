<?php
/**
 * ShopNest - Logout Page
 * Destroy user session and redirect to home
 */

// Start session
session_start();

// Destroy all session data
session_unset();
session_destroy();

// Redirect to home page
header("Location: index.php");
exit();
?>

