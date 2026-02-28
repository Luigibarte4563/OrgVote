<?php
require_once "../config/database.php";
require_once "../system/audit_logger.php";

// Start session safely
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $student_id = trim($_POST['student_id']);
    $password   = trim($_POST['password']);

    $stmt = $conn->prepare("SELECT id, password FROM students WHERE student_id=?");
    $stmt->bind_param("s", $student_id);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($id, $hashed);

    if ($stmt->num_rows == 1) {
        $stmt->fetch();

        if (password_verify($password, $hashed)) {
            $_SESSION['user_id'] = $id;
            $_SESSION['role'] = 'student';
            // Note: Make sure your login script also sets $_SESSION['student_name'] 
            // if you want the dashboard greeting to work.
            logAction($id, "student", "Logged in");
            header("Location: dashboard.php");
            exit();
        } else {
            $message = "Incorrect password.";
        }
    } else {
        $message = "Student ID not found.";
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Login - VOTE-X</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: radial-gradient(circle at top right, #1e3a8a, #0f172a, #020617);
            min-height: 100vh;
            color: #f8fafc;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .glass-card {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }
        .glow-blue { text-shadow: 0 0 15px rgba(59, 130, 246, 0.5); }
        .input-field {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.3s ease;
        }
        .input-field:focus {
            background: rgba(255, 255, 255, 0.08);
            border-color: #3b82f6;
            outline: none;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
        }
    </style>
</head>
<body class="p-6">

    <div class="max-w-md w-full space-y-8 relative">
        <div class="absolute -top-20 -left-20 w-40 h-40 bg-blue-600/20 rounded-full blur-3xl"></div>
        
        <div class="text-center relative z-10">
            <div class="inline-flex items-center justify-center h-16 w-16 bg-blue-600 rounded-2xl mb-4 shadow-lg shadow-blue-500/20">
                <span class="text-3xl font-bold text-white">V</span>
            </div>
            <h2 class="text-3xl font-extrabold tracking-tight">Student Access</h2>
            <p class="mt-2 text-slate-400">Please enter your credentials to continue</p>
        </div>

        <div class="glass-card rounded-[2rem] p-8 md:p-10 relative z-10">
            <?php if($message): ?>
                <div class="mb-6 p-4 rounded-xl bg-red-500/10 border border-red-500/20 text-red-400 text-sm flex items-center gap-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                    <?= $message; ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="space-y-6">
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-widest text-slate-500 mb-2 ml-1">Student ID</label>
                    <input type="text" name="student_id" required 
                        class="input-field w-full px-5 py-4 rounded-xl text-white placeholder-slate-600"
                        placeholder="e.g. 2023-0001">
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-widest text-slate-500 mb-2 ml-1">Password</label>
                    <input type="password" name="password" required 
                        class="input-field w-full px-5 py-4 rounded-xl text-white placeholder-slate-600"
                        placeholder="••••••••">
                </div>

                <button type="submit" 
                    class="w-full bg-blue-600 hover:bg-blue-500 text-white font-bold py-4 rounded-xl transition-all hover:scale-[1.02] active:scale-[0.98] shadow-lg shadow-blue-600/20">
                    Sign In to Portal
                </button>
            </form>

            <div class="mt-8 pt-6 border-t border-white/5 text-center">
                <p class="text-slate-400 text-sm">
                    Don't have an account? 
                    <a href="register.php" class="text-blue-400 font-semibold hover:text-blue-300 transition">Register here</a>
                </p>
            </div>
        </div>

        <p class="text-center text-slate-500 text-[10px] uppercase tracking-[0.3em]">
            Universidad de Dagupan &bull; Secure Protocol
        </p>
    </div>

</body>
</html>