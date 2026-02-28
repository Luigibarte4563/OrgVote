<?php
require_once "../config/session.php";
// Assuming SYSTEM_NAME is defined in your config
if(!defined('SYSTEM_NAME')) define('SYSTEM_NAME', 'VOTE-X'); 

// Logic for status
$status = include "../system/check_voting_time.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?= SYSTEM_NAME; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: radial-gradient(circle at top right, #1e3a8a, #0f172a, #020617);
            min-height: 100vh;
            color: #f8fafc;
        }
        .glass-card {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .nav-glass {
            background: rgba(15, 23, 42, 0.8);
            backdrop-filter: blur(8px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }
        .glow-blue { text-shadow: 0 0 15px rgba(59, 130, 246, 0.5); }
    </style>
</head>
<body>

    <nav class="nav-glass sticky top-0 z-50 px-6 py-4">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <div class="flex items-center gap-2">
                <div class="h-8 w-8 bg-blue-600 rounded-lg flex items-center justify-center font-bold text-white">V</div>
                <span class="text-xl font-extrabold tracking-tight glow-blue"><?= SYSTEM_NAME; ?></span>
            </div>
            
            <div class="hidden md:flex items-center gap-8 text-sm font-medium text-slate-300">
                <a href="dashboard.php" class="text-blue-400">Dashboard</a>
                <a href="vote_status.php" class="hover:text-white transition">My Vote</a>
                <a href="transparency.php" class="hover:text-white transition">Transparency</a>
            </div>

            <div class="flex items-center gap-4">
                <span class="text-xs bg-white/10 px-3 py-1 rounded-full text-slate-400 border border-white/10">Student</span>
                <a href="logout.php" class="text-sm font-semibold text-red-400 hover:text-red-300 transition">Logout</a>
            </div>
        </div>
    </nav>

    <main class="max-w-4xl mx-auto pt-16 px-6">
        <header class="mb-10">
            <h1 class="text-4xl font-bold mb-2">Welcome back, Student!</h1>
            <p class="text-slate-400">Manage your vote and monitor election real-time data.</p>
        </header>

        <div class="glass-card rounded-3xl p-8 md:p-12 text-center relative overflow-hidden">
            <div class="absolute -top-10 -right-10 w-32 h-32 bg-blue-600/10 rounded-full blur-3xl"></div>

            <div class="relative z-10">
                <?php if ($status == "not_started"): ?>
                    <div class="inline-flex p-4 rounded-full bg-yellow-500/10 mb-6">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-yellow-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h2 class="text-3xl font-bold mb-4">Voting hasn't started</h2>
                    <p class="text-slate-400 mb-8 max-w-md mx-auto">The polls are currently getting prepared. Please check back later for the official opening.</p>
                    <button disabled class="bg-white/5 border border-white/10 text-slate-500 cursor-not-allowed font-bold py-4 px-10 rounded-xl">
                        Waiting for Start
                    </button>

                <?php elseif ($status == "closed"): ?>
                    <div class="inline-flex p-4 rounded-full bg-red-500/10 mb-6">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h2 class="text-3xl font-bold mb-4">Voting is Closed</h2>
                    <p class="text-slate-400 mb-8 max-w-md mx-auto">This election has concluded. You can now view the audited results and transparency report.</p>
                    <a href="transparency.php" class="inline-block bg-white text-slate-900 hover:bg-slate-200 transition font-bold py-4 px-10 rounded-xl">
                        View Final Results
                    </a>

                <?php else: ?>
                    <div class="inline-flex p-4 rounded-full bg-emerald-500/10 mb-6 animate-pulse">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h2 class="text-3xl font-bold mb-4">The Polls are Open</h2>
                    <p class="text-slate-400 mb-8 max-w-md mx-auto">Your voice matters. Cast your vote securely using our encrypted blockchain-inspired protocol.</p>
                    <a href="vote.php" class="inline-block bg-blue-600 hover:bg-blue-500 shadow-lg shadow-blue-500/20 transition-all hover:-translate-y-1 text-white font-bold py-4 px-12 rounded-xl">
                        Vote Now
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <div class="mt-8 grid grid-cols-1 md:grid-cols-2 gap-6 text-sm">
            <div class="glass-card p-6 rounded-2xl flex items-center gap-4">
                <div class="text-blue-400 bg-blue-400/10 p-3 rounded-lg">🔒</div>
                <div>
                    <h4 class="font-semibold">Secure Session</h4>
                    <p class="text-slate-500">Encrypted end-to-end</p>
                </div>
            </div>
            <div class="glass-card p-6 rounded-2xl flex items-center gap-4">
                <div class="text-emerald-400 bg-emerald-400/10 p-3 rounded-lg">⚡</div>
                <div>
                    <h4 class="font-semibold">Real-time Stats</h4>
                    <p class="text-slate-500">Live data synchronization</p>
                </div>
            </div>
        </div>
    </main>

</body>
</html>