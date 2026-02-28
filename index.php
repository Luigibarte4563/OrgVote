<?php
require_once "config/constants.php";
require_once "config/database.php"; // Ensure database connection is included
require_once __DIR__ . '/system/check_voting_time.php';

// Fetch election details once - this contains 'end_datetime'
$election = getElectionDetails();

// Get the current status (active, not_started, or closed)
$status = checkVotingStatus(); 

// REMOVED: getElectionCountdown() and getElectionInfo() to prevent Fatal Errors
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= SYSTEM_NAME; ?> - Home</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: radial-gradient(circle at top right, #1e3a8a, #0f172a, #020617);
            min-height: 100vh;
            color: #f8fafc;
            overflow-x: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .glass-card {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.37);
        }
        .glow-blue { text-shadow: 0 0 15px rgba(59, 130, 246, 0.5); }
        .btn-futuristic {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .btn-futuristic:hover {
            box-shadow: 0 0 20px rgba(59, 130, 246, 0.4);
            transform: translateY(-2px);
        }
        .blob {
            position: absolute;
            width: 500px;
            height: 500px;
            background: rgba(29, 78, 216, 0.15);
            filter: blur(80px);
            border-radius: 50%;
            z-index: -1;
            animation: move 20s infinite alternate;
        }
        @keyframes move {
            from { transform: translate(-10%, -10%); }
            to { transform: translate(20%, 20%); }
        }
    </style>
</head>
<body class="p-6">

    <div class="blob" style="top: -10%; left: -10%;"></div>
    <div class="blob" style="bottom: -10%; right: -10%; background: rgba(30, 58, 138, 0.1);"></div>

    <div class="max-w-2xl w-full text-center space-y-8 z-10">
        
        <header class="space-y-4">
            <div class="inline-block p-1 rounded-full bg-blue-500/20 border border-blue-500/30 mb-4 px-4 py-1 text-xs font-semibold uppercase tracking-widest text-blue-400">
                Universidad de Dagupan Official Portal
            </div>
            <h1 class="text-5xl md:text-6xl font-extrabold tracking-tight">
                Welcome to <span class="text-blue-500 glow-blue"><?= SYSTEM_NAME; ?></span>
            </h1>
            <p class="text-slate-400 text-lg max-w-lg mx-auto leading-relaxed">
                Experience the next generation of campus democracy. Secure, transparent, and built for the UDD community.
            </p>
        </header>

        <div class="glass-card rounded-3xl p-8 md:p-10 space-y-6">
            <?php if($election && $status == "active"): ?>
                <div class="space-y-2">
                    <span class="flex items-center justify-center gap-2 text-emerald-400 font-medium animate-pulse">
                        <span class="h-2 w-2 rounded-full bg-emerald-400"></span>
                        Election Live
                    </span>
                    <h2 class="text-2xl font-bold text-slate-100 italic">"<?= htmlspecialchars($election['election_name']); ?>"</h2>
                </div>

                <div class="bg-black/20 rounded-2xl py-6 px-4 border border-white/5">
                    <p class="text-slate-400 text-sm uppercase tracking-widest mb-3">Time Remaining</p>
                    <h3 id="countdown" class="text-4xl md:text-5xl font-mono font-bold text-blue-400 tracking-tighter">--:--:--</h3>
                </div>

                <script src="assets/js/countdown.js"></script>
                <script>
                    // Only start if function exists
                    if (typeof startCountdown === "function") {
                        startCountdown("<?= $election['end_datetime']; ?>");
                    } else {
                        console.error("countdown.js failed to load or function missing.");
                    }
                </script>

            <?php elseif($election && $status == "not_started"): ?>
                <div class="py-4">
                    <div class="bg-blue-500/10 rounded-2xl p-6 border border-blue-500/20">
                        <p class="text-blue-300">Upcoming Election: <strong><?= htmlspecialchars($election['election_name']); ?></strong></p>
                        <p class="text-slate-400 text-sm mt-2">
                            Starts on: <span class="text-slate-200"><?= date("F j, Y \a\\t g:i A", strtotime($election['start_datetime'])); ?></span>
                        </p>
                    </div>
                </div>
            <?php else: ?>
                <div class="py-10">
                    <p class="text-slate-500 italic">No active or upcoming elections at this time.</p>
                </div>
            <?php endif; ?>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-8">
                <a href="student/login.php" class="btn-futuristic bg-blue-600 hover:bg-blue-500 text-white font-bold py-4 px-6 rounded-xl flex items-center justify-center gap-2 group">
                    <span>Student Login</span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
                <a href="admin/login.php" class="btn-futuristic bg-white/5 hover:bg-white/10 text-slate-200 font-bold py-4 px-6 rounded-xl border border-white/10 flex items-center justify-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                    <span>Admin Access</span>
                </a>
            </div>
        </div>

        <footer class="text-slate-500 text-xs uppercase tracking-[0.2em]">
            &copy; <?= date("Y"); ?> Universidad de Dagupan &bull; Secure Voting Protocol
        </footer>
    </div>

</body>
</html>