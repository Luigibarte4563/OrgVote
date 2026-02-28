<?php
require_once "../config/database.php";
require_once "../config/session.php";
require_once "../includes/auth_check.php";
require_once "../system/audit_logger.php";

/*
|--------------------------------------------------------------------------
| ADD PARTYLIST
|--------------------------------------------------------------------------
*/
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_partylist'])) {

    $name = trim($_POST['name']);
    $description = trim($_POST['description']);

    // Check duplicate
    $check = $conn->prepare("SELECT id FROM partylist WHERE name = ?");
    $check->bind_param("s", $name);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $message = "Partylist already exists.";
    } else {

        $stmt = $conn->prepare("
            INSERT INTO partylist (name, description)
            VALUES (?, ?)
        ");
        $stmt->bind_param("ss", $name, $description);

        if ($stmt->execute()) {
            logAction($_SESSION['user_id'], "admin", "Added Partylist: " . $name);
            $message = "Partylist added successfully.";
        } else {
            $message = "Error adding partylist.";
        }

        $stmt->close();
    }

    $check->close();
}

/*
|--------------------------------------------------------------------------
| DELETE PARTYLIST
|--------------------------------------------------------------------------
*/
if (isset($_GET['delete'])) {

    $id = intval($_GET['delete']);

    $stmt = $conn->prepare("DELETE FROM partylist WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        logAction($_SESSION['user_id'], "admin", "Deleted Partylist ID: " . $id);
        $message = "Partylist deleted.";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Partylist</title>
</head>
<body>

<h2>Manage Partylist</h2>

<?php if(isset($message)) echo "<p><b>$message</b></p>"; ?>

<!-- ADD PARTYLIST FORM -->
<form method="POST">
    <h3>Add New Partylist</h3>

    Partylist Name:<br>
    <input type="text" name="name" required><br><br>

    Description:<br>
    <textarea name="description" rows="3"></textarea><br><br>

    <button type="submit" name="add_partylist">Add Partylist</button>
</form>

<hr>

<h3>Existing Partylist</h3>

<table border="1" cellpadding="10">
    <tr>
        <th>ID</th>
        <th>Name</th>
        <th>Description</th>
        <th>Date Created</th>
        <th>Action</th>
    </tr>

<?php
$result = $conn->query("SELECT * FROM partylist ORDER BY created_at DESC");

while ($row = $result->fetch_assoc()):
?>

<tr>
    <td><?= $row['id']; ?></td>
    <td><?= htmlspecialchars($row['name']); ?></td>
    <td><?= htmlspecialchars($row['description']); ?></td>
    <td><?= $row['created_at']; ?></td>
    <td>
        <a href="?delete=<?= $row['id']; ?>"
           onclick="return confirm('Are you sure you want to delete this partylist?');">
           Delete
        </a>
    </td>
</tr>

<?php endwhile; ?>

</table>

<br>
<a href="dashboard.php">Back to Dashboard</a>

</body>
</html>