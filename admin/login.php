<?php
// Start session at the very top
session_start();

// Include database and audit logger
require_once "../config/database.php";
require_once "../system/audit_logger.php";

// Assuming SYSTEM_NAME is in a config, otherwise:
if(!defined('SYSTEM_NAME')) define('SYSTEM_NAME', 'VOTE-X'); 

// If admin already logged in, redirect to dashboard
if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    header("Location: dashboard.php");
    exit();
}

$message = "";

// Handle POST request for login
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (!empty($username) && !empty($password)) {
        // Prepare query to get admin data
        $stmt = $conn->prepare("SELECT id, password, name FROM admins WHERE username=? LIMIT 1");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();
        $stmt->bind_result($id, $hashed, $name);

        if ($stmt->num_rows === 1) {
            $stmt->fetch();

            // Verify password
            if (password_verify($password, $hashed)) {
                // Set session variables
                $_SESSION['user_id'] = $id;
                $_SESSION['role'] = 'admin';
                $_SESSION['admin_name'] = $name;

                // Log login action
                // logAction($id, "admin", "Logged in"); // Uncomment if logic exists

                header("Location: dashboard.php");
                exit();
            } else {
                $message = "Invalid administrative credentials.";
            }
        } else {
            $message = "Administrative account not found.";
        }
        $stmt->close();
    } else {
        $message = "Please enter your administrative credentials.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Authentication - <?= SYSTEM_NAME; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: radial-gradient(circle at top right, #0f172a, #020617);
            min-height: 100vh;
            color: #f8fafc;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .glass-card {
            background: rgba(255, 255, 255, 0.02);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }
        .input-glass {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.3s ease;
        }
        .input-glass:focus {
            background: rgba(255, 255, 255, 0.07);
            border-color: #3b82f6;
            outline: none;
            box-shadow: 0 0 15px rgba(59, 130, 246, 0.2);
        }
    </style>
</head>
<body class="p-6">

    <div class="w-full max-w-md">
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center h-16 w-16 bg-blue-600 rounded-2xl mb-4 shadow-xl shadow-blue-600/20 font-black text-2xl">V</div>
            <h1 class="text-3xl font-extrabold tracking-tight italic"><?= SYSTEM_NAME; ?> <span class="text-blue-500 not-italic text-sm uppercase tracking-widest ml-1 font-bold">Admin</span></h1>
            <p class="text-slate-500 mt-2">Authorized Personnel Access Only</p>
        </div>

        <div class="glass-card rounded-[2.5rem] p-8 md:p-10">
            <?php if (!empty($message)) : ?>
                <div class="mb-6 p-4 rounded-xl bg-red-500/10 border border-red-500/20 text-red-400 text-sm flex items-center gap-3 animate-pulse">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>

            <form method="POST" autocomplete="off" class="space-y-6">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-widest text-slate-500 mb-2 ml-1">Administrative Username</label>
                    <input type="text" name="username" required 
                           class="w-full input-glass rounded-xl px-4 py-3.5 text-white placeholder:text-slate-600"
                           placeholder="Enter username">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-widest text-slate-500 mb-2 ml-1">Security Keyphrase</label>
                    <input type="password" name="password" required 
                           class="w-full input-glass rounded-xl px-4 py-3.5 text-white placeholder:text-slate-600"
                           placeholder="••••••••">
                </div>

                <button type="submit" 
                        class="w-full bg-blue-600 hover:bg-blue-500 text-white font-bold py-4 rounded-xl shadow-lg shadow-blue-600/20 transition-all hover:-translate-y-0.5 active:translate-y-0">
                    Authenticate Account
                </button>
            </form>

            <div class="mt-8 pt-6 border-t border-white/5 text-center">
                <p class="text-sm text-slate-500">System maintenance? <a href="register.php" class="text-blue-400 hover:text-blue-300 font-semibold transition">Initialize New Admin</a></p>
            </div>
        </div>

        <p class="text-center mt-10 text-[10px] text-slate-600 uppercase tracking-[0.3em]">
            &copy; <?= date('Y'); ?> Universidad de Dagupan &bull; Governance Protocol
        </p>
    </div>

</body>
</html>