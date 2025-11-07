<?php
// ===========================================
// ALMA UNIVERSITY LMS - Edit Course (Admin)
// ===========================================
session_start();
require_once "config/db_config.php"; // Uses $db (PDO)

// Restrict access to admin only
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$page_title = "Edit Course";
$message = "";

// ✅ Validate course ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: manage_courses.php");
    exit;
}

$course_id = intval($_GET['id']);

// ✅ Fetch course data
try {
    $stmt = $db->prepare("SELECT * FROM student_courses WHERE course_id = ?");
    $stmt->execute([$course_id]);
    $course = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$course) {
        header("Location: manage_courses.php?error=notfound");
        exit;
    }
} catch (PDOException $e) {
    $message = "❌ Database error: " . $e->getMessage();
}

// ✅ Handle course update
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $course_name = trim($_POST['course_name']);
    $code = trim($_POST['code']);
    $department_id = !empty($_POST['department_id']) ? intval($_POST['department_id']) : null;

    if ($course_name && $code) {
        try {
            $stmt = $db->prepare("UPDATE student_courses SET course_name = ?, code = ?, department_id = ? WHERE course_id = ?");
            $stmt->execute([$course_name, $code, $department_id, $course_id]);
            $message = "✅ Course updated successfully!";
            
            // Refresh data
            $stmt = $db->prepare("SELECT * FROM student_courses WHERE course_id = ?");
            $stmt->execute([$course_id]);
            $course = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $message = "❌ Error updating course: " . $e->getMessage();
        }
    } else {
        $message = "⚠️ Please fill in all required fields.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($page_title); ?> | ALMA LMS</title>
    <style>
        :root {
            --primary: #0A1C40;
            --accent: #DAA520;
            --secondary: #F57C00;
            --bg: #F4F7F9;
            --light: #fff;
        }
        body { font-family: 'Segoe UI', sans-serif; background-color: var(--bg); margin: 0; }
        header { background: var(--primary); color: white; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; }
        .btn { background: var(--accent); border: none; color: white; padding: 8px 15px; border-radius: 5px; cursor: pointer; }
        .btn:hover { background: var(--secondary); }
        main { max-width: 600px; margin: 40px auto; background: var(--light); padding: 25px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: var(--primary); border-bottom: 2px solid var(--accent); padding-bottom: 5px; }
        form label { display: block; margin-top: 15px; font-weight: 600; color: var(--primary); }
        input[type=text] { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; margin-top: 5px; }
        .message { padding: 10px; margin-bottom: 15px; border-radius: 6px; font-weight: 600; }
        .success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .actions { margin-top: 20px; display: flex; justify-content: space-between; }
    </style>
</head>
<body>

<header>
    <div><h2>ALMA LMS | Edit Course</h2></div>
    <div>
        <a href="manage_courses.php" class="btn">⬅ Back to Courses</a>
        <a href="logout.php" class="btn" style="background:#c0392b;">Logout</a>
    </div>
</header>

<main>
    <h1>Edit Course</h1>

    <?php if ($message): ?>
        <div class="message <?php echo (strpos($message, '✅') !== false) ? 'success' : 'error'; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <form method="POST">
        <label>Course Name:</label>
        <input type="text" name="course_name" value="<?php echo htmlspecialchars($course['course_name']); ?>" required>

        <label>Course Code:</label>
        <input type="text" name="code" value="<?php echo htmlspecialchars($course['code']); ?>" required>

        <label>Department ID (optional):</label>
        <input type="text" name="department_id" value="<?php echo htmlspecialchars($course['department_id'] ?? ''); ?>">

        <div class="actions">
            <button type="submit" class="btn">💾 Update Course</button>
            <a href="manage_courses.php" class="btn" style="background:#7f8c8d;">Cancel</a>
        </div>
    </form>
</main>

</body>
</html>
