<?php
require_once "../config/session.php";
require_once "../config/database.php";
require_once "../system/audit_logger.php";

// Start session safely
if (session_status() == PHP_SESSION_NONE) session_start();

// Redirect if not admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$message = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name  = trim($_POST['election_name']);
    $start = trim($_POST['start_datetime']);
    $end   = trim($_POST['end_datetime']);

    if ($start >= $end) {
        $message = "Start date must be before end date.";
    } else {
        $stmt = $conn->prepare("INSERT INTO election_settings (election_name, start_datetime, end_datetime) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $name, $start, $end);
        if ($stmt->execute()) {
            $message = "Election schedule saved successfully!";
            logAction($_SESSION['user_id'], "admin", "Set new election: $name");
        } else {
            $message = "Error saving election.";
        }
        $stmt->close();
    }
}

// Get latest election
$res = $conn->query("SELECT * FROM election_settings ORDER BY start_datetime DESC LIMIT 1");
$latestElection = $res->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Election</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="container">
    <h2>Manage Election Schedule</h2>

    <?php if ($message) echo "<p><b>$message</b></p>"; ?>

    <form method="POST">
        Election Name:<br>
        <input type="text" name="election_name" required value="<?= $latestElection['election_name'] ?? ''; ?>"><br><br>

        Start Date & Time:<br>
        <input type="datetime-local" name="start_datetime" required value="<?= isset($latestElection['start_datetime']) ? date('Y-m-d\TH:i', strtotime($latestElection['start_datetime'])) : ''; ?>"><br><br>

        End Date & Time:<br>
        <input type="datetime-local" name="end_datetime" required value="<?= isset($latestElection['end_datetime']) ? date('Y-m-d\TH:i', strtotime($latestElection['end_datetime'])) : ''; ?>"><br><br>

        <button type="submit">Save Election</button>
    </form>
</div>
</body>
</html>