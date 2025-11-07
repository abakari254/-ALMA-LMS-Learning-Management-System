<?php
session_start();
require_once "config/db_config.php";

// Ensure admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Fetch all admin activity logs
try {
    $stmt = $db->prepare("SELECT activity_id, admin_id, action, description, created_at FROM admin_activity ORDER BY created_at DESC");
    $stmt->execute();
    $activities = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Activity - ALMA LMS</title>
<link rel="stylesheet" href="assets/css/style.css">
<style>
body { font-family:'Segoe UI', Arial, sans-serif; background:#f8faff; padding:30px; }
.container { max-width:1000px; margin:auto; background:#fff; padding:20px; border-radius:10px; box-shadow:0 3px 10px rgba(0,0,0,0.05); }
h2 { color:#004aad; margin-bottom:20px; }
table { width:100%; border-collapse:collapse; margin-top:10px; }
th, td { padding:10px; border-bottom:1px solid #eee; text-align:left; }
th { background:#f1f5f9; color:#333; }
tr:nth-child(even) { background:#f9f9f9; }
.status { font-weight:bold; }
a.back { display:inline-block; margin-top:20px; text-decoration:none; color:#004aad; }
a.back:hover { text-decoration:underline; }
</style>
</head>
<body>

<div class="container">
    <h2>📝 Admin Activity Logs</h2>
    <?php if(empty($activities)): ?>
        <p>No activity logs found.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Admin ID</th>
                    <th>Action</th>
                    <th>Description</th>
                    <th>Created At</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($activities as $act): ?>
                    <tr>
                        <td><?= htmlspecialchars($act['activity_id']) ?></td>
                        <td><?= htmlspecialchars($act['admin_id']) ?></td>
                        <td><?= htmlspecialchars($act['action']) ?></td>
                        <td><?= htmlspecialchars($act['description']) ?></td>
                        <td><?= htmlspecialchars($act['created_at']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <a href="admin_dashboard.php" class="back">← Back to Dashboard</a>
</div>

</body>
</html>
