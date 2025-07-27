<?php
session_start();
include "includes/db.php";

if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

$user = $_SESSION['username'];
$currentMonth = date('Y-m');

// Get total income and expense
$income_sql = "SELECT SUM(amount) AS total_income FROM income WHERE username='$user' AND DATE_FORMAT(created_at, '%Y-%m') = '$currentMonth'";
$expense_sql = "SELECT SUM(amount) AS total_expense FROM expense WHERE username='$user' AND DATE_FORMAT(created_at, '%Y-%m') = '$currentMonth'";

$income_result = mysqli_query($conn, $income_sql);
$expense_result = mysqli_query($conn, $expense_sql);

$income_row = mysqli_fetch_assoc($income_result);
$expense_row = mysqli_fetch_assoc($expense_result);

$total_income = $income_row['total_income'] ?? 0;
$total_expense = $expense_row['total_expense'] ?? 0;
$balance = $total_income - $total_expense;

// Get activity log
$log_sql = "
    SELECT id, amount, created_at AS date, source AS label, 'Income' AS type 
    FROM income 
    WHERE username='$user' AND DATE_FORMAT(created_at, '%Y-%m') = '$currentMonth'

    UNION ALL

    SELECT id, amount, created_at AS date, category AS label, 'Expense' AS type 
    FROM expense 
    WHERE username='$user' AND DATE_FORMAT(created_at, '%Y-%m') = '$currentMonth'

    ORDER BY date DESC
";

$log_result = mysqli_query($conn, $log_sql);
?>
<!DOCTYPE html>
<html>
<head>
    <title>📆 Monthly Summary</title>
    <style>
        body {
            font-family: Arial;
            background: #f8f9fa;
            padding: 0;
            margin: 0;
        }

        /* Navbar styles */
        .navbar {
            background-color: #2c3e50;
            overflow: hidden;
            padding: 10px 20px;
        }
        .navbar a {
            float: left;
            display: block;
            color: #ecf0f1;
            text-align: center;
            padding: 12px 16px;
            text-decoration: none;
            font-weight: bold;
        }
        .navbar a:hover {
            background-color: #1abc9c;
            color: white;
        }

        .summary-box {
            background: white;
            padding: 20px;
            border-radius: 10px;
            max-width: 800px;
            margin: 30px auto;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
        }
        .totals {
            display: flex;
            justify-content: space-around;
            margin-bottom: 30px;
        }
        .totals div {
            background: #e9ecef;
            padding: 15px;
            border-radius: 8px;
            width: 28%;
            text-align: center;
        }
        .log {
            text-align: left;
        }
        .log-entry {
            background: #ffffff;
            padding: 12px 16px;
            border-left: 5px solid;
            margin-bottom: 10px;
            border-radius: 6px;
            box-shadow: 0 0 5px rgba(0,0,0,0.05);
        }
        .income { border-color: #28a745; }
        .expense { border-color: #dc3545; }
        .log-entry small {
            color: #888;
        }
    </style>
</head>
<body>

<div class="navbar">
    <a href="dashboard.php">Home</a>
    
    <a href="reminders.php">Reminders</a>
    <a href="savings_tracker.php">Savings</a>
    <a href="monthly_summary.php">Summary</a>
    <a href="logout.php">Logout</a>
</div>

<div class="summary-box">
    <h2>📆 Monthly Summary - <?php echo date("F Y"); ?></h2>

    <div class="totals">
        <div><strong>💰 Total Income</strong><br>₹<?php echo number_format($total_income, 2); ?></div>
        <div><strong>💸 Total Expense</strong><br>₹<?php echo number_format($total_expense, 2); ?></div>
        <div><strong>💼 Balance</strong><br>₹<?php echo number_format($balance, 2); ?></div>
    </div>

    <div class="log">
        <h3>📝 Activity Log:</h3>
        <?php if (mysqli_num_rows($log_result) > 0): ?>
        <?php while ($row = mysqli_fetch_assoc($log_result)): ?>
            <div class="log-entry <?php echo strtolower($row['type']); ?>">
                <strong><?php echo ucfirst($row['type']); ?>:</strong> ₹<?php echo number_format($row['amount'], 2); ?> for <em><?php echo ucfirst($row['label']); ?></em><br>
                <small>📅 <?php echo date("d M, Y", strtotime($row['date'])); ?></small>

                <form method="POST" action="delete_entry.php" style="display:inline;">
                    <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                    <input type="hidden" name="type" value="<?php echo strtolower($row['type']); ?>">
                    <button type="submit" onclick="return confirm('Are you sure you want to delete this entry?')" style="background: none; border: none; color: red; cursor: pointer;">🗑 Delete</button>
                </form>
            </div>
        <?php endwhile; ?>
        <?php else: ?>
            <p>No activity recorded for this month yet.</p>
        <?php endif; ?>
    </div>
</div>

</body>
</html>