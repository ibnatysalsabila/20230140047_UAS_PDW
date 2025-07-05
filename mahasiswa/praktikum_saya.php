<?php
session_start();
include 'templates/header_mahasiswa.php';

// --- KONEKSI DATABASE ---
$host = 'localhost'; $dbname = 'db_simpraks'; $user = 'root'; $pass = '';
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) { die("Koneksi gagal: " . $e->getMessage()); }

$id_mahasiswa = $_SESSION['user_id'];

// Query mengambil semua modul dari praktikum yang diikuti mahasiswa
$sql = "
    SELECT 
        mp.nama_praktikum,
        m.id as id_modul,
        m.judul_modul,
        m.deskripsi,
        m.file_materi,
        pl.id as id_pengumpulan,
        pl.file_laporan,
        pl.nilai
    FROM pendaftaran_praktikum pp
    JOIN mata_praktikum mp ON pp.id_praktikum = mp.id
    JOIN modul m ON mp.id = m.id_praktikum
    LEFT JOIN pengumpulan_laporan pl ON m.id = pl.id_modul AND pl.id_mahasiswa = :id_mahasiswa
    WHERE pp.id_mahasiswa = :id_mahasiswa
    ORDER BY mp.nama_praktikum, m.id
";
$stmt = $pdo->prepare($sql);
$stmt->execute(['id_mahasiswa' => $id_mahasiswa]);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Kelompokkan hasil berdasarkan nama praktikum
$grouped_praktikum = [];
foreach ($results as $row) {
    $grouped_praktikum[$row['nama_praktikum']][] = $row;
}

