<?php
session_start();
include "includes/db.php";

// Redirect if not logged in
if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

$user = $_SESSION['username'];

// Fetch budgets
$budgets = [];
$sql_budget = "SELECT category, amount FROM budgets WHERE username = '$user'";
$result_budget = mysqli_query($conn, $sql_budget);

while ($row = mysqli_fetch_assoc($result_budget)) {
    $budgets[$row['category']] = $row['amount'];
}

// Fetch expenses
$expenses = [];
$sql_expense = "SELECT category, SUM(amount) as total FROM expense WHERE username = '$user' GROUP BY category";
$result_expense = mysqli_query($conn, $sql_expense);

while ($row = mysqli_fetch_assoc($result_expense)) {
    $expenses[$row['category']] = $row['total'];
}

// Determine alerts
$alerts = [];
foreach ($budgets as $category => $budgetAmount) {
    if (isset($expenses[$category]) && $expenses[$category] > $budgetAmount) {
        $overspent = $expenses[$category] - $budgetAmount;
        $alerts[] = [
            'category' => $category,
            'budget' => $budgetAmount,
            'spent' => $expenses[$category],
            'overspent' => $overspent
        ];
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Budget Alerts</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            padding: 30px;
            font-family: 'Segoe UI', sans-serif;
        }
        .alert-heading {
            font-weight: bold;
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

<h2 class="mb-4 text-primary">🚨 Budget Alerts</h2>

<?php if (empty($alerts)) : ?>
    <div class="alert alert-success" role="alert">
        🎉 Good job! You are within your budget for all categories.
    </div>
<?php else : ?>
    <?php foreach ($alerts as $alert) : ?>
        <div class="alert alert-danger shadow-sm border border-danger">
            <h4 class="alert-heading">⚠ Overspent in <?php echo htmlspecialchars($alert['category']); ?></h4>
            <p>
                <strong>Budget:</strong> ₹<?php echo number_format($alert['budget'], 2); ?><br>
                <strong>Spent:</strong> ₹<?php echo number_format($alert['spent'], 2); ?><br>
                <strong>Overspent by:</strong> ₹<?php echo number_format($alert['overspent'], 2); ?>
            </p>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>