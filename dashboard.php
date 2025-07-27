<?php
session_start();
include "includes/db.php";
if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Smart Personal Finance Tracker</title>
    <style>
         body {
        margin: 0;
        font-family: Arial, sans-serif;
        display: flex;
    }
.sidebar {
        width: 220px;
        background-color: #2c3e50;
        min-height: 100vh;
        color: white;
        padding-top: 20px;
        position: fixed;
    }
.sidebar h2 {
        text-align: center;
        margin-bottom: 30px;
    }
.sidebar a {
        display: block;
        color: white;
        padding: 12px 20px;
        text-decoration: none;
        transition: background 0.3s;
    }
.sidebar a:hover {
        background-color: #1abc9c;
    }
.content {
        margin-left: 220px;
        padding: 30px;
        flex-grow: 1;
        background-color: #f4f4f4;
        min-height: 100vh;
    }
</style>
<div class="sidebar">
    <h2 style = "color:white;">💰 Tracker</h2>
    <a href="dashboard.php">📊 Dashboard</a>
    <a href="set_budget.php">🎯 Set Budget</a>
  <a href="budget_summary.php">📋 Budget Summary</a>
    <a href="alerts.php">🚨 Alerts</a>
    <a href="charts.php">📈 Reports</a>
    <a href="expense_prediction.php">🔮 Expense Prediction</a>
    <a href="simulator_calculate.php">💡 Simulator</a>
    <a href="savings_tracker.php"><i class="fas fa-piggy-bank"></i>💰 Savings Tracker</a>
    <a href="monthly_summary.php">📅 Monthly Summary</a>
    <a href="rewards.php">🏆 Rewards</a>
    <a href="reminders.php">📅 Reminders</a>
    <a href="logout.php">🔒 Logout</a>
</div>
<div class="content">
<style>
        body {
            font-family: Arial, sans-serif;
            background: #f0f4f8;
            margin: 0;
            padding: 0;
        }
        header {
            background-color: #4b7bec;
            color: white;
            padding: 20px;
            text-align: center;
        }
        .section {
            background: white;
            border-radius: 10px;
            width: 80%;
            margin: 30px auto;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h2 {
            color: #333;
        }
        input[type="text"], input[type="number"] {
            width: 90%;
            padding: 8px;
            margin: 8px 0;
            border-radius: 5px;
            border: 1px solid #ccc;
        }
        button {
            padding: 10px 20px;
            background-color: #4b7bec;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        button:hover {
            background-color: #3867d6;
        }
    </style>
</head>
<body>
<header>
    <h1>Welcome, <?php echo $_SESSION['username']; ?> 👋</h1>
    <?php
    $quotes = [
        "A budget is telling your money where to go instead of wondering where it went. — Dave Ramsey",
        "Don't tell me where your priorities are. Show me where you spend your money and I'll tell you what they are. — James W. Frick",
        "Financial freedom is available to those who learn about it and work for it. — Robert Kiyosaki",
        "Beware of little expenses; a small leak will sink a great ship. — Benjamin Franklin",
        "It's not your salary that makes you rich, it's your spending habits. — Charles A. Jaffe",
        "Money grows on the tree of persistence. — Japanese Proverb",
        "Do not save what is left after spending, but spend what is left after saving. — Warren Buffett"
    ];
$random_quote = $quotes[array_rand($quotes)];
    echo "<p style='font-style: italic; color:rgb(239, 242, 247); margin-top: 10px;'>\"$random_quote\"</p>";
    ?>
</header>
<div class="section">
    <h2>➕ Add Income</h2>
    <form action="income.php" method="POST">
        <input type="number" name="amount" placeholder="Enter Amount" required><br>
        <input type="text" name="source" placeholder="Source (e.g., Salary, Freelance)" required><br>
        <button type="submit">Add Income</button>
    </form>
</div>
 <div class="section">
    <h2>➖ Add Expense</h2>
    <form action="expense.php" method="POST">
        <input type="number" name="amount" placeholder="Enter Amount" required><br>
        <input type="text" name="category" placeholder="Category (e.g., Food, Transport)" required><br>
        <button type="submit">Add Expense</button>
    </form>
</div>
<?php
$user = $_SESSION['username'];
$incomeQuery = "SELECT SUM(amount) AS total_income FROM income WHERE username='$user'";
$expenseQuery = "SELECT SUM(amount) AS total_expense FROM expense WHERE username='$user'";
$incomeResult = mysqli_query($conn, $incomeQuery);
$expenseResult = mysqli_query($conn, $expenseQuery);
$totalIncome = mysqli_fetch_assoc($incomeResult)['total_income'] ?? 0;
$totalExpense = mysqli_fetch_assoc($expenseResult)['total_expense'] ?? 0;
$savings = $totalIncome - $totalExpense;
?>
<?php
$user = $_SESSION['username'];
$incomeQuery = "SELECT SUM(amount) AS total_income FROM income WHERE username='$user'";
$expenseQuery = "SELECT SUM(amount) AS total_expense FROM expense WHERE username='$user'";
$incomeResult = mysqli_query($conn, $incomeQuery);
$expenseResult = mysqli_query($conn, $expenseQuery);
$totalIncome = mysqli_fetch_assoc($incomeResult)['total_income'] ?? 0;
$totalExpense = mysqli_fetch_assoc($expenseResult)['total_expense'] ?? 0;
$savings = $totalIncome - $totalExpense;
?>
<div class="section summary">
    <h2>📊 Financial Summary</h2>
    <p><strong>Total Income:</strong> ₹<?php echo $totalIncome; ?></p>
    <p><strong>Total Expense:</strong> ₹<?php echo $totalExpense; ?></p>
    <p><strong>Savings:</strong> ₹<?php echo $savings; ?></p>
    <style>
    body {
        font-family: 'Segoe UI', sans-serif;
        background: #f0f4f8;
        padding: 20px;
        color: #333;
    }
    .section {
        background: white;
        padding: 20px;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        margin-bottom: 20px;
    }
    input, button {
        margin: 10px 0;
        padding: 10px;
        width: 90%;
        border-radius: 8px;
        border: 1px solid #ccc;
    }
    button {
        background-color: #4CAF50;
        color: white;
        border: none;
        cursor: pointer;
    }
    button:hover {
        background-color: #45a049;
    }
    .summary p {
        font-size: 18px;
        margin: 5px 0;
    }
</style>
</div>