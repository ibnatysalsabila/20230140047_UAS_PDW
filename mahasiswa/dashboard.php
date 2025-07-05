<?php
session_start();
include 'templates/header_mahasiswa.php';

// --- KONEKSI DATABASE ---
$host = 'localhost'; $dbname = 'db_simpraks'; $user = 'root'; $pass = '';
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) { die("Koneksi gagal: " . $e->getMessage()); }

// --- PERBAIKAN DI SINI ---
// Pastikan variabel tersedia untuk digunakan di konten halaman ini
$id_mahasiswa = $_SESSION['user_id'];
$nama_mahasiswa = $_SESSION['nama']; 

// Hitung Praktikum Diikuti
$stmt_prak_diikuti = $pdo->prepare("SELECT COUNT(DISTINCT id_praktikum) FROM pendaftaran_praktikum WHERE id_mahasiswa = ?");
$stmt_prak_diikuti->execute([$id_mahasiswa]);
$praktikum_diikuti = $stmt_prak_diikuti->fetchColumn();

// Hitung Tugas Selesai (Sudah Mengumpulkan)
$stmt_selesai = $pdo->prepare("SELECT COUNT(*) FROM pengumpulan_laporan WHERE id_mahasiswa = ?");
$stmt_selesai->execute([$id_mahasiswa]);
$tugas_selesai = $stmt_selesai->fetchColumn();

// Hitung Tugas Menunggu (Total Modul - Tugas Selesai)
$stmt_total_modul = $pdo->prepare("SELECT COUNT(m.id) FROM modul m JOIN pendaftaran_praktikum pp ON m.id_praktikum = pp.id_praktikum WHERE pp.id_mahasiswa = ?");
$stmt_total_modul->execute([$id_mahasiswa]);
$total_modul = $stmt_total_modul->fetchColumn();
$tugas_menunggu = max(0, $total_modul - $tugas_selesai); // Gunakan max(0, ...) agar tidak minus

// Hitung progress percentage
$progress_percentage = $total_modul > 0 ? round(($tugas_selesai / $total_modul) * 100) : 0;

// Get current time for greeting
$current_hour = date('H');
$greeting = '';
if ($current_hour < 12) {
    $greeting = 'Selamat Pagi';
} elseif ($current_hour < 17) {
    $greeting = 'Selamat Siang';
} else {
    $greeting = 'Selamat Malam';
}
?>

