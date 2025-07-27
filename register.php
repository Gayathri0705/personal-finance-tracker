<?php
session_start();
require 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($username) || empty($password) || empty($confirm_password)) {
        $error = "Please fill all fields.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $error = "Username already taken.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
            $stmt->bind_param("ss", $username, $hashed_password);
            if ($stmt->execute()) {
                $_SESSION['username'] = $username;
                header("Location: dashboard.php");
                exit();
            } else {
                $error = "Registration failed. Please try again.";
            }
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Register</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        * {
            box-sizing: border-box;
        }
        body {
            background: #7cb8d6;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
        }
        .wrapper {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .form-box {
            background-color: #ffffff;
            padding: 40px 30px;
            border-radius: 12px;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
        }
        .form-box h2 {
            text-align: center;
            margin-bottom: 25px;
            color: #1f2937;
        }
        .form-box input[type="text"],
        .form-box input[type="password"] {
            width: 100%;
            padding: 12px;
            margin-bottom: 20px;
            border: 1.8px solid #cbd5e1;
            border-radius: 8px;
            font-size: 15px;
            transition: border-color 0.3s;
        }
        .form-box input:focus {
            border-color: #2563eb;
            outline: none;
        }
        .form-box input[type="submit"] {
            width: 100%;
            background: #2563eb;
            color: white;
            border: none;
            padding: 12px;
            font-size: 16px;
            border-radius: 8px;
            cursor: pointer;
            transition: background 0.3s;
        }
        .form-box input[type="submit"]:hover {
            background: #1e3a8a;
        }
        .form-box .error {
            background: rgb(255, 230, 230);
            color: #b91c1c;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 6px;
            text-align: center;
            font-weight: 600;
        }
        .form-box p.link {
            text-align: center;
            margin-top: 15px;
            font-size: 14px;
            color: #475569;
        }
        .form-box p.link a {
            color: #2563eb;
            text-decoration: none;
            font-weight: bold;
        }
        .form-box p.link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
<div class="wrapper">
    <div class="form-box">
        <h2>Create an Account</h2>
        <?php if (!empty($error)) : ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <form method="post" action="">
            <input type="text" name="username" placeholder="Username" required />
            <input type="password" name="password" placeholder="Password" required />
            <input type="password" name="confirm_password" placeholder="Confirm Password" required />
            <input type="submit" value="Register" />
        </form>
        <p class="link">Already registered? <a href="index.php">Login here</a></p>
    </div>
</div>
</body>
</html>