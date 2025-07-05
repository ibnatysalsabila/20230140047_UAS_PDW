<?php
// Tidak perlu session_start() karena sudah ada di file layout utama (dashboard.php)

// --- KONEKSI DATABASE ---
$host = 'localhost';
$dbname = 'db_simpraks';
$user = 'root';
$pass = '';
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Koneksi ke database gagal: " . $e->getMessage());
}

// --- LOGIKA FILTER ---
$filter_praktikum = $_GET['filter_praktikum'] ?? '';
$filter_status = $_GET['filter_status'] ?? '';

// --- QUERY SQL ---
$sql = "SELECT
            pl.id, pl.file_laporan, pl.tanggal_upload, pl.nilai,
            u.nama AS nama_mahasiswa,
            m.judul_modul,
            mp.nama_praktikum
        FROM
            pengumpulan_laporan AS pl
        JOIN users AS u ON pl.id_mahasiswa = u.id
        JOIN modul AS m ON pl.id_modul = m.id
        JOIN mata_praktikum AS mp ON m.id_praktikum = mp.id
        WHERE 1=1";

$params = [];

if (!empty($filter_praktikum)) {
    $sql .= " AND mp.id = :id_praktikum";
    $params[':id_praktikum'] = $filter_praktikum;
}

if ($filter_status === 'dinilai') {
    $sql .= " AND pl.nilai IS NOT NULL";
} elseif ($filter_status === 'belum_dinilai') {
    $sql .= " AND pl.nilai IS NULL";
}

$sql .= " ORDER BY pl.tanggal_upload DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$laporan_list = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Ambil daftar praktikum untuk dropdown
$praktikum_options_stmt = $pdo->query("SELECT id, nama_praktikum FROM mata_praktikum ORDER BY nama_praktikum");
$praktikum_options = $praktikum_options_stmt->fetchAll(PDO::FETCH_ASSOC);

// Fungsi getInitials sudah ada di dashboard.php, tidak perlu dideklarasikan lagi
?>

