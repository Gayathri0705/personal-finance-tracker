<?php
session_start();
include "includes/db.php";

if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

$user = $_SESSION['username'];

// 1. Expense Category Distribution
$sql = "SELECT category, SUM(amount) as total FROM expense WHERE username = '$user' GROUP BY category";
$result = mysqli_query($conn, $sql);
$categories = [];
$totals = [];
while ($row = mysqli_fetch_assoc($result)) {
    $categories[] = $row['category'];
    $totals[] = $row['total'];
}

// 2 & 3. Monthly Income vs Expense + Savings
$sql_monthly = "
SELECT 
    DATE_FORMAT(month_date, '%Y-%m') AS month,
    SUM(CASE WHEN source IS NOT NULL THEN amount ELSE 0 END) AS total_income,
    SUM(CASE WHEN category IS NOT NULL THEN amount ELSE 0 END) AS total_expense
FROM (
    SELECT amount, created_at AS month_date, source, NULL as category FROM income WHERE username = '$user'
    UNION ALL
    SELECT amount, created_at as  month_date, NULL, category FROM expense WHERE username = '$user'
) AS combined
GROUP BY month
ORDER BY month ASC
";
$result_monthly = mysqli_query($conn, $sql_monthly);
$months = $monthly_income = $monthly_expense = $savings = [];

while ($row = mysqli_fetch_assoc($result_monthly)) {
    $months[] = $row['month'];
    $monthly_income[] = $row['total_income'];
    $monthly_expense[] = $row['total_expense'];
    $savings[] = $row['total_income'] - $row['total_expense'];
}

// 4. Budget vs Actual
$sql_budget = "SELECT category, amount FROM budgets WHERE username = '$user'";
$result_budget = mysqli_query($conn, $sql_budget);
$budget_categories = $budget_amounts = $actual_amounts = [];

while ($row = mysqli_fetch_assoc($result_budget)) {
    $cat = $row['category'];
    $budget_categories[] = $cat;
    $budget_amounts[] = $row['amount'];

    $sql_actual = "SELECT SUM(amount) as total FROM expense WHERE username = '$user' AND category = '$cat'";
    $res_actual = mysqli_query($conn, $sql_actual);
    $row_actual = mysqli_fetch_assoc($res_actual);
    $actual_amounts[] = $row_actual['total'] ?? 0;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Charts and Reports</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f4f6f9;
            padding-bottom: 50px;
        }
        .chart-container {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            margin-bottom: 40px;
        }
        canvas {
            max-height: 350px;
        }
        .btn-download {
            margin-top: 10px;
        }
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark px-4 mb-4">
    <a class="navbar-brand" href="dashboard.php">💰Smart Finance Tracker</a>
    <div class="collapse navbar-collapse">
        <ul class="navbar-nav ms-auto">
            <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
           
            <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
        </ul>
    </div>
</nav>

<div class="container">
    <h2 class="mb-4 text-primary text-center">📊 Financial Reports</h2>

    <div class="chart-container">
        <h4>📌 Expense Category Distribution</h4>
        <canvas id="pieChart"></canvas>
        <button class="btn btn-primary btn-download" onclick="downloadChart('pieChart')">Download Pie Chart</button>
    </div>

    <div class="chart-container">
        <h4>📆 Monthly Income vs Expense</h4>
        <canvas id="barChart"></canvas>
        <button class="btn btn-primary btn-download" onclick="downloadChart('barChart')">Download Bar Chart</button>
    </div>

    <div class="chart-container">
        <h4>📈 Monthly Savings Trend</h4>
        <canvas id="lineChart"></canvas>
        <button class="btn btn-primary btn-download" onclick="downloadChart('lineChart')">Download Line Chart</button>
    </div>

    <div class="chart-container">
        <h4>🎯 Budget vs Actual Spending</h4>
        <canvas id="doughnutChart"></canvas>
        <button class="btn btn-primary btn-download" onclick="downloadChart('doughnutChart')">Download Doughnut Chart</button>
    </div>
</div>

<script>
const pieCtx = document.getElementById('pieChart').getContext('2d');
new Chart(pieCtx, {
    type: 'pie',
    data: {
        labels: <?php echo json_encode($categories); ?>,
        datasets: [{
            data: <?php echo json_encode($totals); ?>,
            backgroundColor: ['#ff6384', '#36a2eb', '#ffce56', '#4bc0c0', '#9966ff', '#ff9f40']
        }]
    }
});

const barCtx = document.getElementById('barChart').getContext('2d');
new Chart(barCtx, {
    type: 'bar',
    data: {
        labels: <?php echo json_encode($months); ?>,
        datasets: [
            {
                label: 'Income',
                data: <?php echo json_encode($monthly_income); ?>,
                backgroundColor: '#4CAF50'
            },
            {
                label: 'Expense',
                data: <?php echo json_encode($monthly_expense); ?>,
                backgroundColor: '#f44336'
            }
        ]
    },
    options: { responsive: true, maintainAspectRatio: false }
});

const lineCtx = document.getElementById('lineChart').getContext('2d');
new Chart(lineCtx, {
    type: 'line',
    data: {
        labels: <?php echo json_encode($months); ?>,
        datasets: [{
            label: 'Savings',
            data: <?php echo json_encode($savings); ?>,
            fill: false,
            borderColor: '#007bff',
            tension: 0.2
        }]
    },
    options: { responsive: true, maintainAspectRatio: false }
});

const doughnutCtx = document.getElementById('doughnutChart').getContext('2d');
new Chart(doughnutCtx, {
    type: 'doughnut',
    data: {
        labels: <?php echo json_encode($budget_categories); ?>,
        datasets: [
            {
                label: 'Budgeted',
                data: <?php echo json_encode($budget_amounts); ?>,
                backgroundColor: '#36a2eb'
            },
            {
                label: 'Actual',
                data: <?php echo json_encode($actual_amounts); ?>,
                backgroundColor: '#ff6384'
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false
    }
});

function downloadChart(id) {
    const link = document.createElement('a');
    link.download = id + ".png";
    link.href = document.getElementById(id).toDataURL("image/png");
    link.click();
}
</script>

</body>
</html>