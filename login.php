<?php 
error_reporting(E_ALL);
ini_set('display_errors', 1);

// ===========================================================
// ALMA University - Login Page (Secure PDO + Email Verification Popup)
// ===========================================================

session_start();
require_once "config/db_config.php"; // uses $db as PDO

$message = "";
$message_type = "";
$show_resend_popup = false; // controls modal display

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($email) || empty($password)) {
        $message = "Please enter both email and password.";
        $message_type = "error";
    } else {
        try {
            $stmt = $db->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->execute();

            if ($stmt->rowCount() === 1) {
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                if (password_verify($password, $user['password'])) {

                    // Check if email verified
                    if ((int)$user['email_verified'] === 0) {
                        $message = "⚠️ Please verify your email before logging in.";
                        $message_type = "warning";
                        $show_resend_popup = true;
                    } 
                    // Check account status
                    elseif (strtolower($user['status']) !== 'active' || (int)$user['is_active'] === 0) {
                        $message = "❌ Your account has been suspended. Please contact administration.";
                        $message_type = "error";
                    } else {
                        // ✅ SUCCESS: store session & redirect
                        $_SESSION['user_id'] = $user['user_id'];
                        $_SESSION['full_name'] = $user['full_name'];
                        $_SESSION['role'] = $user['role'];

                        // Redirect based on role
                        switch ($user['role']) {
                            case 'admin':
                                header("Location: admin_dashboard.php");
                                break;
                            case 'lecturer':
                                header("Location: lecturer_dashboard.php");
                                break;
                            default:
                                header("Location: students_dashboard.php");
                                break;
                        }
                        exit;
                    }
                } else {
                    $message = "❌ Invalid password.";
                    $message_type = "error";
                }
            } else {
                $message = "❌ No account found with that email.";
                $message_type = "error";
            }
        } catch (PDOException $e) {
            $message = "System error occurred. Please try again later.";
            $message_type = "error";
            error_log("Login error: " . $e->getMessage());
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>ALMA University Login</title>
    <style>
        body {
            background: #1e2a78;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            font-family: 'Poppins', sans-serif;
            margin: 0;
        }
        .login-container {
            background: #fff;
            padding: 40px 50px;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            width: 380px;
            text-align: center;
        }
        h2 {
            color: #1e2a78;
            margin-bottom: 20px;
        }
        input {
            width: 100%;
            padding: 12px;
            margin: 10px 0 20px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 15px;
        }
        button {
            width: 100%;
            background: #1e2a78;
            color: #fff;
            padding: 12px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            transition: 0.3s;
        }
        button:hover {
            background: #24319c;
        }
        .message {
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 15px;
            font-size: 14px;
        }
        .error { background: #ffe5e5; color: #d63031; }
        .warning { background: #fff4e5; color: #e67e22; }
        .success { background: #e5ffe8; color: #27ae60; }
        a { color: #1e2a78; text-decoration: none; font-weight: 500; }

        /* Popup Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 999;
            left: 0; top: 0;
            width: 100%; height: 100%;
            background-color: rgba(0,0,0,0.5);
            justify-content: center;
            align-items: center;
        }
        .modal-content {
            background: #fff;
            padding: 30px;
            border-radius: 10px;
            text-align: center;
            width: 350px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.3);
        }
        .modal-content h3 { color: #1e2a78; margin-bottom: 10px; }
        .modal-content input { width: 90%; margin-top: 10px; padding: 10px; border-radius: 6px; border: 1px solid #ccc; }
        .modal-content button {
            margin-top: 15px;
            width: 45%;
            display: inline-block;
            padding: 10px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }
        .close-btn { background: #ccc; color: #000; }
        .resend-btn { background: #1e2a78; color: #fff; }
        .resend-btn:hover { background: #24319c; }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>ALMA University Login</h2>

        <?php if (!empty($message)): ?>
            <div class="message <?= htmlspecialchars($message_type) ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <label for="email">Email Address</label>
            <input type="email" name="email" id="email" placeholder="Enter your email" required>

            <label for="password">Password</label>
            <input type="password" name="password" id="password" placeholder="Enter your password" required>

            <button type="submit">Login</button>
        </form>

        <p style="margin-top: 15px;">Don’t have an account? <a href="register.php">Register here</a></p>
    </div>

    <!-- Popup for resend verification -->
    <div class="modal" id="resendModal">
        <div class="modal-content">
            <h3>Email Verification Required</h3>
            <p>Your email is not verified. Enter your email to resend the verification link.</p>
            <form action="resend_verification.php" method="POST">
                <input type="email" name="email" placeholder="Enter your email" required>
                <div style="margin-top: 10px;">
                    <button type="submit" class="resend-btn">Resend Email</button>
                    <button type="button" class="close-btn" onclick="closeModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Show modal only if user is unverified
        const showPopup = <?= $show_resend_popup ? 'true' : 'false'; ?>;
        if (showPopup) {
            document.getElementById('resendModal').style.display = 'flex';
        }
        function closeModal() {
            document.getElementById('resendModal').style.display = 'none';
        }
    </script>
</body>
</html>
