<?php
session_start();
include "includes/db.php";

if (!isset($_SESSION['username'])) {
    http_response_code(403);
    exit("Unauthorized");
}

$user = $_SESSION['username'];

$stmt = $conn->prepare("UPDATE users SET simulator_used = simulator_used + 1 WHERE username = ?");
$stmt->bind_param("s", $user);
$stmt->execute();
$stmt->close();

echo "success";
?>