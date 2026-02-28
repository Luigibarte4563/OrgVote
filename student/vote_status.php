<?php
require_once "../config/session.php";
require_once "../config/database.php"; // Added to handle the candidate query
require_once "../system/check_voting_time.php";
require_once "../system/audit_logger.php";

if(!defined('SYSTEM_NAME')) define('SYSTEM_NAME', 'VOTE-X'); 

// Redirect if not logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: login.php");
    exit();
}

// Logic for status
// Assuming these functions are defined in check_voting_time.php
$status = getElectionStatus();
$election = getElectionDetails();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Voting Portal - <?= SYSTEM_NAME; ?></title>
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
            transition: all 0.3s ease;
        }
        .nav-glass {
            background: rgba(15, 23, 42, 0.8);
            backdrop-filter: blur(8px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }
        .glow-blue { text-shadow: 0 0 15px rgba(59, 130, 246, 0.5); }
        
        /* Custom Radio Styling */
        .candidate-input:checked + .candidate-card {
            background: rgba(59, 130, 246, 0.1);
            border-color: #3b82f6;
            transform: translateY(-4px);
            box-shadow: 0 10px 25px -5px rgba(59, 130, 246, 0.4);
        }
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
                <a href="vote_status.php" class="text-blue-400">My Vote</a>
                <a href="transparency.php" class="hover:text-white transition">Transparency</a>
            </div>

            <div class="flex items-center gap-4">
                <span class="text-xs bg-white/10 px-3 py-1 rounded-full text-slate-400 border border-white/10">Student</span>
                <a href="logout.php" class="text-sm font-semibold text-red-400 hover:text-red-300 transition">Logout</a>
            </div>
        </div>
    </nav>

    <main class="max-w-5xl mx-auto pt-12 px-6 pb-20">
        
        <?php if (!$election): ?>
            <div class="glass-card rounded-3xl p-12 text-center max-w-2xl mx-auto mt-10">
                <h2 class="text-2xl font-bold mb-4">No Active Elections</h2>
                <p class="text-slate-400 mb-8">There are currently no scheduled elections. Please check back later for announcements.</p>
                <a href="dashboard.php" class="inline-block bg-white/5 border border-white/10 text-white px-8 py-3 rounded-xl hover:bg-white/10 transition">Return to Dashboard</a>
            </div>

        <?php else: ?>
            <header class="mb-10 text-center md:text-left flex flex-col md:flex-row md:items-end justify-between gap-6">
                <div>
                    <h1 class="text-4xl font-extrabold mb-2"><?= htmlspecialchars($election['election_name']); ?></h1>
                    <div class="flex items-center gap-3 text-slate-400">
                        <span class="px-3 py-1 bg-blue-600/20 text-blue-400 rounded-full text-xs font-bold uppercase tracking-wider">Election Portal</span>
                        <span>•</span>
                        <p class="text-sm">Secure Cryptographic Voting</p>
                    </div>
                </div>

                <?php if ($status == "active"): ?>
                <div class="glass-card px-8 py-4 rounded-2xl flex flex-col items-center md:items-end border-emerald-500/20">
                    <p class="text-[10px] font-bold text-emerald-400 uppercase tracking-[0.2em] mb-1">Time Remaining</p>
                    <h2 id="countdown" class="text-3xl font-black tracking-tighter text-white tabular-nums">00:00:00</h2>
                </div>
                <?php endif; ?>
            </header>

            <?php if ($status == "not_started"): ?>
                <div class="glass-card rounded-[2.5rem] p-12 text-center border-yellow-500/20">
                    <div class="w-20 h-20 bg-yellow-500/10 rounded-full flex items-center justify-center mx-auto mb-6">
                        <svg class="w-10 h-10 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <h2 class="text-3xl font-bold mb-4">Voting Opens Soon</h2>
                    <p class="text-slate-400 mb-8 max-w-md mx-auto">This election is scheduled to begin on:<br>
                        <span class="text-white font-semibold"><?= date("F j, Y \a\\t g:i A", strtotime($election['start_datetime'])); ?></span>
                    </p>
                    <button disabled class="bg-white/5 border border-white/10 text-slate-500 cursor-not-allowed font-bold py-4 px-10 rounded-xl">Voting Not Yet Open</button>
                </div>

            <?php elseif ($status == "active"): ?>
                <form method="POST" action="submit_vote.php">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-12">
                        <?php
                        $res = $conn->query("SELECT * FROM candidates ORDER BY id ASC");
                        while ($row = $res->fetch_assoc()):
                        ?>
                            <label class="relative cursor-pointer group">
                                <input type="radio" name="candidate_id" value="<?= $row['id']; ?>" class="candidate-input hidden" required>
                                <div class="candidate-card glass-card h-full p-8 rounded-[2rem] flex flex-col items-center text-center">
                                    <div class="w-24 h-24 rounded-3xl bg-gradient-to-br from-blue-600 to-blue-400 flex items-center justify-center text-3xl font-black text-white mb-6 shadow-lg shadow-blue-600/20">
                                        <?= strtoupper(substr($row['name'], 0, 1)); ?>
                                    </div>
                                    <h3 class="text-xl font-bold mb-2 group-hover:text-blue-400 transition"><?= htmlspecialchars($row['name']); ?></h3>
                                    <p class="text-slate-500 text-sm leading-relaxed mb-6">Candidate for <?= SYSTEM_NAME; ?></p>
                                    <div class="mt-auto px-4 py-2 rounded-full border border-white/5 text-[10px] font-bold text-slate-400 uppercase tracking-widest group-hover:border-blue-500/50 group-hover:text-blue-400 transition">Select Candidate</div>
                                </div>
                            </label>
                        <?php endwhile; ?>
                    </div>

                    <div class="fixed bottom-10 left-1/2 -translate-x-1/2 z-50 w-full max-w-xs px-6">
                        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-500 text-white font-extrabold py-5 rounded-2xl shadow-2xl shadow-blue-600/40 transition-all transform hover:scale-105 active:scale-95">
                            Cast Secure Vote
                        </button>
                    </div>
                </form>

            <?php elseif ($status == "closed"): ?>
                <div class="glass-card rounded-[2.5rem] p-12 text-center border-red-500/20">
                    <div class="w-20 h-20 bg-red-500/10 rounded-full flex items-center justify-center mx-auto mb-6">
                        <svg class="w-10 h-10 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <h2 class="text-3xl font-bold mb-4">Election Concluded</h2>
                    <p class="text-slate-400 mb-8 max-w-md mx-auto">Voting for this election has officially ended. You can now view the audited transparency results.</p>
                    <a href="transparency.php" class="inline-block bg-white text-slate-900 font-bold py-4 px-10 rounded-xl hover:bg-slate-200 transition">View Final Results</a>
                </div>
            <?php endif; ?>

        <?php endif; ?>
    </main>

    <?php if ($status == "active"): ?>
    <script src="../assets/js/countdown.js"></script>
    <script>
        // Inline helper if countdown.js isn't found
        function startCountdown(endTime) {
            const display = document.querySelector('#countdown');
            const target = new Date(endTime).getTime();

            setInterval(() => {
                const now = new Date().getTime();
                const diff = target - now;

                if (diff < 0) {
                    display.innerHTML = "EXPIRED";
                    return;
                }

                const h = Math.floor(diff / (1000 * 60 * 60));
                const m = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
                const s = Math.floor((diff % (1000 * 60)) / 1000);

                display.innerHTML = `${h.toString().padStart(2, '0')}:${m.toString().padStart(2, '0')}:${s.toString().padStart(2, '0')}`;
            }, 1000);
        }
        startCountdown("<?= $election['end_datetime']; ?>");
    </script>
    <?php endif; ?>

</body>
</html>