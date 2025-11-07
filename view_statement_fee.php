<?php
session_start();
require_once "config/db_config.php";

// Show errors during debugging (optional — remove when live)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// ✅ Restrict access to logged-in users only
if (!isset($_SESSION['role'])) {
    header("Location: login.php");
    exit;
}

$user_id = isset($_GET['user_id']) && is_numeric($_GET['user_id']) ? (int)$_GET['user_id'] : null;

try {
    if ($user_id) {
        // ✅ Fetch single user and fee info
        $stmt = $db->prepare("
            SELECT 
                u.user_id, 
                u.full_name, 
                u.email, 
                u.role,
                COALESCE(f.fee_amount, 0) AS fee_amount, 
                COALESCE(f.amount_paid, 0) AS amount_paid, 
                COALESCE(f.balance, 0) AS balance
            FROM users u
            LEFT JOIN user_fees f ON u.user_id = f.user_id
            WHERE u.user_id = ?
            LIMIT 1
        ");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            $error_message = "User not found.";
        } else {
            // ✅ Fetch payment history
            $stmt = $db->prepare("
                SELECT 
                    transaction_ref,
                    amount_paid,
                    payment_date,
                    payment_method,
                    status
                FROM payment_history
                WHERE user_id = ?
                ORDER BY payment_date DESC
            ");
            $stmt->execute([$user_id]);
            $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    } else {
        // ✅ No user selected — show all users instead (for admin view)
        $stmt = $db->query("
            SELECT 
                u.user_id, 
                u.full_name, 
                u.email, 
                u.role,
                COALESCE(f.balance, 0) AS balance
            FROM users u
            LEFT JOIN user_fees f ON u.user_id = f.user_id
            ORDER BY u.full_name ASC
        ");
        $all_users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

} catch (PDOException $e) {
    $error_message = "Database error: " . htmlspecialchars($e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $user_id ? "Fee Statement - " . htmlspecialchars($user['full_name'] ?? '') : "Manage Fee Statements" ?></title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
    body { font-family: 'Poppins', sans-serif; background: #f4f6f9; margin: 0; }
    .container { max-width: 950px; margin: 40px auto; background: white; border-radius: 12px; padding: 30px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
    h1 { color: #1e2a78; margin-bottom: 20px; }
    h3 { color: #24319c; margin-top: 30px; }
    table { width: 100%; border-collapse: collapse; margin-top: 10px; }
    th, td { padding: 10px; border: 1px solid #ddd; text-align: left; font-size: 14px; }
    th { background: #1e2a78; color: #fff; }
    .summary { display: flex; justify-content: space-between; margin-top: 20px; flex-wrap: wrap; }
    .card { flex: 1; min-width: 180px; background: #f8f9fc; padding: 15px; margin: 10px; border-radius: 8px; text-align: center; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
    .card h2 { margin: 0; color: #1e2a78; font-size: 20px; }
    .btn-back { background: #1e2a78; color: white; padding: 8px 15px; text-decoration: none; border-radius: 5px; display: inline-block; }
    .btn-back:hover { background: #24319c; }
    .btn-view { background: #27ae60; color: white; padding: 6px 12px; border-radius: 4px; text-decoration: none; }
    .btn-view:hover { background: #2ecc71; }
    .no-records { text-align: center; padding: 20px; color: #888; }
</style>
</head>
<body>

<div class="container">
<?php if (isset($error_message)): ?>
    <p style="text-align:center; color:#e74c3c;"><?= $error_message ?></p>

<?php elseif (!$user_id): ?>
    <h1>Manage Fee Statements</h1>
    <table>
        <tr>
            <th>Student Name</th>
            <th>Email</th>
            <th>Role</th>
            <th>Balance (KES)</th>
            <th>Action</th>
        </tr>
        <?php if ($all_users): ?>
            <?php foreach ($all_users as $u): ?>
                <tr>
                    <td><?= htmlspecialchars($u['full_name']) ?></td>
                    <td><?= htmlspecialchars($u['email']) ?></td>
                    <td><?= htmlspecialchars($u['role']) ?></td>
                    <td><?= number_format($u['balance'], 2) ?></td>
                    <td><a class="btn-view" href="?user_id=<?= $u['user_id'] ?>">View Statement</a></td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="5" class="no-records">No users found.</td></tr>
        <?php endif; ?>
    </table>

<?php else: ?>
    <?php 
        // ✅ Determine where to go back based on role
        $backPage = ($_SESSION['role'] === 'admin') ? 'admin_dashboard.php' : 'view_statement_fee.php';
    ?>
    <a href="<?= $backPage ?>" class="btn-back"><i class="fa-solid fa-arrow-left"></i> Exit</a>

    <h1>Fee Statement</h1>
    <p>
        <strong>Student Name:</strong> <?= htmlspecialchars($user['full_name']) ?><br>
        <strong>Email:</strong> <?= htmlspecialchars($user['email']) ?><br>
        <strong>Role:</strong> <?= htmlspecialchars($user['role']) ?>
    </p>

    <div class="summary">
        <div class="card">
            <h2>KES <?= number_format($user['fee_amount'], 2) ?></h2>
            <p>Total Fee</p>
        </div>
        <div class="card">
            <h2>KES <?= number_format($user['amount_paid'], 2) ?></h2>
            <p>Amount Paid</p>
        </div>
        <div class="card">
            <h2>KES <?= number_format($user['balance'], 2) ?></h2>
            <p>Outstanding Balance</p>
        </div>
    </div>

    <h3>Payment History</h3>
    <?php if ($payments && count($payments) > 0): ?>
        <table>
            <tr>
                <th>Transaction ID</th>
                <th>Amount</th>
                <th>Payment Date</th>
                <th>Method</th>
                <th>Status</th>
            </tr>
            <?php foreach ($payments as $p): ?>
                <tr>
                    <td><?= htmlspecialchars($p['transaction_ref']) ?></td>
                    <td><?= number_format($p['amount_paid'], 2) ?></td>
                    <td><?= htmlspecialchars($p['payment_date']) ?></td>
                    <td><?= htmlspecialchars($p['payment_method']) ?></td>
                    <td><?= htmlspecialchars(ucfirst($p['status'])) ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php else: ?>
        <div class="no-records">No payment history found for this student.</div>
    <?php endif; ?>
<?php endif; ?>
</div>

</body>
</html>
