<?php
session_start();
require 'includes/db.php';

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($username) || empty($new_password) || empty($confirm_password)) {
        $error = "Please fill in all fields.";
    } elseif ($new_password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows === 1) {
            $stmt->close();
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE username = ?");
            $update_stmt->bind_param("ss", $hashed_password, $username);
            if ($update_stmt->execute()) {
                $success = "Password reset successfully! You can now <a href='index.php' style='color:#1e90ff;'>login</a>.";
            } else {
                $error = "Failed to reset password. Please try again.";
            }
        } else {
            $error = "Username does not exist.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Reset Password</title>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap');

        * {
            box-sizing: border-box;
        }
        body {
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            font-family: 'Montserrat', sans-serif;
            margin: 0; padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .container {
            background: #fff;
            border-radius: 12px;
            padding: 40px 35px;
            max-width: 400px;
            width: 100%;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            text-align: center;
        }
        h2 {
            margin-bottom: 25px;
            color: #333;
            font-weight: 700;
            letter-spacing: 1px;
        }
        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 14px 12px;
            margin-bottom: 20px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s ease;
        }
        input[type="text"]:focus, input[type="password"]:focus {
            border-color: #2575fc;
            outline: none;
        }
        input[type="submit"] {
            background: #2575fc;
            color: white;
            border: none;
            padding: 15px;
            width: 100%;
            font-size: 18px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 700;
            transition: background 0.3s ease;
        }
        input[type="submit"]:hover {
            background: #1a52d1;
        }
        .message {
            margin-bottom: 20px;
            padding: 12px 15px;
            border-radius: 8px;
            font-weight: 600;
        }
        .error {
            background: #f8d7da;
            color: #842029;
            border: 1px solid #f5c2c7;
        }
        .success {
            background: #d1e7dd;
            color: #0f5132;
            border: 1px solid #badbcc;
        }
        p.link {
            margin-top: 15px;
            font-size: 14px;
            color: #555;
        }
        p.link a {
            color: #2575fc;
            text-decoration: none;
            font-weight: 600;
        }
        p.link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Reset Password</h2>
        <?php if($error): ?>
            <div class="message error"><?php echo htmlspecialchars($error); ?></div>
        <?php elseif($success): ?>
            <div class="message success"><?php echo $success; ?></div>
        <?php endif; ?>

        <form method="post" action="">
            <input type="text" name="username" placeholder="Enter your username" required />
            <input type="password" name="new_password" placeholder="Enter new password" required />
            <input type="password" name="confirm_password" placeholder="Confirm new password" required />
            <input type="submit" value="Reset Password" />
        </form>

        <p class="link"><a href="index.php">Back to Login</a></p>
    </div>
</body>
</html>