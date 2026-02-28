<?php
require_once "../config/database.php";

function logAction($user_id, $role, $action) {
    global $conn;

    if (session_status() == PHP_SESSION_NONE) session_start();

    $stmt = $conn->prepare("INSERT INTO audit_logs (user_id, role, action) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $user_id, $role, $action);
    $stmt->execute();
    $stmt->close();
}