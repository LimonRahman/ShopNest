<?php
/**
 * Database Connection File
 * Provides database connection for ShopNest e-commerce site
 */

$dbconnection = mysqli_connect("localhost", "root", "", "shopNest");
if (!$dbconnection) {
    die("Connection failed: " . mysqli_connect_error());
}
?>