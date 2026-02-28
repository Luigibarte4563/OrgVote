<?php
require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/check_voting_time.php";
require_once __DIR__ . "/audit_logger.php";

function hasAlreadyVoted($student_id) {
    global $conn;

    $stmt = $conn->prepare("SELECT id FROM votes WHERE student_id = ?");
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $stmt->store_result();

    return $stmt->num_rows > 0;
}

function submitVote($student_id, $candidate_id) {
    global $conn;

    // Check election status
    if (getElectionStatus() !== "active") {
        return "Voting is not active.";
    }

    // Prevent double voting
    if (hasAlreadyVoted($student_id)) {
        return "You have already voted.";
    }

    $stmt = $conn->prepare("INSERT INTO votes (student_id, candidate_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $student_id, $candidate_id);

    if ($stmt->execute()) {

        // Log action
        logAction($student_id, "Submitted vote for candidate ID: " . $candidate_id);

        return "Vote submitted successfully.";
    }

    return "Error submitting vote.";
}
?>