<?php
/**
 * Student Session Management
 */

// Secure session cookie settings - MUST be before session_start()
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.use_strict_mode', 1);

// Start session safely
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Start student session namespace
if (!isset($_SESSION['student'])) {
    $_SESSION['student'] = [];
}

// Check student login
function checkStudentAccess() {
    if (!isset($_SESSION['student']['user_id'])) {
        header("Location: ../student/login.php?error=unauthorized");
        exit();
    }
}

// Student login
function studentLogin($id, $name) {
    $_SESSION['student']['user_id'] = $id;
    $_SESSION['student']['student_name'] = $name;
}

// Student logout
function studentLogout() {
    unset($_SESSION['student']);
    header("Location: ../student/login.php");
    exit();
}
?>