<div class="min-h-screen bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50">
    <!-- Header Section -->
    <div class="bg-white/80 backdrop-blur-sm border-b border-white/20 sticky top-0 z-10">
        <div class="max-w-7xl mx-auto px-6 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <div class="hidden md:flex items-center space-x-2 text-sm text-gray-500">
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="flex items-center space-x-2 text-sm text-gray-600">
                    </div>
                </div>
            </div>
        </div>
    </div>

        <!-- Table Section -->
        <div class="bg-white/60 backdrop-blur-sm rounded-2xl shadow-xl border border-white/20 overflow-hidden">
            <div class="px-6 py-4 bg-gradient-to-r from-gray-50 to-gray-100">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <i class="ph-bold ph-file-text text-2xl text-gray-600"></i>
                        <h3 class="text-lg font-semibold text-gray-800">Laporan Masuk</h3>
                    </div>
                    <div class="flex items-center space-x-2 text-sm text-gray-600">
                        <i class="ph-bold ph-database"></i>
                        <span><?= count($laporan_list) ?> laporan</span>
                    </div>
                </div>
            </div>

            <?php if (empty($laporan_list)): ?>
                <div class="text-center py-20">
                    <div class="bg-gradient-to-r from-blue-100 to-purple-100 w-32 h-32 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="ph-bold ph-file-dashed text-6xl text-gray-400"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-600 mb-2">Tidak ada laporan ditemukan</h3>
                    <p class="text-gray-500 mb-6">Belum ada laporan yang sesuai dengan filter yang dipilih</p>
                    <div class="flex justify-center space-x-4">
                        <a href="dashboard.php?page=laporan_masuk" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-600 to-purple-600 text-white font-semibold rounded-xl hover:from-blue-700 hover:to-purple-700 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                            <i class="ph-bold ph-arrow-clockwise mr-2"></i>
                            Reset Filter
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
                            <tr>
                                <th class="px-6 py-4 text-left font-semibold text-gray-700">
                                    <i class="ph-bold ph-user mr-2"></i>MAHASISWA
                                </th>
                                <th class="px-6 py-4 text-left font-semibold text-gray-700">
                                    <i class="ph-bold ph-book mr-2"></i>MODUL & PRAKTIKUM
                                </th>
                                <th class="px-6 py-4 text-left font-semibold text-gray-700">
                                    <i class="ph-bold ph-calendar mr-2"></i>TANGGAL UPLOAD
                                </th>
                                <th class="px-6 py-4 text-left font-semibold text-gray-700">
                                    <i class="ph-bold ph-check-circle mr-2"></i>STATUS
                                </th>
                                <th class="px-6 py-4 text-center font-semibold text-gray-700">
                                    <i class="ph-bold ph-gear mr-2"></i>AKSI
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php foreach ($laporan_list as $index => $laporan): ?>
                            <tr class="hover:bg-gradient-to-r hover:from-blue-50 hover:to-purple-50 transition-all duration-300 group">
                                <td class="px-6 py-4">
                                    <div class="flex items-center space-x-4">
                                        <div class="relative">
                                            <div class="w-12 h-12 bg-gradient-to-r from-blue-500 to-purple-500 flex items-center justify-center rounded-full font-bold text-white text-sm shadow-lg">
                                                <?= getInitials($laporan['nama_mahasiswa']) ?>
                                            </div>
                                            <div class="absolute -top-1 -right-1 w-4 h-4 bg-green-500 rounded-full border-2 border-white flex items-center justify-center">
                                                <i class="ph-bold ph-check text-white text-xs"></i>
                                            </div>
                                        </div>
                                        <div>
                                            <h4 class="font-semibold text-gray-800 group-hover:text-blue-600 transition-colors duration-300">
                                                <?= htmlspecialchars($laporan['nama_mahasiswa']) ?>
                                            </h4>
                                            <p class="text-gray-500 text-sm">Mahasiswa</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="space-y-1">
                                        <h4 class="font-semibold text-gray-800">
                                            <?= htmlspecialchars($laporan['judul_modul']) ?>
                                        </h4>
                                        <div class="inline-flex items-center">
                                            <span class="px-3 py-1 text-xs font-medium rounded-full bg-gradient-to-r from-blue-100 to-purple-100 text-blue-700 border border-blue-200">
                                                <i class="ph-bold ph-graduation-cap mr-1"></i>
                                                <?= htmlspecialchars($laporan['nama_praktikum']) ?>
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center space-x-2">
                                        <div class="bg-gray-100 p-2 rounded-lg">
                                            <i class="ph-bold ph-calendar text-gray-600"></i>
                                        </div>
                                        <div>
                                            <p class="text-gray-800 font-medium">
                                                <?= date('d M Y', strtotime($laporan['tanggal_upload'])) ?>
                                            </p>
                                            <p class="text-gray-500 text-sm">
                                                <?= date('H:i', strtotime($laporan['tanggal_upload'])) ?>
                                            </p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <?php if ($laporan['nilai'] !== null): ?>
                                        <div class="inline-flex items-center">
                                            <span class="px-4 py-2 text-sm font-semibold rounded-full bg-gradient-to-r from-green-500 to-green-600 text-white shadow-lg">
                                                <i class="ph-bold ph-check-circle mr-1"></i>
                                                Dinilai (<?= htmlspecialchars($laporan['nilai']) ?>)
                                            </span>
                                        </div>
                                    <?php else: ?>
                                        <div class="inline-flex items-center">
                                            <span class="px-4 py-2 text-sm font-semibold rounded-full bg-gradient-to-r from-yellow-500 to-orange-500 text-white shadow-lg animate-pulse">
                                                <i class="ph-bold ph-clock mr-1"></i>
                                                Belum Dinilai
                                            </span>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex justify-center items-center space-x-2">
                                        <a href="beri_nilai.php?id=<?= $laporan['id'] ?>" 
                                           title="Beri Nilai" 
                                           class="group/btn p-3 rounded-xl bg-gradient-to-r from-yellow-500 to-orange-500 text-white hover:from-yellow-600 hover:to-orange-600 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                                            <i class="ph-bold ph-star text-lg group-hover/btn:scale-110 transition-transform duration-300"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
