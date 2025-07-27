<?php
session_start();
include "includes/db.php";

if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

$user = $_SESSION['username'];
$currentMonth = date('Y-m');

// 💰 Income and Expense Totals
$income = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(amount) AS total FROM income WHERE username='$user' AND DATE_FORMAT(created_at, '%Y-%m')='$currentMonth'"))['total'] ?? 0;
$expense = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(amount) AS total FROM expense WHERE username='$user' AND DATE_FORMAT(created_at, '%Y-%m')='$currentMonth'"))['total'] ?? 0;

$saved = $income - $expense;
$rewards = [];

// 💎 Saving Milestones
if ($saved >= 20000) {
    $rewards[] = ["🥇 Gold Saver Badge", "You saved ₹20,000+ this month!"];
} elseif ($saved >= 10000) {
    $rewards[] = ["🥈 Silver Saver Badge", "You saved ₹10,000+ this month!"];
} elseif ($saved >= 5000) {
    $rewards[] = ["🌟 Bronze Saver Badge", "You saved ₹5,000+ this month!"];
}

// 💸 No Expenses in 7 Days
$sevenDaysAgo = date('Y-m-d', strtotime('-7 days'));
$expense_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS count FROM expense WHERE username='$user' AND created_at >= '$sevenDaysAgo'"))['count'] ?? 0;
if ($expense_count == 0) {
    $rewards[] = ["💸 Budget Discipline Star", "You haven’t spent anything in the last 7 days!"];
}

// 🧘 Minimal Spending
if ($expense < 1000 && $expense > 0) {
    $rewards[] = ["🧘 Frugal Living Badge", "You lived under ₹1000 this month!"];
}

// 📒 Logged Income & Expense
$income_log = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS count FROM income WHERE username='$user' AND DATE_FORMAT(created_at, '%Y-%m')='$currentMonth'"))['count'] ?? 0;
$expense_log = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS count FROM expense WHERE username='$user' AND DATE_FORMAT(created_at, '%Y-%m')='$currentMonth'"))['count'] ?? 0;
if ($income_log > 0 && $expense_log > 0) {
    $rewards[] = ["📒 Active Logger Badge", "You've logged both income and expenses this month!"];
}

// 🔥 7-Day Login Streak
$dates = [];
for ($i = 0; $i < 7; $i++) {
    $dates[] = "'" . date('Y-m-d', strtotime("-$i days")) . "'";
}
$dateList = implode(',', $dates);
$login_check = mysqli_query($conn, "SELECT COUNT(DISTINCT login_date) AS logins FROM logins WHERE username='$user' AND login_date IN ($dateList)");
if (mysqli_fetch_assoc($login_check)['logins'] == 7) {
    $rewards[] = ["🔥 Consistency Streak Flame", "You’ve logged in daily for the past 7 days!"];
}

// 🔥 Monthly Login Days (Single Fire with Count)
$login_days_sql = "SELECT COUNT(DISTINCT login_date) AS total_days 
                   FROM logins 
                   WHERE username='$user' AND DATE_FORMAT(login_date, '%Y-%m') = '$currentMonth'";
$login_days_result = mysqli_query($conn, $login_days_sql);
$login_days = mysqli_fetch_assoc($login_days_result)['total_days'] ?? 0;
$fire_display = $login_days > 0 ? "🔥" . $login_days : "";

// 🧠 Simulator Use & Goal Completion
$user_data = mysqli_fetch_assoc(mysqli_query($conn, "SELECT simulator_used, goals_completed, reward_badge FROM users WHERE username='$user'"));
$simulator_used = $user_data['simulator_used'] ?? 0;

if ($simulator_used >= 20) {
    $rewards[] = ["🧮 Master Budgeter Medal", "Used simulator 20+ times like a pro!"];
} elseif ($simulator_used >= 10) {
    $rewards[] = ["🔁 Strategic Thinker Badge", "10+ simulations show your planning skills!"];
} elseif ($simulator_used >= 5) {
    $rewards[] = ["🧠 Smart Planner", "You've used the simulator 5+ times!"];
}
$reward_badge = $user_data['reward_badge'] ?? "";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>🏆 Your Rewards</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #74ebd5, #ACB6E5);
            margin: 0; padding: 0;
            min-height: 100vh;
            color: #333;
        }
        nav {
            background: #34495e;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        nav .logo {
            color: #fff;
            font-weight: bold;
            font-size: 22px;
            text-decoration: none;
        }
        nav .nav-links a {
            color: #fff;
            text-decoration: none;
            margin-left: 20px;
            font-size: 16px;
            font-weight: 500;
        }
        nav .nav-links a:hover {
            color: #f1c40f;
        }
        .container {
            max-width: 700px;
            margin: 40px auto;
            padding: 30px;
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.15);
        }
        h1 {
            text-align: center;
            margin-bottom: 25px;
            color: #2c3e50;
        }
        .badge {
            background: #27ae60;
            color: white;
            padding: 18px;
            margin: 15px 0;
            border-radius: 12px;
            font-size: 20px;
            box-shadow: 0 6px 12px rgba(0,0,0,0.15);
        }
        .info-box {
            background: #ecf0f1;
            padding: 20px;
            border-radius: 10px;
            font-size: 17px;
            color: #2c3e50;
            margin-top: 20px;
            box-shadow: inset 0 0 10px rgba(0,0,0,0.05);
        }
        .fire-display {
            font-size: 30px;
            text-align: center;
            margin-top: 25px;
            color: #e74c3c;
            user-select: none;
        }
        footer {
            text-align: center;
            color: white;
            font-weight: 500;
            margin: 40px 0 20px;
        }
    </style>
</head>
<body>

<nav>
    <a class="logo" href="dashboard.php">FinanceApp</a>
    <div class="nav-links">
        <a href="dashboard.php">Dashboard</a>
        <a href="rewards.php">Rewards</a>
        <a href="logout.php" onclick="return confirm('Are you sure?')">Logout</a>
    </div>
</nav>

<div class="container">
    <h1>🏆 Your Rewards for <?php echo date('F Y'); ?></h1>

    <?php if (empty($rewards)): ?>
        <div class="info-box">
            No rewards earned yet. Keep going! 🌱
        </div>
    <?php else: ?>
        <?php foreach ($rewards as $reward): ?>
            <div class="badge">
                <strong><?php echo htmlspecialchars($reward[0]); ?></strong><br />
                <small><?php echo htmlspecialchars($reward[1]); ?></small>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <div class="info-box">
        <strong>Total Saved This Month:</strong> ₹<?php echo number_format($saved, 2); ?><br>
        <strong>Simulator Used:</strong> <?php echo (int)$simulator_used; ?> time(s)<br>
        
    </div>

    <div class="fire-display" title="Monthly Login Streak">
        <?php echo $fire_display ?: "No login streak yet 🔥"; ?>
    </div>

    <?php if ($reward_badge): ?>
        <div class="badge" style="background:#f39c12;">
            <strong>Your Badge:</strong><br>
            <?php echo htmlspecialchars($reward_badge); ?>
        </div>
    <?php endif; ?>
</div>

<footer>
    &copy; <?php echo date('Y'); ?> FinanceApp. All rights reserved.
</footer>

</body>
</html>