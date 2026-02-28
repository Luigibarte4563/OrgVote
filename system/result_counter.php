<?php
require_once __DIR__ . "/../config/database.php";

function getVoteResults() {
    global $conn;

    $sql = "
        SELECT candidates.id,
               candidates.name,
               COUNT(votes.id) AS total_votes
        FROM candidates
        LEFT JOIN votes ON candidates.id = votes.candidate_id
        GROUP BY candidates.id
        ORDER BY total_votes DESC
    ";

    $result = $conn->query($sql);

    $data = [];

    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }

    return $data;
}
?>