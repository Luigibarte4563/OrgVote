<?php
function getElectionStatus() {
    global $conn; // database connection
    $sql = "SELECT status FROM election_settings ORDER BY election_id DESC LIMIT 1";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['status'];
    }
    return null;
}

function getElectionDetails() {
    global $conn;
    $sql = "SELECT * FROM election_settings ORDER BY election_id DESC LIMIT 1";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    return null;
}
?>