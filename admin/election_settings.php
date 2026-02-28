<?php
/**
 * admin/election_settings.php
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/../config/admin_session.php";
require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../system/audit_logger.php";

if(!defined('SYSTEM_NAME')) define('SYSTEM_NAME', 'VOTE-X'); 
$admin_name = $_SESSION['admin_name'] ?? 'Administrator';

$message = "";

// Processing the form
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'];
    $start = $_POST['start'];
    $end   = $_POST['end'];

    $stmt = $conn->prepare("INSERT INTO elections (election_name, start_datetime, end_datetime) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $title, $start, $end);
    
    if($stmt->execute()) {
        if(function_exists('logAction')) {
            logAction($_SESSION['user_id'], "admin", "Created election schedule: $title");
        }
        $message = "Protocol established successfully.";
    } else {
        $message = "Error: System failed to initialize schedule.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Election Settings - <?php echo SYSTEM_NAME; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: radial-gradient(circle at top right, #0f172a, #020617);
            min-height: 100vh;
            color: #f8fafc;
        }
        .glass-card {
            background: rgba(255, 255, 255, 0.02);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }
        .input-field {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: white;
            transition: all 0.3s ease;
        }
        .input-field:focus {
            outline: none;
            border-color: #3b82f6;
            background: rgba(59, 130, 246, 0.05);
            box-shadow: 0 0 15px rgba(59, 130, 246, 0.1);
        }
        .nav-glass {
            background: rgba(15, 23, 42, 0.95);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }
    </style>
</head>
<body class="pb-12">

    <nav class="nav-glass sticky top-0 z-50 px-4 md:px-6 py-4">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <div class="flex items-center gap-2">
                <div class="h-8 w-8 bg-blue-600 rounded-lg flex items-center justify-center font-bold text-white shadow-lg shadow-blue-600/30">V</div>
                <span class="text-xl font-extrabold tracking-tight"><?php echo SYSTEM_NAME; ?></span>
            </div>
            <div class="hidden lg:flex items-center gap-5 text-slate-400 font-semibold text-xs uppercase tracking-widest">
                <a href="dashboard.php" class="hover:text-white transition">Dashboard</a>
                <a href="election_settings.php" class="text-blue-400">Settings</a>
                <a href="logout.php" class="text-red-400">Terminal Exit</a>
            </div>
        </div>
    </nav>

    <main class="max-w-3xl mx-auto pt-16 px-6">
        
        <div class="mb-10 text-center">
            <h1 class="text-4xl font-black tracking-tight mb-2">Protocol Settings</h1>
            <p class="text-slate-500 italic">Initialize or modify the cryptographic voting window.</p>
        </div>

        <?php if($message): ?>
            <div class="mb-8 p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-sm font-bold text-center animate-pulse">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <section class="glass-card rounded-[2.5rem] p-8 md:p-12 shadow-2xl relative overflow-hidden">
            <div class="absolute -bottom-24 -left-24 w-64 h-64 bg-blue-600/10 rounded-full blur-3xl"></div>

            <form method="POST" class="relative z-10 space-y-8">
                <div class="space-y-2">
                    <label class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-500 ml-2">Election Identifier</label>
                    <input type="text" name="title" required 
                           placeholder="e.g., Supreme Student Council 2026"
                           class="input-field w-full px-6 py-4 rounded-2xl text-lg font-semibold">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-500 ml-2">Start Timestamp</label>
                        <input type="datetime-local" name="start" required 
                               class="input-field w-full px-6 py-4 rounded-2xl font-mono text-sm">
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-500 ml-2">End Timestamp</label>
                        <input type="datetime-local" name="end" required 
                               class="input-field w-full px-6 py-4 rounded-2xl font-mono text-sm">
                    </div>
                </div>

                <div class="pt-6">
                    <button type="submit" 
                            class="w-full bg-blue-600 hover:bg-blue-500 text-white font-black uppercase tracking-widest py-5 rounded-2xl transition-all shadow-xl shadow-blue-600/20 active:scale-[0.98]">
                        Save Protocol Configuration
                    </button>
                    <p class="text-center text-[10px] text-slate-600 mt-6 uppercase tracking-[0.1em]">
                        Warning: Changing timestamps during live polling may affect data integrity.
                    </p>
                </div>
            </form>
        </section>

        <div class="mt-8 text-center">
            <a href="dashboard.php" class="text-slate-500 hover:text-white text-xs font-bold uppercase tracking-widest transition">
                ← Return to Command Center
            </a>
        </div>
    </main>

</body>
</html>