<?php
$servername = "localhost";          
$dbname = "223573";
$username = "223573";             
$password = "3R0CfizL";          


$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error){
    die("Database error: " . $conn->connect_error);
}

if ($conn) {
    $conn->query("SET time_zone = '+01:00'");
}

$conn->set_charset("utf8mb4");
?>

