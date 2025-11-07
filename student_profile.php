<?php
session_start();
require_once "config/db_config.php";

// Ensure student is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch current student info
$stmt = $db->prepare("SELECT full_name, email, admission_no FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);

// Handle form submission
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);

    // Simple validation
    if (empty($full_name) || empty($email)) {
        $message = "Full name and email cannot be empty.";
    } else {
        // Update database
        $stmt = $db->prepare("UPDATE users SET full_name = ?, email = ? WHERE user_id = ?");
        if ($stmt->execute([$full_name, $email, $user_id])) {
            $message = "Profile updated successfully!";
            // Refresh the student info
            $student['full_name'] = $full_name;
            $student['email'] = $email;
        } else {
            $message = "Failed to update profile. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit Profile - ALMA LMS</title>
<link rel="stylesheet" href="assets/css/style.css">
<style>
body { font-family:'Segoe UI', Arial, sans-serif; background:#f8faff; padding:30px; }
form { max-width:500px; margin:auto; background:#fff; padding:20px; border-radius:10px; box-shadow:0 3px 10px rgba(0,0,0,0.05); }
label { display:block; margin-top:15px; font-weight:bold; }
input[type=text], input[type=email] { width:100%; padding:10px; margin-top:5px; border:1px solid #ccc; border-radius:6px; }
button { margin-top:20px; padding:10px 15px; background:#004aad; color:white; border:none; border-radius:6px; cursor:pointer; }
button:hover { background:#003380; }
.message { margin-top:15px; font-weight:bold; color:green; }
a.back { display:inline-block; margin-top:20px; text-decoration:none; color:#004aad; }
a.back:hover { text-decoration:underline; }
</style>
</head>
<body>

<h2>Edit Profile</h2>

<?php if(!empty($message)): ?>
<p class="message"><?= htmlspecialchars($message) ?></p>
<?php endif; ?>

<form method="POST">
    <label for="full_name">Full Name</label>
    <input type="text" id="full_name" name="full_name" value="<?= htmlspecialchars($student['full_name']) ?>" required>

    <label for="email">Email</label>
    <input type="email" id="email" name="email" value="<?= htmlspecialchars($student['email']) ?>" required>

    <button type="submit">Update Profile</button>
</form>

<a href="students_dashboard.php" class="back">← Back to Dashboard</a>

</body>
</html>
