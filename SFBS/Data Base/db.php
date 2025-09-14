<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Database configuration
$host = "127.0.0.1";     // TCP connection
$db_name = "sports_booking";
$db_user = "root";   
$db_pass = "";  

try {
    // PDO connection
    $conn = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8", $db_user, $db_pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // echo "DB Connected"; // debug use korte paro
} catch(PDOException $e){
    die("Database Connection Failed: " . $e->getMessage());
}
?>
