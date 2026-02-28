<?php
/**
 * Session management for Admin and Student
 * Handles separate session namespaces for each role
 */

// ----------------------------
// Start admin session safely
// ----------------------------
function adminSessionStart() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    // Admin namespace
    if (!isset($_SESSION['admin'])) {
        $_SESSION['admin'] = [];
    }
}

// ----------------------------
// Start student session safely
// ----------------------------
function studentSessionStart() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    // Student namespace
    if (!isset($_SESSION['student'])) {
        $_SESSION['student'] = [];
    }
}

// ----------------------------
// Check Admin Access
// ----------------------------
function checkAdminAccess() {
    adminSessionStart();
    if (!isset($_SESSION['admin']['user_id'])) {
        // Not logged in as admin
        header("Location: ../admin/login.php?error=unauthorized");
        exit();
    }
}

// ----------------------------
// Check Student Access
// ----------------------------
function checkStudentAccess() {
    studentSessionStart();
    if (!isset($_SESSION['student']['user_id'])) {
        // Not logged in as student
        header("Location: ../student/login.php?error=unauthorized");
        exit();
    }
}

// ----------------------------
// Admin Login
// ----------------------------
function adminLogin($id, $name) {
    adminSessionStart();
    $_SESSION['admin']['user_id'] = $id;
    $_SESSION['admin']['admin_name'] = $name;
}

// ----------------------------
// Student Login
// ----------------------------
function studentLogin($id, $name) {
    studentSessionStart();
    $_SESSION['student']['user_id'] = $id;
    $_SESSION['student']['student_name'] = $name;
}

// ----------------------------
// Admin Logout
// ----------------------------
function adminLogout() {
    adminSessionStart();
    unset($_SESSION['admin']);
    header("Location: ../admin/login.php");
    exit();
}

// ----------------------------
// Student Logout
// ----------------------------
function studentLogout() {
    studentSessionStart();
    unset($_SESSION['student']);
    header("Location: ../student/login.php");
    exit();
}

// ----------------------------
// Secure session cookie settings
// ----------------------------
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.use_strict_mode', 1);
?>