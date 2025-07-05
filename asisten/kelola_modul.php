<?php
// Koneksi DB
$host = 'localhost'; $dbname = 'db_simpraks'; $user = 'root'; $pass = '';
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Koneksi gagal: " . $e->getMessage());
}

// --- LOGIKA FILTER ---
$filter_praktikum = $_GET['filter_praktikum'] ?? '';

// Query dasar
$sql = "SELECT m.*, mp.nama_praktikum
        FROM modul m
        JOIN mata_praktikum mp ON m.id_praktikum = mp.id";

$params = [];
if (!empty($filter_praktikum)) {
    $sql .= " WHERE m.id_praktikum = :id_praktikum";
    $params[':id_praktikum'] = $filter_praktikum;
}
$sql .= " ORDER BY m.id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$modul_list = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Ambil daftar praktikum untuk dropdown filter
$praktikum_options = $pdo->query("SELECT id, nama_praktikum FROM mata_praktikum ORDER BY nama_praktikum")->fetchAll(PDO::FETCH_ASSOC);
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
                    <a href="tambah_modul.php" class="group inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-600 to-purple-600 text-white font-semibold rounded-xl hover:from-blue-700 hover:to-purple-700 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                        <i class="ph-bold ph-plus mr-2 group-hover:rotate-90 transition-transform duration-300"></i>
                        Tambah Modul
                    </a>
                </div>
            </div>
        </div>
    </div>

        <!-- Table Section -->
        <div class="bg-white/60 backdrop-blur-sm rounded-2xl shadow-xl border border-white/20 overflow-hidden">
            <div class="px-6 py-4 bg-gradient-to-r from-gray-50 to-gray-100">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <i class="ph-bold ph-list text-2xl text-gray-600"></i>
                        <h3 class="text-lg font-semibold text-gray-800">Daftar Modul</h3>
                    </div>
                    <div class="flex items-center space-x-2 text-sm text-gray-600">
                        <i class="ph-bold ph-database"></i>
                        <span><?= count($modul_list) ?> modul</span>
                    </div>
                </div>
            </div>

            <?php if (empty($modul_list)): ?>
                <div class="text-center py-20">
                    <div class="bg-gradient-to-r from-blue-100 to-purple-100 w-32 h-32 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="ph-bold ph-folder-simple-dashed text-6xl text-gray-400"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-600 mb-2">Tidak ada modul ditemukan</h3>
                    <p class="text-gray-500 mb-6">Belum ada modul yang sesuai dengan filter yang dipilih</p>
                    <a href="tambah_modul.php" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-600 to-purple-600 text-white font-semibold rounded-xl hover:from-blue-700 hover:to-purple-700 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                        <i class="ph-bold ph-plus mr-2"></i>
                        Tambah Modul Pertama
                    </a>
                </div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
                            <tr>
                                <th class="px-6 py-4 text-left font-semibold text-gray-700">
                                    <i class="ph-bold ph-file-text mr-2"></i>MODUL
                                </th>
                                <th class="px-6 py-4 text-left font-semibold text-gray-700">
                                    <i class="ph-bold ph-graduation-cap mr-2"></i>PRAKTIKUM
                                </th>
                                <th class="px-6 py-4 text-center font-semibold text-gray-700">
                                    <i class="ph-bold ph-gear mr-2"></i>AKSI
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php foreach ($modul_list as $index => $modul): ?>
                            <tr class="hover:bg-gradient-to-r hover:from-blue-50 hover:to-purple-50 transition-all duration-300 group">
                                <td class="px-6 py-4">
                                    <div class="flex items-start space-x-4">
                                        <div class="bg-gradient-to-r from-blue-500 to-purple-500 text-white w-12 h-12 rounded-xl flex items-center justify-center font-bold text-lg shadow-lg">
                                            <?= $index + 1 ?>
                                        </div>
                                        <div class="flex-1">
                                            <h4 class="font-bold text-gray-800 text-lg mb-1 group-hover:text-blue-600 transition-colors duration-300">
                                                <?= htmlspecialchars($modul['judul_modul']) ?>
                                            </h4>
                                            <p class="text-gray-600 text-sm leading-relaxed">
                                                <?= htmlspecialchars(substr(str_replace(["\r\n", "\r", "\n"], " ", $modul['deskripsi']), 0, 120)) ?>
                                                <?= strlen($modul['deskripsi']) > 120 ? '...' : '' ?>
                                            </p>
                                            <div class="flex items-center mt-2 text-xs text-gray-500">
                                                <i class="ph-bold ph-calendar mr-1"></i>
                                                <span>Terakhir diupdate: <?= date('d M Y', strtotime($modul['created_at'] ?? 'now')) ?></span>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="inline-flex items-center">
                                        <span class="px-4 py-2 text-sm font-semibold rounded-full bg-gradient-to-r from-blue-100 to-purple-100 text-blue-700 border border-blue-200">
                                            <i class="ph-bold ph-graduation-cap mr-1"></i>
                                            <?= htmlspecialchars($modul['nama_praktikum']) ?>
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex justify-center items-center space-x-2">
                                        <a href="/SistemPengumpulanTugas/uploads/materi/<?= htmlspecialchars($modul['file_materi']) ?>" 
                                           target="_blank" 
                                           title="Lihat File" 
                                           class="group/btn p-3 rounded-xl bg-gradient-to-r from-green-500 to-green-600 text-white hover:from-green-600 hover:to-green-700 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                                            <i class="ph-bold ph-eye text-lg group-hover/btn:scale-110 transition-transform duration-300"></i>
                                        </a>
                                        <a href="edit_modul.php?id=<?= $modul['id'] ?>" 
                                           title="Edit" 
                                           class="group/btn p-3 rounded-xl bg-gradient-to-r from-yellow-500 to-orange-500 text-white hover:from-yellow-600 hover:to-orange-600 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                                            <i class="ph-bold ph-pencil-simple text-lg group-hover/btn:scale-110 transition-transform duration-300"></i>
                                        </a>
                                        <a href="hapus_modul.php?id=<?= $modul['id'] ?>" 
                                           onclick="return confirm('⚠️ Yakin ingin menghapus modul ini?\n\nData yang sudah dihapus tidak dapat dikembalikan!')" 
                                           title="Hapus" 
                                           class="group/btn p-3 rounded-xl bg-gradient-to-r from-red-500 to-red-600 text-white hover:from-red-600 hover:to-red-700 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                                            <i class="ph-bold ph-trash-simple text-lg group-hover/btn:scale-110 transition-transform duration-300"></i>
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

    // Add loading state to buttons
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

    // Add confirmation with better styling for delete buttons
    document.querySelectorAll('a[href*="hapus_modul"]').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const modulName = this.closest('tr').querySelector('h4').textContent;
            
            if (confirm(`🗑️ Hapus Modul "${modulName}"?\n\n⚠️ Tindakan ini tidak dapat dibatalkan!`)) {
                window.location.href = this.href;
            }
        });
    });
});
</script>