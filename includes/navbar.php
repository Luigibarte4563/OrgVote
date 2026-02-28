<?php
require_once "../config/session.php";
?>

<nav style="background:#333;padding:10px;">
    <a href="../student/dashboard.php" style="color:white;margin-right:15px;">Dashboard</a>
    <a href="../student/vote_status.php" style="color:white;margin-right:15px;">Vote Status</a>
    <a href="../student/transparency.php" style="color:white;margin-right:15px;">Results</a>

    <?php if(isset($_SESSION['user_id'])): ?>
        <a href="../student/logout.php" style="color:white;float:right;">Logout</a>
    <?php endif; ?>
</nav>
<br>