<?php
/**
 * admin/dashboard.php
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/../config/admin_session.php";
require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../system/check_voting_time.php";

if (function_exists('checkAccess')) {
    checkAccess('admin');
} else {
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        header("Location: login.php");
        exit();
    }
}

if(!defined('SYSTEM_NAME')) define('SYSTEM_NAME', 'VOTE-X'); 

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
            border: 1px solid rgba(255, 255, 255, 0.08);
        }
        .nav-glass {
            background: rgba(15, 23, 42, 0.95);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }
        .glow-blue { text-shadow: 0 0 15px rgba(59, 130, 246, 0.5); }

        #mobile-menu {
            transition: all 0.3s ease-in-out;
            max-height: 0;
            overflow: hidden;
        }
        #mobile-menu.open {
            max-height: 600px;
            padding-bottom: 1.5rem;
        }
        .nav-link {
            transition: all 0.2s;
            font-size: 0.825rem;
        }
        .nav-link:hover { color: #60a5fa; }
    </style>
</head>
<body>

    <nav class="nav-glass sticky top-0 z-50 px-4 md:px-6 py-4">
        <div class="max-w-7xl mx-auto">
            <div class="flex justify-between items-center">
                <div class="flex items-center gap-2">
                    <div class="h-8 w-8 bg-blue-600 rounded-lg flex items-center justify-center font-bold text-white shadow-lg shadow-blue-600/30">V</div>
                    <span class="text-xl font-extrabold tracking-tight glow-blue"><?php echo SYSTEM_NAME; ?></span>
                </div>
                
                <div class="hidden lg:flex items-center gap-5 text-slate-300 font-semibold">
                    <a href="dashboard.php" class="nav-link text-blue-400">Dashboard</a>
                    <a href="manage_students.php" class="nav-link">Voters</a>
                    <a href="manage_positions.php" class="nav-link">Positions</a>
                    <a href="manage_partylist.php" class="nav-link">Partylists</a>
                    <a href="manage_candidates.php" class="nav-link">Candidates</a>
                    <a href="view_results.php" class="nav-link">Results</a>
                    <a href="election_settings.php" class="nav-link">Settings</a>
                </div>

                <div class="flex items-center gap-3">
                    <div class="hidden sm:flex flex-col text-right pr-3 border-r border-white/10">
                        <span class="text-[9px] font-bold text-slate-500 uppercase tracking-widest">Administrator</span>
                        <span class="text-xs font-semibold text-slate-300"><?php echo htmlspecialchars($admin_name); ?></span>
                    </div>
                    
                    <a href="logout.php" class="hidden md:block bg-red-500/10 hover:bg-red-500/20 text-red-400 px-4 py-2 rounded-xl text-xs font-black uppercase tracking-widest border border-red-500/20 transition-all">Logout</a>
                    
                    <button id="menu-btn" class="lg:hidden p-2 text-slate-300 hover:text-white focus:outline-none">
                        <svg id="menu-icon" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <div id="mobile-menu" class="lg:hidden flex flex-col gap-4 mt-4 text-sm font-medium text-slate-300 border-t border-white/5 pt-0">
                <a href="dashboard.php" class="text-blue-400 pt-4">Dashboard</a>
                <a href="manage_students.php">Manage Voters</a>
                <a href="manage_positions.php">Manage Positions</a>
                <a href="manage_partylist.php">Manage Partylists</a>
                <a href="manage_candidates.php">Manage Candidates</a>
                <a href="view_results.php">Election Results</a>
                <a href="election_settings.php">System Settings</a>
                <div class="flex justify-between items-center pt-4 border-t border-white/5">
                    <span class="text-xs text-slate-500 italic"><?php echo htmlspecialchars($admin_name); ?></span>
                    <a href="logout.php" class="text-red-400 font-bold">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto pt-12 px-6 pb-20">
        <header class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6 mb-12">
            <div>
                <h1 class="text-4xl font-black mb-2 tracking-tight">Command Center</h1>
                <p class="text-slate-400 italic font-medium">System overview and protocol management.</p>
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
                                <p class="text-slate-400 leading-relaxed max-w-xl">System set to auto-start on <span class="text-white font-bold"><?php echo date("M d, Y @ H:i", strtotime($election['start_datetime'])); ?></span>.</p>
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
                            <a href="update_election.php" class="text-center bg-white/10 hover:bg-white/20 text-white font-bold px-8 py-4 rounded-2xl transition-all border border-white/10 backdrop-blur-md">Modify Timeline</a>
                            <a href="transparency.php" class="text-center bg-blue-600 hover:bg-blue-500 text-white font-bold px-8 py-4 rounded-2xl transition-all shadow-lg shadow-blue-600/30">Audit Trail</a>
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
    </main>

    <script>
        const menuBtn = document.getElementById('menu-btn');
        const mobileMenu = document.getElementById('mobile-menu');
        const menuIcon = document.getElementById('menu-icon');

        menuBtn.addEventListener('click', () => {
            mobileMenu.classList.toggle('open');
            if (mobileMenu.classList.contains('open')) {
                menuIcon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>';
            } else {
                menuIcon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>';
            }
        });
    </script>
</body>
</html>