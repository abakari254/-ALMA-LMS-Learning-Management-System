<?php
// PHP SCRIPT START
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if the user is logged in to adjust navigation links
$is_logged_in = isset($_SESSION['user_id']);
$role = $_SESSION['role'] ?? 'guest';
$dashboard_link = $is_logged_in ? ($role . '_dashboard.php') : 'login.php';

// PHP SCRIPT END
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Features | ALMA UNIVERSITY LMS</title>
    
    <style>
        :root {
            --color-primary: #0A1C40; /* Deep Navy Blue */
            --color-accent: #DAA520; /* University Gold */
            --color-secondary-cta: #F57C00; /* Bold Orange */
            --color-bg: #F4F7F9; 
            --color-light: #ffffff;
            --color-text: #333;
        }

        body { font-family: 'Helvetica', sans-serif; background-color: var(--color-bg); margin: 0; color: var(--color-text); line-height: 1.6; }
        
        /* Header Styling */
        header { background-color: var(--color-primary); box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1); }
        nav { max-width: 1200px; margin: 0 auto; padding: 15px 20px; display: flex; justify-content: space-between; align-items: center; }
        .logo { font-size: 1.5em; font-weight: 700; color: var(--color-light); text-transform: uppercase; }
        nav ul { list-style: none; display: flex; margin: 0; padding: 0; }
        nav ul li { margin-left: 25px; }
        nav ul a { color: var(--color-light); text-decoration: none; font-weight: 600; transition: color 0.3s; }
        nav ul a:hover { color: var(--color-accent); }

        main { max-width: 1200px; margin: 30px auto; padding: 0 20px; }

        /* Hero/Title Section */
        .features-hero {
            text-align: center;
            padding: 40px 0;
            margin-bottom: 40px;
        }
        .features-hero h1 {
            color: var(--color-primary);
            font-size: 3em;
            font-weight: 800;
            margin-bottom: 10px;
        }
        .features-hero p {
            font-size: 1.2em;
            color: #555;
            max-width: 800px;
            margin: 0 auto;
        }

        /* Feature Grid */
        .feature-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 30px;
        }
        .feature-card {
            background: var(--color-light);
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            border-top: 5px solid var(--color-accent); /* Gold Bold Strip */
        }
        .feature-card h3 {
            color: var(--color-primary);
            font-size: 1.7em;
            font-weight: 700;
            margin-top: 0;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
        }
        .feature-card h3 span { /* Icon styling */
            font-size: 1.2em;
            color: var(--color-secondary-cta);
            margin-right: 10px;
        }
        .feature-card p {
            color: #666;
            margin-bottom: 0;
        }

        /* Call to Action at bottom */
        .cta-footer {
            text-align: center;
            padding: 50px 0;
        }
        .btn-main-cta {
            text-decoration: none;
            font-weight: 700;
            padding: 15px 40px;
            border-radius: 8px;
            background-color: var(--color-primary);
            color: var(--color-light);
            transition: background-color 0.3s;
            text-transform: uppercase;
        }
        .btn-main-cta:hover {
            background-color: var(--color-accent);
            color: var(--color-primary);
        }
    </style>
</head>
<body>

    <header>
        <nav>
            <div class="logo">
                **ALMA UNIVERSITY** LMS
            </div>
            <ul>
                <li><a href="index.php">HOME</a></li>
                <?php if ($is_logged_in): ?>
                    <li><a href="<?php echo $dashboard_link; ?>">DASHBOARD</a></li>
                    <li><a href="logout.php">LOGOUT</a></li>
                <?php else: ?>
                    <li><a href="login.php">LOGIN</a></li>
                    <li><a href="register.php">REGISTER</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>

    <main>
        
        <section class="features-hero">
            <h1>**Core Capabilities of ALMA LMS**</h1>
            <p>Designed for academic excellence, our Learning Management System provides a comprehensive, secure, and intuitive environment for students and administrators alike.</p>
        </section>

        <section class="feature-grid">
            
            <div class="feature-card">
                <h3><span>&#128218;</span> Intuitive Course Delivery</h3>
                <p>Seamless access to all lessons, multimedia content, and reading materials organized by unit (using your **`courses`** and **`lessons`** tables).</p>
            </div>
            
            <div class="feature-card">
                <h3><span>&#9989;</span> Secure Submission & Grading</h3>
                <p>Students can easily upload assignments and take quizzes (using **`submissions`** and **`assignments`**). Instructors provide private, targeted feedback.</p>
            </div>

            <div class="feature-card">
                <h3><span>&#128202;</span> Transparent Gradebook</h3>
                <p>Real-time access to the student's personal grade book (powered by the **`grades`** table) with detailed breakdowns of performance metrics.</p>
            </div>
            
            <div class="feature-card">
                <h3><span>&#127941;</span> Achievement Badges</h3>
                <p>Gamify learning by rewarding students with digital badges for milestones and excellent work (managed by **`badge_definitions`** and **`user_badges`**).</p>
            </div>

            <div class="feature-card">
                <h3><span>&#128274;</span> Multi-Role Access Control</h3>
                <p>Separate, secure portals for Students and Admins ensure data integrity and system stability (based on the **`role`** in the **`users`** table).</p>
            </div>
            
            <div class="feature-card">
                <h3><span>&#128221;</span> System Activity Logging</h3>
                <p>Every critical system action is recorded (in the **`activity_logs`** table) for auditing, security review, and compliance tracking.</p>
            </div>

            <div class="feature-card">
                <h3><span>&#128226;</span> Integrated Communications</h3>
                <p>Built-in announcement and messaging tools (using **`announcements`** and **`messages`**) facilitate crucial administrative and peer communication.</p>
            </div>

            <div class="feature-card">
                <h3><span>&#128187;</span> Robust User Management</h3>
                <p>Administrators have full control to edit, suspend, or delete user accounts (via **`admin_user_management.php`** using the **`users`** table).</p>
            </div>

        </section>

        <section class="cta-footer">
            <p style="font-size: 1.2em; font-weight: 600; color: var(--color-primary);">Ready to experience professional academic management?</p>
            <a href="<?php echo $is_logged_in ? $dashboard_link : 'register.php'; ?>" class="btn-main-cta">
                <?php echo $is_logged_in ? 'GO TO YOUR DASHBOARD' : 'START REGISTRATION NOW'; ?>
            </a>
        </section>

    </main>

</body>
</html>