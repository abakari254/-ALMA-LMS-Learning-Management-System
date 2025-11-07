<?php
session_start();
require_once "config/db_config.php";

// Ensure student is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    exit("<p>Please log in to view payments.</p>");
}

$user_id = $_SESSION['user_id'];

// Fetch last 5 payments
$stmt = $db->prepare("
    SELECT amount, payment_date, status, reference 
    FROM payment_history 
    WHERE user_id = ? 
    ORDER BY payment_date DESC 
    LIMIT 5
");
$stmt->execute([$user_id]);
$payments = $stmt->fetchAll(PDO::FETCH_ASSOC);

if(!$payments) {
    echo "<p>No recent payments found.</p>";
    exit;
}
?>

<table>
    <tr>
        <th>Amount (Ksh)</th>
        <th>Date</th>
        <th>Status</th>
        <th>Reference</th>
    </tr>
    <?php foreach($payments as $pay): ?>
    <tr>
        <td><?= number_format($pay['amount'],2) ?></td>
        <td><?= htmlspecialchars($pay['payment_date']) ?></td>
        <td class="status <?= strtolower($pay['status']) ?>"><?= htmlspecialchars($pay['status']) ?></td>
        <td><?= htmlspecialchars($pay['reference']) ?></td>
    </tr>
    <?php endforeach; ?>
</table>
