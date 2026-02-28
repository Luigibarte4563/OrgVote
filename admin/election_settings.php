<?php
require_once "../config/database.php";
require_once "../system/audit_logger.php";
require_once "../config/session.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $title = $_POST['title'];
    $start = $_POST['start'];
    $end   = $_POST['end'];

    $stmt = $conn->prepare("
        INSERT INTO elections (election_name, start_datetime, end_datetime)
        VALUES (?, ?, ?)
    ");

    $stmt->bind_param("sss", $title, $start, $end);
    $stmt->execute();

    logAction($_SESSION['user_id'], "admin", "Created election schedule");

    echo "Election scheduled successfully.";
}
?>

<h2>Election Settings</h2>

<form method="POST">
    Title:<br>
    <input type="text" name="title" required><br><br>

    Start:<br>
    <input type="datetime-local" name="start" required><br><br>

    End:<br>
    <input type="datetime-local" name="end" required><br><br>

    <button type="submit">Save</button>
</form>