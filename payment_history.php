<?php
session_start();
require_once "config/db_config.php";

// ✅ Ensure student is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// ✅ Fetch student name
$stmt = $db->prepare("SELECT full_name FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
$full_name = $user ? $user['full_name'] : 'Unknown Student';

// ✅ Fetch all payments with real status
$stmt = $db->prepare("
    SELECT amount_paid,full_name, payment_date, status, transaction_ref
    FROM payment_history
    WHERE user_id = ?
    ORDER BY payment_date DESC
");
$stmt->execute([$user_id]);
$payments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Payment History - ALMA LMS</title>
<style>
body { font-family:'Segoe UI', Arial, sans-serif; background:#f8faff; padding:30px; margin:0; }
h2 { color:#004aad; margin-bottom:5px; }
p.sub { color:#555; margin-top:0; font-size:15px; }
table { width:100%; border-collapse:collapse; margin-top:12px; background:#fff; border-radius:8px; overflow:hidden; box-shadow:0 2px 10px rgba(0,0,0,0.05); }
th, td { padding:12px 10px; border-bottom:1px solid #eee; text-align:left; font-size:15px; }
th { background:#004aad; color:#fff; text-transform:uppercase; font-size:14px; letter-spacing:0.5px; }
.status.success { color:green; font-weight:bold; }
.status.pending { color:orange; font-weight:bold; }
.status.failed { color:red; font-weight:bold; }
a.back { display:inline-block; margin-top:20px; text-decoration:none; color:#004aad; font-weight:500; }
a.back:hover { text-decoration:underline; }
</style>
</head>
<body>

<h2>🧾 Full Payment History</h2>
<p class="sub"><strong>Student Name:</strong> <?= htmlspecialchars($full_name) ?></p>

<?php if (count($payments) > 0): ?>
<table>
    <tr>
        <th>Amount (Ksh)</th>
        <th>Date</th>
        <th>Status</th>
        <th>Reference</th>
    </tr>
    <?php foreach ($payments as $pay): ?>
    <tr>
        <td><?= number_format($pay['amount_paid'], 2) ?></td>
        <td><?= htmlspecialchars($pay['payment_date']) ?></td>
        <td class="status <?= strtolower($pay['status']) ?>">
            <?= htmlspecialchars(ucfirst($pay['status'])) ?>
        </td>
        <td><?= htmlspecialchars($pay['transaction_ref']) ?></td>
    </tr>
    <?php endforeach; ?>
</table>
<?php else: ?>
<p>No payment records found.</p>
<?php endif; ?>

<a href="students_dashboard.php" class="back">← Back to Dashboard</a>
</body>
</html>
