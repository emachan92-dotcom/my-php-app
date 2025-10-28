<?php
$servername = "10.0.2.4";
$username   = "webapp";
$password   = "Nakaema202510";
$dbname     = "myappdb";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>

