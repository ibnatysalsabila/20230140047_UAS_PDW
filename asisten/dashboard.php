<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['nama']) || $_SESSION['role'] !== 'asisten') {
    header('Location: ../login.php');
    exit;
}
$nama_asisten = $_SESSION['nama'];
$page = $_GET['page'] ?? 'beranda'; // Halaman default

function getInitials($name) {
    $words = explode(" ", $name);
    $initials = "";
    foreach ($words as $w) {
        $initials .= mb_substr($w, 0, 1);
    }
    return strtoupper(substr($initials, 0, 2));
}
$initials = getInitials($nama_asisten);

$menus = [
    'beranda' => ['icon' => 'ph-house', 'title' => 'Dashboard'],
    'manajemen_modul' => ['icon' => 'ph-books', 'title' => 'Manajemen Modul'],
    'laporan_masuk' => ['icon' => 'ph-paper-plane-tilt', 'title' => 'Laporan Masuk'],
    'kelola_pengguna' => ['icon' => 'ph-users-three', 'title' => 'Kelola Pengguna'],
    'manajemen_praktikum' => ['icon' => 'ph-flask', 'title' => 'Manajemen Praktikum']
];
$currentPageTitle = $menus[$page]['title'] ?? 'Dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $currentPageTitle ?> - Panel Asisten</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <style>
        body { 
            font-family: 'Inter', sans-serif; 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        .sidebar-link.active { 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white; 
            box-shadow: 0 8px 32px rgba(102, 126, 234, 0.3);
            transform: translateX(4px);
        }
        .sidebar-link.active::before { 
            content: ''; 
            position: absolute; 
            left: 0; 
            top: 0; 
            bottom: 0; 
            width: 4px; 
            background: linear-gradient(to bottom, #60A5FA, #3B82F6);
            border-radius: 0 4px 4px 0;
        }
        .stat-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .stat-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }
        .glass-effect {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        .search-container {
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        .search-container:focus-within {
            background: rgba(255, 255, 255, 0.3);
            box-shadow: 0 0 0 2px rgba(255, 255, 255, 0.5);
        }
        .floating-animation {
            animation: float 6s ease-in-out infinite;
        }
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
        .pulse-animation {
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        .gradient-text {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .sidebar {
            background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%);
            box-shadow: 4px 0 24px rgba(0, 0, 0, 0.1);
        }
        .main-content {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
        }
        .content-area {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
    </style>
</head>
<body>
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <aside class="w-72 sidebar text-white flex flex-col relative overflow-hidden">
            <!-- Decorative elements -->
            <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-br from-blue-400/20 to-purple-400/20 rounded-full blur-xl"></div>
            <div class="absolute bottom-20 left-0 w-24 h-24 bg-gradient-to-br from-purple-400/20 to-pink-400/20 rounded-full blur-xl"></div>
            
            <!-- Header -->
            <div class="h-20 flex items-center px-6 border-b border-gray-700/50 relative z-10">
                <div class="w-12 h-12 bg-gradient-to-br from-blue-400 to-purple-600 rounded-xl flex items-center justify-center floating-animation">
                    <i class="ph-bold ph-student text-2xl text-white"></i>
                </div>
                <div class="ml-4">
                    <h1 class="text-2xl font-bold bg-gradient-to-r from-blue-400 to-purple-400 bg-clip-text text-transparent">SIMPRAKS</h1>
                    <p class="text-xs text-gray-400">Asisten Management</p>
                </div>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 px-4 py-6 space-y-2 relative z-10">
                <?php
                foreach ($menus as $key => $menu) {
                    $isActive = ($page == $key) ? 'active' : '';
                    $hasNotification = ($key == 'laporan_masuk') ? true : false;
                    
                    echo '<a href="dashboard.php?page='.$key.'" class="sidebar-link '.$isActive.' relative flex items-center px-4 py-4 rounded-xl transition-all duration-300 group">';
                    echo '<div class="w-10 h-10 bg-white/10 rounded-lg flex items-center justify-center group-hover:bg-white/20 transition-all duration-300">';
                    echo '<i class="ph-bold '.$menu['icon'].' text-xl"></i>';
                    echo '</div>';
                    echo '<span class="ml-4 font-medium">'.$menu['title'].'</span>';
                    
                    if ($hasNotification && $key != $page) {
                        echo '<span class="ml-auto bg-red-500 text-white text-xs px-2 py-1 rounded-full">2</span>';
                    }
                    
                    echo '</a>';
                }
                ?>
            </nav>

            <!-- User Profile & Logout -->
            <div class="p-4 border-t border-gray-700/50 relative z-10">
                <div class="flex items-center space-x-3 mb-4 p-3 rounded-xl bg-white/5">
                    <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-purple-600 text-white flex items-center justify-center rounded-xl font-bold text-lg">
                        <?= htmlspecialchars($initials) ?>
                    </div>
                    <div class="flex-1">
                        <p class="font-semibold text-sm text-white"><?= htmlspecialchars($nama_asisten) ?></p>
                        <p class="text-xs text-gray-400">Asisten</p>
                    </div>
                    <div class="w-2 h-2 bg-green-400 rounded-full pulse-animation"></div>
                </div>
                <a href="../logout.php" class="flex items-center justify-center w-full px-4 py-3 bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white font-semibold rounded-xl transition-all duration-300 hover:shadow-lg">
                    <i class="ph-bold ph-sign-out mr-2"></i> Logout
                </a>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col main-content">
            <!-- Header -->
            <header class="h-20 glass-effect border-b border-white/10 flex items-center justify-between px-8">
                <div>
                    <h1 class="text-3xl font-bold text-white"><?= $currentPageTitle ?></h1>
                    <p class="text-white/70 text-sm">
                        <?php
                        if ($page == 'beranda') {
                            echo "Selamat datang , " . htmlspecialchars($nama_asisten) . "! ke halaman dashboard asisten.";
                        } else {
                            echo "kelola " . strtolower($currentPageTitle) . " secara efisien dan mudah.";
                        }
                        ?>
                    </p>
                </div>
                <div class="flex items-center space-x-6">
                    <div class="relative search-container rounded-full">
                        <i class="ph ph-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-white/70 text-xl"></i>
                        <input type="text" placeholder="Search anything..." class="bg-transparent text-white placeholder-white/50 rounded-full pl-12 pr-4 py-3 w-80 focus:outline-none">
                    </div>
                    <div class="flex items-center space-x-2">
                        <button class="relative p-2 rounded-full bg-white/10 hover:bg-white/20 transition-all duration-300">
                            <i class="ph-bold ph-bell text-white text-xl"></i>
                            <span class="absolute -top-1 -right-1 w-5 h-5 bg-red-500 text-white text-xs rounded-full flex items-center justify-center">3</span>
                        </button>
                        <button class="p-2 rounded-full bg-white/10 hover:bg-white/20 transition-all duration-300">
                            <i class="ph-bold ph-gear text-white text-xl"></i>
                        </button>
                    </div>
                </div>
            </header>

            <!-- Dashboard Content -->
            <main class="flex-1 p-8 overflow-y-auto">
                <?php
                if ($page == 'beranda') {
                    // Dashboard content
                    ?>


                    <!-- Stats Cards -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                        <!-- Total Modul -->
                        <div class="stat-card p-6 rounded-2xl group">
                            <div class="flex items-center justify-between mb-4">
                                <div class="w-14 h-14 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                                    <i class="ph-bold ph-books text-2xl text-white"></i>
                                </div>
                                <div class="text-right">
                                    <p class="text-3xl font-bold gradient-text">8</p>
                                    <p class="text-sm text-gray-600">Total Modul</p>
                                </div>
                            </div>
                            <div class="flex items-center text-green-600 text-sm">
                            </div>
                        </div>

                        <!-- Laporan Masuk -->
                        <div class="stat-card p-6 rounded-2xl group">
                            <div class="flex items-center justify-between mb-4">
                                <div class="w-14 h-14 bg-gradient-to-br from-green-500 to-green-600 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                                    <i class="ph-bold ph-paper-plane-tilt text-2xl text-white"></i>
                                </div>
                                <div class="text-right">
                                    <p class="text-3xl font-bold gradient-text">2</p>
                                    <p class="text-sm text-gray-600">Laporan Masuk</p>
                                </div>
                            </div>
                            <div class="flex items-center text-blue-600 text-sm">
                            </div>
                        </div>

                        <!-- Belum Dinilai -->
                        <div class="stat-card p-6 rounded-2xl group">
                            <div class="flex items-center justify-between mb-4">
                                <div class="w-14 h-14 bg-gradient-to-br from-yellow-500 to-yellow-600 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                                    <i class="ph-bold ph-clock text-2xl text-white"></i>
                                </div>
                                <div class="text-right">
                                    <p class="text-3xl font-bold gradient-text">0</p>
                                    <p class="text-sm text-gray-600">Belum Dinilai</p>
                                </div>
                            </div>
                            <div class="flex items-center text-green-600 text-sm">
                                
                            </div>
                        </div>

                        <!-- Total Pengguna -->
                        <div class="stat-card p-6 rounded-2xl group">
                            <div class="flex items-center justify-between mb-4">
                                <div class="w-14 h-14 bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                                    <i class="ph-bold ph-users-three text-2xl text-white"></i>
                                </div>
                                <div class="text-right">
                                    <p class="text-3xl font-bold gradient-text">5</p>
                                    <p class="text-sm text-gray-600">Total Pengguna</p>
                                </div>
                            </div>
                            <div class="flex items-center text-green-600 text-sm">
                                
                            </div>
                        </div>
                    </div>

                  
                        <!-- Recent Activities -->
                        <div class="lg:col-span-2">
                            <div class="stat-card p-6 rounded-2xl">
                                <div class="flex items-center justify-between mb-6">
                                    <h3 class="text-xl font-bold text-gray-800">Aktivitas Terbaru</h3>
                                </div>
                                <div class="space-y-4">
                                    <div class="flex items-center space-x-4 p-4 bg-gray-50 rounded-xl">
                                        <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                                            <i class="ph-bold ph-file-text text-blue-600"></i>
                                        </div>
                                        <div class="flex-1">
                                            <p class="font-medium text-gray-800">Satu laporan praktikum baru telah diserahkan.</p>
                                            <p class="text-sm text-gray-600">2 jam yang lalu</p>
                                        </div>
                                    </div>
                                    <div class="flex items-center space-x-4 p-4 bg-gray-50 rounded-xl">
                                        <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                                            <i class="ph-bold ph-user-plus text-green-600"></i>
                                        </div>
                                        <div class="flex-1">
                                            <p class="font-medium text-gray-800">Seorang pengguna baru berhasil terdaftar di sistem</p>
                                            <p class="text-sm text-gray-600">5 jam yang lalu</p>
                                        </div>
                                    </div>
                                    <div class="flex items-center space-x-4 p-4 bg-gray-50 rounded-xl">
                                        <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center">
                                            <i class="ph-bold ph-books text-purple-600"></i>
                                        </div>
                                        <div class="flex-1">
                                            <p class="font-medium text-gray-800">Terdapat pembaruan pada salah satu modul pembelajaran.</p>
                                            <p class="text-sm text-gray-600">1 hari yang lalu</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    <?php
                } else {
                    // Other pages content
                    ?>
                    <div class="content-area p-8 rounded-2xl">
                        <?php
                        $allowed_pages = [
                            'beranda' => 'parts/beranda.php',
                            'manajemen_modul' => 'kelola_modul.php',
                            'laporan_masuk' => 'laporan_masuk.php',
                            'kelola_pengguna' => 'kelola_pengguna.php',
                            'manajemen_praktikum' => 'kelola_praktikum.php'
                        ];
                        
                        if (array_key_exists($page, $allowed_pages) && file_exists($allowed_pages[$page])) {
                            include $allowed_pages[$page];
                        } else {
                            echo '<div class="text-center py-16">';
                            echo '<div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">';
                            echo '<i class="ph-bold ph-file-x text-4xl text-gray-400"></i>';
                            echo '</div>';
                            echo '<h3 class="text-2xl font-bold text-gray-800 mb-2">Halaman Tidak Ditemukan</h3>';
                            echo '<p class="text-gray-600 mb-6">File konten untuk halaman ini tidak dapat ditemukan atau hilang.</p>';
                            echo '<a href="dashboard.php?page=beranda" class="inline-flex items-center space-x-2 bg-gradient-to-r from-blue-500 to-blue-600 text-white py-3 px-6 rounded-xl hover:from-blue-600 hover:to-blue-700 transition-all duration-300">';
                            echo '<i class="ph-bold ph-arrow-left"></i>';
                            echo '<span>Kembali ke Dashboard</span>';
                            echo '</a>';
                            echo '</div>';
                        }
                        ?>
                    </div>
                    <?php
                }
                ?>
            </main>
        </div>
    </div>

    <script>
        // Add some interactive features
        document.addEventListener('DOMContentLoaded', function() {
            // Smooth scroll for quick actions
            const quickActionLinks = document.querySelectorAll('a[href^="dashboard.php"]');
            quickActionLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    // Add loading state
                    this.style.opacity = '0.7';
                    setTimeout(() => {
                        this.style.opacity = '1';
                    }, 200);
                });
            });

            // Search functionality
            const searchInput = document.querySelector('input[placeholder="Search anything..."]');
            if (searchInput) {
                searchInput.addEventListener('input', function(e) {
                    // Add search logic here
                    console.log('Searching for:', e.target.value);
                });
            }
        });
    </script>
</body>
</html>