// Hitung statistik untuk setiap praktikum
$praktikum_stats = [];
foreach ($grouped_praktikum as $nama_praktikum => $modules) {
    $total = count($modules);
    $selesai = count(array_filter($modules, function($m) { return $m['id_pengumpulan'] !== null; }));
    $dinilai = count(array_filter($modules, function($m) { return $m['nilai'] !== null; }));
    $praktikum_stats[$nama_praktikum] = [
        'total' => $total,
        'selesai' => $selesai,
        'dinilai' => $dinilai,
        'progress' => $total > 0 ? round(($selesai / $total) * 100) : 0
    ];
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

    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateX(-30px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    .animate-fadeInUp {
        animation: fadeInUp 0.6s ease-out;
    }

    .animate-slideIn {
        animation: slideIn 0.6s ease-out;
    }

    .card-hover {
        transition: all 0.3s ease;
    }

    .card-hover:hover {
        transform: translateY(-3px);
        box-shadow: 0 15px 30px -5px rgba(0, 0, 0, 0.1);
    }

    .module-item {
        transition: all 0.3s ease;
    }

    .module-item:hover {
        background-color: #F8FAFC;
        border-left: 4px solid #3B82F6;
        padding-left: 20px;
    }

    .progress-ring {
        transition: stroke-dasharray 0.5s ease-in-out;
    }

    .status-badge {
        transition: all 0.3s ease;
    }

    .status-badge:hover {
        transform: scale(1.05);
    }

    .btn-primary {
        background: linear-gradient(135deg, #3B82F6 0%, #1D4ED8 100%);
        transition: all 0.3s ease;
    }

    .btn-primary:hover {
        background: linear-gradient(135deg, #1D4ED8 0%, #1E40AF 100%);
        transform: translateY(-2px);
        box-shadow: 0 8px 16px rgba(59, 130, 246, 0.3);
    }

    .empty-state {
        background: linear-gradient(135deg, #F8FAFC 0%, #E2E8F0 100%);
    }
</style>

<div class="min-h-screen bg-gray-50">
    <!-- Header Section -->
    <div class="bg-gradient-to-r from-blue-600 via-blue-700 to-purple-800 p-8 rounded-2xl shadow-2xl mb-8 text-white animate-fadeInUp">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-4xl font-bold mb-2 flex items-center">
                    <i class="ph-bold ph-flask mr-3"></i>
                    Praktikum Saya
                </h1>
                <p class="text-blue-100 text-lg">
                    Kelola dan pantau progress semua modul praktikum yang kamu ikuti
                </p>
            </div>
            <div class="text-right">
                <div class="bg-white bg-opacity-20 backdrop-blur-sm rounded-lg p-4">
                    <p class="text-sm text-blue-100">Total Praktikum</p>
                    <p class="text-3xl font-bold"><?= count($grouped_praktikum) ?></p>
                </div>
            </div>
        </div>
    </div>

    <?php if (empty($grouped_praktikum)): ?>
        <!-- Empty State -->
        <div class="empty-state text-center p-16 rounded-2xl shadow-lg border-0 animate-fadeInUp">
            <div class="mb-6">
                <i class="ph-bold ph-folder-simple-dashed text-8xl text-gray-400"></i>
            </div>
            <h2 class="text-3xl font-bold text-gray-700 mb-4">Belum Ada Praktikum Terdaftar</h2>
            <p class="text-gray-500 text-lg mb-8 max-w-md mx-auto">
                Kamu belum terdaftar di praktikum apapun. Mulai petualangan belajarmu dengan mendaftar praktikum yang tersedia.
            </p>
            <a href="katalog_praktikum.php" class="btn-primary inline-flex items-center px-8 py-4 text-white font-semibold rounded-xl text-lg">
                <i class="ph-bold ph-magnifying-glass mr-2"></i>
                Cari Praktikum
            </a>
        </div>
    <?php else: ?>
        <!-- Praktikum List -->
        <?php $delay = 0; ?>
        <?php foreach ($grouped_praktikum as $nama_praktikum => $modules): ?>
            <div class="bg-white rounded-2xl shadow-lg border-0 mb-8 overflow-hidden card-hover animate-fadeInUp" style="animation-delay: <?= $delay * 0.1 ?>s;">
                <!-- Praktikum Header -->
                <div class="bg-gradient-to-r from-gray-50 to-gray-100 p-6 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="p-3 bg-blue-100 rounded-xl mr-4">
                                <i class="ph-bold ph-book text-2xl text-blue-600"></i>
                            </div>
                            <div>
                                <h2 class="text-2xl font-bold text-gray-800"><?= htmlspecialchars($nama_praktikum) ?></h2>
                                <p class="text-gray-600 text-sm mt-1">
                                    <?= $praktikum_stats[$nama_praktikum]['selesai'] ?> dari <?= $praktikum_stats[$nama_praktikum]['total'] ?> modul selesai
                                </p>
                            </div>
                        </div>
                        
                        <!-- Progress Circle -->
                        <div class="flex items-center space-x-4">
                            <div class="relative w-16 h-16">
                                <svg class="transform -rotate-90 w-16 h-16">
                                    <circle cx="32" cy="32" r="28" stroke="#E5E7EB" stroke-width="4" fill="none" />
                                    <circle cx="32" cy="32" r="28" stroke="#3B82F6" stroke-width="4" fill="none" 
                                            stroke-dasharray="<?= 2 * pi() * 28 ?>" 
                                            stroke-dashoffset="<?= 2 * pi() * 28 * (1 - $praktikum_stats[$nama_praktikum]['progress'] / 100) ?>"
                                            class="progress-ring" />
                                </svg>
                                <div class="absolute inset-0 flex items-center justify-center">
                                    <span class="text-sm font-bold text-blue-600"><?= $praktikum_stats[$nama_praktikum]['progress'] ?>%</span>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-sm text-gray-500">Progress</div>
                                <div class="text-lg font-bold text-gray-800"><?= $praktikum_stats[$nama_praktikum]['progress'] ?>%</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modules List -->
                <div class="divide-y divide-gray-100">
                    <?php foreach ($modules as $index => $modul): ?>
                        <div class="p-6 module-item animate-slideIn" style="animation-delay: <?= ($delay * 0.1) + ($index * 0.05) ?>s;">
                            <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between">
                                <!-- Module Info -->
                                <div class="flex-1 mb-4 lg:mb-0 pr-4">
                                    <div class="flex items-start space-x-3">
                                        <div class="p-2 bg-blue-50 rounded-lg mt-1">
                                            <i class="ph-bold ph-file-text text-blue-600 text-lg"></i>
                                        </div>
                                        <div class="flex-1">
                                            <h3 class="text-lg font-bold text-gray-900 mb-1"><?= htmlspecialchars($modul['judul_modul']) ?></h3>
                                            <p class="text-gray-600 text-sm mb-3 leading-relaxed"><?= htmlspecialchars($modul['deskripsi']) ?></p>
                                            
                                            <?php if (!empty($modul['file_materi'])): ?>
                                                <a href="/SistemPengumpulanTugas/uploads/materi/<?= htmlspecialchars($modul['file_materi']) ?>" target="_blank" 
                                                   class="inline-flex items-center text-sm text-blue-600 hover:text-blue-800 bg-blue-50 px-3 py-1 rounded-lg hover:bg-blue-100 transition-colors">
                                                    <i class="ph-bold ph-file-pdf mr-2"></i>
                                                    Lihat Materi
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>

                                <!-- Status & Actions -->
                                <div class="flex-shrink-0 w-full lg:w-auto lg:ml-6 flex flex-col items-start lg:items-end space-y-3">
                                    <!-- Status Badge -->
                                    <?php if ($modul['id_pengumpulan']): ?>
                                        <?php if ($modul['nilai'] !== null): ?>
                                            <div class="flex items-center space-x-2">
                                                <span class="status-badge px-4 py-2 text-sm font-semibold rounded-full bg-green-100 text-green-800 flex items-center">
                                                    <i class="ph-bold ph-check-circle mr-1"></i>
                                                    Dinilai: <?= htmlspecialchars($modul['nilai']) ?>
                                                </span>
                                            </div>
                                            <a href="/SistemPengumpulanTugas/uploads/laporan/<?= htmlspecialchars($modul['file_laporan']) ?>" target="_blank" 
                                               class="text-sm text-gray-500 hover:text-blue-600 flex items-center transition-colors">
                                                <i class="ph-bold ph-eye mr-1"></i>
                                                Lihat Laporan
                                            </a>
                                        <?php else: ?>
                                            <div class="flex items-center space-x-2">
                                                <span class="status-badge px-4 py-2 text-sm font-semibold rounded-full bg-yellow-100 text-yellow-800 flex items-center">
                                                    <i class="ph-bold ph-clock mr-1"></i>
                                                    Menunggu Penilaian
                                                </span>
                                            </div>
                                            <a href="/SistemPengumpulanTugas/uploads/laporan/<?= htmlspecialchars($modul['file_laporan']) ?>" target="_blank" 
                                               class="text-sm text-gray-500 hover:text-blue-600 flex items-center transition-colors">
                                                <i class="ph-bold ph-eye mr-1"></i>
                                                Lihat Laporan
                                            </a>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <div class="flex items-center space-x-2">
                                            <span class="status-badge px-4 py-2 text-sm font-semibold rounded-full bg-red-100 text-red-800 flex items-center">
                                                <i class="ph-bold ph-x-circle mr-1"></i>
                                                Belum Mengumpulkan
                                            </span>
                                        </div>
                                        <a href="kumpul_laporan.php?id_modul=<?= $modul['id_modul'] ?>" 
                                           class="btn-primary inline-flex items-center px-6 py-3 text-white font-semibold rounded-xl text-sm">
                                            <i class="ph-bold ph-upload mr-2"></i>
                                            Upload Laporan
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php $delay++; ?>
        <?php endforeach; ?>

        <!-- Summary Stats -->
        <div class="bg-white rounded-2xl shadow-lg border-0 p-8 animate-fadeInUp" style="animation-delay: <?= $delay * 0.1 ?>s;">
            <h3 class="text-xl font-bold text-gray-800 mb-6 flex items-center">
                <i class="ph-bold ph-chart-bar text-blue-600 mr-2"></i>
                Ringkasan Progress
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <?php 
                $total_modul = array_sum(array_column($praktikum_stats, 'total'));
                $total_selesai = array_sum(array_column($praktikum_stats, 'selesai'));
                $total_dinilai = array_sum(array_column($praktikum_stats, 'dinilai'));
                ?>
                <div class="text-center p-6 bg-gradient-to-r from-blue-50 to-blue-100 rounded-xl">
                    <div class="text-3xl font-bold text-blue-600 mb-2"><?= $total_modul ?></div>
                    <div class="text-sm text-blue-800 font-medium">Total Modul</div>
                </div>
                <div class="text-center p-6 bg-gradient-to-r from-green-50 to-green-100 rounded-xl">
                    <div class="text-3xl font-bold text-green-600 mb-2"><?= $total_selesai ?></div>
                    <div class="text-sm text-green-800 font-medium">Telah Dikumpulkan</div>
                </div>
                <div class="text-center p-6 bg-gradient-to-r from-purple-50 to-purple-100 rounded-xl">
                    <div class="text-3xl font-bold text-purple-600 mb-2"><?= $total_dinilai ?></div>
                    <div class="text-sm text-purple-800 font-medium">Sudah Dinilai</div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include 'templates/footer_mahasiswa.php'; ?>