<?php
// admin_course_management.php - REAL DATA AND ACTION IMPLEMENTATION

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Security Check: Only Admin role can access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}
require 'config/db_config.php'; // Assumes $db is the PDO connection object

$user_id = $_SESSION['user_id'];
$message = '';

// --- 1. ACTION HANDLERS (Create, Publish/Unpublish, Delete) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    try {
        switch ($action) {
            case 'create':
                $new_title = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_SPECIAL_CHARS);
                $new_code = filter_input(INPUT_POST, 'code', FILTER_SANITIZE_SPECIAL_CHARS);
                $dept_id = filter_input(INPUT_POST, 'department_id', FILTER_VALIDATE_INT);
                
                // Input validation (basic)
                if (empty($new_title) || empty($new_code) || !$dept_id) {
                     $message = "Error: Please provide a valid title, code, and select a department.";
                     break;
                }

                // Insert into 'courses' table with status 0 (Draft)
                $stmt_create = $db->prepare("INSERT INTO courses (title, code, department_id, status) VALUES (?, ?, ?, 0)");
                $stmt_create->execute([$new_title, $new_code, $dept_id]);
                
                $message = "Course **$new_title ($new_code)** created successfully as a Draft.";
                break;

            case 'publish':
            case 'unpublish':
                $course_id = filter_input(INPUT_POST, 'course_id', FILTER_VALIDATE_INT);
                $new_status = ($action === 'publish') ? 1 : 0;
                $status_text = ($new_status === 1) ? 'Published' : 'Drafted';
                
                if ($course_id) {
                    $stmt_status = $db->prepare("UPDATE courses SET status = ? WHERE course_id = ?");
                    $stmt_status->execute([$new_status, $course_id]);
                    $message = "Course ID **$course_id** status updated to **$status_text**.";
                }
                break;
                
            case 'delete':
                $course_id = filter_input(INPUT_POST, 'course_id', FILTER_VALIDATE_INT);
                
                if ($course_id) {
                    // Deleting the course.
                    $stmt_delete = $db->prepare("DELETE FROM courses WHERE course_id = ?");
                    $stmt_delete->execute([$course_id]);
                    
                    $message = "Course ID **$course_id** and related content has been **DELETED**.";
                }
                break;
        }
    } catch (PDOException $e) {
        error_log("Admin Course Mgmt Error: " . $e->getMessage());
        $message = "Database error: Could not complete action. Check logs.";
    }
    // Redirect to self to clear POST data and show message
    header('Location: admin_course_management.php?msg=' . urlencode($message));
    exit;
}

