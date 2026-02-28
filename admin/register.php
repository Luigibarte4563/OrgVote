<?php
require_once "../config/database.php";
require_once "../system/audit_logger.php";

// Start session only if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username  = trim($_POST['username']);
    $name      = trim($_POST['name']);
    $email     = trim($_POST['email']);
    $password  = trim($_POST['password']);
    $confirm   = trim($_POST['confirm_password']);

    // Validate password
    if ($password !== $confirm) {
        $message = "Passwords do not match.";
    } else {

        // Check duplicate username or email
        $check = $conn->prepare("SELECT id FROM admins WHERE username=? OR email=?");
        $check->bind_param("ss", $username, $email);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $message = "Username or Email already exists.";
        } else {

            // Hash password
            $hashed = password_hash($password, PASSWORD_DEFAULT);

            // Insert admin
            $stmt = $conn->prepare("INSERT INTO admins (username, name, email, password) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $username, $name, $email, $hashed);

            if ($stmt->execute()) {
                $message = "Admin registration successful!";
                logAction(0, "system", "New admin registered: $username");
            } else {
                $message = "Error occurred during registration.";
            }

            $stmt->close();
        }

        $check->close();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Registration</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="container">
    <h2>Admin Registration</h2>

    <?php if($message) echo "<p><b>$message</b></p>"; ?>

    <form method="POST">
        Username:<br>
        <input type="text" name="username" required><br><br>

        Name:<br>
        <input type="text" name="name" required><br><br>

        Email (optional):<br>
        <input type="email" name="email"><br><br>

        Password:<br>
        <input type="password" name="password" required><br><br>

        Confirm Password:<br>
        <input type="password" name="confirm_password" required><br><br>

        <button type="submit">Register</button>
    </form>

    <br>
    <a href="login.php">Already have an account? Login here</a>
</div>
</body>
</html>