<?php
require_once "config/db_config.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $stmt = $db->prepare("SELECT full_name FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $newToken = rand(100000, 999999);
        $db->prepare("UPDATE users SET token = ?, email_verified = 0 WHERE email = ?")->execute([$newToken, $email]);

        $link = "https://ashabakari.eagletechafrica.com/verify_email.php?token=$newToken";
        $subject = "Resend Verification - ALMA University LMS";
        $message = "Hi {$user['full_name']},\n\nHere is your new verification code: $newToken\nClick below to verify:\n$link\n\nALMA University LMS";
        $headers = "From: ALMA University <info@ashabakari.eagletechafrica.com>\r\n";

        if (mail($email, $subject, $message, $headers)) {
            echo "<script>alert('✅ Verification link resent successfully. Check your inbox.'); window.location='login.php';</script>";
        } else {
            echo "<script>alert('❌ Failed to send verification email.'); window.location='register.php';</script>";
        }
    } else {
        echo "<script>alert('Email not found. Please register again.'); window.location='register.php';</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Resend Verification - ALMA University LMS</title>
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
    .resend-container {
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
        font-size: 14px;
        margin-bottom: 20px;
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
    a {
        color: #004aad;
        text-decoration: none;
        font-size: 13px;
    }
    a:hover {
        text-decoration: underline;
    }
</style>
</head>
<body>
<div class="resend-container">
    <h2>Resend Verification Email</h2>
    <p>Enter your email below and we’ll send you a new verification link.</p>
    <form method="POST" action="">
        <input type="email" name="email" placeholder="Enter your registered email" required>
        <button type="submit">Resend Link</button>
    </form>
    <p><a href="register.php">Back to Registration</a></p>
</div>
</body>
</html>
