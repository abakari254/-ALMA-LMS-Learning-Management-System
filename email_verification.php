<?php
// =========================================================
// verify_email.php – HANDLER FOR EMAIL VERIFICATION LINKS
// =========================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require 'config/db_config.php'; 

$message = "";
$is_success = false;
$token = $_GET['token'] ?? '';

if (empty($token)) {
    $message = "Invalid verification link. Token is missing.";
} else {
    try {
        global $db;
        
        // Find user by verification token
        $stmt = $db->prepare("SELECT user_id, token_expiry, is_verified FROM users WHERE verification_token = ?");
        $stmt->execute([$token]);
        $user = $stmt->fetch();

        if (!$user) {
            $message = "Invalid or expired verification token.";
        } elseif ($user['is_verified']) {
            $message = "Your account is already verified! Please proceed to login.";
            $is_success = true;
        } elseif (strtotime($user['token_expiry']) < time()) {
            $message = "Verification token has expired. Please contact support to resend the link.";
        } else {
            // Verification successful: Update user status
            $stmt = $db->prepare("UPDATE users SET is_verified = 1, verification_token = NULL, token_expiry = NULL WHERE user_id = ?");
            $stmt->execute([$user['user_id']]);

            $message = "Success! Your ALMA University account is now verified. You can now log in.";
            $is_success = true;
        }

    } catch (Exception $e) {
        error_log("Verification Error: " . $e->getMessage());
        $message = "A system error occurred during verification. Please try again later.";
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Account Verification</title>
<style>
/* Reusing core styles for consistency */
body {
    font-family: 'Segoe UI', sans-serif;
    background-color: #f5f7fa;
    margin: 0;
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
}
.message-container {
    background: white;
    padding: 40px;
    border-radius: 12px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    width: 400px;
    text-align: center;
}
h1 {
    color: #1a237e;
    margin-bottom: 20px;
}
.result {
    padding: 15px;
    border-radius: 6px;
    font-weight: bold;
}
.success {
    background-color: #e8f5e9;
    color: #1b5e20;
}
.error {
    background-color: #ffebee;
    color: #b71c1c;
}
a {
    color: #1a237e;
    text-decoration: none;
    font-weight: bold;
}
a:hover {
    text-decoration: underline;
}
</style>
</head>
<body>
    <div class="message-container">
        <h1>Account Verification</h1>
        <div class="result <?= $is_success ? 'success' : 'error' ?>">
            <?= htmlspecialchars($message) ?>
        </div>
        <p style="margin-top: 25px;">
            <a href="login.php">Go to Login</a>
        </p>
    </div>
</body>
</html>
