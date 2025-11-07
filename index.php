<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ALMA UNIVERSITY LMS | Home</title>
    
    <?php
        session_start();
        
        // ✅ If user has logged in and role exists, prepare their dashboard link
        if (isset($_SESSION['user_id']) && isset($_SESSION['role'])) {
            // Default role-based dashboard path
            $dashboard_link = '/' . $_SESSION['role'] . '/dashboard'; 
        }

        // ✅ If they previously visited a dashboard, use that stored session instead
        if (isset($_SESSION['last_dashboard'])) {
            $dashboard_link = $_SESSION['last_dashboard'];
        }
    ?>
    
    <style>
        :root {
            /* Bold, Professional Color Palette */
            --color-primary: #0A1C40; /* Deep Navy Blue (Alma University Main) */
            --color-accent: #DAA520; /* University Gold (Alma Accent) */
            --color-secondary-cta: #F57C00; /* Bold Orange */
            --color-bg: #F4F7F9; /* Light Grey Background */
            --color-text: #333;
            --color-light: #ffffff;
            --font-heading: 'Arial', sans-serif;
            --font-body: 'Helvetica', sans-serif;
        }

        body {
            font-family: var(--font-body);
            background-color: var(--color-bg);
            margin: 0;
            color: var(--color-text);
            line-height: 1.6;
        }

        /* --- Header and Navigation --- */
        header {
            background-color: var(--color-primary);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        nav {
            max-width: 1200px;
            margin: 0 auto;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .logo {
            font-size: 1.5em;
            font-weight: 700;
            color: var(--color-light);
            text-transform: uppercase;
        }
        nav ul {
            list-style: none;
            display: flex;
            margin: 0;
            padding: 0;
        }
        nav ul li {
            margin-left: 25px;
        }
        nav ul a {
            color: var(--color-light);
            text-decoration: none;
            font-weight: 600;
            padding: 5px 0;
            transition: color 0.3s;
        }
        nav ul a:hover {
            color: var(--color-accent); /* Gold hover effect */
        }

        /* --- Main Content Layout --- */
        main {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* --- Hero Section (Boldest part) --- */
        #hero {
            background: var(--color-light);
            padding: 80px 40px;
            text-align: center;
            border-radius: 10px;
            margin-top: 40px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        }
        #hero h1 {
            color: var(--color-primary);
            font-size: 3em;
            font-weight: 800;
            margin-bottom: 15px;
        }
        #hero p {
            font-size: 1.25em;
            color: #555;
            margin-bottom: 30px;
            max-width: 700px;
            margin-left: auto;
            margin-right: auto;
        }

        /* --- Call to Action Buttons --- */
        .cta-buttons a {
            text-decoration: none;
            font-weight: 700;
            padding: 15px 30px;
            border-radius: 8px;
            transition: background-color 0.3s, transform 0.1s;
            display: inline-block;
            margin: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .button.primary {
            background-color: var(--color-accent); /* Bold Gold CTA */
            color: var(--color-primary);
            border: 2px solid var(--color-accent);
        }
        .button.primary:hover {
            background-color: #e0ac1f;
            transform: translateY(-2px);
        }
        .button.secondary {
            background-color: var(--color-primary); /* Bold Navy CTA */
            color: var(--color-light);
            border: 2px solid var(--color-primary);
        }
        .button.secondary:hover {
            background-color: var(--color-light);
            color: var(--color-primary);
            transform: translateY(-2px);
        }

        /* --- Features Section --- */
        #features {
            padding: 60px 0;
            text-align: center;
        }
        #features h2 {
            font-size: 2.2em;
            color: var(--color-primary);
            margin-bottom: 50px;
            border-bottom: 3px solid var(--color-accent);
            display: inline-block;
            padding-bottom: 10px;
        }
        .feature-list {
            display: flex;
            justify-content: space-around;
            gap: 20px;
        }
        .feature-list div {
            flex: 1;
            background: var(--color-light);
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            border-top: 5px solid var(--color-secondary-cta); /* Bold strip */
        }
        .feature-list h3 {
            color: var(--color-primary);
            font-size: 1.3em;
            margin-top: 0;
            margin-bottom: 15px;
        }
        .feature-list p {
            color: #666;
            font-size: 1em;
        }

        hr {
            border: 0;
            height: 1px;
            background-image: linear-gradient(to right, rgba(0, 0, 0, 0), rgba(10, 28, 64, 0.3), rgba(0, 0, 0, 0));
            margin: 40px 0;
        }
        
        /* --- Footer --- */
        footer {
            background-color: var(--color-primary);
            color: var(--color-light);
            text-align: center;
            padding: 20px;
            margin-top: 40px;
            font-size: 0.9em;
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
                <?php if (isset($dashboard_link)): ?>
                    <li><a href="<?php echo $dashboard_link; ?>">GO TO DASHBOARD</a></li>
                    <li><a href="logout.php">LOGOUT</a></li>
                <?php else: ?>
                    <li><a href="features.php">FEATURES</a></li>
                    <li><a href="login.php">LOGIN</a></li>
                    <li><a href="register.php">REGISTER</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>

    <main>
        
        <section id="hero">
            <h1>**Your Future, Digitally Delivered by ALMA.**</h1>
            <p>Access your courses, materials, assignments, and grades instantly through the Official **ALMA UNIVERSITY** Learning Management System. Start your learning journey today.</p>
            
            <div class="cta-buttons">
                <?php if (isset($dashboard_link)): ?>
                    <a href="<?php echo $dashboard_link; ?>" class="button primary">CONTINUE TO YOUR DASHBOARD</a>
                <?php else: ?>
                    <a href="login.php" class="button primary">**LOG IN** SECURELY</a>
                    <a href="register.php" class="button secondary">**NEW STUDENT REGISTER**</a>
                <?php endif; ?>
            </div>
        </section>

        <hr>

        <section id="features">
            <h2>**Key ALMA LMS Features**</h2>
            <div class="feature-list">
                <div>
                    <h3>📚 Course Access & Materials</h3>
                    <p>View lessons, download resources, and track your weekly progress in all enrolled units.</p>
                </div>
                <div>
                    <h3>✅ Secure Assignment Submissions</h3>
                    <p>Submit assignments and manage quizzes with high security. Receive timely, constructive feedback.</p>
                </div>
                <div>
                    <h3>💬 Communication & Updates</h3>
                    <p>Stay updated with official announcements and use the messaging system to connect with lecturers and peers.</p>
                </div>
            </div>
        </section>

        <section id="about" style="padding: 40px 0;">
            </section>

    </main>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> **ALMA UNIVERSITY**. All rights reserved. | Professional and Secure Learning Management System.</p>
    </footer>

</body>
</html>
