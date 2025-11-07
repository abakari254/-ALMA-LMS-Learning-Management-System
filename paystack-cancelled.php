<?php
session_start();
require_once "config/db_config.php";

// Ensure only logged-in users can access
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Payment Cancelled</title>
<style>
body {
    font-family: 'Poppins', sans-serif;
    background: #f4f6f9;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
    margin: 0;
}
.container {
    max-width: 450px;
    background: #fff;
    padding: 30px;
    border-radius: 10px;
    box-shadow: 0 6px 20px rgba(0,0,0,0.1);
    text-align: center;
}
h2 {
    color: #d9534f;
}
p {
    font-size: 16px;
    color: #555;
}
button {
    background: #1e2a78;
    color: #fff;
    padding: 10px 25px;
    border: none;
    border-radius: 6px;
    margin-top: 20px;
    cursor: pointer;
}
button:hover {
    background: #24319c;
}
.icon {
    font-size: 48px;
    margin-bottom: 10px;
    color: #d9534f;
}
</style>
</head>
<body>
<div class="container">
    <div class="icon">✖️</div>
    <h2>Payment Cancelled</h2>
    <p>Your payment was cancelled before completion.</p>
    <p>No charges have been made to your account.</p>
    <button onclick="window.location.href='students_dashboard.php'">Return to Dashboard</button>
</div>
</body>
</html>
