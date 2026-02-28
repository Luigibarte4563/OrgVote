<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    // Secure session settings
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.use_strict_mode', 1);
    
    session_start();

    // Regenerate session ID periodically to prevent hijacking
    if (!isset($_SESSION['created'])) {
        session_regenerate_id(true);
        $_SESSION['created'] = time();
    }
}

/**
 * Checks if a user is logged in as a specific role.
 * Redirects to the appropriate login page if unauthorized.
 *
 * @param string $requiredRole 'admin' or 'student'
 */
function checkAccess($requiredRole) {
    $currentFile = basename($_SERVER['PHP_SELF']);

    // Prevent redirect loops: allow login.php and index.php
    $allowedPages = ['login.php', 'index.php', 'register.php'];
    if (in_array($currentFile, $allowedPages)) {
        return;
    }

    // If session role is not set or doesn't match required role
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== $requiredRole) {
        // Clear session safely
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();

        // Redirect based on role
        if ($requiredRole === 'admin') {
            header("Location: login.php?error=unauthorized");
        } else {
            header("Location: ../student/login.php?error=unauthorized");
        }
        exit();
    }
}
?>