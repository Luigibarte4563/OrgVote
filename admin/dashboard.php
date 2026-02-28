<?php
require_once "../config/session.php";
require_once "../system/check_voting_time.php";

$election = getElectionDetails();
$status = getElectionStatus();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<div class="container">
    <h2>Admin Dashboard</h2>

    <?php if($election): ?>
        <h3>Current Election: <?= $election['election_name']; ?></h3>
        <?php if($status == "not_started"): ?>
            <p class="status-upcoming">Election has not started yet. Starts on <?= date("F j, Y \a\\t g:i A", strtotime($election['start_datetime'])); ?></p>
        <?php elseif($status == "active"): ?>
            <p class="status-active">Election is ongoing!</p>
            <p>Time Remaining:</p>
            <h3 id="countdown"></h3>
            <script src="../assets/js/countdown.js"></script>
            <script>
                startCountdown("<?= $election['end_datetime']; ?>");
            </script>
        <?php elseif($status == "closed"): ?>
            <p class="status-closed">Election has ended.</p>
        <?php endif; ?>
    <?php else: ?>
        <p>No election scheduled at the moment.</p>
    <?php endif; ?>

    <ul>
        <li><a href="manage_students.php">Manage Students</a></li>
        <li><a href="manage_candidates.php">Manage Candidates</a></li>
        <li><a href="manage_partylist.php">Manage Partylist</a></li>
        <li><a href="election_settings.php">Election Settings</a></li>
        <li><a href="view_results.php">View Results</a></li>
        <li><a href="view_logs.php">Audit Logs</a></li>
        <li><a href="logout.php">Logout</a></li>
    </ul>
</div>

</body>
</html>