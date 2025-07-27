<?php
session_start();
include "includes/db.php";

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

$user = $_SESSION['username'];

// Fetch budgets and expenses grouped by category for the user
$sql = "
SELECT 
    b.category,
    b.amount AS budget_amount,
    IFNULL(SUM(e.amount), 0) AS expense_amount
FROM budgets b
LEFT JOIN expense e ON b.category = e.category AND e.username = b.username
WHERE b.username = '$user'
GROUP BY b.category, b.amount
ORDER BY b.category
";

$result = mysqli_query($conn, $sql);
if (!$result) {
    die("Error running query: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Budget Summary</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding-top: 70px;
        }
        .legend div {
            margin-bottom: 5px;
            font-weight: 500;
        }
        .legend span {
            display: inline-block;
            width: 20px;
            height: 20px;
            margin-right: 8px;
            border-radius: 4px;
        }
        .card {
            margin-bottom: 15px;
            box-shadow: 0 0 10px rgb(0 0 0 / 0.1);
        }
    </style>
</head>
<body>

<!-- ✅ NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">💰Smart Finance Tracker</a>
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
                <li class="nav-item"><a class="nav-link" href="set_budget.php">Set Budget</a></li>
                
                <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
            </ul>
        </div>
    </div>
</nav>

<div class="container">
    <h2 class="mb-4">📊 Budget Summary for <?php echo htmlspecialchars($user); ?></h2>

    <!-- Legend for progress bar colors -->
    <div class="legend mb-4">
        <div><span style="background:#198754;"></span> Under 75% used (Good)</div>
        <div><span style="background:#ffc107;"></span> 75% - 100% used (Warning)</div>
        <div><span style="background:#dc3545;"></span> Over 100% used (Over Budget)</div>
    </div>

    <?php 
    if (mysqli_num_rows($result) == 0) {
        echo "<p>No budget data found. Please set your budgets first.</p>";
    } else {
        while ($row = mysqli_fetch_assoc($result)) {
            $category = htmlspecialchars($row['category']);
            $budget = (float)$row['budget_amount'];
            $expense = (float)$row['expense_amount'];
            $percent_used = $budget > 0 ? ($expense / $budget) * 100 : 0;

            if ($percent_used < 75) {
                $progress_class = "bg-success";
            } elseif ($percent_used <= 100) {
                $progress_class = "bg-warning";
            } else {
                $progress_class = "bg-danger";
            }

            $percent_display = round($percent_used, 2);
            $remaining = $budget - $expense;
            $remaining_display = $remaining >= 0 ? $remaining : 0;
    ?>

    <div class="card p-3">
        <h5><?php echo $category; ?></h5>
        <p><strong>Budget:</strong> ₹<?php echo number_format($budget, 2); ?> |
           <strong>Spent:</strong> ₹<?php echo number_format($expense, 2); ?> |
           <strong>Remaining:</strong> ₹<?php echo number_format($remaining_display, 2); ?></p>

        <div class="progress" style="height: 25px;">
            <div class="progress-bar <?php echo $progress_class; ?>" role="progressbar" style="width: <?php echo min($percent_display, 100); ?>%;" aria-valuenow="<?php echo $percent_display; ?>" aria-valuemin="0" aria-valuemax="100">
                <?php echo $percent_display; ?>%
            </div>
        </div>

        <?php if ($percent_used > 100): ?>
            <div class="alert alert-danger mt-2" role="alert">
                ⚠ You have exceeded your budget for <strong><?php echo $category; ?></strong> by ₹<?php echo number_format(abs($remaining), 2); ?>!
            </div>
        <?php endif; ?>
    </div>

    <?php 
        }
    }
    ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>