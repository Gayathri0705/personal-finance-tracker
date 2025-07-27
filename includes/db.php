<?php
$host = "127.0.0.1";  // safer than 'localhost' for some XAMPP setups
$port = "3307";       // important change here
$user = "root";
$password = "";
$database = "finance_project";

$conn = mysqli_connect($host, $user, $password, $database, $port);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>