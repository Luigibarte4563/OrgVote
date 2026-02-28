<?php
require_once __DIR__ . '/../config/database.php';

function getElectionDetails() {
    global $conn;
    $sql = "SELECT * FROM election_settings ORDER BY election_id DESC LIMIT 1";
    $result = $conn->query($sql);
    return ($result && $result->num_rows > 0) ? $result->fetch_assoc() : null;
}

function checkVotingStatus() {
    $election = getElectionDetails();
    $now = date('Y-m-d H:i:s');

    if (!$election) return 'no_election';
    if ($now < $election['start_datetime']) return 'not_started';
    if ($now > $election['end_datetime']) return 'closed';
    return 'active';
}

function getElectionInfo() {
    $election = getElectionDetails();
    if (!$election) return ['name' => 'No election scheduled', 'status' => 'no_election'];
    return ['name' => $election['election_name'], 'status' => checkVotingStatus()];
}
?>