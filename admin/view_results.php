<?php
require_once "../system/result_counter.php";

$results = getVoteResults();
?>

<h2>Election Results</h2>

<table border="1" cellpadding="10">
    <tr>
        <th>Candidate</th>
        <th>Total Votes</th>
    </tr>

    <?php foreach ($results as $row): ?>
        <tr>
            <td><?= $row['name']; ?></td>
            <td><?= $row['total_votes']; ?></td>
        </tr>
    <?php endforeach; ?>
</table>