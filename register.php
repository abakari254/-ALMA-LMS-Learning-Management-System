<?php
// ===========================================
// ALMA UNIVERSITY LMS - Registration with Email Verification + Resend Option
// ===========================================
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();
require_once "config/db_config.php"; // Must define $db = new PDO(...)

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // 🛡️ Honeypot check
    if (!empty($_POST['website'])) {
        $_SESSION['message'] = "Bot detected.";
        $_SESSION['message_type'] = "error";
    } else {
        $full_name = trim($_POST['full_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        $gender = $_POST['gender'] ?? '';
        $dob = $_POST['dob'] ?? '';

        if (!$full_name || !$email || !$password || !$confirm_password || !$gender || !$dob) {
            $_SESSION['message'] = "All fields are required.";
            $_SESSION['message_type'] = "error";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['message'] = "Invalid email format.";
            $_SESSION['message_type'] = "error";
        } elseif ($password !== $confirm_password) {
            $_SESSION['message'] = "Passwords do not match.";
            $_SESSION['message_type'] = "error";
        } else {
            // ✅ Check if email already exists
            $check = $db->prepare("SELECT user_id, email_verified FROM users WHERE email = ?");
            $check->execute([$email]);
            $existing = $check->fetch(PDO::FETCH_ASSOC);

            if ($existing) {
                if ($existing['email_verified'] == 0) {
                    $_SESSION['message'] = "This email is already registered but not verified. <a href='resend_verification.php'>Resend verification email?</a>";
                    $_SESSION['message_type'] = "warning";
                } else {
                    $_SESSION['message'] = "Email already registered. Please <a href='login.php'>log in</a>.";
                    $_SESSION['message_type'] = "error";
                }
            } else {
                // ✅ Create account
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $token = rand(100000, 999999);
                $email_verified = 0;

                $stmt = $db->prepare("INSERT INTO users 
                    (full_name, email, password, gender, dob, token, email_verified, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
                $stmt->execute([$full_name, $email, $hashed_password, $gender, $dob, $token, $email_verified]);

                // ✅ Get the new user_id
                $user_id = $db->lastInsertId();

                // ✅ Automatically create a default fee record
                // ✅ Automatically create a default fee record (with error logging)
            try {
                $default_fee_amount = 4000; // adjust as needed
                $stmt_fee = $db->prepare("INSERT INTO user_fees (user_id, fee_amount, amount_paid, balance) VALUES (?, ?, 0, ?)");
                $stmt_fee->execute([$user_id, $default_fee_amount, $default_fee_amount]);
                } catch (PDOException $e) {
                error_log("Fee record creation failed for user_id $user_id: " . $e->getMessage());
                }

                // ✅ Send email
                $verify_link = "https://ashabakari.eagletechafrica.com/verify_email.php?token=" . $token;
                $subject = "Verify Your Email - ALMA University LMS";
                $message = "Hi $full_name,\n\nPlease verify your account using this code: $token\nOr click the link below:\n$verify_link\n\nThank you,\nALMA University LMS";
                $headers = "From: ALMA University <info@ashabakari.eagletechafrica.com>\r\n";
                $headers .= "Reply-To: info@ashabakari.eagletechafrica.com\r\n";
                $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

                if (mail($email, $subject, $message, $headers)) {
                    $_SESSION['message'] = "✅ Registration successful! A verification email has been sent to $email. Didn’t receive it? <a href='resend_verification.php'>Click here to resend</a>.";
                    $_SESSION['message_type'] = "success";
                } else {
                    $_SESSION['message'] = "✅ Registration successful, but email not sent. <a href='resend_verification.php'>Resend now</a>.";
                    $_SESSION['message_type'] = "warning";
                }
            }
        }
    }
    header("Location: register.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>ALMA University - Registration</title>
  <style>
    body {
        font-family: 'Poppins', sans-serif;
        background: #1e2a78;
        height: 100vh;
        display: flex;
        justify-content: center;
        align-items: center;
        margin: 0;
    }
    .form-container {
        background: #fff;
        border-radius: 12px;
        padding: 40px 30px;
        width: 420px;
        max-width: 90%;
        box-shadow: 0 4px 25px rgba(0,0,0,0.3);
        text-align: center;
    }
    h1 { 
        color: #1e2a78; 
        font-weight: 700; 
        margin-bottom: 10px; 
        font-size: 28px;
        line-height: 1.2;
    }
    h2 { 
        color: #333; 
        margin-bottom: 20px; 
        font-size: 18px; 
    }
    label { 
        display: block; 
        text-align: left; 
        margin-top: 12px; 
        color: #333; 
        font-weight: 500; 
        font-size: 14px;
    }
    input, select {
        width: 100%; 
        padding: 4px; 
        margin-top: 5px;
        border: 1px solid #ccc; 
        border-radius: 6px; 
        font-size: 14px;
        box-sizing: border-box;
    }
    button {
        background: #1e2a78; 
        color: white; 
        border: none;
        padding: 14px; 
        width: 100%; 
        border-radius: 6px;
        font-size: 16px; 
        cursor: pointer; 
        transition: 0.3s; 
        margin-top: 20px;
    }
    button:hover { 
        background: #24319c; 
    }
    .alert { 
        padding: 12px; 
        border-radius: 6px; 
        margin-bottom: 20px; 
        font-size: 14px; 
    }
    .success { background: #d4edda; color: #155724; }
    .error { background: #f8d7da; color: #721c24; }
    .warning { background: #fff3cd; color: #856404; }
    a { color: #1e2a78; text-decoration: none; font-weight: 500; }
    a:hover { text-decoration: underline; }
    p { font-size: 13px; margin-top: 15px; }
  </style>
</head>
<body>
  <div class="form-container">
    <h1>ALMA University</h1>
    <h2>Create Your Account</h2>

    <?php
    if (isset($_SESSION['message'])) {
        echo '<div class="alert ' . ($_SESSION['message_type'] ?? 'info') . '">' . $_SESSION['message'] . '</div>';
        unset($_SESSION['message'], $_SESSION['message_type']);
    }
    ?>

    <form method="POST" action="">
      <label>Full Name</label>
      <input type="text" name="full_name" required>

      <label>Email Address</label>
      <input type="email" name="email" required>

      <label>Password</label>
      <input type="password" name="password" required>

      <label>Confirm Password</label>
      <input type="password" name="confirm_password" required>

      <label>Gender</label>
      <select name="gender" required>
        <option value="">Select gender</option>
        <option>Male</option>
        <option>Female</option>
        <option>Other</option>
      </select>

      <label>Date of Birth</label>
      <input type="date" name="dob" required>

      <input type="text" name="website" style="display:none;">

      <button type="submit">Register Account</button>

      <p>Already registered? <a href="login.php">Login here</a></p>
    </form>
  </div>
</body>
</html>
