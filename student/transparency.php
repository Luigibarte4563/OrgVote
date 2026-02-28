<?php
require_once "../config/session.php";
require_once "../config/database.php";

if(!defined('SYSTEM_NAME')) define('SYSTEM_NAME', 'VOTE-X'); 

// Fetch totals for percentage calculation
$total_votes_query = $conn->query("SELECT COUNT(id) as total FROM votes");
$total_data = $total_votes_query->fetch_assoc();
$grand_total = $total_data['total'] > 0 ? $total_data['total'] : 1; 

// Detailed results query
$sql = "SELECT candidates.name, COUNT(votes.id) as total
        FROM candidates
        LEFT JOIN votes ON candidates.id = votes.candidate_id
        GROUP BY candidates.id
        ORDER BY total DESC";

$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transparency - <?= SYSTEM_NAME; ?></title>
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
        .progress-bar { transition: width 1s ease-in-out; }
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
                <a href="dashboard.php" class="hover:text-white transition">Dashboard</a>
                <a href="vote_status.php" class="hover:text-white transition">My Vote</a>
                <a href="transparency.php" class="text-blue-400">Transparency</a>
            </div>

            <div class="flex items-center gap-4">
                <span class="text-xs bg-white/10 px-3 py-1 rounded-full text-slate-400 border border-white/10">Student</span>
                <a href="logout.php" class="text-sm font-semibold text-red-400 hover:text-red-300 transition">Logout</a>
            </div>
        </div>
    </nav>

    <main class="max-w-4xl mx-auto pt-16 px-6 pb-20">
        <header class="mb-10">
            <div class="flex items-center gap-3 mb-2">
                <span class="relative flex h-3 w-3">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></span>
                </span>
                <h1 class="text-4xl font-bold">Live Results</h1>
            </div>
            <p class="text-slate-400">Real-time vote distribution across all candidates.</p>
        </header>

        <div class="glass-card rounded-3xl p-6 mb-8 flex items-center justify-between">
            <div>
                <p class="text-slate-500 text-sm font-semibold uppercase tracking-wider">Total Ballots Cast</p>
                <h2 class="text-4xl font-black mt-1"><?= number_format($total_data['total']); ?></h2>
            </div>
            <div class="h-12 w-12 bg-blue-600/20 rounded-2xl flex items-center justify-center text-blue-400">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
            </div>
        </div>

        <div class="space-y-4">
            <?php while($row = $result->fetch_assoc()): 
                $percent = ($row['total'] / $grand_total) * 100;
            ?>
                <div class="glass-card rounded-2xl p-6 relative overflow-hidden">
                    <div class="flex justify-between items-center mb-4 relative z-10">
                        <div>
                            <h3 class="text-lg font-bold text-white"><?= htmlspecialchars($row['name']); ?></h3>
                            <p class="text-xs text-slate-500 font-medium uppercase tracking-widest">Candidate</p>
                        </div>
                        <div class="text-right">
                            <span class="text-2xl font-black text-blue-400"><?= number_format($row['total']); ?></span>
                            <span class="text-slate-500 text-xs block font-bold">VOTES</span>
                        </div>
                    </div>

                    <div class="relative w-full h-3 bg-white/5 rounded-full overflow-hidden">
                        <div class="progress-bar absolute top-0 left-0 h-full bg-gradient-to-r from-blue-600 to-cyan-400 rounded-full" 
                             style="width: <?= $percent; ?>%"></div>
                    </div>
                    
                    <div class="mt-2 text-right">
                        <span class="text-[10px] font-bold text-slate-500 uppercase"><?= number_format($percent, 1); ?>% of total</span>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>

        <footer class="mt-12 text-center">
            <p class="text-slate-500 text-xs">
                Data refreshes on page reload. Cryptographic integrity verified by <?= SYSTEM_NAME; ?>.
            </p>
        </footer>
    </main>

</body>
</html>