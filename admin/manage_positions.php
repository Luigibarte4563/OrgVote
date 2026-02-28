<?php
/**
 * admin/manage_positions.php
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. FIX: Using the correct path and session check
require_once __DIR__ . "/../config/admin_session.php";
require_once __DIR__ . "/../config/database.php";

// 2. FIX: Standardizing access check (matching your dashboard logic)
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

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_position'])) {
        $name = trim($_POST['position_name']);
        $desc = trim($_POST['description']);
        if ($name !== '') {
            $stmt = $conn->prepare("INSERT INTO positions (position_name, description) VALUES (?, ?)");
            $stmt->bind_param("ss", $name, $desc);
            $stmt->execute();
            $stmt->close();
        }
    }

    if (isset($_POST['edit_position'])) {
        $id = intval($_POST['position_id']);
        $name = trim($_POST['position_name']);
        $desc = trim($_POST['description']);
        if ($id && $name !== '') {
            $stmt = $conn->prepare("UPDATE positions SET position_name = ?, description = ? WHERE id = ?");
            $stmt->bind_param("ssi", $name, $desc, $id);
            $stmt->execute();
            $stmt->close();
        }
    }

    if (isset($_POST['delete_position'])) {
        $id = intval($_POST['position_id']);
        if ($id) {
            $stmt = $conn->prepare("DELETE FROM positions WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();
        }
    }

    // 3. FIX: Redirect to the CORRECT filename (ensure this matches your actual file)
    header("Location: manage_positions.php");
    exit();
}

$result = $conn->query("SELECT * FROM positions ORDER BY id DESC");
$positions = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Positions - <?php echo SYSTEM_NAME; ?></title>
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
                <div class="h-8 w-8 bg-blue-600 rounded-lg flex items-center justify-center font-bold text-white shadow-lg">V</div>
                <span class="text-xl font-extrabold tracking-tight italic"><?php echo SYSTEM_NAME; ?></span>
            </div>
            <div class="hidden md:flex items-center gap-6 text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">
                <a href="dashboard.php" class="hover:text-white transition">Dashboard</a>
                <a href="manage_positions.php" class="text-blue-400">Positions</a>
                <a href="logout.php" class="hover:text-red-400 transition">Exit</a>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto pt-12 px-6 pb-20">
        <header class="mb-12">
            <h1 class="text-4xl font-black tracking-tight">Election Tiers</h1>
            <p class="text-slate-400 font-medium">Define official positions and leadership roles.</p>
        </header>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            
            <div class="lg:col-span-4">
                <section class="glass-card rounded-[2rem] p-8 sticky top-28">
                    <h3 class="text-xs font-black uppercase tracking-widest text-blue-500 mb-6 italic">Initialize Role</h3>
                    <form method="POST" class="space-y-4">
                        <div>
                            <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest ml-1">Position Title</label>
                            <input type="text" name="position_name" required placeholder="e.g. President" class="input-field w-full px-5 py-4 rounded-xl font-semibold mt-1">
                        </div>
                        <div>
                            <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest ml-1">Responsibilities</label>
                            <textarea name="description" placeholder="Brief description..." class="input-field w-full px-5 py-4 rounded-xl text-sm h-24 mt-1"></textarea>
                        </div>
                        <button type="submit" name="add_position" class="w-full bg-blue-600 hover:bg-blue-500 text-white font-black uppercase tracking-widest py-4 rounded-xl transition-all shadow-lg shadow-blue-600/20">
                            Create Position
                        </button>
                    </form>
                </section>
            </div>

            <div class="lg:col-span-8">
                <div class="glass-card rounded-[2rem] overflow-hidden">
                    <table class="w-full">
                        <thead class="bg-white/5 text-[10px] font-black uppercase tracking-widest text-slate-500">
                            <tr>
                                <th class="px-6 py-4 text-left">Role Detail</th>
                                <th class="px-6 py-4 text-left">Management Terminal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5">
                            <?php foreach ($positions as $pos): ?>
                            <tr class="hover:bg-white/[0.02] transition">
                                <td class="px-6 py-6">
                                    <div class="font-bold text-white"><?php echo htmlspecialchars($pos['position_name']); ?></div>
                                    <div class="text-xs text-slate-500 mt-1 max-w-xs truncate"><?php echo htmlspecialchars($pos['description']); ?></div>
                                </td>
                                <td class="px-6 py-6">
                                    <div class="flex items-center gap-2">
                                        <form method="POST" class="flex gap-2 bg-black/20 p-2 rounded-xl border border-white/5">
                                            <input type="hidden" name="position_id" value="<?php echo $pos['id']; ?>">
                                            <input type="text" name="position_name" value="<?php echo htmlspecialchars($pos['position_name']); ?>" class="bg-transparent border-none text-xs font-bold text-blue-400 focus:ring-0 w-24" required>
                                            <button type="submit" name="edit_position" class="text-[10px] font-black text-emerald-500 uppercase hover:text-emerald-400">Save</button>
                                        </form>

                                        <form method="POST" onsubmit="return confirm('Archive this position?');">
                                            <input type="hidden" name="position_id" value="<?php echo $pos['id']; ?>">
                                            <button type="submit" name="delete_position" class="p-2 text-slate-500 hover:text-red-400 transition">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</body>
</html>