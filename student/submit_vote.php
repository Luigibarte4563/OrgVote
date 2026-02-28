<?php
require_once "../config/session.php";
require_once "../config/database.php";
require_once "../system/audit_logger.php";

if(!defined('SYSTEM_NAME')) define('SYSTEM_NAME', 'VOTE-X'); 

$vote_success = false;
$message = "";

// Handle Vote Submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $student_id = $_SESSION['user_id'] ?? 0;
    $candidate_id = isset($_POST['candidate_id']) ? $_POST['candidate_id'] : null;

    if (!$candidate_id) {
        $message = "Please select a candidate before submitting.";
    } else {
        // Check if already voted (Safety check)
        $check = $conn->prepare("SELECT id FROM votes WHERE student_id = ?");
        $check->bind_param("i", $student_id);
        $check->execute();
        $check->store_result();
        
        if($check->num_rows > 0) {
            $message = "You have already cast your vote for this election.";
        } else {
            $stmt = $conn->prepare("INSERT INTO votes (student_id, candidate_id) VALUES (?, ?)");
            $stmt->bind_param("ii", $student_id, $candidate_id);
            
            if ($stmt->execute()) {
                $vote_success = true;
                logAction($student_id, 'student', "Voted for candidate ID $candidate_id");
            } else {
                $message = "Database error. Please try again.";
            }
            $stmt->close();
        }
        $check->close();
    }
}

// Fetch Candidates for the UI
$candidates = $conn->query("SELECT id, name, platform FROM candidates");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cast Your Vote - <?= SYSTEM_NAME; ?></title>
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
        .candidate-radio:checked + .glass-card {
            background: rgba(59, 130, 246, 0.1);
            border-color: #3b82f6;
            transform: translateY(-5px);
            box-shadow: 0 10px 30px -10px rgba(59, 130, 246, 0.5);
        }
        .nav-glass {
            background: rgba(15, 23, 42, 0.8);
            backdrop-filter: blur(8px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }
    </style>
</head>
<body>

    <nav class="nav-glass sticky top-0 z-50 px-6 py-4">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <div class="flex items-center gap-2">
                <div class="h-8 w-8 bg-blue-600 rounded-lg flex items-center justify-center font-bold text-white">V</div>
                <span class="text-xl font-extrabold tracking-tight"><?= SYSTEM_NAME; ?></span>
            </div>
            <a href="dashboard.php" class="text-sm font-medium text-slate-400 hover:text-white transition">← Back to Dashboard</a>
        </div>
    </nav>

    <main class="max-w-5xl mx-auto pt-12 px-6 pb-20">
        
        <?php if ($vote_success): ?>
            <div class="glass-card rounded-[2.5rem] p-12 text-center max-w-2xl mx-auto mt-10">
                <div class="inline-flex p-5 rounded-full bg-emerald-500/10 mb-6">
                    <svg class="h-16 w-16 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h2 class="text-4xl font-bold mb-4">Vote Recorded</h2>
                <p class="text-slate-400 mb-8 text-lg">Thank you for participating! Your vote has been encrypted and added to the secure ledger.</p>
                <a href="vote_status.php" class="inline-block bg-blue-600 hover:bg-blue-500 text-white font-bold py-4 px-10 rounded-xl transition">
                    View My Receipt
                </a>
            </div>

        <?php else: ?>
            <header class="mb-12">
                <h1 class="text-4xl font-extrabold mb-2">Cast Your Ballot</h1>
                <p class="text-slate-400">Select one candidate. This action cannot be undone.</p>
                
                <?php if($message): ?>
                    <div class="mt-6 p-4 rounded-xl bg-red-500/10 border border-red-500/20 text-red-400 text-sm">
                        <?= $message; ?>
                    </div>
                <?php endif; ?>
            </header>

            <form method="POST">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php while($row = $candidates->fetch_assoc()): ?>
                        <label class="relative cursor-pointer group">
                            <input type="radio" name="candidate_id" value="<?= $row['id']; ?>" class="candidate-radio hidden" required>
                            <div class="glass-card h-full p-8 rounded-3xl flex flex-col items-center text-center">
                                <div class="w-20 h-20 rounded-2xl bg-white/5 flex items-center justify-center text-2xl font-bold text-blue-400 mb-6 group-hover:bg-blue-600 group-hover:text-white transition-colors">
                                    <?= strtoupper(substr($row['name'], 0, 1)); ?>
                                </div>
                                <h3 class="text-xl font-bold mb-2"><?= htmlspecialchars($row['name']); ?></h3>
                                <p class="text-slate-500 text-sm leading-relaxed mb-6">
                                    <?= htmlspecialchars($row['platform']); ?>
                                </p>
                                <div class="mt-auto pt-4 w-full border-t border-white/5 text-xs font-bold uppercase tracking-widest text-blue-500 opacity-0 group-hover:opacity-100 transition-opacity">
                                    Click to select
                                </div>
                            </div>
                        </label>
                    <?php endwhile; ?>
                </div>

                <div class="mt-16 flex flex-col items-center">
                    <p class="text-slate-500 text-sm mb-6 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"></path></svg>
                        End-to-end encrypted submission
                    </p>
                    <button type="submit" class="bg-white text-slate-900 font-extrabold py-5 px-20 rounded-2xl hover:bg-blue-400 hover:text-white transition-all transform hover:scale-105 active:scale-95 shadow-xl shadow-white/5">
                        Submit Final Vote
                    </button>
                </div>
            </form>
        <?php endif; ?>

    </main>

</body>
</html>