<?php
session_start();
include "includes/db.php";

// Redirect if not logged in
if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $amount = $_POST['amount'];
    $source = $_POST['source'];
    $user = $_SESSION['username'];

    $sql = "INSERT INTO income (username, amount, source) VALUES ('$user', '$amount', '$source')";
    
    if (mysqli_query($conn, $sql)) {
        echo "<script>alert('Income added successfully!'); window.location.href='dashboard.php';</script>";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Add Income</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f8f9fa;
            padding: 40px;
        }
        .container {
            max-width: 500px;
            margin: auto;
            background: #ffffff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 0 10px rgba(0,0,0,0.15);
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #333;
        }
        .navbar {
            margin-bottom: 30px;
        }
    </style>
</head>
<body>

<!-- Navigation Bar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark px-4">
    <a class="navbar-brand" href="dashboard.php">💰 Finance Tracker</a>
    <div class="collapse navbar-collapse">
        <ul class="navbar-nav ms-auto">
            <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
            <li class="nav-item"><a class="nav-link" href="income.php">Add Income</a></li>
            <li class="nav-item"><a class="nav-link" href="budget_summary.php">Budget Summary</a></li>
            <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
        </ul>
    </div>
</nav>

<!-- Income Form -->
<div class="container">
    <h2>Add Income</h2>
    <form method="POST" action="">
        <div class="mb-3">
            <label for="source" class="form-label">Source</label>
            <input type="text" class="form-control" id="source" name="source" required>
        </div>
        <div class="mb-3">
            <label for="amount" class="form-label">Amount (₹)</label>
            <input type="number" class="form-control" id="amount" name="amount" required>
        </div>
        <button type="submit" class="btn btn-primary w-100">Add Income</button>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>