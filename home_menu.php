<?php
session_start();
date_default_timezone_set('Asia/Jakarta');
if (!isset($_SESSION['user_id'])) {
    header("Location: home.php");
    exit;
}

$user_role = $_SESSION['role'] ?? '';
$user_name = $_SESSION['name'] ?? 'User';

$display_role = '';
$role_class = '';
$role_icon = '';

if (in_array($user_role, ['admin', 'all'])) {
    $display_role = 'Administrator';
    $role_class = 'amber';
    $role_icon = 'shield-check';
} elseif (in_array($user_role, ['ms_1', 'ms1'])) {
    $display_role = 'MS1 Operator';
    $role_class = 'blue';
    $role_icon = 'building-factory';
} elseif (in_array($user_role, ['ms_2', 'ms2'])) {
    $display_role = 'MS2 Operator';
    $role_class = 'cyan';
    $role_icon = 'building-factory-2';
} else {
    $display_role = 'No Access';
    $role_class = 'red';
    $role_icon = 'lock';
}

$current_time = date('H:i');
$current_date = date('l, d F Y');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PT. Yadin Supermarket — Control Panel</title>
    <link rel="icon" type="image/png" href="assets/yanmar.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['DM Sans', 'sans-serif'],
                        mono: ['DM Mono', 'monospace'],
                    },
                    colors: {
                        brand: {
                            50:  '#fdf8f0',
                            100: '#faefd8',
                            200: '#f3d89a',
                            300: '#ecc05e',
                            400: '#e4a730',
                            500: '#c88a1a',
                            600: '#a06e12',
                            700: '#7a520e',
                            800: '#55390a',
                            900: '#322107',
                        },
                    },
                    animation: {
                        'fade-up': 'fadeUp 0.5s ease forwards',
                        'fade-in': 'fadeIn 0.4s ease forwards',
                    },
                    keyframes: {
                        fadeUp: {
                            '0%': { opacity: '0', transform: 'translateY(16px)' },
                            '100%': { opacity: '1', transform: 'translateY(0)' },
                        },
                        fadeIn: {
                            '0%': { opacity: '0' },
                            '100%': { opacity: '1' },
                        }
                    }
                }
            }
        }
    </script>
    <style>
        body { font-family: 'DM Sans', sans-serif; }

        .card-btn {
            transition: transform 0.18s ease, box-shadow 0.18s ease, background 0.18s ease;
        }
        .card-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 32px rgba(0,0,0,0.10);
        }
        .card-btn:active {
            transform: translateY(-1px);
        }
        .card-btn.disabled {
            pointer-events: none;
            opacity: 0.38;
        }
        .card-btn.disabled:hover {
            transform: none;
            box-shadow: none;
        }

        .stagger-1 { animation-delay: 0.05s; opacity: 0; }
        .stagger-2 { animation-delay: 0.10s; opacity: 0; }
        .stagger-3 { animation-delay: 0.15s; opacity: 0; }
        .stagger-4 { animation-delay: 0.20s; opacity: 0; }
        .stagger-5 { animation-delay: 0.25s; opacity: 0; }
        .stagger-6 { animation-delay: 0.30s; opacity: 0; }
        .stagger-7 { animation-delay: 0.35s; opacity: 0; }

        .grid-pattern {
            background-color: #f8f7f4;
            background-image:
                linear-gradient(rgba(0,0,0,0.04) 1px, transparent 1px),
                linear-gradient(90deg, rgba(0,0,0,0.04) 1px, transparent 1px);
            background-size: 32px 32px;
        }

        .role-amber  { background: #fef3c7; color: #92400e; border-color: #fcd34d; }
        .role-blue   { background: #dbeafe; color: #1e3a8a; border-color: #93c5fd; }
        .role-cyan   { background: #cffafe; color: #164e63; border-color: #67e8f9; }
        .role-red    { background: #fee2e2; color: #7f1d1d; border-color: #fca5a5; }
    </style>
</head>

<body class="grid-pattern min-h-screen flex items-center justify-center p-6">

    <!-- Outer wrapper -->
    <div class="w-full max-w-2xl animate-fade-up">

        <!-- Top brand bar -->
        <div class="flex items-center justify-between mb-6 animate-fade-in stagger-1 animate-fade-up">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-brand-500 flex items-center justify-center shadow-md">
                    <i class="ti ti-settings text-white" style="font-size:18px"></i>
                </div>
                <div>
                    <p class="text-xs font-mono text-gray-400 tracking-widest uppercase">PT. Yanmar Diesel Indonesia</p>
                    <p class="text-sm font-semibold text-gray-700 leading-none">Supermarket</p>
                </div>
            </div>
            <div class="text-right">
                <p class="text-xs text-gray-400 font-mono"><?= $current_time ?></p>
                <p class="text-xs text-gray-400"><?= $current_date ?></p>
            </div>
        </div>

        <!-- Main card -->
        <div class="bg-white rounded-3xl shadow-xl border border-gray-100 overflow-hidden">

            <!-- Card header -->
            <div class="px-8 pt-8 pb-6 border-b border-gray-100">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-xs font-mono text-gray-400 tracking-widest uppercase mb-1">Control Panel</p>
                        <h1 class="text-2xl font-semibold text-gray-900">Selamat datang<?php if ($user_name !== 'User'): ?>, <span class="text-brand-600"><?= htmlspecialchars($user_name) ?></span><?php endif; ?></h1>
                        <p class="text-sm text-gray-400 mt-1">Pilih aksi yang ingin dilakukan dari menu di bawah.</p>
                    </div>
                    <!-- Role badge -->
                    <div class="role-<?= $role_class ?> flex-shrink-0 flex items-center gap-2 border rounded-2xl px-4 py-2 text-sm font-medium">
                        <i class="ti ti-<?= $role_icon ?>" style="font-size:16px"></i>
                        <span><?= $display_role ?></span>
                    </div>
                </div>
            </div>

            <!-- Menu grid -->
            <div class="p-8">
                <p class="text-xs font-mono text-gray-400 tracking-widest uppercase mb-4">Menu utama</p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">

                    <?php
                    $isAdmin = in_array($user_role, ['admin', 'all']);
                    $isMs1   = in_array($user_role, ['ms_1', 'ms1']);
                    $isMs2   = in_array($user_role, ['ms_2', 'ms2']);

                    $menus = [
                        [
                            'label'  => 'Tambah User',
                            'desc'   => 'Data karyawan + akun login sekaligus',
                            'icon'   => 'user-plus',
                            'href'   => 'user_setting.php',
                            'active' => $isAdmin,
                            'stagger'=> 1,
                            'color'  => 'violet',
                        ],
                        [
                            'label'  => 'Monitoring Display',
                            'desc'   => 'Pantau status sistem',
                            'icon'   => 'device-desktop-analytics',
                            'href'   => 'monitor.php',
                            'active' => true,
                            'stagger'=> 2,
                            'color'  => 'teal',
                        ],
                        [
                            'label'  => 'Dashboard MS1',
                            'desc'   => 'Ringkasan data toko 1',
                            'icon'   => 'layout-dashboard',
                            'href'   => 'index.php?lokasi=1',
                            'active' => ($isAdmin || $isMs1),
                            'stagger'=> 3,
                            'color'  => 'blue',
                        ],
                        [
                            'label'  => 'Dashboard MS2',
                            'desc'   => 'Ringkasan data toko 2',
                            'icon'   => 'layout-dashboard',
                            'href'   => 'index.php?lokasi=2',
                            'active' => ($isAdmin || $isMs2),
                            'stagger'=> 4,
                            'color'  => 'cyan',
                        ],
                        [
                            'label'  => $isMs1 ? 'Transaksi MS1' : ($isMs2 ? 'Transaksi MS2' : 'Transaction System'),
                            'desc'   => 'Kelola transaksi kasir',
                            'icon'   => 'receipt',
                            'href'   => $isMs1 ? 'transaction.php?lokasi=1' : ($isMs2 ? 'transaction.php?lokasi=2' : 'transaction.php'),
                            'active' => ($isAdmin || $isMs1 || $isMs2),
                            'stagger'=> 5,
                            'color'  => 'amber',
                        ],
                    ];

                    $iconColors = [
                        'violet' => ['bg' => 'bg-violet-50', 'text' => 'text-violet-600'],
                        'indigo' => ['bg' => 'bg-indigo-50', 'text' => 'text-indigo-600'],
                        'teal'   => ['bg' => 'bg-teal-50',   'text' => 'text-teal-600'],
                        'blue'   => ['bg' => 'bg-blue-50',   'text' => 'text-blue-600'],
                        'cyan'   => ['bg' => 'bg-cyan-50',   'text' => 'text-cyan-600'],
                        'amber'  => ['bg' => 'bg-amber-50',  'text' => 'text-amber-600'],
                    ];

                    foreach ($menus as $m):
                        $ic = $iconColors[$m['color']];
                        $disabledCls = $m['active'] ? '' : 'disabled';
                        $tag = $m['active'] ? 'a' : 'div';
                        $href = $m['active'] ? ' href="' . $m['href'] . '"' : '';
                    ?>
                    <<?= $tag ?><?= $href ?> class="card-btn <?= $disabledCls ?> group flex items-center gap-4 p-4 rounded-2xl border border-gray-100 bg-gray-50 hover:bg-white hover:border-gray-200 cursor-pointer no-underline animate-fade-up stagger-<?= $m['stagger'] ?>">
                        <div class="<?= $ic['bg'] ?> <?= $ic['text'] ?> w-11 h-11 rounded-xl flex items-center justify-center flex-shrink-0 transition-transform duration-200 group-hover:scale-110">
                            <i class="ti ti-<?= $m['icon'] ?>" style="font-size:20px"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-semibold text-gray-800 text-sm leading-tight"><?= $m['label'] ?></p>
                            <p class="text-xs text-gray-400 mt-0.5"><?= $m['desc'] ?></p>
                        </div>
                        <?php if ($m['active']): ?>
                        <i class="ti ti-arrow-right text-gray-300 group-hover:text-gray-500 group-hover:translate-x-1 transition-all duration-200" style="font-size:16px"></i>
                        <?php else: ?>
                        <i class="ti ti-lock text-gray-300" style="font-size:14px"></i>
                        <?php endif; ?>
                    </<?= $tag ?>>
                    <?php endforeach; ?>

                </div>

                <!-- Divider -->
                <div class="border-t border-gray-100 my-5"></div>

                <!-- Logout -->
                <a href="signout.php" class="card-btn animate-fade-up stagger-7 group flex items-center gap-4 p-4 rounded-2xl border border-red-100 bg-red-50 hover:bg-red-600 cursor-pointer no-underline transition-colors duration-200">
                    <div class="bg-red-100 group-hover:bg-red-500 w-11 h-11 rounded-xl flex items-center justify-center flex-shrink-0 transition-colors duration-200 text-red-500 group-hover:text-white">
                        <i class="ti ti-logout" style="font-size:20px"></i>
                    </div>
                    <div class="flex-1">
                        <p class="font-semibold text-red-600 group-hover:text-white text-sm transition-colors duration-200">Logout</p>
                        <p class="text-xs text-red-400 group-hover:text-red-200 mt-0.5 transition-colors duration-200">Keluar dari sesi ini</p>
                    </div>
                    <i class="ti ti-arrow-right text-red-300 group-hover:text-red-200 group-hover:translate-x-1 transition-all duration-200" style="font-size:16px"></i>
                </a>
            </div>

        </div>

        <!-- Footer note -->
        <p class="text-center text-xs text-gray-400 mt-5 font-mono animate-fade-in stagger-7 animate-fade-up">
            copyright: valen   
        </p>

    </div>

</body>
</html>
