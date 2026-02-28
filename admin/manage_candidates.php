<?php
require_once "../config/database.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];

    $stmt = $conn->prepare("INSERT INTO candidates (name) VALUES (?)");
    $stmt->bind_param("s", $name);
    $stmt->execute();
}
?>

<h2>Manage Candidates</h2>

<form method="POST">
    Candidate Name:
    <input type="text" name="name" required>
    <button type="submit">Add Candidate</button>
</form>

<hr>

<?php
$result = $conn->query("SELECT * FROM candidates");

while ($row = $result->fetch_assoc()) {
    echo $row['name'] . "<br>";
}
?>