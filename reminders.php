<?php
session_start();
include "includes/db.php";

if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

$user = $_SESSION['username'];

// Add reminder
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_reminder'])) {
    $title = $_POST['title'];
    $amount = $_POST['amount'];
    $remind_on = $_POST['remind_on'];
    $note = $_POST['note'];
    $sql = "INSERT INTO reminders (username, title, amount, remind_on, note) 
            VALUES ('$user', '$title', '$amount', '$remind_on', '$note')";
    mysqli_query($conn, $sql);
    header("Location: reminders.php");
    exit();
}

// Mark as completed
if (isset($_GET['complete_id'])) {
    $id = $_GET['complete_id'];
    $sql = "UPDATE reminders SET completed = 1 WHERE id='$id' AND username='$user'";
    mysqli_query($conn, $sql);
    header("Location: reminders.php");
    exit();
}

// Fetch reminders
$active_reminders = mysqli_query($conn, "SELECT * FROM reminders WHERE username='$user' AND completed=0 ORDER BY remind_on ASC");
$completed_reminders = mysqli_query($conn, "SELECT * FROM reminders WHERE username='$user' AND completed=1 ORDER BY remind_on ASC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>🔔 Your Reminders</title>
    <style>
        body {
            font-family: Arial;
            background: #f5f5f5;
            padding: 30px;
        }

        h2 {
            text-align: center;
            color: #333;
        }

        .form-box {
            max-width: 500px;
            background: #fff;
            margin: auto;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        .form-box input, .form-box textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 12px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }

        .form-box input[type="submit"] {
            background: #3498db;
            color: white;
            border: none;
            cursor: pointer;
        }

        table {
            width: 100%;
            max-width: 800px;
            margin: auto;
            background: #fff;
            border-collapse: collapse;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        th, td {
            padding: 12px;
            text-align: center;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #3498db;
            color: white;
        }

        .complete-btn {
            background: #28a745;
            color: white;
            padding: 6px 12px;
            text-decoration: none;
            border-radius: 4px;
        }

        .complete-btn:hover {
            background: #218838;
        }

        .done-row {
            background-color: #e0e0e0;
            color: #666;
            text-decoration: line-through;
        }

        .overdue {
            background-color: #fff3cd;
        }

        .note-warning {
            color: #e67e22;
            font-size: 13px;
            margin-top: 5px;
        }
    </style>
</head>
<body>

<nav style="background:#3498db; padding:10px 0; margin-bottom:30px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
    <div style="max-width: 800px; margin: auto; display: flex; justify-content: space-between; align-items: center; font-family: Arial, sans-serif;">
        <div style="color: white; font-weight: bold; font-size: 20px;">💡 Finance Project</div>
        <div>
            <a href="dashboard.php" style="color: white; text-decoration: none; margin: 0 15px;">Dashboard</a>
            <a href="reminders.php" style="color: white; text-decoration: none; margin: 0 15px; font-weight: bold; border-bottom: 2px solid white;">Reminders</a>
            <a href="logout.php" style="color: white; text-decoration: none; margin: 0 15px;">Logout</a>
        </div>
    </div>
</nav>

<h2>🔔 Your Reminders</h2>

<div class="form-box">
    <form method="POST" action="">
        <input type="text" name="title" placeholder="Reminder Title" required>
        <input type="number" name="amount" placeholder="Amount (optional)">
        <input type="date" name="remind_on" required>
        <textarea name="note" placeholder="Note (optional)"></textarea>
        <input type="submit" name="add_reminder" value="Add Reminder">
    </form>
</div>

<h3 style="text-align:center;">📝 Active Reminders</h3>
<table>
    <tr>
        <th>Title</th>
        <th>Due Date</th>
        <th>Amount</th>
        <th>Note</th>
        <th>Action</th>
    </tr>

    <?php while ($row = mysqli_fetch_assoc($active_reminders)): ?>
        <?php
            $due_date = strtotime($row['remind_on']);
            $today = strtotime(date('Y-m-d'));
            $is_past_due = $due_date < $today;
        ?>
        <tr class="<?php echo $is_past_due ? 'overdue' : ''; ?>">
            <td><?php echo htmlspecialchars($row['title']); ?></td>
            <td><?php echo date('d M Y', $due_date); ?></td>
            <td>₹<?php echo number_format($row['amount'], 2); ?></td>
            <td>
                <?php echo htmlspecialchars($row['note']); ?>
                <?php if ($is_past_due): ?>
                    <div class="note-warning">⚠ Your due date has passed.</div>
                <?php endif; ?>
            </td>
            <td>
                <a href="reminders.php?complete_id=<?php echo $row['id']; ?>" 
                   class="complete-btn"
                   onclick="return confirm('<?php echo $is_past_due ? 'Your due date has passed. ' : ''; ?>Do you want to mark this reminder as completed?')">
                   ✔ Mark as Done
                </a>
            </td>
        </tr>
    <?php endwhile; ?>
</table>

<h3 style="text-align:center; margin-top: 40px;">✅ Completed Reminders</h3>
<table>
    <tr>
        <th>Title</th>
        <th>Due Date</th>
        <th>Amount</th>
        <th>Note</th>
    </tr>
    <?php while ($row = mysqli_fetch_assoc($completed_reminders)): ?>
        <tr class="done-row">
            <td><?php echo htmlspecialchars($row['title']); ?></td>
            <td><?php echo date('d M Y', strtotime($row['remind_on'])); ?></td>
            <td>₹<?php echo number_format($row['amount'], 2); ?></td>
            <td><?php echo htmlspecialchars($row['note']); ?></td>
        </tr>
    <?php endwhile; ?>
</table>

</body>
</html>