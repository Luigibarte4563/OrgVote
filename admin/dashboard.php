<?php
/**
 * DEBUGGING BLOCK 
 * Remove these 2 lines once the UI shows up on localhost
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 1. Start session manually if not started to prevent checkAccess errors
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Fix Paths: Using __DIR__ ensures localhost finds files regardless of nesting
require_once __DIR__ . "/../config/admin_session.php";
require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../system/check_voting_time.php";

// 3. Security Check
if (function_exists('checkAccess')) {
    checkAccess('admin');
} else {
    // Fallback if session.php fails to load the function
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        header("Location: login.php");
        exit();
    }
}

// 4. Define Constants if missing
if(!defined('SYSTEM_NAME')) define('SYSTEM_NAME', 'VOTE-X'); 

// 5. Data Fetching (Wrapped in null-checks to prevent crashes)
$election = (function_exists('getElectionDetails')) ? getElectionDetails() : null;
$status = (function_exists('getElectionStatus')) ? getElectionStatus() : "none";
$admin_name = $_SESSION['admin_name'] ?? 'Administrator';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - <?php echo SYSTEM_NAME; ?></title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;800&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: radial-gradient(circle at top right, #0f172a, #020617);
            min-height: 100vh;
            color: #f8fafc;
            margin: 0;
        }
        .glass-card {
            background: rgba(255, 255, 255, 0.02);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px); /* Safari support */
            border: 1px solid rgba(255, 255, 255, 0.08);
        }
        .nav-glass {
            background: rgba(15, 23, 42, 0.8);
            backdrop-filter: blur(8px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }
        .module-card {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .module-card:hover {
            background: rgba(59, 130, 246, 0.05);
            border-color: rgba(59, 130, 246, 0.3);
            transform: translateY(-4px);
        }
        .glow-blue { text-shadow: 0 0 15px rgba(59, 130, 246, 0.5); }
    </style>
</head>
<body>

    <nav class="nav-glass sticky top-0 z-50 px-6 py-4">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <div class="flex items-center gap-2">
                <div class="h-8 w-8 bg-blue-600 rounded-lg flex items-center justify-center font-bold text-white shadow-lg shadow-blue-600/30">V</div>
                <span class="text-xl font-extrabold tracking-tight glow-blue"><?php echo SYSTEM_NAME; ?></span>
            </div>
            
            <div class="flex items-center gap-4">
                <div class="text-right hidden md:block">
                    <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest leading-none mb-1">Authenticated As</p>
                    <p class="text-sm font-semibold text-white"><?php echo htmlspecialchars($admin_name); ?></p>
                </div>
                <a href="logout.php" class="ml-4 bg-red-500/10 hover:bg-red-500/20 text-red-400 px-5 py-2 rounded-xl text-xs font-black uppercase tracking-widest border border-red-500/20 transition-all">Logout</a>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto pt-12 px-6 pb-20">
        <header class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6 mb-12">
            <div>
                <h1 class="text-4xl font-black mb-2 tracking-tight">Command Center</h1>
                <p class="text-slate-400 italic font-medium">Monitoring and managing active protocols.</p>
            </div>

            <?php if($election): ?>
                <div class="glass-card px-6 py-4 rounded-2xl flex items-center gap-6 border-l-4 <?php echo $status == 'active' ? 'border-emerald-500' : ($status == 'not_started' ? 'border-yellow-500' : 'border-red-500'); ?>">
                    <div>
                        <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">Current Instance</p>
                        <h4 class="font-bold text-white"><?php echo htmlspecialchars($election['election_name']); ?></h4>
                    </div>
                    <?php if($status == 'active'): ?>
                        <div class="text-right pl-6 border-l border-white/10">
                            <p class="text-[10px] font-bold text-emerald-400 uppercase tracking-widest mb-1">Time Remaining</p>
                            <h4 id="countdown" class="font-mono font-bold text-emerald-400 tabular-nums text-lg">00:00:00</h4>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </header>

        <section class="glass-card rounded-[2.5rem] p-8 md:p-12 mb-12 relative overflow-hidden shadow-2xl shadow-black/50">
            <div class="absolute -top-24 -right-24 w-64 h-64 bg-blue-600/10 rounded-full blur-3xl"></div>
            
            <div class="relative z-10">
                <?php if($election): ?>
                    <div class="flex flex-col md:flex-row gap-8 items-center">
                        <div class="flex-1">
                            <?php if($status == "not_started"): ?>
                                <span class="bg-yellow-500/10 text-yellow-500 px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest border border-yellow-500/20 mb-4 inline-block">Scheduled</span>
                                <h2 class="text-3xl font-extrabold mb-4">Election Preparation</h2>
                                <p class="text-slate-400 leading-relaxed max-w-xl">System set to auto-start on <span class="text-white font-bold"><?php echo date("M d, Y @ H:i", strtotime($election['start_datetime'])); ?></span>. Ensure all candidates are properly registered.</p>
                            <?php elseif($status == "active"): ?>
                                <span class="bg-emerald-500/10 text-emerald-500 px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest border border-emerald-500/20 mb-4 inline-block animate-pulse">Ongoing</span>
                                <h2 class="text-3xl font-extrabold mb-4">Live Polling Phase</h2>
                                <p class="text-slate-400 leading-relaxed max-w-xl">Cryptographic voting is active. Monitoring real-time logs for system integrity.</p>
                            <?php elseif($status == "closed"): ?>
                                <span class="bg-red-500/10 text-red-400 px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest border border-red-500/20 mb-4 inline-block">Concluded</span>
                                <h2 class="text-3xl font-extrabold mb-4">Final Results Available</h2>
                                <p class="text-slate-400 leading-relaxed max-w-xl">The voting window has closed. You can now proceed to official tallying.</p>
                            <?php endif; ?>
                        </div>
                        <div class="w-full md:w-auto flex flex-col sm:flex-row gap-4">
                            <a href="election_settings.php" class="text-center bg-white/10 hover:bg-white/20 text-white font-bold px-8 py-4 rounded-2xl transition-all border border-white/10 backdrop-blur-md">Settings</a>
                            <a href="view_results.php" class="text-center bg-blue-600 hover:bg-blue-500 text-white font-bold px-8 py-4 rounded-2xl transition-all shadow-lg shadow-blue-600/30">Live Results</a>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="text-center py-10">
                        <div class="h-16 w-16 bg-white/5 rounded-2xl flex items-center justify-center mx-auto mb-6 border border-white/10">
                            <svg class="w-8 h-8 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        </div>
                        <h2 class="text-2xl font-bold mb-2">No Active Election</h2>
                        <p class="text-slate-500 mb-8 text-sm max-w-xs mx-auto">Please initialize an election period to enable system functions.</p>
                        <a href="election_settings.php" class="bg-blue-600 text-white font-bold px-10 py-4 rounded-2xl inline-block shadow-lg shadow-blue-600/30">Initialize Profile</a>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <a href="manage_students.php" class="module-card glass-card p-6 rounded-[2rem] group">
                <div class="h-12 w-12 bg-blue-500/10 rounded-xl flex items-center justify-center text-blue-400 mb-6 group-hover:bg-blue-600 group-hover:text-white transition-all">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                </div>
                <h3 class="text-lg font-bold mb-2">Voters</h3>
                <p class="text-slate-500 text-xs leading-relaxed">Manage student records and eligibility status.</p>
            </a>

            <a href="manage_candidates.php" class="module-card glass-card p-6 rounded-[2rem] group">
                <div class="h-12 w-12 bg-emerald-500/10 rounded-xl flex items-center justify-center text-emerald-400 mb-6 group-hover:bg-emerald-600 group-hover:text-white transition-all">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                </div>
                <h3 class="text-lg font-bold mb-2">Candidates</h3>
                <p class="text-slate-500 text-xs leading-relaxed">Oversee candidate profiles and platforms.</p>
            </a>

            <a href="manage_partylist.php" class="module-card glass-card p-6 rounded-[2rem] group">
                <div class="h-12 w-12 bg-purple-500/10 rounded-xl flex items-center justify-center text-purple-400 mb-6 group-hover:bg-purple-600 group-hover:text-white transition-all">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                </div>
                <h3 class="text-lg font-bold mb-2">Groups</h3>
                <p class="text-slate-500 text-xs leading-relaxed">Organize political affiliations and lineups.</p>
            </a>

            <a href="view_logs.php" class="module-card glass-card p-6 rounded-[2rem] group">
                <div class="h-12 w-12 bg-slate-500/10 rounded-xl flex items-center justify-center text-slate-400 mb-6 group-hover:bg-slate-600 group-hover:text-white transition-all">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                </div>
                <h3 class="text-lg font-bold mb-2">Security</h3>
                <p class="text-slate-500 text-xs leading-relaxed">Review system events and access logs.</p>
            </a>
        </div>
    </main>

    <?php if($status == "active" && $election): ?>
    <script src="../assets/js/countdown.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof startCountdown === "function") {
                startCountdown("<?php echo $election['end_datetime']; ?>");
            }
        });
    </script>
    <?php endif; ?>

</body>
</html>