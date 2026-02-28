<?php
require_once "../system/result_counter.php";
$results = getVoteResults();
?>

<h2>Transparency Page</h2>

<?php foreach ($results as $row): ?>
    <?= $row['name']; ?> - <?= $row['total_votes']; ?> votes <br>
<?php endforeach; ?>