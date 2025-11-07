<?php
session_start();ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$_SESSION['last_dashboard'] = 'admin_dashboard.php';

require_once "config/db_config.php"; // PDO connection $db

// Restrict access to admin only
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Fetch dashboard metrics
try {
    $stmt = $db->query("SELECT COUNT(*) as total_users FROM users");
    $total_users = $stmt->fetch(PDO::FETCH_ASSOC)['total_users'];

    $stmt = $db->query("SELECT COUNT(*) as active_courses FROM student_courses WHERE status='active'");
    $active_courses = $stmt->fetch(PDO::FETCH_ASSOC)['active_courses'];

    $stmt = $db->query("SELECT COUNT(*) as pending_approvals FROM users WHERE email_verified=0");
    $pending_approvals = $stmt->fetch(PDO::FETCH_ASSOC)['pending_approvals'];

    $stmt = $db->query("SELECT * FROM admin_activity ORDER BY created_at DESC LIMIT 5");
    $recent_activities = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

// Card colors
$card_colors = [
    'users' => '#1e2a78',
    'courses' => '#27ae60',
    'pending' => ($pending_approvals > 0) ? '#e74c3c' : '#f39c12'
];

// Load all users (for modal view)
$users = $db->query("
    SELECT u.user_id, u.full_name, u.email, u.role, u.email_verified, 
           f.balance, f.amount_paid, f.fee_amount
    FROM users u
    LEFT JOIN user_fees f ON u.user_id = f.user_id
    ORDER BY u.user_id DESC
")->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Dashboard - ALMA LMS</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
    body { font-family: 'Poppins', sans-serif; margin: 0; background: #f4f6f9; }
    .sidebar { width: 220px; height: 100vh; background: #1e2a78; color: #fff; position: fixed; padding-top: 20px; }
    .sidebar a { display: block; padding: 15px 20px; color: #fff; text-decoration: none; transition: 0.2s; }
    .sidebar a:hover { background: #24319c; }
    .main { margin-left: 220px; padding: 20px; }
    .dashboard-cards { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; }
    .card { background: #fff; padding: 20px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); display: flex; align-items: center; justify-content: space-between; cursor: pointer; transition: 0.3s; }
    .card:hover { transform: scale(1.03); }
    .card i { font-size: 40px; }
    .card .info { text-align: right; }
    .card .info h3 { margin: 0; font-size: 24px; }
    .card .info p { margin: 5px 0 0 0; font-size: 14px; color: #555; }
    .activities { margin-top: 30px; background: #fff; padding: 20px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
    .activities h3 { margin-top: 0; }
    .activities ul { list-style: none; padding: 0; }
    .activities li { padding: 10px 0; border-bottom: 1px solid #eee; }
    table { width: 100%; border-collapse: collapse; margin-top: 10px; }
    th, td { padding: 8px 10px; border: 1px solid #ddd; text-align: left; font-size: 14px; }
    th { background: #1e2a78; color: white; }
    .btn { padding: 5px 10px; background: #1e2a78; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 12px; }
    .btn:hover { background: #24319c; }
    /* Modal styling */
    .modal { display: none; position: fixed; z-index: 10; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.6); }
    .modal-content { background: white; margin: 5% auto; padding: 20px; border-radius: 12px; width: 90%; max-width: 900px; }
    .close { float: right; font-size: 22px; cursor: pointer; color: #555; }
    .close:hover { color: red; }
</style>
</head>
<body>

<div class="sidebar">
    <h2 style="text-align:center;">ALMA LMS</h2>
    <a href="admin_dashboard.php"><i class="fa-solid fa-chart-line"></i> Dashboard</a>
    <a href="manage_users.php"><i class="fa-solid fa-users"></i> Manage Users</a>
    <a href="manage_courses.php"><i class="fa-solid fa-book"></i> Manage Courses</a>
    <a href="view_statement_fee.php"><i class="fa-solid fa-money-bill-wave"></i> Manage Fee Statements</a>
    <a href="admin_activity.php"><i class="fa-solid fa-list"></i> Activity Log</a>
    <a href="logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
</div>

<div class="main">
    <h1>Admin Dashboard</h1>
    <div class="dashboard-cards">
        <div class="card" style="border-left: 5px solid <?= $card_colors['users'] ?>;" onclick="openModal('usersModal')">
            <i class="fa-solid fa-users" style="color: <?= $card_colors['users'] ?>;"></i>
            <div class="info">
                <h3><?= $total_users ?></h3>
                <p>Total Users</p>
            </div>
        </div>
        <div class="card" style="border-left: 5px solid <?= $card_colors['courses'] ?>;" onclick="openModal('coursesModal')">
            <i class="fa-solid fa-book" style="color: <?= $card_colors['courses'] ?>;"></i>
            <div class="info">
                <h3><?= $active_courses ?></h3>
                <p>Active Courses</p>
            </div>
        </div>
        <div class="card" style="border-left: 5px solid <?= $card_colors['pending'] ?>;" onclick="openModal('pendingModal')">
            <i class="fa-solid fa-clock" style="color: <?= $card_colors['pending'] ?>;"></i>
            <div class="info">
                <h3><?= $pending_approvals ?></h3>
                <p>Pending Approvals</p>
            </div>
        </div>
    </div>

    <div class="activities">
        <h3>Recent Admin Activities</h3>
        <ul>
            <?php if($recent_activities): ?>
                <?php foreach($recent_activities as $activity): ?>
                    <li>
                        <?= htmlspecialchars($activity['action']) ?> -
                        <small><?= htmlspecialchars($activity['created_at']) ?></small>
                    </li>
                <?php endforeach; ?>
            <?php else: ?>
                <li>No recent activities.</li>
            <?php endif; ?>
        </ul>
    </div>
</div>

<!-- Users Modal -->
<div id="usersModal" class="modal">
  <div class="modal-content">
    <span class="close" onclick="closeModal('usersModal')">&times;</span>
    <h3>All Users & Fee Balances</h3>
    <table>
      <tr>
        <th>Name</th><th>Email</th><th>Role</th><th>Verified</th><th>Fee Amount</th><th>Paid</th><th>Balance</th><th>Action</th>
      </tr>
      <?php foreach($users as $u): ?>
        <tr>
          <td><?= htmlspecialchars($u['full_name']) ?></td>
          <td><?= htmlspecialchars($u['email']) ?></td>
          <td><?= htmlspecialchars($u['role']) ?></td>
          <td><?= $u['email_verified'] ? 'Yes' : 'No' ?></td>
          <td><?= $u['fee_amount'] ?? '0.00' ?></td>
          <td><?= $u['amount_paid'] ?? '0.00' ?></td>
          <td><?= $u['balance'] ?? '0.00' ?></td>
          <td><a href="view_fee_statement.php?user_id=<?= $u['id'] ?>" class="btn">View Statement</a></td>
        </tr>
      <?php endforeach; ?>
    </table>
  </div>
</div>

<!-- Pending Approvals Modal -->
<div id="pendingModal" class="modal">
  <div class="modal-content">
    <span class="close" onclick="closeModal('pendingModal')">&times;</span>
    <h3>Pending Approvals</h3>
    <table>
      <tr><th>Name</th><th>Email</th><th>Action</th></tr>
      <?php
      $pending = $db->query("SELECT id, full_name, email FROM users WHERE email_verified=0")->fetchAll(PDO::FETCH_ASSOC);
      foreach ($pending as $p): ?>
        <tr>
          <td><?= htmlspecialchars($p['full_name']) ?></td>
          <td><?= htmlspecialchars($p['email']) ?></td>
          <td><a href="verify_user.php?id=<?= $p['id'] ?>" class="btn">Approve</a></td>
        </tr>
      <?php endforeach; ?>
    </table>
  </div>
</div>

<!-- Active Courses Modal -->
<div id="coursesModal" class="modal">
  <div class="modal-content">
    <span class="close" onclick="closeModal('coursesModal')">&times;</span>
    <h3>Active Courses</h3>
    <table>
      <tr><th>Student ID</th><th>Course Name</th><th>Status</th></tr>
      <?php
      $courses = $db->query("SELECT user_id, course_name, status FROM student_courses WHERE status='active'")->fetchAll(PDO::FETCH_ASSOC);
      foreach ($courses as $c): ?>
        <tr>
          <td><?= htmlspecialchars($c['user_id']) ?></td>
          <td><?= htmlspecialchars($c['course_name']) ?></td>
          <td><?= htmlspecialchars($c['status']) ?></td>
        </tr>
      <?php endforeach; ?>
    </table>
  </div>
</div>

<script>
function openModal(id) { document.getElementById(id).style.display = "block"; }
function closeModal(id) { document.getElementById(id).style.display = "none"; }
window.onclick = function(e) {
    const modals = document.querySelectorAll(".modal");
    modals.forEach(modal => { if (e.target === modal) modal.style.display = "none"; });
}
</script>

</body>
</html>
