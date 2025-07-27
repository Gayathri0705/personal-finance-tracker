<?php
session_start();
include "includes/db.php";

// Redirect if not logged in
if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_SESSION['username'];
    $category = $_POST['category'];
    $amount = $_POST['amount'];
    $month_year = $_POST['month_year'];

    $sql = "INSERT INTO budgets (username, category, amount, month_year) 
            VALUES ('$username', '$category', '$amount', '$month_year')";
    
    if (mysqli_query($conn, $sql)) {
        $success = "Budget set successfully!";
    } else {
        $error = "Error: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Set Budget</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f4f6f8;
            padding: 0;
            margin: 0;
        }
        .navbar {
            background-color: #2c3e50;
            padding: 15px;
            color: white;
            display: flex;
            justify-content: space-between;
        }
        .navbar a {
            color: white;
            text-decoration: none;
            margin-right: 20px;
            font-weight: bold;
        }
        .navbar a:hover {
            text-decoration: underline;
        }
        .container {
            max-width: 600px;
            margin: 60px auto;
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 0 10px rgba(0,0,0,0.15);
        }
        h2 {
            text-align: center;
            color: #333;
        }
        input, select {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border-radius: 8px;
            border: 1px solid #ccc;
        }
        button {
            background-color: #007bff;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 8px;
            width: 100%;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover {
            background-color: #0056b3;
        }
        .msg {
            text-align: center;
            margin-top: 15px;
            color: green;
        }
        .error {
            color: red;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <div><strong>💰Smart Finance Tracker</strong></div>
        <div>
            <a href="dashboard.php">Dashboard</a>
            <a href="logout.php">Logout</a>
        </div>
    </div>

    <div class="container">
        <h2>Set Your Monthly Budget</h2>
        <form method="post">
            <label for="category">Category</label>
            <select name="category" required>
                <option value="Food">Food</option>
                <option value="Transport">Transport</option>
                <option value="Utilities">Utilities</option>
                <option value="Entertainment">Entertainment</option>
                <option value="Others">Others</option>
            </select>

            <label for="amount">Amount</label>
            <input type="number" name="amount" required>

            <label for="month_year">Month & Year (YYYY-MM)</label>
            <input type="month" name="month_year" required>

            <button type="submit">Set Budget</button>
        </form>

        <?php
        if (isset($success)) {
            echo "<div class='msg'>$success</div>";
        } elseif (isset($error)) {
            echo "<div class='msg error'>$error</div>";
        }
        ?>
    </div>
</body>
</html>