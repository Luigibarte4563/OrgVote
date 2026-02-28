<?php
require_once "../config/database.php";
require_once "../config/session.php";

if(!defined('SYSTEM_NAME')) define('SYSTEM_NAME', 'VOTE-X'); 

$status = include "../system/check_voting_time.php";

// 1. Check Voting Status
if ($status != "active") {
    header("Location: dashboard.php?error=not_active");
    exit();
}

// 2. Check if already voted
$user_id = $_SESSION['user_id'];
$checkVote = $conn->query("SELECT * FROM votes WHERE student_id='$user_id'");
if ($checkVote->num_rows > 0) {
    header("Location: vote_status.php?voted=true");
    exit();
}

$candidates = $conn->query("SELECT * FROM candidates");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cast Vote - <?= SYSTEM_NAME; ?></title>
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
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .nav-glass {
            background: rgba(15, 23, 42, 0.8);
            backdrop-filter: blur(8px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }
        /* Hide default radio but keep functional */
        .candidate-radio:checked + .candidate-ui {
            background: rgba(59, 130, 246, 0.1);
            border-color: #3b82f6;
            box-shadow: 0 0 20px rgba(59, 130, 246, 0.3);
            transform: translateY(-5px);
        }
        .candidate-radio:checked + .candidate-ui .check-mark {
            opacity: 1;
            transform: scale(1);
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
            <div class="flex items-center gap-4">
                <span class="text-xs bg-white/10 px-3 py-1 rounded-full text-slate-400 border border-white/10">Student</span>
                <a href="logout.php" class="text-sm font-semibold text-red-400 hover:text-red-300 transition">Logout</a>
            </div>
        </div>
    </nav>

    <main class="max-w-5xl mx-auto pt-12 px-6 pb-32">
        <header class="mb-10">
            <h1 class="text-4xl font-bold mb-2">Cast Your Vote</h1>
            <p class="text-slate-400">Select one candidate. This action is final and cannot be undone.</p>
        </header>

        <form method="POST" action="submit_vote.php" id="voteForm">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php while($row = $candidates->fetch_assoc()): ?>
                    <label class="relative block cursor-pointer group">
                        <input type="radio" name="candidate_id" value="<?= $row['id'] ?>" class="candidate-radio sr-only" required onchange="showSubmit()">
                        
                        <div class="candidate-ui glass-card h-full p-8 rounded-[2rem] flex flex-col items-center text-center relative">
                            <div class="check-mark opacity-0 scale-50 transition-all duration-300 absolute top-4 right-4 h-6 w-6 bg-blue-600 rounded-full flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>

                            <div class="w-20 h-20 bg-white/5 rounded-2xl flex items-center justify-center mb-6 group-hover:bg-blue-600/10 transition">
                                <span class="text-2xl font-bold text-blue-400"><?= strtoupper(substr($row['name'], 0, 1)) ?></span>
                            </div>

                            <h3 class="text-xl font-bold mb-1 text-white"><?= htmlspecialchars($row['name']) ?></h3>
                            <p class="text-slate-500 text-sm mb-4 tracking-wide uppercase font-semibold">Official Candidate</p>
                            
                            <div class="w-full h-px bg-white/5 mb-4"></div>
                            
                            <p class="text-slate-400 text-sm leading-relaxed italic">
                                "Ready to serve the student body with integrity and transparency."
                            </p>
                        </div>
                    </label>
                <?php endwhile; ?>
            </div>

            <div id="submitBar" class="fixed bottom-8 left-1/2 -translate-x-1/2 w-full max-w-md px-6 hidden animate-bounce-in">
                <div class="glass-card p-4 rounded-2xl shadow-2xl border-blue-500/30 bg-slate-900/90 backdrop-blur-xl">
                    <button type="submit" 
                            onclick="return confirm('Are you sure? You can only vote once.')"
                            class="w-full bg-blue-600 hover:bg-blue-500 text-white font-bold py-4 rounded-xl shadow-lg shadow-blue-600/30 transition-all flex items-center justify-center gap-2">
                        <span>Confirm and Submit Vote</span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>
            </div>
        </form>
    </main>

    <script>
        function showSubmit() {
            const bar = document.getElementById('submitBar');
            bar.classList.remove('hidden');
            bar.classList.add('flex');
        }
    </script>

</body>
</html>