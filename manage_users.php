<?php
session_start();
require_once "config/db_config.php"; // PDO $db

// Restrict access to admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Handle suspend/activate/delete actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $user_id = $_POST['user_id'] ?? '';

    if ($user_id && is_numeric($user_id)) {
        try {
            if ($action === 'delete') {
                $stmt = $db->prepare("DELETE FROM users WHERE user_id=:id AND role='student'");
                $stmt->execute([':id' => $user_id]);
            } elseif ($action === 'toggle_status') {
                // Get current status
                $stmt = $db->prepare("SELECT is_active FROM users WHERE user_id=:id AND role='student'");
                $stmt->execute([':id' => $user_id]);
                $status = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($status) {
                    $new_status = $status['is_active'] ? 0 : 1;
                    $stmt = $db->prepare("UPDATE users SET is_active=:status WHERE user_id=:id");
                    $stmt->execute([':status' => $new_status, ':id' => $user_id]);
                }
            } elseif ($action === 'edit') {
                // Update user info
                $full_name = $_POST['full_name'] ?? '';
                $email = $_POST['email'] ?? '';
                $gender = $_POST['gender'] ?? '';
                $dob = $_POST['dob'] ?? '';
                $stmt = $db->prepare("UPDATE users SET full_name=:full_name, email=:email, gender=:gender, dob=:dob WHERE user_id=:id AND role='student'");
                $stmt->execute([
                    ':full_name' => $full_name,
                    ':email' => $email,
                    ':gender' => $gender,
                    ':dob' => $dob,
                    ':id' => $user_id
                ]);
            }
        } catch (PDOException $e) {
            $error_message = "Error: " . $e->getMessage();
        }
    }
}

// Fetch all students
$stmt = $db->query("SELECT * FROM users WHERE role='student' ORDER BY created_at DESC");
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manage Students - ALMA LMS</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
    body { font-family: 'Poppins', sans-serif; margin:0; background:#f4f6f9; }
    .sidebar { width:220px; height:100vh; background:#1e2a78; color:#fff; position:fixed; padding-top:20px; }
    .sidebar a { display:block; padding:15px 20px; color:#fff; text-decoration:none; transition:.2s; }
    .sidebar a:hover { background:#24319c; }
    .main { margin-left:220px; padding:20px; }
    table { width:100%; border-collapse: collapse; background:#fff; border-radius:10px; overflow:hidden; box-shadow:0 4px 15px rgba(0,0,0,0.1); }
    th, td { padding:12px; text-align:left; border-bottom:1px solid #eee; }
    th { background:#1e2a78; color:#fff; }
    tr:hover { background:#f1f1f1; }
    .status-active { color:#27ae60; font-weight:600; }
    .status-suspended { color:#e74c3c; font-weight:600; }
    .btn { padding:6px 12px; border:none; border-radius:6px; cursor:pointer; margin-right:4px; font-size:14px; }
    .btn-edit { background:#3498db; color:#fff; }
    .btn-toggle { background:#f39c12; color:#fff; }
    .btn-delete { background:#e74c3c; color:#fff; }
    /* Modal styles */
    .modal { display:none; position:fixed; z-index:999; left:0; top:0; width:100%; height:100%; background: rgba(0,0,0,0.5); justify-content:center; align-items:center; }
    .modal-content { background:#fff; padding:20px 30px; border-radius:10px; width:400px; position:relative; }
    .close-btn { position:absolute; top:10px; right:15px; background:#ccc; border:none; padding:5px 8px; cursor:pointer; border-radius:50%; }
    .modal-content input, .modal-content select { width:100%; padding:10px; margin:8px 0; border:1px solid #ccc; border-radius:6px; }
    .modal-content button { width:48%; margin-top:10px; }
</style>
</head>
<body>

<div class="sidebar">
    <h2 style="text-align:center;">ALMA LMS</h2>
    <a href="admin_dashboard.php"><i class="fa-solid fa-chart-line"></i> Dashboard</a>
    <a href="manage_users.php"><i class="fa-solid fa-users"></i> Manage Users</a>
    <a href="manage_courses.php"><i class="fa-solid fa-book"></i> Manage Courses</a>
    <a href="admin_activity.php"><i class="fa-solid fa-list"></i> Activity Log</a>
    <a href="logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
</div>

<div class="main">
    <h1>Manage Students</h1>

    <?php if(isset($error_message)) echo "<div style='color:red;margin-bottom:10px;'>$error_message</div>"; ?>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Full Name</th>
                <th>Email</th>
                <th>Gender</th>
                <th>DOB</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($students as $index => $s): ?>
            <tr>
                <td><?= $index+1 ?></td>
                <td><?= htmlspecialchars($s['full_name']) ?></td>
                <td><?= htmlspecialchars($s['email']) ?></td>
                <td><?= htmlspecialchars($s['gender']) ?></td>
                <td><?= htmlspecialchars($s['dob']) ?></td>
                <td class="<?= $s['is_active'] ? 'status-active' : 'status-suspended' ?>">
                    <?= $s['is_active'] ? 'Active' : 'Suspended' ?>
                </td>
                <td>
                    <button class="btn btn-edit" onclick="openEditModal(<?= $s['user_id'] ?>,'<?= htmlspecialchars($s['full_name'],ENT_QUOTES) ?>','<?= htmlspecialchars($s['email'],ENT_QUOTES) ?>','<?= $s['gender'] ?>','<?= $s['dob'] ?>')">Edit</button>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="user_id" value="<?= $s['user_id'] ?>">
                        <input type="hidden" name="action" value="toggle_status">
                        <button type="submit" class="btn btn-toggle"><?= $s['is_active'] ? 'Suspend' : 'Activate' ?></button>
                    </form>
                    <form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this student?');">
                        <input type="hidden" name="user_id" value="<?= $s['user_id'] ?>">
                        <input type="hidden" name="action" value="delete">
                        <button type="submit" class="btn btn-delete">Delete</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Edit Modal -->
<div class="modal" id="editModal">
    <div class="modal-content">
        <button class="close-btn" onclick="closeModal()">×</button>
        <h3>Edit Student</h3>
        <form method="POST">
            <input type="hidden" name="user_id" id="edit_user_id">
            <input type="hidden" name="action" value="edit">
            <label>Full Name</label>
            <input type="text" name="full_name" id="edit_full_name" required>
            <label>Email</label>
            <input type="email" name="email" id="edit_email" required>
            <label>Gender</label>
            <select name="gender" id="edit_gender" required>
                <option value="">Select Gender</option>
                <option value="Male">Male</option>
                <option value="Female">Female</option>
                <option value="Other">Other</option>
            </select>
            <label>Date of Birth</label>
            <input type="date" name="dob" id="edit_dob" required>
            <button type="submit" class="btn btn-edit">Save</button>
            <button type="button" class="btn close-btn" onclick="closeModal()">Cancel</button>
        </form>
    </div>
</div>

<script>
function openEditModal(id, full_name, email, gender, dob) {
    document.getElementById('edit_user_id').value = id;
    document.getElementById('edit_full_name').value = full_name;
    document.getElementById('edit_email').value = email;
    document.getElementById('edit_gender').value = gender;
    document.getElementById('edit_dob').value = dob;
    document.getElementById('editModal').style.display = 'flex';
}
function closeModal() {
    document.getElementById('editModal').style.display = 'none';
}
</script>

</body>
</html>