// --- 2. DATA RETRIEVAL (List all Courses and Departments) ---
try {
    // 2a. List all courses with details
    $stmt_courses = $db->query("
        SELECT 
            C.course_id, C.title, C.code, C.status, D.department_name AS department_name,
            (SELECT COUNT(*) FROM lessons L WHERE L.course_id = C.course_id) AS lesson_count,
            (SELECT COUNT(*) FROM enrollments E WHERE E.course_id = C.course_id) AS enrollment_count
        FROM courses C
        JOIN departments D ON C.department_id = D.department_id
        ORDER BY C.course_id DESC
    ");
    $courses = $stmt_courses->fetchAll(PDO::FETCH_ASSOC);

    // 2b. Retrieve all departments for the creation form dropdown
    $stmt_departments = $db->query("SELECT department_id, department_name FROM departments ORDER BY department_name");
    $departments = $stmt_departments->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log("Course List DB Error: " . $e->getMessage());
    $courses = [];
    $departments = [];
    $message = "Database error: Could not retrieve course list or departments.";
}

// Retrieve message after redirect
if (isset($_GET['msg'])) {
    $message = urldecode($_GET['msg']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Management | ALMA UNIVERSITY LMS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <style>
        /* --- BRANDED VARIABLES AND RESET --- */
        :root {
            --color-primary: #1a237e; /* Deep Blue */
            --color-secondary: #3f51b5; /* Medium Blue */
            --color-accent: #ffc107; /* Yellow/Gold Accent */
            --color-background: #f4f7fa; /* Light Gray Background */
            --color-success: #28a745; /* Green */
            --color-danger: #dc3545; /* Red */
        }
        body { font-family: Arial, sans-serif; background-color: var(--color-background); margin: 0; padding: 0; }

        /* --- HEADER (MATCHING DASHBOARD) --- */
        .header {
            background-color: var(--color-primary);
            color: white;
            padding: 10px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 3px solid var(--color-secondary);
        }
        .logo { font-size: 20px; font-weight: bold; }
        .nav-links a { color: white; text-decoration: none; margin-left: 20px; font-weight: bold; transition: color 0.2s; }
        .nav-links a:hover { color: var(--color-accent); }

        /* --- CONTENT LAYOUT --- */
        main { padding: 30px; }
        h1 { color: var(--color-primary); margin-bottom: 25px; font-size: 28px; }
        
        /* Grid for list and form */
        .management-grid {
            display: grid;
            grid-template-columns: 2fr 1fr; /* List takes 2/3, Form takes 1/3 */
            gap: 30px;
        }
        .card { 
            background-color: white; 
            padding: 25px; 
            border-radius: 8px; 
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05); 
        }
        .card-header {
            font-size: 18px;
            font-weight: bold;
            color: var(--color-primary);
            margin-bottom: 15px;
        }

        /* --- MESSAGE BOX --- */
        .system-message-box {
            padding: 15px; 
            background-color: #d4edda; /* Default: Success (Light Green) */
            color: #155724; 
            border: 1px solid #c3e6cb; 
            border-radius: 5px; 
            margin-bottom: 20px;
        }
        .system-message-box.error {
            background-color: #f8d7da; /* Light Red */
            color: #721c24; 
            border-color: #f5c6cb;
        }

        /* --- TABLE STYLING --- */
        .course-table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        .course-table th { 
            background-color: var(--color-primary); 
            color: white; 
            text-align: left; 
            padding: 12px 15px; 
            font-size: 13px; 
            text-transform: uppercase; 
        }
        .course-table td { padding: 10px 15px; border-bottom: 1px solid #eee; font-size: 14px; vertical-align: middle; }
        .course-table tbody tr:last-child td { border-bottom: none; }
        
        /* --- STATUS BADGES --- */
        .status-badge { 
            padding: 5px 8px; 
            border-radius: 4px; 
            font-size: 11px; 
            font-weight: bold; 
            display: inline-block;
        }
        .status-published { 
            background-color: #d4edda; /* Light Green */
            color: var(--color-success); /* Dark Green */
        }
        .status-draft { 
            background-color: #ffedcc; /* Light Orange */
            color: #856404; /* Dark Yellow/Orange */
        }

        /* --- ACTION BUTTONS --- */
        .btn { 
            padding: 8px 10px; 
            margin-right: 5px; 
            border: none; 
            border-radius: 4px; 
            cursor: pointer; 
            font-size: 12px; 
            font-weight: bold; 
            transition: background-color 0.2s;
            text-decoration: none; /* For the Lessons button */
            display: inline-block;
        }
        .btn-lessons { background-color: var(--color-secondary); color: white; }
        .btn-lessons:hover { background-color: var(--color-primary); }

        .btn-edit { background-color: #007bff; color: white; }
        .btn-edit:hover { background-color: #0056b3; }
        
        .btn-publish { background-color: var(--color-success); color: white; } /* Green */
        .btn-publish:hover { background-color: #1e7e34; }

        .btn-unpublish { background-color: var(--color-accent); color: #333; } /* Yellow/Orange */
        .btn-unpublish:hover { background-color: #e0a800; }

        .btn-delete { background-color: var(--color-danger); color: white; } /* Red */
        .btn-delete:hover { background-color: #c82333; }
        
        /* --- CREATE FORM STYLING --- */
        .form-group { margin-bottom: 20px; }
        .creation-form-area label { display: block; margin-bottom: 5px; font-weight: bold; color: #333; }
        .creation-form-area input[type="text"],
        .creation-form-area select {
            width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; font-size: 14px;
        }
        .btn-create { background-color: var(--color-primary) !important; color: white; padding: 12px; font-size: 16px;}
        .w-100 { width: 100%; }

        /* --- RESPONSIVE ADJUSTMENTS --- */
        @media (max-width: 900px) {
            .management-grid {
                grid-template-columns: 1fr; /* Stack panels vertically on smaller screens */
            }
        }
    </style>
</head>
<body>

<div class="header">
    <div class="logo">
        **ALMA UNIVERSITY** LMS | ADMIN
    </div>
    <div class="nav-links">
        <a href="admin_dashboard.php">DASHBOARD</a>
        <a href="user_management.php">USER MGMT</a>
        <a href="logout.php">LOGOUT</a>
    </div>
</div>

<main>
    <h1>Course Management Console</h1>
    
    <?php 
        // Determine message class based on content
        $msg_class = (strpos($message, 'Error') !== false || strpos($message, 'error') !== false || strpos($message, 'failed') !== false) ? 'error' : '';
    ?>
    <?php if (!empty($message)): ?>
        <div class="system-message-box <?php echo $msg_class; ?>">
            **System Message:** <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <div class="management-grid">
        
        <div class="course-list-area">
            <div class="card">
                <div class="card-header">
                    All Courses (<?php echo count($courses); ?>)
                </div>
                <table class="course-table">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Title</th>
                            <th>Deptment_name.</th>
                            <th>Lessons</th>
                            <th>Enrolled</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($courses)): ?>
                            <tr><td colspan="7" style="text-align: center; font-style: italic; color: #666;">No courses found in the database. Start by creating one!</td></tr>
                        <?php endif; ?>
                        
                        <?php foreach ($courses as $course): ?>
                            <tr>
                                <td>**<?php echo htmlspecialchars($course['code']); ?>**</td>
                                <td><?php echo htmlspecialchars($course['title']); ?></td>
                                <td><?php echo htmlspecialchars($course['department_name']); ?></td>
                                <td><?php echo $course['lesson_count']; ?></td>
                                <td><?php echo $course['enrollment_count']; ?></td>
                                <td>
                                    <?php if ($course['status'] == 1): ?>
                                        <span class="status-badge status-published">Published</span>
                                    <?php else: ?>
                                        <span class="status-badge status-draft">Draft</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="admin_lesson_management.php?course_id=<?php echo $course['course_id']; ?>" class="btn btn-lessons">Lessons</a>
                                    
                                    <button class="btn btn-edit" onclick="alert('TODO: Implement Edit Modal for Course ID: <?php echo $course['course_id']; ?>')">Edit</button>
                                    
                                    <form method="POST" action="course_management.php" style="display: inline-block;">
                                        <input type="hidden" name="course_id" value="<?php echo $course['course_id']; ?>">
                                        <input type="hidden" name="action" value="<?php echo ($course['status'] == 1) ? 'unpublish' : 'publish'; ?>">
                                        <button type="submit" class="btn <?php echo ($course['status'] == 1) ? 'btn-unpublish' : 'btn-publish'; ?>" onclick="return confirm('Confirm status change for <?php echo htmlspecialchars($course['code']); ?>?');">
                                            <?php echo ($course['status'] == 1) ? 'Unpublish' : 'Publish'; ?>
                                        </button>
                                    </form>

                                    <form method="POST" action="course_management.php" style="display: inline-block;">
                                        <input type="hidden" name="course_id" value="<?php echo $course['course_id']; ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <button type="submit" class="btn btn-delete" onclick="return confirm('WARNING! Delete course <?php echo htmlspecialchars($course['code']); ?>? This is permanent.');">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="creation-form-area">
            <div class="card">
                <div class="card-header">
                    **Create New Course**
                </div>
                <form method="POST" action="course_management.php">
                    <input type="hidden" name="action" value="create">
                    
                    <div class="form-group">
                        <label for="title">Course Title</label>
                        <input type="text" id="title" name="title" required>
                    </div>

                    <div class="form-group">
                        <label for="code">Course Code (e.g., CS101)</label>
                        <input type="text" id="code" name="code" maxlength="6" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="department_id">Department</label>
                        <select id="department_id" name="department_id" required>
                            <option value="">-- Select Department --</option>
                            <?php foreach ($departments as $dept): ?>
                                <option value="<?php echo $dept['department_id']; ?>">
                                    <?php echo htmlspecialchars($dept['department_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn btn-create w-100">
                        CREATE COURSE
                    </button>
                </form>
            </div>
        </div>
        
    </div>
</main>

</body>
</html>