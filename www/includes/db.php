<?php
// Database credentials
$servername = "localhost"; 
$username = "root";         
$password = "";             
$dbname = "lost_and_found"; 

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    $error_message = "Database Connection Failed: " . $conn->connect_error;
    echo "<script>console.error(" . json_encode($error_message) . ");</script>";
    die();
}
?>

