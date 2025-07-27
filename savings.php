<?php
session_start();
include "includes/db.php";

$user = $_SESSION['username'];

// Get total income, expense
$incomeQuery = "SELECT SUM(amount) AS total_income FROM income WHERE username = '$user'";
$expenseQuery = "SELECT SUM(amount) AS total_expense FROM expenses WHERE username = '$user'";

$incomeResult = mysqli_query($conn, $incomeQuery);
$expenseResult = mysqli_query($conn, $expenseQuery);

$income = mysqli_fetch_assoc($incomeResult)['total_income'] ?? 0;
$expense = mysqli_fetch_assoc($expenseResult)['total_expense'] ?? 0;

$savings = $income - $expense;

// Grouped by month
$monthlyQuery = "
SELECT 
    MONTH(date) AS month, 
    YEAR(date) AS year,
    SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) AS total_income,
    SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) AS total_expense
FROM (
    SELECT amount, date, 'income' AS type FROM income WHERE username = '$user'
    UNION ALL
    SELECT amount, date, 'expense' AS type FROM expenses WHERE username = '$user'
) AS combined
GROUP BY YEAR(date), MONTH(date)
ORDER BY YEAR(date) DESC, MONTH(date) DESC
LIMIT 6;
";

$monthlyResult = mysqli_query($conn, $monthlyQuery);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Savings Overview</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 20px; }
        .summary { background: white; padding: 20px; margin-bottom: 20px; border-radius: 10px; }
        table { width: 100%; border-collapse: collapse; background: white; }
        th, td { padding: 12px; border: 1px solid #ddd; text-align: center; }
        th { background-color: #4CAF50; color: white; }
        h2 { color: #333; }
    </style>
</head>
<body>

    <div class="summary">
        <h2>Total Savings Overview</h2>
        <p><strong>Total Income:</strong> ₹<?php echo $income; ?></p>
        <p><strong>Total Expenses:</strong> ₹<?php echo $expense; ?></p>
        <p><strong>Total Savings:</strong> ₹<?php echo $savings; ?></p>
    </div>

    <div class="summary">
        <h2>Monthly Savings (Last 6 Months)</h2>
        <table>
            <tr>
                <th>Month</th>
                <th>Income</th>
                <th>Expense</th>
                <th>Savings</th>
            </tr>
            <?php
            while ($row = mysqli_fetch_assoc($monthlyResult)) {
                $monthName = date('F Y', mktime(0, 0, 0, $row['month'], 10, $row['year']));
                $monthlyIncome = $row['total_income'];
                $monthlyExpense = $row['total_expense'];
                $monthlySavings = $monthlyIncome - $monthlyExpense;

                echo "<tr>
                        <td>$monthName</td>
                        <td>₹$monthlyIncome</td>
                        <td>₹$monthlyExpense</td>
                        <td>₹$monthlySavings</td>
                      </tr>";
            }
            ?>
        </table>
    </div>

</body>
</html>