<style>
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes pulse {
        0%, 100% {
            transform: scale(1);
        }
        50% {
            transform: scale(1.05);
        }
    }

    .animate-fadeInUp {
        animation: fadeInUp 0.8s ease-out;
    }

    .animate-pulse-custom {
        animation: pulse 2s infinite;
    }

    .card-hover {
        transition: all 0.3s ease;
    }

    .card-hover:hover {
        transform: translateY(-5px);
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    }

    .progress-bar {
        background: linear-gradient(90deg, #3B82F6 0%, #1D4ED8 100%);
        border-radius: 10px;
        height: 8px;
        transition: width 1s ease-in-out;
    }

    .notification-item {
        transition: all 0.3s ease;
    }

    .notification-item:hover {
        background-color: #F8FAFC;
        transform: translateX(5px);
    }

    .gradient-text {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }
</style>

<div class="min-h-screen bg-gray-50">
    <!-- Welcome Section -->
    <div class="bg-gradient-to-r from-blue-600 via-blue-700 to-purple-800 p-8 rounded-2xl shadow-2xl mb-8 text-white animate-fadeInUp relative overflow-hidden">
        <div class="absolute inset-0 bg-black opacity-10"></div>
        <div class="absolute top-0 right-0 w-64 h-64 bg-white opacity-5 rounded-full -translate-y-32 translate-x-32"></div>
        <div class="absolute bottom-0 left-0 w-48 h-48 bg-white opacity-5 rounded-full translate-y-24 -translate-x-24"></div>
        
        <div class="relative z-10">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h1 class="text-4xl font-bold mb-2">
                        <?= $greeting ?>, <?= htmlspecialchars(explode(' ', $nama_mahasiswa)[0]) ?>! 🎓
                    </h1>
                    <p class="text-blue-100 text-lg">
                        Terus semangat dalam menyelesaikan semua modul praktikummu.
                    </p>
                </div>
                <div class="text-right">
                    <div class="bg-white bg-opacity-20 backdrop-blur-sm rounded-lg p-4">
                        <p class="text-sm text-blue-100">Progress Keseluruhan</p>
                        <p class="text-3xl font-bold"><?= $progress_percentage ?>%</p>
                    </div>
                </div>
            </div>
            
            <!-- Progress Bar -->
            <div class="bg-white bg-opacity-20 rounded-full p-1 mb-4">
                <div class="progress-bar" style="width: <?= $progress_percentage ?>%"></div>
            </div>
            
            <p class="text-sm text-blue-100">
                <?= $tugas_selesai ?> dari <?= $total_modul ?> tugas telah diselesaikan
            </p>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <!-- Praktikum Diikuti -->
        <div class="bg-white p-6 rounded-2xl shadow-lg border-0 card-hover animate-fadeInUp relative overflow-hidden">
            <div class="absolute top-0 right-0 w-20 h-20 bg-blue-100 rounded-full -translate-y-10 translate-x-10 opacity-50"></div>
            <div class="flex items-center justify-between relative z-10">
                <div>
                    <div class="flex items-center space-x-4">
                        <div class="p-4 rounded-2xl bg-gradient-to-r from-blue-500 to-blue-600 text-white shadow-lg">
                            <i class="ph-bold ph-flask text-3xl"></i>
                        </div>
                        <div>
                            <p class="text-4xl font-bold text-gray-800 mb-1"><?= $praktikum_diikuti ?></p>
                            <p class="text-gray-500 text-sm font-medium">Praktikum Diikuti</p>
                        </div>
                    </div>
                </div>
                <div class="text-blue-600">
                    <i class="ph-bold ph-trend-up text-2xl"></i>
                </div>
            </div>
        </div>

        <!-- Tugas Selesai -->
        <div class="bg-white p-6 rounded-2xl shadow-lg border-0 card-hover animate-fadeInUp relative overflow-hidden" style="animation-delay: 0.1s;">
            <div class="absolute top-0 right-0 w-20 h-20 bg-green-100 rounded-full -translate-y-10 translate-x-10 opacity-50"></div>
            <div class="flex items-center justify-between relative z-10">
                <div>
                    <div class="flex items-center space-x-4">
                        <div class="p-4 rounded-2xl bg-gradient-to-r from-green-500 to-green-600 text-white shadow-lg">
                            <i class="ph-bold ph-check-circle text-3xl"></i>
                        </div>
                        <div>
                            <p class="text-4xl font-bold text-gray-800 mb-1"><?= $tugas_selesai ?></p>
                            <p class="text-gray-500 text-sm font-medium">Tugas Selesai</p>
                        </div>
                    </div>
                </div>
                <div class="text-green-600">
                    <i class="ph-bold ph-crown text-2xl"></i>
                </div>
            </div>
        </div>

        <!-- Tugas Menunggu -->
        <div class="bg-white p-6 rounded-2xl shadow-lg border-0 card-hover animate-fadeInUp relative overflow-hidden" style="animation-delay: 0.2s;">
            <div class="absolute top-0 right-0 w-20 h-20 bg-yellow-100 rounded-full -translate-y-10 translate-x-10 opacity-50"></div>
            <div class="flex items-center justify-between relative z-10">
                <div>
                    <div class="flex items-center space-x-4">
                        <div class="p-4 rounded-2xl bg-gradient-to-r from-yellow-500 to-yellow-600 text-white shadow-lg <?= $tugas_menunggu > 0 ? 'animate-pulse-custom' : '' ?>">
                            <i class="ph-bold ph-clock text-3xl"></i>
                        </div>
                        <div>
                            <p class="text-4xl font-bold text-gray-800 mb-1"><?= $tugas_menunggu ?></p>
                            <p class="text-gray-500 text-sm font-medium">Tugas Menunggu</p>
                        </div>
                    </div>
                </div>
                <div class="text-yellow-600">
                    <i class="ph-bold ph-lightning text-2xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Notifications -->
    <div class="bg-white p-6 rounded-2xl shadow-lg border-0 animate-fadeInUp" style="animation-delay: 0.4s;">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-xl font-bold text-gray-800 flex items-center">
                <i class="ph-bold ph-bell text-blue-600 mr-2"></i>
                Notifikasi Terbaru
            </h3>
            <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm font-medium">
                3 Baru
            </span>
        </div>
        
        <div class="space-y-4">
            <div class="notification-item p-4 rounded-xl border-l-4 border-yellow-400 bg-yellow-50">
                <div class="flex items-start space-x-3">
                    <div class="p-2 bg-yellow-100 text-yellow-600 rounded-full">
                        <i class="ph-bold ph-star text-lg"></i>
                    </div>
                    <div class="flex-1">
                        <p class="text-gray-700 font-medium">Selamat Datang di Semester Baru</p>
                        <p class="text-gray-600 text-sm mt-1">
                            Selamat memulai semester  <a href="#" class="font-semibold text-blue-600 hover:underline">Genap 2024/2025</a>.
                        </p>
                        <p class="text-xs text-gray-500 mt-2">2 menit yang lalu</p>
                    </div>
                </div>
            </div>
            
            <div class="notification-item p-4 rounded-xl border-l-4 border-red-400 bg-red-50">
                <div class="flex items-start space-x-3">
                    <div class="p-2 bg-red-100 text-red-600 rounded-full">
                        <i class="ph-bold ph-timer text-lg"></i>
                    </div>
                    <div class="flex-1">
                        <p class="text-gray-700 font-medium">Pendaftaran Praktikum telah dibuka</p>
                        <p class="text-gray-600 text-sm mt-1">
                            Pendaftaran untuk praktikum semester baru telah dibuka hingga <a href="#" class="font-semibold text-blue-600 hover:underline">31 Juli 2025</a> 
                        </p>
                        <p class="text-xs text-gray-500 mt-2">1 jam yang lalu</p>
                    </div>
                </div>
            </div>
            
            <div class="notification-item p-4 rounded-xl border-l-4 border-green-400 bg-green-50">
                <div class="flex items-start space-x-3">
                    <div class="p-2 bg-green-100 text-green-600 rounded-full">
                        <i class="ph-bold ph-check-fat text-lg"></i>
                    </div>
                    <div class="flex-1">
                        <p class="text-gray-700 font-medium">Modul Praktikum Baru Tersedia</p>
                        <p class="text-gray-600 text-sm mt-1">
                            Modul praktikum baru telah di tambahkan pada <a href="#" class="font-semibold text-blue-600 hover:underline">Katalog Praktikum</a>.
                        </p>
                        <p class="text-xs text-gray-500 mt-2">3 jam yang lalu</p>
                    </div>
                </div>
            </div>
        </div>
        


<?php include 'templates/footer_mahasiswa.php'; ?>