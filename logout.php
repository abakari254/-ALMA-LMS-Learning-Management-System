<?php
// PHP SCRIPT START
// ----------------------------------------------------

// 1. Start the session if it hasn't been started yet.
// This is necessary to access the session variables.
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// 2. Unset all session variables.
// This clears any stored user data, ID, role, and tokens (like CSRF).
$_SESSION = array();

// 3. Destroy the session.
// This cleans the server's session storage for the current session ID.
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    // Set the session cookie's expiration to a past time to force removal
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 4. Finally, destroy the session.
session_destroy();

// 5. Redirect the user to the landing page.
// The user is securely logged out and returned to the system entrance.
header('Location: index.php');
exit;

// ----------------------------------------------------
// PHP SCRIPT END
?>