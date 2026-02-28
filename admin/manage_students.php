<?php
require_once "../config/database.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $student_id = $_POST['student_id'];

    $stmt = $conn->prepare("INSERT INTO students (student_id) VALUES (?)");
    $stmt->bind_param("s", $student_id);
    $stmt->execute();
}
?>

<h2>Manage Students</h2>

<form method="POST">
    Student ID:
    <input type="text" name="student_id" required>
    <button type="submit">Add Student</button>
</form>

<hr>

<?php
$result = $conn->query("SELECT * FROM students");

while ($row = $result->fetch_assoc()) {
    echo $row['student_id'] . "<br>";
}
?>