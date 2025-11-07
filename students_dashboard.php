<?php
// students_dashboard.php
session_start();

// Security check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: login.php");
    exit;
}

$student_name = $_SESSION['full_name'] ?? 'Student';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard | ALMA LMS</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            margin: 0;
            background-color: #f5f7fa;
            display: flex;
        }

        /* Sidebar */
        .sidebar {
            width: 250px;
            background-color: #004aad;
            height: 100vh;
            padding-top: 20px;
            color: white;
            display: flex;
            flex-direction: column;
            position: fixed;
        }

        .sidebar h2 {
            text-align: center;
            color: white;
            margin-bottom: 40px;
            font-size: 1.4em;
            font-weight: 700;
        }

        .sidebar a {
            display: flex;
            align-items: center;
            padding: 14px 20px;
            color: white;
            text-decoration: none;
            font-weight: 500;
            transition: background 0.3s;
        }

        .sidebar a:hover,
        .sidebar a.active {
            background-color: #0a66c2;
            border-left: 5px solid #ffd700;
        }

        .sidebar a i {
            margin-right: 12px;
        }

        /* Main Content */
        .main {
            margin-left: 250px;
            padding: 30px;
            flex-grow: 1;
        }

        h1 {
            color: #004aad;
            font-size: 1.8em;
            margin-bottom: 5px;
        }

        p {
            color: #333;
            font-size: 1em;
        }

        .welcome {
            text-align: right;
            font-weight: 600;
            margin-bottom: 30px;
        }

        /* Dashboard Cards */
        .cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
            margin-top: 30px;
        }

        .card {
            background-color: #fff;
            border-radius: 12px;
            padding: 30px;
            text-align: center;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            transition: transform 0.2s, box-shadow 0.2s;
            cursor: pointer;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 15px rgba(0,0,0,0.15);
        }

        .card i {
            font-size: 40px;
            color: #004aad;
            margin-bottom: 15px;
        }

        .card h3 {
            color: #000;
            font-size: 1.2em;
            margin: 10px 0 5px 0;
        }

        .card p {
            color: #555;
            font-size: 0.95em;
        }

        a.card-link {
            text-decoration: none;
        }
    </style>
    <script src="https://kit.fontawesome.com/a2e0e9e7c2.js" crossorigin="anonymous"></script>
</head>
<body>

    <!-- Sidebar -->
    <div class="sidebar">
        <h2>ALMA LMS</h2>
        <a href="students_dashboard.php" class="active"><i class="fas fa-home"></i> Dashboard</a>
        <a href="student_courses.php"><i class="fas fa-book"></i> My Courses</a>
        <a href="fee_payment.php"><i class="fas fa-money-bill-wave"></i> Fee Payment</a>
        <a href="student_profile.php"><i class="fas fa-user"></i> Profile</a>
        <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>

    <!-- Main Content -->
    <div class="main">
        <div class="welcome">Welcome Back, <?php echo htmlspecialchars($student_name); ?></div>

        <h1>Student Dashboard</h1>
        <p>Here you can manage your courses, check fees, and update your profile.</p>

        <div class="cards">
            <a href="student_courses.php" class="card-link">
                <div class="card">
                    <i class="fas fa-book"></i>
                    <h3>My Courses</h3>
                    <p>View and manage your enrolled courses.</p>
                </div>
            </a>

            <a href="fee_payment.php" class="card-link">
                <div class="card">
                    <i class="fas fa-money-bill-wave"></i>
                    <h3>Fee Payment</h3>
                    <p>Pay your tuition and view payment history.</p>
                </div>
            </a>

            <a href="student_profile.php" class="card-link">
                <div class="card">
                    <i class="fas fa-user"></i>
                    <h3>Profile</h3>
                    <p>Update your personal information.</p>
                </div>
            </a>
        </div>
    </div>

</body>
</html>
