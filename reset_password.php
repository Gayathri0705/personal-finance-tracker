<?php
session_start();
include 'includes/db.php';

if (!isset($_GET['token'])) {
    die('Invalid or missing token');
}

$token = mysqli_real_escape_string($conn, $_GET['token']);

// Verify token & expiry
$result = mysqli_query($conn, "SELECT username, token_expiry FROM users WHERE reset_token='$token'");
if (mysqli_num_rows($result) === 0) {
    die('Invalid token');
}

$row = mysqli_fetch_assoc($result);
$expiry = $row['token_expiry'];
if (strtotime($expiry) < time()) {
    die('Token expired, please request a new password reset.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];

    if ($password !== $password_confirm) {
        echo "Passwords do not match.";
    } else {
        // Hash new password
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        // Update password, clear token and expiry
        mysqli_query($conn, "UPDATE users SET password='$password_hash', reset_token=NULL, token_expiry=NULL WHERE reset_token='$token'");

        echo "Password successfully reset! <a href='index.php'>Login now</a>";
        exit;
    }
}
?>

<form method="POST">
    <label>New Password:</label><br />
    <input type="password" name="password" required /><br />
    <label>Confirm New Password:</label><br />
    <input type="password" name="password_confirm" required /><br />
    <button type="submit">Reset Password</button>
</form>