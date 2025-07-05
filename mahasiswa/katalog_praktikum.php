<?php
session_start();

// --- KONEKSI DATABASE ---
$host = 'localhost'; $dbname = 'db_simpraks'; $user = 'root'; $pass = '';
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) { die("Koneksi gagal: " . $e->getMessage()); }

$id_mahasiswa = $_SESSION['user_id'];

// Logika untuk mendaftar praktikum
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['daftar_praktikum'])) {
    $id_praktikum_to_register = $_POST['id_praktikum'];

    // Pastikan mahasiswa belum terdaftar sebelumnya
    $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM pendaftaran_praktikum WHERE id_praktikum = ? AND id_mahasiswa = ?");
    $stmt_check->execute([$id_praktikum_to_register, $id_mahasiswa]);

    if ($stmt_check->fetchColumn() == 0) {
        // Jika belum terdaftar, daftarkan
        $stmt_register = $pdo->prepare("INSERT INTO pendaftaran_praktikum (id_praktikum, id_mahasiswa) VALUES (?, ?)");
        $stmt_register->execute([$id_praktikum_to_register, $id_mahasiswa]);
        $_SESSION['success_message'] = "Berhasil mendaftar praktikum!";
    }
    // Redirect untuk menghindari re-post form
    header('Location: katalog_praktikum.php');
    exit;
}

// Ambil semua praktikum dan status pendaftaran untuk mahasiswa ini
$sql = "
    SELECT 
        mp.id, 
        mp.kode_praktikum, 
        mp.nama_praktikum,
        pp.id_mahasiswa AS terdaftar
    FROM mata_praktikum mp
    LEFT JOIN pendaftaran_praktikum pp ON mp.id = pp.id_praktikum AND pp.id_mahasiswa = :id_mahasiswa
    ORDER BY mp.nama_praktikum
";
$stmt = $pdo->prepare($sql);
$stmt->execute(['id_mahasiswa' => $id_mahasiswa]);
$katalog_list = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Ambil pesan dari session jika ada, lalu hapus
$success_message = $_SESSION['success_message'] ?? null;
unset($_SESSION['success_message']);

include 'templates/header_mahasiswa.php';
?>



