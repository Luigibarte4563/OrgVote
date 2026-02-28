<?php
/**
 * admin/manage_students.php
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

// Handle Student Registration
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['student_id'])) {
    $student_id = trim($_POST['student_id']);
    
    // Check for duplicates before inserting
    $check = $conn->prepare("SELECT id FROM students WHERE student_id = ?");
    $check->bind_param("s", $student_id);
    $check->execute();
    $check->store_result();
    
    if($check->num_rows == 0) {
        $stmt = $conn->prepare("INSERT INTO students (student_id) VALUES (?)");
        $stmt->bind_param("s", $student_id);
        $stmt->execute();
        header("Location: manage_students.php?status=success");
    } else {
        header("Location: manage_students.php?status=exists");
    }
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Voters - <?php echo SYSTEM_NAME; ?></title>
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
                <a href="manage_students.php" class="text-blue-400 border-b border-blue-400">Voters</a>
                <a href="logout.php" class="hover:text-red-400 transition">Exit</a>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto pt-12 px-6 pb-20">
        <header class="flex flex-col md:flex-row justify-between items-end gap-6 mb-12">
            <div>
                <h1 class="text-4xl font-black tracking-tight">Voter Registry</h1>
                <p class="text-slate-400 font-medium">Whitelisting authorized student identification for secure access.</p>
            </div>
            <div class="glass-card px-6 py-3 rounded-2xl border-l-4 border-blue-500">
                <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Total Authorized</span>
                <h3 class="text-2xl font-black text-white">
                    <?php echo $conn->query("SELECT id FROM students")->num_rows; ?>
                </h3>
            </div>
        </header>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            
            <div class="lg:col-span-4">
                <section class="glass-card rounded-[2rem] p-8 sticky top-28 overflow-hidden">
                    <div class="absolute -top-10 -right-10 w-32 h-32 bg-blue-600/10 rounded-full blur-2xl"></div>
                    
                    <h3 class="text-xs font-black uppercase tracking-widest text-blue-500 mb-6 flex items-center gap-2 relative">
                        Register Student
                    </h3>
                    
                    <form method="POST" class="space-y-6 relative">
                        <div class="space-y-2">
                            <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest ml-1">Student ID / Serial</label>
                            <input type="text" name="student_id" required 
                                   placeholder="e.g. 2024-10001" 
                                   class="input-field w-full px-5 py-4 rounded-xl font-mono text-lg tracking-wider">
                        </div>
                        
                        <button type="submit" 
                                class="w-full bg-blue-600 hover:bg-blue-500 text-white font-black uppercase tracking-widest py-4 rounded-xl transition-all shadow-xl shadow-blue-600/20 active:scale-95">
                            Authorize Access
                        </button>
                    </form>

                    <?php if(isset($_GET['status'])): ?>
                        <div class="mt-6 text-center">
                            <?php if($_GET['status'] == 'success'): ?>
                                <p class="text-[10px] font-bold text-emerald-500 uppercase tracking-widest">Entry added to blockchain</p>
                            <?php elseif($_GET['status'] == 'exists'): ?>
                                <p class="text-[10px] font-bold text-red-400 uppercase tracking-widest">ID already in registry</p>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </section>
            </div>

            <div class="lg:col-span-8">
                <div class="glass-card rounded-[2.5rem] overflow-hidden">
                    <div class="px-8 py-6 border-b border-white/5 bg-white/[0.01] flex items-center justify-between">
                        <h3 class="text-xs font-black uppercase tracking-widest text-slate-500">Authorized Access List</h3>
                        <div class="relative">
                            <input type="text" placeholder="Search ID..." class="bg-white/5 border border-white/10 rounded-lg px-4 py-1.5 text-xs focus:outline-none focus:border-blue-500/50 w-48">
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-white/5 text-[10px] font-black uppercase tracking-widest text-slate-600">
                                <tr>
                                    <th class="px-8 py-4 text-left">Internal Index</th>
                                    <th class="px-8 py-4 text-left">Student Identification</th>
                                    <th class="px-8 py-4 text-right">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-white/5">
                                <?php
                                $result = $conn->query("SELECT * FROM students ORDER BY id DESC");
                                if($result->num_rows > 0):
                                    while ($row = $result->fetch_assoc()):
                                ?>
                                <tr class="hover:bg-white/[0.02] transition group">
                                    <td class="px-8 py-5">
                                        <span class="text-xs font-mono text-slate-600 italic">#<?php echo sprintf("%05d", $row['id']); ?></span>
                                    </td>
                                    <td class="px-8 py-5">
                                        <div class="flex items-center gap-3">
                                            <div class="h-2 w-2 bg-emerald-500 rounded-full group-hover:animate-pulse"></div>
                                            <span class="font-bold text-white tracking-widest font-mono"><?php echo htmlspecialchars($row['student_id']); ?></span>
                                        </div>
                                    </td>
                                    <td class="px-8 py-5 text-right">
                                        <span class="text-[9px] font-black uppercase px-3 py-1 bg-emerald-500/10 text-emerald-500 rounded-full border border-emerald-500/20">
                                            Authorized
                                        </span>
                                    </td>
                                </tr>
                                <?php endwhile; else: ?>
                                    <tr>
                                        <td colspan="3" class="px-8 py-20 text-center">
                                            <p class="text-xs font-black uppercase tracking-widest text-slate-700 italic">No registered voters found</p>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </main>

</body>
</html>