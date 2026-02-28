<?php
require_once "../system/result_counter.php";
if(!defined('SYSTEM_NAME')) define('SYSTEM_NAME', 'VOTE-X'); 

$results = getVoteResults();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Election Results - <?php echo SYSTEM_NAME; ?></title>
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
        .nav-glass {
            background: rgba(15, 23, 42, 0.95);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }
        .table-row-hover:hover {
            background: rgba(59, 130, 246, 0.05);
            transition: all 0.2s ease;
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
                <a href="results.php" class="text-blue-400 border-b-2 border-blue-500 pb-1">Live Results</a>
                <a href="election_settings.php" class="hover:text-white transition">Settings</a>
                <a href="logout.php" class="text-red-400 hover:text-red-300 transition">Terminal Exit</a>
            </div>
        </div>
    </nav>

    <main class="max-w-4xl mx-auto pt-16 px-6">
        
        <div class="mb-10 text-center">
            <h1 class="text-4xl font-black tracking-tight mb-2 uppercase">Election Results</h1>
            <p class="text-slate-500 italic">Real-time cryptographic tallying from the secure ledger.</p>
        </div>

        <section class="glass-card rounded-[2.5rem] p-4 md:p-8 shadow-2xl relative overflow-hidden">
            <div class="absolute -top-24 -right-24 w-64 h-64 bg-blue-600/10 rounded-full blur-3xl"></div>

            <div class="relative z-10 overflow-x-auto">
                <table class="w-full text-left border-separate border-spacing-y-3">
                    <thead>
                        <tr>
                            <th class="px-6 py-4 text-[10px] font-black uppercase tracking-[0.2em] text-slate-500">Candidate Identity</th>
                            <th class="px-6 py-4 text-[10px] font-black uppercase tracking-[0.2em] text-slate-500 text-right">Verified Tally</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(!empty($results)): ?>
                            <?php foreach ($results as $row): ?>
                                <tr class="table-row-hover group">
                                    <td class="px-6 py-5 rounded-l-2xl border-l border-t border-b border-white/5 bg-white/[0.01]">
                                        <div class="flex items-center gap-3">
                                            <div class="h-10 w-10 rounded-full bg-gradient-to-br from-slate-700 to-slate-900 flex items-center justify-center text-blue-400 font-bold border border-white/10 group-hover:border-blue-500/50 transition">
                                                <?= substr($row['name'], 0, 1); ?>
                                            </div>
                                            <span class="text-lg font-semibold tracking-tight text-slate-200"><?= htmlspecialchars($row['name']); ?></span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-5 rounded-r-2xl border-r border-t border-b border-white/5 bg-white/[0.01] text-right">
                                        <span class="font-mono text-2xl font-bold text-blue-400"><?= number_format($row['total_votes']); ?></span>
                                        <span class="text-[10px] text-slate-500 block uppercase tracking-tighter">Units Captured</span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="2" class="px-6 py-12 text-center text-slate-500 italic">
                                    No data packets found in the current protocol window.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="mt-8 pt-6 border-t border-white/5 flex flex-col md:flex-row justify-between items-center gap-4">
                <div class="flex items-center gap-2">
                    <span class="relative flex h-3 w-3">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></span>
                    </span>
                    <span class="text-[10px] font-bold text-emerald-500 uppercase tracking-widest">Live System Feed</span>
                </div>
                <button onclick="window.location.reload()" class="text-[10px] font-bold text-slate-400 hover:text-white uppercase tracking-widest transition flex items-center gap-2">
                    Refresh Manifest ↺
                </button>
            </div>
        </section>

        <div class="mt-12 text-center">
            <a href="dashboard.php" class="group text-slate-500 hover:text-white text-xs font-bold uppercase tracking-widest transition">
                <span class="inline-block transition-transform group-hover:-translate-x-1">←</span> Return to Command Center
            </a>
        </div>
    </main>

</body>
</html>