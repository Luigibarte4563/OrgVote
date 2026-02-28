<?php
require_once "../config/database.php";
require_once "../system/audit_logger.php";

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$message = "";
$message_type = "error"; // To handle green success alerts

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $student_id = trim($_POST['student_id']);
    $name       = trim($_POST['name']);
    $email      = trim($_POST['email']);
    $password   = trim($_POST['password']);
    $confirm    = trim($_POST['confirm_password']);

    if ($password !== $confirm) {
        $message = "Passwords do not match.";
    } else {
        $check = $conn->prepare("SELECT id FROM students WHERE student_id=? OR email=?");
        $check->bind_param("ss", $student_id, $email);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $message = "Student ID or Email already exists.";
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO students (student_id, name, email, password) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $student_id, $name, $email, $hashed);

            if ($stmt->execute()) {
                $message = "Registration successful! You can now login.";
                $message_type = "success";
                logAction(0, "system", "New student registered: $student_id");
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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account - VOTE-X</title>
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

    <div class="max-w-xl w-full space-y-8 relative">
        <div class="absolute -top-20 -right-20 w-64 h-64 bg-blue-600/10 rounded-full blur-3xl"></div>
        
        <div class="text-center relative z-10">
            <h2 class="text-4xl font-extrabold tracking-tight">Create Account</h2>
            <p class="mt-2 text-slate-400">Join the digital voting platform</p>
        </div>

        <div class="glass-card rounded-[2.5rem] p-8 md:p-12 relative z-10">
            
            <?php if($message): ?>
                <div class="mb-8 p-4 rounded-2xl flex items-center gap-3 text-sm <?= $message_type == 'success' ? 'bg-emerald-500/10 border border-emerald-500/20 text-emerald-400' : 'bg-red-500/10 border border-red-500/20 text-red-400' ?>">
                    <?php if($message_type == 'success'): ?>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>
                    <?php else: ?>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>
                    <?php endif; ?>
                    <?= $message; ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold uppercase tracking-widest text-slate-500 mb-2 ml-1">Full Name</label>
                    <input type="text" name="name" required class="input-field w-full px-5 py-4 rounded-xl text-white placeholder-slate-600" placeholder="e.g. Juan Dela Cruz">
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-widest text-slate-500 mb-2 ml-1">Student ID</label>
                    <input type="text" name="student_id" required class="input-field w-full px-5 py-4 rounded-xl text-white placeholder-slate-600" placeholder="2026-XXXX">
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-widest text-slate-500 mb-2 ml-1">Email Address</label>
                    <input type="email" name="email" class="input-field w-full px-5 py-4 rounded-xl text-white placeholder-slate-600" placeholder="name@udd.edu.ph">
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-widest text-slate-500 mb-2 ml-1">Password</label>
                    <input type="password" name="password" required class="input-field w-full px-5 py-4 rounded-xl text-white placeholder-slate-600" placeholder="••••••••">
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-widest text-slate-500 mb-2 ml-1">Confirm Password</label>
                    <input type="password" name="confirm_password" required class="input-field w-full px-5 py-4 rounded-xl text-white placeholder-slate-600" placeholder="••••••••">
                </div>

                <div class="md:col-span-2 mt-4">
                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-500 text-white font-bold py-4 rounded-xl transition-all hover:scale-[1.01] active:scale-[0.99] shadow-lg shadow-blue-600/20">
                        Create My Account
                    </button>
                </div>
            </form>

            <div class="mt-8 pt-6 border-t border-white/5 text-center">
                <p class="text-slate-400 text-sm">
                    Already have an account? 
                    <a href="login.php" class="text-blue-400 font-semibold hover:text-blue-300 transition">Log in instead</a>
                </p>
            </div>
        </div>

        <p class="text-center text-slate-500 text-[10px] uppercase tracking-[0.3em]">
            Secure Registration &bull; VOTE-X Terminal
        </p>
    </div>

</body>
</html>