/* Custom scrollbar for better aesthetics */
::-webkit-scrollbar {
    width: 8px;
    height: 8px;
}

::-webkit-scrollbar-track {
    background: #f1f5f9;
    border-radius: 4px;
}

::-webkit-scrollbar-thumb {
    background: linear-gradient(45deg, #3b82f6, #8b5cf6);
    border-radius: 4px;
}

::-webkit-scrollbar-thumb:hover {
    background: linear-gradient(45deg, #2563eb, #7c3aed);
}

/* Animation for table rows */
@keyframes slideInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

tbody tr {
    animation: slideInUp 0.5s ease-out;
}

/* Pulse animation for pending status */
@keyframes pulse {
    0%, 100% {
        opacity: 1;
    }
    50% {
        opacity: 0.7;
    }
}

.animate-pulse {
    animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

/* Hover effect for cards */
.hover-lift:hover {
    transform: translateY(-2px);
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
}

/* Glassmorphism effect */
.glass {
    background: rgba(255, 255, 255, 0.25);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.18);
}

/* Status indicator animation */
.status-indicator {
    position: relative;
    overflow: hidden;
}

.status-indicator::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
    transition: left 0.5s;
}

.status-indicator:hover::before {
    left: 100%;
}
</style>

<script>
// Add some interactive features
document.addEventListener('DOMContentLoaded', function() {
    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            document.querySelector(this.getAttribute('href')).scrollIntoView({
                behavior: 'smooth'
            });
        });
    });

    // Add loading state to filter buttons
    document.querySelectorAll('button[type="submit"]').forEach(button => {
        button.addEventListener('click', function() {
            const original = this.innerHTML;
            this.innerHTML = '<i class="ph-bold ph-spinner animate-spin mr-2"></i>Memuat...';
            this.disabled = true;
            
            setTimeout(() => {
                this.innerHTML = original;
                this.disabled = false;
            }, 2000);
        });
    });

    // Add tooltip for action buttons
    document.querySelectorAll('[title]').forEach(element => {
        element.addEventListener('mouseenter', function() {
            this.setAttribute('data-tooltip', this.getAttribute('title'));
        });
    });

    // Add download confirmation
    document.querySelectorAll('a[download]').forEach(link => {
        link.addEventListener('click', function(e) {
            const fileName = this.href.split('/').pop();
            if (!confirm(`📥 Unduh file "${fileName}"?`)) {
                e.preventDefault();
            }
        });
    });

    // Add auto-refresh for real-time updates
    let refreshInterval;
    const startAutoRefresh = () => {
        refreshInterval = setInterval(() => {
            // Check if there are new reports
            fetch(window.location.href)
                .then(response => response.text())
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const newCount = doc.querySelector('[data-count]')?.textContent;
                    const currentCount = document.querySelector('[data-count]')?.textContent;
                    
                    if (newCount && currentCount && newCount !== currentCount) {
                        // Show notification for new reports
                        showNotification('📄 Laporan baru tersedia!', 'info');
                    }
                });
        }, 30000); // Check every 30 seconds
    };

    const showNotification = (message, type = 'info') => {
        const notification = document.createElement('div');
        notification.className = `fixed top-4 right-4 p-4 rounded-lg shadow-lg z-50 ${type === 'info' ? 'bg-blue-500 text-white' : 'bg-green-500 text-white'}`;
        notification.innerHTML = `
            <div class="flex items-center space-x-2">
                <i class="ph-bold ph-info text-lg"></i>
                <span>${message}</span>
            </div>
        `;
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.remove();
        }, 5000);
    };

    // Start auto-refresh if page is visible
    if (document.visibilityState === 'visible') {
        startAutoRefresh();
    }

    // Handle page visibility change
    document.addEventListener('visibilitychange', () => {
        if (document.visibilityState === 'visible') {
            startAutoRefresh();
        } else {
            clearInterval(refreshInterval);
        }
    });
});
</script>