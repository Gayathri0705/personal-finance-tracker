<?php
session_start();
session_unset();
session_destroy();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Logged Out - Smart Finance Tracker</title>
    <meta http-equiv="refresh" content="2;url=index.php">
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(to right,rgb(163, 218, 218),rgb(214, 34, 230));
            color: white;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .logout-message {
            text-align: center;
            background: rgba(0,0,0,0.3);
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 0 10px rgba(0,0,0,0.3);
        }

        .logout-message h2 {
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="logout-message">
        <h2>You have been logged out!</h2>
        <p>Redirecting to login page...</p>
    </div>
</body>
</html>