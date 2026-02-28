<?php
require_once "../config/database.php";

date_default_timezone_set("Asia/Manila");

function getElectionStatus() {
    global $conn;

    $current_time = date("Y-m-d H:i:s");

    $sql = "SELECT * FROM elections ORDER BY id DESC LIMIT 1";
    $result = $conn->query($sql);

    if ($result->num_rows == 0) {
        return "no_election";
    }

    $election = $result->fetch_assoc();

    if ($current_time < $election['start_datetime']) {
        return "not_started";
    } elseif ($current_time > $election['end_datetime']) {
        return "closed";
    } else {
        return "active";
    }
}
?>