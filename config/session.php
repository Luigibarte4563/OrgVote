<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    // Secure session settings
    ini_set('session.use_only_cookies', 1);
    ini_set('session.use_strict_mode', 1);

    session_start();

    // Regenerate session ID for security (once per session)
    if (!isset($_SESSION['created'])) {
        session_regenerate_id(true);
        $_SESSION['created'] = time();
    }
}

// ===== Access control =====
// For student pages
if (basename($_SERVER['PHP_SELF']) != 'login.php' && basename($_SERVER['PHP_SELF']) != 'register.php') {
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'student') {
        // Not logged in or wrong role
        header("Location: login.php");
        exit();
    }
}

// For admin pages (optional separate check)
if (strpos($_SERVER['PHP_SELF'], '/admin/') !== false) {
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
        header("Location: ../admin/login.php");
        exit();
    }
}
?>