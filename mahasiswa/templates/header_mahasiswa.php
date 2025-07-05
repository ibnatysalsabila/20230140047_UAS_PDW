<?php
// Pastikan session sudah dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cek jika pengguna belum login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Mahasiswa - <?php echo $pageTitle ?? 'Dashboard'; ?></title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Phosphor Icons -->
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    
    <!-- Custom Styles -->
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        
        * {
            font-family: 'Inter', sans-serif;
        }
        
        .navbar-gradient {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .glass-effect {
            backdrop-filter: blur(16px);
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .nav-link {
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .nav-link::before {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 0;
            height: 2px;
            background: linear-gradient(90deg, #fff, rgba(255,255,255,0.8));
            transition: width 0.3s ease;
        }
        
        .nav-link:hover::before,
        .nav-link.active::before {
            width: 100%;
        }
        
        .mobile-menu {
            transform: translateX(-100%);
            transition: transform 0.3s ease;
        }
        
        .mobile-menu.active {
            transform: translateX(0);
        }
        
        .notification-badge {
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }
        
        .profile-dropdown {
            transform: translateY(-10px);
            opacity: 0;
            transition: all 0.3s ease;
            pointer-events: none;
        }
        
        .profile-dropdown.active {
            transform: translateY(0);
            opacity: 1;
            pointer-events: auto;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-gray-50 via-white to-blue-50 min-h-screen">

    <!-- Navigation -->
    <nav class="navbar-gradient shadow-2xl relative overflow-hidden">
        <!-- Decorative Elements -->
        <div class="absolute inset-0 overflow-hidden pointer-events-none">
            <div class="absolute -top-4 -right-4 w-32 h-32 bg-white/10 rounded-full"></div>
            <div class="absolute -bottom-4 -left-4 w-24 h-24 bg-white/10 rounded-full"></div>
            <div class="absolute top-1/2 left-1/3 w-16 h-16 bg-white/5 rounded-full"></div>
        </div>
        
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="flex items-center justify-between h-20">
                
                <!-- Logo & Brand -->
                <div class="flex items-center space-x-4">
                    <div class="flex-shrink-0">
                        <div class="flex items-center space-x-3">
                            <div class="bg-white/20 backdrop-blur-sm p-2 rounded-xl">
                                <i class="ph-bold ph-graduation-cap text-white text-2xl"></i>
                            </div>
                            <div>
                                <span class="text-white text-2xl font-bold tracking-wide">SIMPRAK</span>
                                <p class="text-white/80 text-xs font-medium">Sistem Praktikum</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Desktop Navigation -->
                <div class="hidden md:block">
                    <div class="flex items-center space-x-1">
                        <?php 
                            $activeClass = 'active bg-white/20 text-white';
                            $inactiveClass = 'text-white/90 hover:text-white hover:bg-white/10';
                        ?>
                        <a href="dashboard.php" class="nav-link <?php echo ($activePage == 'dashboard') ? $activeClass : $inactiveClass; ?> px-4 py-2 rounded-xl text-sm font-medium flex items-center space-x-2">
                            <i class="ph-bold ph-house"></i>
                            <span>Dashboard</span>
                        </a>
                        <a href="praktikum_saya.php" class="nav-link <?php echo ($activePage == 'my_courses') ? $activeClass : $inactiveClass; ?> px-4 py-2 rounded-xl text-sm font-medium flex items-center space-x-2">
                            <i class="ph-bold ph-books"></i>
                            <span>Praktikum Saya</span>
                        </a>
                        <a href="katalog_praktikum.php" class="nav-link <?php echo ($activePage == 'courses') ? $activeClass : $inactiveClass; ?> px-4 py-2 rounded-xl text-sm font-medium flex items-center space-x-2">
                            <i class="ph-bold ph-magnifying-glass"></i>
                            <span>Cari Praktikum</span>
                        </a>
                        <a href="../logout.php" class="bg-red-500 hover:bg-red-600 text-white font-medium py-2 px-4 rounded-xl transition-all duration-300 flex items-center space-x-2 shadow-lg hover:shadow-xl">
                            <i class="ph-bold ph-sign-out"></i>
                            <span>Logout</span>
                        </a>
                    </div>
                </div>

                <!-- User Profile & Actions -->
                <div class="hidden md:flex items-center space-x-4">
                    <!-- Notifications -->
                    <div class="relative">
                        <button class="text-white/90 hover:text-white p-2 rounded-xl hover:bg-white/10 transition-colors">
                            <i class="ph-bold ph-bell text-xl"></i>
                            <span class="notification-badge absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">3</span>
                        </button>
                    </div>

                <!-- Mobile Menu Button -->
                <div class="md:hidden">
                    <button onclick="toggleMobileMenu()" class="text-white/90 hover:text-white p-2 rounded-xl hover:bg-white/10 transition-colors">
                        <i class="ph-bold ph-list text-2xl"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Menu -->
        <div id="mobileMenu" class="mobile-menu md:hidden absolute top-full left-0 w-full bg-white/95 backdrop-blur-md border-t border-white/20 z-40">
            <div class="px-4 py-6 space-y-3">
                <a href="dashboard.php" class="flex items-center space-x-3 p-3 rounded-xl <?php echo ($activePage == 'dashboard') ? 'bg-blue-100 text-blue-700' : 'text-gray-700 hover:bg-gray-100'; ?> transition-colors">
                    <i class="ph-bold ph-house text-xl"></i>
                    <span class="font-medium">Dashboard</span>
                </a>
                <a href="praktikum_saya.php" class="flex items-center space-x-3 p-3 rounded-xl <?php echo ($activePage == 'my_courses') ? 'bg-blue-100 text-blue-700' : 'text-gray-700 hover:bg-gray-100'; ?> transition-colors">
                    <i class="ph-bold ph-books text-xl"></i>
                    <span class="font-medium">Praktikum Saya</span>
                </a>
                <a href="katalog_praktikum.php" class="flex items-center space-x-3 p-3 rounded-xl <?php echo ($activePage == 'courses') ? 'bg-blue-100 text-blue-700' : 'text-gray-700 hover:bg-gray-100'; ?> transition-colors">
                    <i class="ph-bold ph-magnifying-glass text-xl"></i>
                    <span class="font-medium">Cari Praktikum</span>
                </a>
                <hr class="my-4">
                <a href="profile.php" class="flex items-center space-x-3 p-3 rounded-xl text-gray-700 hover:bg-gray-100 transition-colors">
                    <i class="ph-bold ph-user-circle text-xl"></i>
                    <span class="font-medium">Profil Saya</span>
                </a>
                <a href="../logout.php" class="flex items-center space-x-3 p-3 rounded-xl text-red-600 hover:bg-red-50 transition-colors">
                    <i class="ph-bold ph-sign-out text-xl"></i>
                    <span class="font-medium">Logout</span>
                </a>
            </div>
        </div>
    </nav>

    <!-- Main Content Container -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <!-- JavaScript -->
    <script>
        function toggleMobileMenu() {
            const menu = document.getElementById('mobileMenu');
            menu.classList.toggle('active');
        }
        
        function toggleProfileDropdown() {
            const dropdown = document.getElementById('profileDropdown');
            dropdown.classList.toggle('active');
        }
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(event) {
            const dropdown = document.getElementById('profileDropdown');
            const profileButton = event.target.closest('[onclick="toggleProfileDropdown()"]');
            
            if (!profileButton && !dropdown.contains(event.target)) {
                dropdown.classList.remove('active');
            }
        });
        
        // Close mobile menu when clicking outside
        document.addEventListener('click', function(event) {
            const menu = document.getElementById('mobileMenu');
            const menuButton = event.target.closest('[onclick="toggleMobileMenu()"]');
            
            if (!menuButton && !menu.contains(event.target)) {
                menu.classList.remove('active');
            }
        });
    </script>