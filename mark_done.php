<?php
session_start();
include "includes/db.php";

if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

$user = $_SESSION['username'];

if (isset($_GET['id'])) {
    $reminder_id = intval($_GET['id']);

    // Delete reminder if it belongs to user
    $sql = "DELETE FROM reminders WHERE id=$reminder_id AND username='$user'";
    mysqli_query($conn, $sql);
}

// Redirect back to reminders page
header("Location: reminders.php");
exit();
?>