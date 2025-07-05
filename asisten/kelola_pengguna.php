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
$filter_role = $_GET['filter_role'] ?? '';

// Query dasar
$sql = "SELECT id, nama, email, role FROM users";
$params = [];
if (!empty($filter_role)) {
    $sql .= " WHERE role = :role";
    $params[':role'] = $filter_role;
}
$sql .= " ORDER BY role ASC, nama ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fungsi getInitials() sudah ada di dashboard.php, tidak perlu dideklarasikan lagi.
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
                    <a href="tambah_pengguna.php" class="group inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-600 to-purple-600 text-white font-semibold rounded-xl hover:from-blue-700 hover:to-purple-700 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                        <i class="ph-bold ph-plus mr-2 group-hover:rotate-90 transition-transform duration-300"></i>
                        Tambah Pengguna
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
                        <h3 class="text-lg font-semibold text-gray-800">Daftar Pengguna</h3>
                    </div>
                    <div class="flex items-center space-x-2 text-sm text-gray-600">
                        <i class="ph-bold ph-database"></i>
                        <span><?= count($users) ?> pengguna</span>
                    </div>
                </div>
            </div>

            <?php if (empty($users)): ?>
                <div class="text-center py-20">
                    <div class="bg-gradient-to-r from-blue-100 to-purple-100 w-32 h-32 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="ph-bold ph-users-three-dashed text-6xl text-gray-400"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-600 mb-2">Tidak ada pengguna ditemukan</h3>
                    <p class="text-gray-500 mb-6">Belum ada pengguna yang sesuai dengan filter yang dipilih</p>
                    <a href="tambah_pengguna.php" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-600 to-purple-600 text-white font-semibold rounded-xl hover:from-blue-700 hover:to-purple-700 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                        <i class="ph-bold ph-plus mr-2"></i>
                        Tambah Pengguna Pertama
                    </a>
                </div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
                            <tr>
                                <th class="px-6 py-4 text-left font-semibold text-gray-700">
                                    <i class="ph-bold ph-user mr-2"></i>NAMA LENGKAP
                                </th>
                                <th class="px-6 py-4 text-left font-semibold text-gray-700">
                                    <i class="ph-bold ph-envelope mr-2"></i>EMAIL
                                </th>
                                <th class="px-6 py-4 text-left font-semibold text-gray-700">
                                    <i class="ph-bold ph-user-circle mr-2"></i>ROLE
                                </th>
                                <th class="px-6 py-4 text-center font-semibold text-gray-700">
                                    <i class="ph-bold ph-gear mr-2"></i>AKSI
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php foreach ($users as $user): ?>
                            <tr class="hover:bg-gradient-to-r hover:from-blue-50 hover:to-purple-50 transition-all duration-300 group">
                                <td class="px-6 py-4">
                                    <div class="flex items-center space-x-4">
                                        <div class="bg-gradient-to-r from-blue-500 to-purple-500 text-white w-12 h-12 rounded-xl flex items-center justify-center font-bold text-lg shadow-lg">
                                            <?= getInitials($user['nama']) ?>
                                        </div>
                                        <div class="flex-1">
                                            <h4 class="font-bold text-gray-800 text-lg group-hover:text-blue-600 transition-colors duration-300">
                                                <?= htmlspecialchars($user['nama']) ?>
                                            </h4>
                                            <p class="text-gray-500 text-sm">
                                                <i class="ph-bold ph-identification-card mr-1"></i>
                                                ID: <?= $user['id'] ?>
                                            </p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center text-gray-600">
                                        <i class="ph-bold ph-envelope mr-2 text-gray-400"></i>
                                        <span class="font-medium"><?= htmlspecialchars($user['email']) ?></span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="inline-flex items-center">
                                        <?php if ($user['role'] == 'asisten'): ?>
                                            <span class="px-4 py-2 text-sm font-semibold rounded-full bg-gradient-to-r from-green-100 to-green-200 text-green-700 border border-green-300">
                                                <i class="ph-bold ph-chalkboard-teacher mr-1"></i>
                                                Asisten
                                            </span>
                                        <?php else: ?>
                                            <span class="px-4 py-2 text-sm font-semibold rounded-full bg-gradient-to-r from-purple-100 to-purple-200 text-purple-700 border border-purple-300">
                                                <i class="ph-bold ph-graduation-cap mr-1"></i>
                                                Mahasiswa
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex justify-center items-center space-x-2">
                                        <a href="edit_pengguna.php?id=<?= $user['id'] ?>" 
                                           title="Edit" 
                                           class="group/btn p-3 rounded-xl bg-gradient-to-r from-yellow-500 to-orange-500 text-white hover:from-yellow-600 hover:to-orange-600 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                                            <i class="ph-bold ph-pencil-simple text-lg group-hover/btn:scale-110 transition-transform duration-300"></i>
                                        </a>
                                        <a href="hapus_pengguna.php?id=<?= $user['id'] ?>" 
                                           onclick="return confirm('⚠️ Yakin ingin menghapus pengguna ini?\n\nData yang sudah dihapus tidak dapat dikembalikan!')" 
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
    document.querySelectorAll('a[href*="hapus_pengguna"]').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const userName = this.closest('tr').querySelector('h4').textContent;
            
            if (confirm(`🗑️ Hapus Pengguna "${userName}"?\n\n⚠️ Tindakan ini tidak dapat dibatalkan!`)) {
                window.location.href = this.href;
            }
        });
    });

    // Add search functionality
    const searchInput = document.createElement('input');
    searchInput.type = 'text';
    searchInput.placeholder = 'Cari pengguna...';
    searchInput.className = 'w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent';
    
    // Add real-time search filter
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const filter = this.value.toLowerCase();
            const rows = document.querySelectorAll('tbody tr');
            
            rows.forEach(row => {
                const name = row.querySelector('h4').textContent.toLowerCase();
                const email = row.querySelector('td:nth-child(2) span').textContent.toLowerCase();
                
                if (name.includes(filter) || email.includes(filter)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    }
});
</script>