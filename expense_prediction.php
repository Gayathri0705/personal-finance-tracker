<?php
session_start();
include "includes/db.php";

if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

$user = $_SESSION['username'];
$prediction = null;
$selected_category = '';
$months = 3;

$sql = "SELECT DISTINCT category FROM expense WHERE username = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "s", $user);
mysqli_stmt_execute($stmt);
$category_result = mysqli_stmt_get_result($stmt);
$categories = [];

while ($row = mysqli_fetch_assoc($category_result)) {
    $categories[] = $row['category'];
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $selected_category = $_POST['category'];
    $months = (int)$_POST['months'];

    $avg_sql = "
        SELECT ROUND(AVG(amount), 2) AS avg_amount 
        FROM expense 
        WHERE username = ? 
        AND category = ? 
        AND created_at >= DATE_SUB(CURDATE(), INTERVAL ? MONTH)
    ";
    $stmt_avg = mysqli_prepare($conn, $avg_sql);
    mysqli_stmt_bind_param($stmt_avg, "ssi", $user, $selected_category, $months);
    mysqli_stmt_execute($stmt_avg);
    $result = mysqli_stmt_get_result($stmt_avg);
    $row = mysqli_fetch_assoc($result);
    $avg = $row['avg_amount'] ?? 0;

    $prediction = $avg * $months;
}
?><!DOCTYPE html>

<html>
    
<head>
    <meta charset="UTF-8">
    <title>Custom Expense Prediction</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #f4f6f9;
            padding-top: 70px;
        }
        .container {
            max-width: 600px;
            background: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 0 12px rgba(0,0,0,0.1);
        }
        .info {
            font-size: 13px;
            color: #555;
        }
        .result {
            margin-top: 20px;
            background: #dff0d8;
            color: #3c763d;
            padding: 15px;
            border-radius: 8px;
            font-size: 18px;
        }
    </style>
</head>
<body><!-- Navbar --><nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">💰Smart Finance Tracker</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
            <ul class="navbar-nav">
                <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
                <li class="nav-item"><a class="nav-link active" href="#">Prediction</a></li>
                <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
            </ul>
        </div>
    </div>
</nav><!-- Prediction Form --><div class="container mt-4">
    <h3 class="mb-3 text-center text-primary">🔮 Custom Expense Prediction</h3>
<div class="alert alert-info">
    <strong>📢 What is this?</strong><br>
    <div class="alert alert-info">
        <strong>How does it work?</strong><br>
        We use your past <strong><?php echo $months; ?></strong> months' data in the selected category<br>
        to predict future spending.
    </div><form method="POST">
    <div class="mb-3">
        <label class="form-label">Select Category:</label>
        <select name="category" class="form-select" required>
            <option value="">-- Select --</option>
            <?php foreach ($categories as $cat): ?>
                <option value="<?php echo htmlspecialchars($cat); ?>" <?php if ($cat == $selected_category) echo 'selected'; ?>>
                    <?php echo ucfirst(htmlspecialchars($cat)); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="mb-3">
        <label class="form-label">Months to Predict:</label>
        <input type="number" name="months" class="form-control" min="1" value="<?php echo htmlspecialchars($months); ?>" required>
    </div>

    <div class="text-center">
        <button type="submit" class="btn btn-primary">Predict</button>
    </div>
</form>

<?php if ($prediction !== null): ?>
    <div class="result text-center">
        You may spend <strong> ₹ <?php echo number_format($prediction, 2); ?></strong> on 
        <strong><?php echo ucfirst(htmlspecialchars($selected_category)); ?></strong> in the next 
        <strong><?php echo $months; ?></strong> month(s).
    </div>
<?php endif; ?>

</div><script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script></body>
</html>