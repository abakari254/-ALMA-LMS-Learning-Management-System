<?php
session_start();
require_once "config/db_connection.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$fee_id = $_GET['fee_id'] ?? null;
$amount = $_GET['amount'] ?? 0;

if (!$fee_id) {
    die("Missing payment details.");
}

// Fetch updated fee record
$stmt = $db->prepare("SELECT fee_amount, amount_paid, balance FROM user_fees WHERE fee_id = ? AND user_id = ?");
$stmt->execute([$fee_id, $user_id]);
$fees = $stmt->fetch();

if (!$fees) die("Fee record not found.");

$fee_amount = $fees['fee_amount'];
$amount_paid = $fees['amount_paid'];
$balance = $fees['balance'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Payment Successful - ASHA LMS</title>
<meta http-equiv="refresh" content="5;url=students_dashboard.php">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
<style>
body {
  font-family: 'Poppins', sans-serif;
  background: linear-gradient(135deg, #1e2a78, #24319c);
  color: #333;
  display: flex;
  justify-content: center;
  align-items: center;
  height: 100vh;
  margin: 0;
}
.card {
  background: #fff;
  border-radius: 12px;
  box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
  padding: 40px;
  max-width: 420px;
  width: 100%;
  text-align: center;
  animation: fadeIn 0.8s ease-in-out;
}
h2 {
  color: #1e2a78;
  margin-bottom: 15px;
  font-weight: 600;
}
.success-icon {
  font-size: 60px;
  color: #1e2a78;
  margin-bottom: 20px;
}
p {
  font-size: 16px;
  margin: 10px 0;
  color: #333;
}
.dashboard-link {
  display: inline-block;
  margin-top: 25px;
  background: #1e2a78;
  color: #fff;
  text-decoration: none;
  padding: 12px 25px;
  border-radius: 6px;
  transition: background 0.3s;
}
.dashboard-link:hover {
  background: #24319c;
}
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}
.footer-note {
  font-size: 13px;
  color: #777;
  margin-top: 15px;
}
</style>
</head>
<body>
<div class="card">
    <div class="success-icon">✅</div>
    <h2>Payment Successful!</h2>
    <p>Amount Paid: <strong>KES <?= number_format($amount, 2) ?></strong></p>
    <p>Total Paid So Far: <strong>KES <?= number_format($amount_paid, 2) ?></strong></p>
    <p>Remaining Balance: <strong>KES <?= number_format($balance, 2) ?></strong></p>
    <a href="students_dashboard.php" class="dashboard-link">Go to Dashboard</a>
    <p class="footer-note">You’ll be redirected automatically in 5 seconds...</p>
</div>
</body>
</html>
