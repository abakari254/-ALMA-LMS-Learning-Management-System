<?php
session_start();
require_once "config/db_config.php";

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    // Fetch user by token
    $stmt = $db->prepare("SELECT * FROM users WHERE token = ? LIMIT 1");
    $stmt->execute([$token]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // ✅ Update user: set email_verified = 1 and clear token
        $update = $db->prepare("UPDATE users SET email_verified = 1, token = NULL WHERE user_id = ?");
        $update->execute([$user['user_id']]);

        $_SESSION['message'] = "✅ Email verified successfully! You can now log in.";
        $_SESSION['message_type'] = "success";
        header("Location: login.php");
        exit;
    } else {
        $_SESSION['message'] = "❌ Invalid or expired verification link.";
        $_SESSION['message_type'] = "error";
        header("Location: register.php");
        exit;
    }
} else {
    $_SESSION['message'] = "❌ No verification token provided.";
    $_SESSION['message_type'] = "error";
    header("Location: register.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Email Verification - ALMA University LMS</title>
<style>
    body {
        font-family: 'Poppins', sans-serif;
        background: linear-gradient(120deg, #004aad, #007bff);
        height: 100vh;
        margin: 0;
        display: flex;
        justify-content: center;
        align-items: center;
    }
    .verify-container {
        background: #fff;
        border-radius: 12px;
        padding: 40px;
        width: 400px;
        text-align: center;
        box-shadow: 0 4px 20px rgba(0,0,0,0.2);
    }
    h2 {
        color: #004aad;
        margin-bottom: 10px;
    }
    p {
        color: #555;
    }
    form {
        margin-top: 20px;
    }
    input[type="email"] {
        width: 100%;
        padding: 10px;
        margin-bottom: 15px;
        border: 1px solid #ccc;
        border-radius: 6px;
        font-size: 14px;
    }
    button {
        background: #004aad;
        color: white;
        border: none;
        padding: 12px;
        width: 100%;
        border-radius: 6px;
        font-size: 16px;
        cursor: pointer;
        transition: 0.3s;
    }
    button:hover {
        background: #007bff;
    }
</style>
</head>
<body>
<div class="verify-container">
    <h2>Email Verification</h2>

    <?php if (empty($_GET['token'])): ?>
        <p>Please check your email for the verification link.</p>
        <form action="resend_verification.php" method="POST">
            <input type="email" name="email" placeholder="Enter your email to resend link" required>
            <button type="submit">Resend Verification Email</button>
        </form>
    <?php else: ?>
        <p>Processing your verification request...</p>
    <?php endif; ?>
</div>
</body>
</html>
