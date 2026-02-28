<?php
/**
 * admin/manage_partylist.php
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// FIX: Standardized paths to match your working Dashboard/Positions files
require_once __DIR__ . "/../config/admin_session.php"; 
require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../system/audit_logger.php";

// FIX: Standardized access check
if (function_exists('checkAccess')) {
    checkAccess('admin');
} else {
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        header("Location: login.php");
        exit();
    }
}

if(!defined('SYSTEM_NAME')) define('SYSTEM_NAME', 'VOTE-X'); 
$admin_name = $_SESSION['admin_name'] ?? 'Administrator';
$message = "";

/* --- ADD PARTYLIST --- */
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_partylist'])) {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);

    $check = $conn->prepare("SELECT id FROM partylist WHERE name = ?");
    $check->bind_param("s", $name);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $message = "Protocol Error: Partylist identity already registered.";
    } else {
        $stmt = $conn->prepare("INSERT INTO partylist (name, description) VALUES (?, ?)");
        $stmt->bind_param("ss", $name, $description);

        if ($stmt->execute()) {
            if(function_exists('logAction')) {
                logAction($_SESSION['user_id'], "admin", "Added Partylist: " . $name);
            }
            $message = "Deployment Successful: Partylist added to registry.";
        }
        $stmt->close();
    }
    $check->close();
}

/* --- DELETE PARTYLIST --- */
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM partylist WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        if(function_exists('logAction')) {
            logAction($_SESSION['user_id'], "admin", "Deleted Partylist ID: " . $id);
        }
        header("Location: manage_partylist.php");
        exit();
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Partylists - <?php echo SYSTEM_NAME; ?></title>
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
        .input-field:focus { outline: none; border-color: #3b82f6; background: rgba(59, 130, 246, 0.05); }
        .nav-glass { background: rgba(15, 23, 42, 0.95); backdrop-filter: blur(12px); border-bottom: 1px solid rgba(255, 255, 255, 0.05); }
    </style>
</head>
<body>

    <nav class="nav-glass sticky top-0 z-50 px-4 md:px-6 py-4">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <div class="flex items-center gap-2">
                <div class="h-8 w-8 bg-blue-600 rounded-lg flex items-center justify-center font-bold text-white shadow-lg shadow-blue-600/30">V</div>
                <span class="text-xl font-extrabold tracking-tight italic"><?php echo SYSTEM_NAME; ?></span>
            </div>
            <div class="hidden md:flex items-center gap-6 text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">
                <a href="dashboard.php" class="hover:text-white transition">Dashboard</a>
                <a href="manage_partylist.php" class="text-blue-400 border-b border-blue-400">Partylists</a>
                <a href="logout.php" class="hover:text-red-400 transition">Exit</a>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto pt-12 px-6 pb-20">
        <header class="mb-12">
            <h1 class="text-4xl font-black tracking-tight">Partylist Alliances</h1>
            <p class="text-slate-400 font-medium">Categorize candidates under official political organizations.</p>
        </header>

        <?php if($message): ?>
            <div class="mb-8 p-4 rounded-2xl bg-blue-500/10 border border-blue-500/20 text-blue-400 text-xs font-bold text-center">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            
            <div class="lg:col-span-4">
                <section class="glass-card rounded-[2rem] p-8 sticky top-28">
                    <h3 class="text-xs font-black uppercase tracking-widest text-blue-500 mb-6 italic">Alliance Entry</h3>
                    <form method="POST" class="space-y-4">
                        <div>
                            <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest ml-1">Partylist Name</label>
                            <input type="text" name="name" required placeholder="Organization Name" class="input-field w-full px-5 py-4 rounded-xl font-semibold mt-1">
                        </div>
                        <div>
                            <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest ml-1">Platform Summary</label>
                            <textarea name="description" placeholder="Mission and vision..." class="input-field w-full px-5 py-4 rounded-xl text-sm h-32 mt-1"></textarea>
                        </div>
                        <button type="submit" name="add_partylist" class="w-full bg-blue-600 hover:bg-blue-500 text-white font-black uppercase tracking-widest py-4 rounded-xl transition-all shadow-lg shadow-blue-600/20 active:scale-95">
                            Authorize Partylist
                        </button>
                    </form>
                </section>
            </div>

            <div class="lg:col-span-8">
                <div class="glass-card rounded-[2.5rem] overflow-hidden">
                    <table class="w-full">
                        <thead class="bg-white/5 text-[10px] font-black uppercase tracking-widest text-slate-500">
                            <tr>
                                <th class="px-8 py-5 text-left">Organization Info</th>
                                <th class="px-8 py-5 text-left">Registration Date</th>
                                <th class="px-8 py-5 text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5">
                            <?php
                            $result = $conn->query("SELECT * FROM partylist ORDER BY created_at DESC");
                            if($result->num_rows > 0):
                                while ($row = $result->fetch_assoc()):
                            ?>
                            <tr class="hover:bg-white/[0.02] transition">
                                <td class="px-8 py-6">
                                    <div class="font-bold text-white text-lg"><?php echo htmlspecialchars($row['name']); ?></div>
                                    <div class="text-xs text-slate-500 mt-1 italic"><?php echo htmlspecialchars($row['description']); ?></div>
                                </td>
                                <td class="px-8 py-6">
                                    <span class="text-xs font-mono text-slate-400"><?php echo date("Y.m.d", strtotime($row['created_at'])); ?></span>
                                </td>
                                <td class="px-8 py-6 text-center">
                                    <a href="?delete=<?= $row['id']; ?>" 
                                       onclick="return confirm('Archive this alliance? Data cannot be recovered.');"
                                       class="inline-block p-3 bg-red-500/10 text-red-400 rounded-xl hover:bg-red-500 hover:text-white transition-all shadow-lg shadow-red-500/10">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; else: ?>
                                <tr>
                                    <td colspan="3" class="px-8 py-20 text-center">
                                        <p class="text-xs font-black uppercase tracking-widest text-slate-600 italic">No Alliances Registered in the System</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

</body>
</html>