<?php
session_start();
include "includes/db.php";

if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

$user = $_SESSION['username'];

// Get monthly savings automatically = income - expense
$query = "
    SELECT 
        DATE_FORMAT(created_at, '%Y-%m') AS month,
        SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) AS total_income,
        SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) AS total_expense
    FROM (
        SELECT amount, created_at, 'income' AS type FROM income WHERE username = ?
        UNION ALL
        SELECT amount, created_at, 'expense' AS type FROM expense WHERE username = ?
    ) AS all_data
    GROUP BY month
    ORDER BY month ASC
";

$stmt = $conn->prepare($query);
$stmt->bind_param("ss", $user, $user);
$stmt->execute();
$result = $stmt->get_result();

$savings_data = [];
$total_saved = 0;

while ($row = $result->fetch_assoc()) {
    $month = $row['month'];
    $saved = floatval($row['total_income']) - floatval($row['total_expense']);
    $savings_data[] = [
        'month' => $month,
        'saved' => $saved
    ];
    $total_saved += $saved;
}
$stmt->close();
?><!DOCTYPE html><html>
<head>
    <title>Savings Tracker</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #eef3f7;
            padding: 30px;
            text-align: center;
            margin: 0;
        }
        nav {
            background-color: #28a745;
            padding: 12px 30px;
            display: flex;
            justify-content: center;
            gap: 25px;
            margin-bottom: 30px;
        }
        nav a {
            color: white;
            text-decoration: none;
            font-weight: bold;
            font-size: 16px;
            padding: 8px 12px;
            border-radius: 6px;
            transition: background-color 0.3s ease;
        }
        nav a:hover, nav a.active {
            background-color: #1e7e34;
        }.container {
        max-width: 600px;
        margin: auto;
        background: white;
        padding: 25px;
        border-radius: 12px;
        box-shadow: 0 0 15px rgba(0,0,0,0.1);
        text-align: center;
    }
    h2 {
        color: #333;
    }
    .total {
        font-size: 22px;
        color: #155724;
        background: #d4edda;
        padding: 15px;
        border-radius: 8px;
        margin: 20px 0;
    }
    canvas {
        margin-top: 30px;
        max-width: 100%;
    }
</style>

</head>
<body><nav>
    <a href="dashboard.php">Dashboard</a>
    <a href="savings_tracker.php" class="active">Savings Tracker</a>
    <a href="logout.php">Logout</a>
</nav><div class="container">
    <h2>💰 Savings Tracker</h2><div class="total">
    Total Saved: ₹<?php echo number_format($total_saved, 2); ?>
</div>

<canvas id="savingsChart" width="500" height="300"></canvas>

</div><script src="https://cdn.jsdelivr.net/npm/chart.js"></script><script>
    const savingsData = <?php echo json_encode($savings_data); ?>;

    const labels = savingsData.map(item => item.month);
    const amounts = savingsData.map(item => parseFloat(item.saved));

    const ctx = document.getElementById('savingsChart').getContext('2d');

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Savings (₹)',
                data: amounts,
                backgroundColor: 'rgba(40, 167, 69, 0.5)',
                borderColor: 'rgba(40, 167, 69, 1)',
                borderWidth: 2
            }]
        },
        options: {
            scales: {
                x: {
                    title: {
                        display: true,
                        text: 'Month'
                    }
                },
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Amount Saved (₹)'
                    }
                }
            },
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                },
                tooltip: {
                    enabled: true
                }
            }
        }
    });
</script></body>
</html>