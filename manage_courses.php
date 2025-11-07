<?php
// ===========================================
// ALMA UNIVERSITY LMS - Admin Course Management
// ===========================================
session_start();
require_once "config/db_config.php"; // Uses $db (PDO)

// Restrict access to admin only
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$page_title = "Manage Courses";
$message = "";

// ✅ ADD NEW COURSE
if (isset($_POST['action']) && $_POST['action'] === 'add_course') {
    $course_name = trim($_POST['course_name']);
    $code = trim($_POST['code']);
    $department_id = !empty($_POST['department_id']) ? intval($_POST['department_id']) : null;

    if ($course_name && $code) {
        try {
            $stmt = $db->prepare("INSERT INTO student_courses (course_name, code, department_id) VALUES (?, ?, ?)");
            $stmt->execute([$course_name, $code, $department_id]);
            $message = "✅ Course added successfully!";
        } catch (PDOException $e) {
            $message = "❌ Error adding course: " . $e->getMessage();
        }
    } else {
        $message = "⚠️ Please fill in all required fields.";
    }
}

// ✅ EDIT COURSE
if (isset($_POST['action']) && $_POST['action'] === 'edit_course') {
    $course_id = intval($_POST['course_id']);
    $course_name = trim($_POST['course_name']);
    $code = trim($_POST['code']);
    $department_id = !empty($_POST['department_id']) ? intval($_POST['department_id']) : null;

    try {
        $stmt = $db->prepare("UPDATE student_courses SET course_name = ?, code = ?, department_id = ? WHERE course_id = ?");
        $stmt->execute([$course_name, $code, $department_id, $course_id]);
        $message = "✅ Course updated successfully!";
    } catch (PDOException $e) {
        $message = "❌ Error updating course: " . $e->getMessage();
    }
}

// ✅ DELETE COURSE
if (isset($_GET['delete'])) {
    $course_id = intval($_GET['delete']);
    try {
        $stmt = $db->prepare("DELETE FROM student_courses WHERE course_id = ?");
        $stmt->execute([$course_id]);
        $message = "🗑️ Course deleted successfully!";
    } catch (PDOException $e) {
        $message = "❌ Error deleting course: " . $e->getMessage();
    }
}

// ✅ FETCH ALL COURSES
try {
    $stmt = $db->query("SELECT * FROM student_courses ORDER BY course_id DESC");
    $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $courses = [];
    $message = "❌ Database error: " . $e->getMessage();
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
        main { max-width: 1000px; margin: 30px auto; background: var(--light); padding: 25px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: var(--primary); border-bottom: 2px solid var(--accent); padding-bottom: 5px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 10px; border-bottom: 1px solid #ccc; text-align: left; }
        th { background-color: var(--accent); color: white; }
        .message { padding: 10px; margin-bottom: 15px; border-radius: 6px; font-weight: 600; }
        .success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        form { margin-bottom: 20px; }
        input[type=text], select { padding: 8px; width: 100%; border: 1px solid #ccc; border-radius: 4px; margin-top: 5px; }
        .actions a { margin-right: 10px; text-decoration: none; color: var(--primary); font-weight: 600; }
        .actions a:hover { color: var(--secondary); }
    </style>
</head>
<body>
<header>
    <div><h2>ALMA LMS | Manage Courses</h2></div>
    <div>
        <a href="admin_dashboard.php" class="btn">⬅ Dashboard</a>
        <a href="logout.php" class="btn" style="background:#c0392b;">Logout</a>
    </div>
</header>

<main>
    <h1><?php echo htmlspecialchars($page_title); ?></h1>

    <?php if ($message): ?>
        <div class="message <?php echo (strpos($message, '✅') !== false) ? 'success' : 'error'; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <!-- ✅ Add / Edit Form -->
    <form method="POST">
        <input type="hidden" name="action" value="add_course">
        <h3>Add New Course</h3>
        <label>Course Name:</label>
        <input type="text" name="course_name" required>
        <label>Course Code:</label>
        <input type="text" name="code" required>
        <label>Department ID (optional):</label>
        <input type="text" name="department_id">
        <button type="submit" class="btn">➕ Add Course</button>
    </form>

    <!-- ✅ Display Courses -->
    <h3>Existing Courses</h3>
    <?php if (empty($courses)): ?>
        <p>No courses found.</p>
    <?php else: ?>
        <table>
            <tr>
                <th>ID</th>
                <th>Course Name</th>
                <th>Code</th>
                <th>Department</th>
                <th>Actions</th>
            </tr>
            <?php foreach ($courses as $course): ?>
                <tr>
                    <td><?php echo htmlspecialchars($course['course_id']); ?></td>
                    <td><?php echo htmlspecialchars($course['course_name']); ?></td>
                    <td><?php echo htmlspecialchars($course['code']); ?></td>
                    <td><?php echo htmlspecialchars($course['department_id'] ?? '—'); ?></td>
                    <td class="actions">
                        <a href="edit_course.php?id=<?php echo $course['course_id']; ?>">✏️ Edit</a>
                        <a href="manage_courses.php?delete=<?php echo $course['course_id']; ?>" onclick="return confirm('Are you sure you want to delete this course?')">🗑️ Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>
</main>
</body>
</html>