<div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-purple-50">
    <!-- Header Section -->
    <div class="relative overflow-hidden bg-gradient-to-r from-blue-600 to-purple-600 rounded-2xl mb-8 shadow-xl">
        <div class="absolute inset-0 bg-black opacity-10"></div>
        <div class="relative px-8 py-12">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-4xl font-bold text-white mb-2">
                        <i class="ph-bold ph-graduation-cap mr-3"></i>
                        Katalog Praktikum
                    </h1>
                    <p class="text-blue-100 text-lg">Temukan dan daftar praktikum yang sesuai dengan minat Anda</p>
                </div>
                <div class="hidden md:block">
                    <div class="bg-white/20 backdrop-blur-sm rounded-full p-4">
                        <i class="ph-bold ph-flask text-white text-4xl"></i>
                    </div>
                </div>
            </div>
        </div>
        <!-- Decorative Elements -->
        <div class="absolute top-0 right-0 w-32 h-32 bg-white/10 rounded-full -translate-y-16 translate-x-16"></div>
        <div class="absolute bottom-0 left-0 w-20 h-20 bg-white/10 rounded-full translate-y-10 -translate-x-10"></div>
    </div>

    <?php if ($success_message): ?>
        <div class="bg-gradient-to-r from-green-400 to-green-600 text-white p-6 mb-8 rounded-2xl shadow-lg animate-pulse" role="alert">
            <div class="flex items-center">
                <i class="ph-bold ph-check-circle text-2xl mr-3"></i>
                <div>
                    <h4 class="font-bold text-lg">Sukses!</h4>
                    <p class="text-green-100"><?= $success_message ?></p>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Statistics Section -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 font-medium">Total Praktikum</p>
                    <p class="text-2xl font-bold text-gray-900"><?= count($katalog_list) ?></p>
                </div>
                <div class="bg-blue-100 p-3 rounded-full">
                    <i class="ph-bold ph-books text-blue-600 text-xl"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 font-medium">Terdaftar</p>
                    <p class="text-2xl font-bold text-green-600"><?= count(array_filter($katalog_list, fn($p) => $p['terdaftar'])) ?></p>
                </div>
                <div class="bg-green-100 p-3 rounded-full">
                    <i class="ph-bold ph-check-circle text-green-600 text-xl"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 font-medium">Tersedia</p>
                    <p class="text-2xl font-bold text-orange-600"><?= count(array_filter($katalog_list, fn($p) => !$p['terdaftar'])) ?></p>
                </div>
                <div class="bg-orange-100 p-3 rounded-full">
                    <i class="ph-bold ph-clock text-orange-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Praktikum Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        <?php if (empty($katalog_list)): ?>
            <div class="col-span-full">
                <div class="bg-white text-center p-16 rounded-2xl shadow-sm border border-gray-200">
                    <div class="bg-gray-100 rounded-full w-24 h-24 flex items-center justify-center mx-auto mb-6">
                        <i class="ph-bold ph-books-dashed text-4xl text-gray-400"></i>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-700 mb-2">Belum Ada Praktikum</h2>
                    <p class="text-gray-500 mb-6">Praktikum akan segera tersedia. Silakan periksa kembali nanti.</p>
                    <button class="bg-blue-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-blue-700 transition-colors">
                        <i class="ph-bold ph-refresh mr-2"></i>
                        Muat Ulang
                    </button>
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($katalog_list as $praktikum): ?>
                <div class="group bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-xl transition-all duration-300 transform hover:-translate-y-2">
                    <!-- Card Header -->
                    <div class="bg-gradient-to-r from-blue-500 to-purple-500 p-6 text-white relative overflow-hidden">
                        <div class="absolute inset-0 bg-black/10"></div>
                        <div class="relative z-10">
                            <div class="flex items-center justify-between mb-3">
                                <span class="bg-white/20 backdrop-blur-sm px-3 py-1 rounded-full text-sm font-medium">
                                    <?= htmlspecialchars($praktikum['kode_praktikum']) ?>
                                </span>
                                <?php if ($praktikum['terdaftar']): ?>
                                    <div class="bg-green-500 p-2 rounded-full">
                                        <i class="ph-bold ph-check text-white text-sm"></i>
                                    </div>
                                <?php else: ?>
                                    <div class="bg-white/20 p-2 rounded-full">
                                        <i class="ph-bold ph-flask text-white text-sm"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <h3 class="text-lg font-bold leading-tight">
                                <?= htmlspecialchars($praktikum['nama_praktikum']) ?>
                            </h3>
                        </div>
                        <!-- Decorative circle -->
                        <div class="absolute -top-4 -right-4 w-16 h-16 bg-white/10 rounded-full"></div>
                    </div>

                    <!-- Card Body -->
                    <div class="p-6">
                        <div class="flex items-center text-gray-600 mb-4">
                            <i class="ph-bold ph-calendar mr-2"></i>
                            <span class="text-sm">Semester Aktif</span>
                        </div>
                        
                        <!-- Status Badge -->
                        <?php if ($praktikum['terdaftar']): ?>
                            <div class="flex items-center justify-center bg-green-50 text-green-700 p-3 rounded-xl mb-4">
                                <i class="ph-bold ph-check-circle mr-2 text-lg"></i>
                                <span class="font-semibold">Anda Sudah Terdaftar</span>
                            </div>
                        <?php else: ?>
                            <div class="flex items-center justify-center bg-orange-50 text-orange-700 p-3 rounded-xl mb-4">
                                <i class="ph-bold ph-clock mr-2 text-lg"></i>
                                <span class="font-semibold">Tersedia untuk Pendaftaran</span>
                            </div>
                        <?php endif; ?>

                        <!-- Action Button -->
                        <?php if ($praktikum['terdaftar']): ?>
                            <button disabled class="w-full bg-gray-100 text-gray-500 py-3 rounded-xl font-semibold cursor-not-allowed flex items-center justify-center">
                                <i class="ph-bold ph-check mr-2"></i>
                                Sudah Terdaftar
                            </button>
                        <?php else: ?>
                            <form action="katalog_praktikum.php" method="POST">
                                <input type="hidden" name="id_praktikum" value="<?= $praktikum['id'] ?>">
                                <button type="submit" name="daftar_praktikum" class="w-full bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white py-3 rounded-xl font-semibold transition-all duration-300 transform hover:scale-105 flex items-center justify-center group">
                                    <i class="ph-bold ph-plus mr-2 group-hover:rotate-90 transition-transform"></i>
                                    Daftar Sekarang
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    

<?php include 'templates/footer_mahasiswa.php'; ?>