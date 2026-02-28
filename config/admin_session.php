<?php
// ----------------------------
// admin_session.php
// ----------------------------

// 1️⃣ Set secure session ini settings BEFORE session_start
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.use_strict_mode', 1);

    // 2️⃣ Start the session safely
    session_start();
}

// 3️⃣ Initialize admin namespace
if (!isset($_SESSION['admin'])) {
    $_SESSION['admin'] = [];
}

// ----------------------------
// Admin functions
// ----------------------------
function checkAdminAccess() {
    if (!isset($_SESSION['admin']['user_id'])) {
        header("Location: ../admin/login.php?error=unauthorized");
        exit();
    }
}

function adminLogin($id, $name) {
    $_SESSION['admin']['user_id'] = $id;
    $_SESSION['admin']['admin_name'] = $name;
}

function adminLogout() {
    unset($_SESSION['admin']);
    header("Location: ../admin/login.php");
    exit();
}
?>