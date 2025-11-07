<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once "config/db_config.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch user info
$stmt = $db->prepare("SELECT email, full_name FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) die("User not found.");

// Fetch fees
$stmt = $db->prepare("SELECT fee_id, fee_amount, amount_paid, balance FROM user_fees WHERE user_id = ? LIMIT 1");
$stmt->execute([$user_id]);
$fees = $stmt->fetch();

if (!$fees) die("No fee record found.");

$fee_id = $fees['fee_id'];
$fee_amount = $fees['fee_amount'];
$amount_paid = $fees['amount_paid'];
$balance = $fees['balance'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Fee Payment</title>
<style>
body { font-family: Poppins, sans-serif; background: #f4f6f9; }
.container { max-width: 500px; margin: 50px auto; background: #fff; padding: 30px; border-radius: 10px; box-shadow: 0 6px 20px rgba(0,0,0,0.1);}
h2 { color: #1e2a78; text-align:center; }
.balance { font-size: 18px; margin-bottom: 15px; }
input { width: 100%; padding: 10px; margin: 10px 0; border-radius: 6px; border:1px solid #ccc; }
button { background: #1e2a78; color: #fff; padding: 12px; border:none; border-radius:6px; width:100%; cursor:pointer; }
button:hover { background: #24319c; }
</style>
</head>
<body>
<div class="container">
    <h2>Fee Payment</h2>
    <div class="balance">
        <strong>Total Fee:</strong> KES <?= number_format($fee_amount, 2) ?><br>
        <strong>Amount Paid:</strong> KES <?= number_format($amount_paid, 2) ?><br>
        <strong>Balance:</strong> KES <?= number_format($balance, 2) ?>
    </div>

    <?php if ($balance > 0): ?>
    <form action="https://eagletechafrica.com/ashabakari/paystack-api.php" method="POST">
        <input type="hidden" name="user_id" value="<?= $user_id ?>">
        <input type="hidden" name="fee_id" value="<?= $fee_id ?>">
        <input type="hidden" name="email" value="<?= htmlspecialchars($user['email']) ?>">
        <label>Enter Amount to Pay (KES)</label>
        <input type="number" name="amount" max="<?= $balance ?>" min="1" required>
        <button type="submit">Pay Now</button>
    </form>
    <?php else: ?>
    <p style="color:green; text-align:center;">All fees are fully paid!</p>
    <?php endif; ?>
</div>
</body>
</html>
