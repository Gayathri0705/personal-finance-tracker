<?php
session_start();
include "includes/db.php";

if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

$user = $_SESSION['username'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>💡 Spend/Save Simulator</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #6697ca;
            padding: 30px;
            text-align: center;
            margin: 0;
        }

        .navbar {
            background-color: #fff;
            padding: 12px 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            display: flex;
            justify-content: center;
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .navbar a {
            margin: 0 15px;
            text-decoration: none;
            color: #333;
            font-weight: bold;
        }

        .container {
            background: white;
            max-width: 500px;
            margin: 40px auto 0 auto;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }

        h2 {
            color: #333;
        }

        select, input[type="number"] {
            width: 80%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 6px;
        }

        button {
            background-color: #007bff;
            color: white;
            padding: 10px 18px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            margin-top: 10px;
        }

        button:hover {
            background-color: #0056b3;
        }

        .result {
            margin-top: 20px;
            font-size: 18px;
            background: #d4edda;
            color: #155724;
            padding: 12px;
            border-radius: 8px;
            display: none;
        }
    </style>
</head>
<body>

    <div class="navbar">
        <a href="dashboard.php">Dashboard</a>
        <a href="logout.php">Logout</a>
    </div>

    <div class="container">
        <h2>💡 Spend/Save Simulator</h2>

        <label for="type">Choose Type:</label><br>
        <select id="type">
            <option value="spend">Spend (e.g., Rent)</option>
            <option value="save">Save (e.g., SIP)</option>
        </select><br>

        <label for="amount">Monthly Amount (₹):</label><br>
        <input type="number" id="amount" placeholder="Enter amount" required><br>

        <label for="months">Number of Months:</label><br>
        <input type="number" id="months" placeholder="Enter months" required><br>

        <button onclick="calculate()">Calculate Total</button>

        <div id="result" class="result"></div>
    </div>

<script>
    function calculate() {
        const type = document.getElementById("type").value;
        const amount = parseFloat(document.getElementById("amount").value);
        const months = parseInt(document.getElementById("months").value);
        const resultBox = document.getElementById("result");

        if (isNaN(amount) || isNaN(months) || amount <= 0 || months <= 0) {
            resultBox.style.display = 'block';
            resultBox.style.background = '#f8d7da';
            resultBox.style.color = '#721c24';
            resultBox.innerText = "❌ Please enter valid positive numbers.";
            return;
        }

        const total = amount * months;
        let message = "";

        if (type === "spend") {
            message = "You will spend a total of ₹" + total.toFixed(2) + " in " + months + " months.";
        } else {
            message = "You will save a total of ₹" + total.toFixed(2) + " in " + months + " months.";
        }

        resultBox.style.display = 'block';
        resultBox.style.background = '#d4edda';
        resultBox.style.color = '#155724';
        resultBox.innerText = message;

        // Increment simulator usage on backend
        fetch('increment_simulator_usage.php')
            .then(res => res.text())
            .then(data => console.log("Simulator usage recorded"))
            .catch(err => console.error("Failed to update simulator usage", err));
    }
</script>

</body>
</html>