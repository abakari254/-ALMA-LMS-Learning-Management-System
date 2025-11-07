<?php
// student_courses.php - ENHANCED VERSION (View + Enroll)

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// ✅ Security Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header('Location: login.php');
    exit;
}

require 'config/db_config.php';

$user_id = $_SESSION['user_id'];
$page_title = "My Enrolled Courses";
$enrolled_courses = [];
$available_courses = [];
$message = "";

// ✅ Get corresponding student_id for this logged-in user
try {
    $stmt = $db->prepare("SELECT student_id FROM students WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);

    // 🚀 AUTO-CREATE STUDENT RECORD IF NOT FOUND (Duplicate Email Safe)
    if (!$student) {
        $getUser = $db->prepare("SELECT full_name, email FROM users WHERE user_id = ?");
        $getUser->execute([$user_id]);
        $user = $getUser->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // ✅ Check if another student record exists with the same email
            $checkStudent = $db->prepare("SELECT student_id FROM students WHERE email = ?");
            $checkStudent->execute([$user['email']]);
            $existingStudent = $checkStudent->fetch(PDO::FETCH_ASSOC);

            if ($existingStudent) {
                // Re-link the existing student record to this user_id instead of inserting duplicate
                $update = $db->prepare("UPDATE students SET user_id = ? WHERE email = ?");
                $update->execute([$user_id, $user['email']]);
                $student_id = $existingStudent['student_id'];
                $message = "ℹ️ Linked to existing student record.";
            } else {
                // Create a new student record safely
                $insertStudent = $db->prepare("
                    INSERT INTO students (user_id, full_name, email, created_at)
                    VALUES (?, ?, ?, NOW())
                ");
                $insertStudent->execute([$user_id, $user['full_name'], $user['email']]);
                $student_id = $db->lastInsertId();
                $message = "✅ Student profile automatically created.";
            }

            // Fetch the student record
            $stmt = $db->prepare("SELECT student_id FROM students WHERE user_id = ?");
            $stmt->execute([$user_id]);
            $student = $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            $message = "❌ No matching user found in users table.";
        }
    }

    $student_id = $student ? $student['student_id'] : null;
} catch (PDOException $e) {
    $message = "❌ Database error: " . $e->getMessage();
    $student_id = null;
}

// ✅ 1. Handle Enrollment Request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['course_id']) && $student_id) {
    $course_id = intval($_POST['course_id']);

    try {
        // Check if already enrolled
        $check = $db->prepare("SELECT * FROM enrollments WHERE student_id = ? AND course_id = ?");
        $check->execute([$student_id, $course_id]);

        if ($check->rowCount() > 0) {
            $message = "⚠️ You are already enrolled in this course.";
        } else {
            // Enroll student
            $insert = $db->prepare("
                INSERT INTO enrollments (student_id, user_id, course_id, enrollment_date, progress)
                VALUES (?, ?, ?, NOW(), 0)
            ");
            $insert->execute([$student_id, $user_id, $course_id]);
            $message = "✅ Successfully enrolled in the new course!";
        }
    } catch (PDOException $e) {
        $message = "❌ Enrollment failed: " . $e->getMessage();
    }
}

// ✅ 2. Fetch Enrolled Courses
try {
    $stmt = $db->prepare("
        SELECT 
            C.course_id, 
            C.course_name AS title, 
            C.code, 
            C.department_id, 
            E.progress,
            E.enrollment_date
        FROM student_courses C
        JOIN enrollments E ON C.course_id = E.course_id 
        WHERE E.student_id = ?
        ORDER BY E.enrollment_date DESC
    ");
    $stmt->execute([$student_id]);
    $enrolled_courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching enrolled courses: " . $e->getMessage());
}

// ✅ 3. Fetch Available Courses (Not Yet Enrolled)
try {
    $stmt = $db->prepare("
        SELECT 
            course_id, 
            course_name AS title, 
            code, 
            department_id
        FROM student_courses
        WHERE course_id NOT IN (
            SELECT course_id FROM enrollments WHERE student_id = ?
        )
        ORDER BY course_id DESC
    ");
    $stmt->execute([$student_id]);
    $available_courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching available courses: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?> | ALMA LMS</title>

    <style>
        :root {
            --color-primary: #0A1C40;
            --color-accent: #DAA520;
            --color-secondary-cta: #F57C00;
            --color-bg: #F4F7F9;
            --color-light: #ffffff;
        }
        body { font-family: 'Helvetica', sans-serif; background-color: var(--color-bg); margin: 0; color: #333; }
        header { background-color: var(--color-primary); color: white; padding: 15px 40px; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1); }
        .nav-content { display: flex; justify-content: space-between; align-items: center; max-width: 1200px; margin: 0 auto; }
        .logo { font-size: 1.5em; font-weight: 700; }
        .nav-links a { color: white; text-decoration: none; margin-left: 15px; font-weight: 600; }

        main { max-width: 1200px; margin: 30px auto; padding: 0 20px; }
        h1 { color: var(--color-primary); font-weight: 800; border-bottom: 3px solid var(--color-accent); padding-bottom: 5px; margin-bottom: 30px; }

        .message { padding: 12px; margin-bottom: 20px; border-radius: 6px; font-weight: 600; }
        .success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }

        .course-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 30px; }
        .course-card {
            background: var(--color-light);
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            padding: 20px;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .course-card:hover { transform: translateY(-5px); box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15); }
        .course-card h3 { color: var(--color-primary); margin-top: 0; }

        .progress-bar-container { background: #e0e0e0; border-radius: 5px; height: 10px; overflow: hidden; }
        .progress-bar-fill { height: 100%; background-color: var(--color-accent); }

        .btn {
            background-color: var(--color-primary);
            color: white;
            padding: 10px 18px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
        }
        .btn:hover { background-color: var(--color-secondary-cta); }

        .enroll-btn { background-color: var(--color-accent); }
        .enroll-btn:hover { background-color: #c89f1c; }

        .section-title { margin-top: 50px; color: var(--color-primary); border-bottom: 2px solid var(--color-accent); padding-bottom: 4px; }
    </style>
</head>
<body>

<header>
    <div class="nav-content">
        <div class="logo">ALMA LMS | Courses</div>
        <div class="nav-links">
            <a href="students_dashboard.php" class="btn">⬅ Back to Dashboard</a>
            <a href="logout.php">Logout</a>
        </div>
    </div>
</header>

<main>
    <h1><?php echo htmlspecialchars($page_title); ?> (<?php echo count($enrolled_courses); ?>)</h1>

    <?php if ($message): ?>
        <div class="message <?php echo (strpos($message, '✅') !== false || strpos($message, 'ℹ️') !== false) ? 'success' : 'error'; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <!-- ✅ Enrolled Courses Section -->
    <div class="course-grid">
        <?php if (empty($enrolled_courses)): ?>
            <p>You are not currently enrolled in any courses.</p>
        <?php else: ?>
            <?php foreach ($enrolled_courses as $course): 
                $progress = (int)$course['progress'];
            ?>
                <div class="course-card">
                    <h3><?php echo htmlspecialchars($course['title']); ?></h3>
                    <small>Code: <?php echo htmlspecialchars($course['code']); ?></small>
                    <p>Start Date: <?php echo htmlspecialchars($course['enrollment_date']); ?></p>
                    <div class="progress-bar-container">
                        <div class="progress-bar-fill" style="width: <?php echo $progress; ?>%;"></div>
                    </div>
                    <p><strong>Progress:</strong> <?php echo $progress; ?>%</p>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- ✅ Available Courses Section -->
    <h2 class="section-title">Available Courses to Enroll</h2>

    <div class="course-grid">
        <?php if (empty($available_courses)): ?>
            <p>All courses are already enrolled. 🎓</p>
        <?php else: ?>
            <?php foreach ($available_courses as $course): ?>
                <div class="course-card">
                    <h3><?php echo htmlspecialchars($course['title']); ?></h3>
                    <small>Code: <?php echo htmlspecialchars($course['code']); ?></small>
                    <form method="POST" style="margin-top: 10px;">
                        <input type="hidden" name="course_id" value="<?php echo $course['course_id']; ?>">
                        <button type="submit" class="btn enroll-btn">+ Enroll</button>
                    </form>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</main>

</body>
</html>
