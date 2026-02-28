<?php
/**
 * admin/manage_candidates.php
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/../config/admin_session.php";
require_once __DIR__ . "/../config/database.php";

if(!defined('SYSTEM_NAME')) define('SYSTEM_NAME', 'VOTE-X'); 
$admin_name = $_SESSION['admin_name'] ?? 'Administrator';

// Handle Post Request
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['name'])) {
    $name = $_POST['name'];
    $stmt = $conn->prepare("INSERT INTO candidates (name) VALUES (?)");
    $stmt->bind_param("s", $name);
    $stmt->execute();
    // Refresh to prevent resubmission
    header("Location: manage_candidates.php?success=1");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Candidates - <?php echo SYSTEM_NAME; ?></title>
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
        }
        .candidate-card {
            transition: all 0.3s ease;
            border-left: 2px solid transparent;
        }
        .candidate-card:hover {
            background: rgba(255, 255, 255, 0.05);
            border-left: 2px solid #3b82f6;
            transform: translateX(5px);
        }
        .nav-glass {
            background: rgba(15, 23, 42, 0.95);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }
    </style>
</head>
<body>

    <nav class="nav-glass sticky top-0 z-50 px-4 md:px-6 py-4">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <div class="flex items-center gap-2">
                <div class="h-8 w-8 bg-blue-600 rounded-lg flex items-center justify-center font-bold text-white shadow-lg">V</div>
                <span class="text-xl font-extrabold tracking-tight italic"><?php echo SYSTEM_NAME; ?></span>
            </div>
            <div class="hidden md:flex items-center gap-6 text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">
                <a href="dashboard.php" class="hover:text-white transition">Dashboard</a>
                <a href="manage_candidates.php" class="text-blue-400">Candidates</a>
                <a href="logout.php" class="hover:text-red-400 transition">Exit</a>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto pt-12 px-6 pb-20">
        <header class="mb-12">
            <h1 class="text-4xl font-black tracking-tight">Candidate Registry</h1>
            <p class="text-slate-400 font-medium">Deploying and managing authorized election participants.</p>
        </header>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            
            <div class="lg:col-span-4">
                <section class="glass-card rounded-[2rem] p-8 sticky top-28">
                    <h3 class="text-sm font-black uppercase tracking-widest text-blue-500 mb-6 flex items-center gap-2">
                        <span class="h-2 w-2 bg-blue-500 rounded-full animate-ping"></span>
                        New Entry
                    </h3>
                    
                    <form method="POST" class="space-y-6">
                        <div class="space-y-2">
                            <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest ml-1">Full Legal Name</label>
                            <input type="text" name="name" required placeholder="Enter candidate name..." 
                                   class="input-field w-full px-5 py-4 rounded-xl font-semibold">
                        </div>
                        
                        <button type="submit" 
                                class="w-full bg-white/5 hover:bg-blue-600 border border-white/10 hover:border-blue-500 text-white font-bold py-4 rounded-xl transition-all shadow-xl active:scale-95">
                            Register Candidate
                        </button>
                    </form>

                    <div class="mt-8 pt-8 border-t border-white/5">
                        <p class="text-[10px] text-slate-600 leading-relaxed uppercase font-bold">
                            Security Note: All entries are logged with a timestamp and admin signature for audit transparency.
                        </p>
                    </div>
                </section>
            </div>

            <div class="lg:col-span-8">
                <div class="glass-card rounded-[2rem] overflow-hidden">
                    <div class="px-8 py-6 border-b border-white/5 flex justify-between items-center bg-white/[0.01]">
                        <h3 class="text-xs font-black uppercase tracking-widest text-slate-400">Authorized Candidates</h3>
                        <span class="bg-blue-500/10 text-blue-400 px-3 py-1 rounded-full text-[10px] font-bold">
                            SYSTEM ACTIVE
                        </span>
                    </div>

                    <div class="p-4 min-h-[400px]">
                        <?php
                        $result = $conn->query("SELECT * FROM candidates ORDER BY id DESC");
                        if ($result->num_rows > 0):
                            while ($row = $result->fetch_assoc()): ?>
                                <div class="candidate-card flex items-center justify-between p-5 rounded-2xl mb-2">
                                    <div class="flex items-center gap-4">
                                        <div class="h-10 w-10 rounded-full bg-gradient-to-tr from-slate-800 to-slate-700 flex items-center justify-center border border-white/10 text-xs font-bold text-slate-400">
                                            <?php 
                                                $initials = explode(' ', $row['name']);
                                                echo strtoupper(substr($initials[0], 0, 1) . (isset($initials[1]) ? substr($initials[1], 0, 1) : ''));
                                            ?>
                                        </div>
                                        <div>
                                            <h4 class="font-bold text-white tracking-tight"><?php echo htmlspecialchars($row['name']); ?></h4>
                                            <p class="text-[10px] text-slate-500 font-mono">ID-REF: <?php echo sprintf("%04d", $row['id']); ?></p>
                                        </div>
                                    </div>
                                    
                                    <div class="flex items-center gap-3">
                                        <button class="p-2 hover:bg-white/5 rounded-lg text-slate-500 hover:text-white transition">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                        </button>
                                        <button class="p-2 hover:bg-red-500/10 rounded-lg text-slate-500 hover:text-red-400 transition">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </div>
                                </div>
                            <?php endwhile;
                        else: ?>
                            <div class="flex flex-col items-center justify-center py-20 text-slate-600">
                                <svg class="w-12 h-12 mb-4 opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                                <p class="text-xs font-black uppercase tracking-[0.2em]">No Candidates Deployed</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

        </div>
    </main>

</body>
</html>