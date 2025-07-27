<?php
session_start();
include "includes/db.php";

if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $amount = trim($_POST['amount']);
    $category = trim($_POST['category']);
    $user = $_SESSION['username'];

    if (!is_numeric($amount) || $amount <= 0) {
        echo "<script>alert('Please enter a valid amount');</script>";
    } else {
        $sql = "INSERT INTO expense (username, amount, category, created_at) 
                VALUES (?, ?, ?, NOW())";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "sds", $user, $amount, $category);

        if (mysqli_stmt_execute($stmt)) {
            echo "<script>alert('Expense added successfully!'); window.location.href='dashboard.php';</script>";
        } else {
            echo "Error: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Expense</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f4f6f9;
            padding-top: 70px;
        }
        .container {
            max-width: 500px;
            background: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>

<!-- ✅ Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">💰 Finance Tracker</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navContent">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse justify-content-end" id="navContent">
            <ul class="navbar-nav">
                <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
                <li class="nav-item"><a class="nav-link active" href="#">Add Expense</a></li>
                <li class="nav-item"><a class="nav-link" href="charts.php">Reports</a></li>
                <li class="nav-item"><a class="nav-link" href="Expense_Prediction.php">Prediction</a></li>
                <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
            </ul>
        </div>
    </div>
</nav>

<!-- ✅ Form -->
<div class="container mt-5">
    <h3 class="text-center text-primary">📝 Add a New Expense</h3>
    <form method="POST" class="mt-4">
        <div class="mb-3">
            <label class="form-label">Amount (₹):</label>
            <input type="number" name="amount" class="form-control" required min="1" step="0.01">
        </div>

        <div class="mb-3">
            <label class="form-label">Category:</label>
            <input type="text" name="category" class="form-control" required placeholder="e.g. Food, Transport">
        </div>

        <div class="text-center">
            <button type="submit" class="btn btn-primary">Add Expense</button>
        </div>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>