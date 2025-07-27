<?php
session_start();
include "includes/db.php";

if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id = intval($_POST['id']);
    $type = $_POST['type'];
    $user = $_SESSION['username'];

    if ($type === 'income') {
        $stmt = $conn->prepare("DELETE FROM income WHERE id = ? AND username = ?");
    } elseif ($type === 'expense') {
        $stmt = $conn->prepare("DELETE FROM expense WHERE id = ? AND username = ?");
    } else {
        die("Invalid type.");
    }

    $stmt->bind_param("is", $id, $user);

    if ($stmt->execute()) {
        header("Location: monthly_summary.php");
        exit();
    } else {
        echo "Error deleting entry: " . $stmt->error;
    }

    $stmt->close();
}
?>