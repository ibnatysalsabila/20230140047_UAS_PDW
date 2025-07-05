<?php
session_start();

require_once 'templates/header_mahasiswa.php'; // SESUAIKAN PATH INI!

// Pastikan pengguna sudah login dan merupakan mahasiswa
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'mahasiswa') {
    header('Location: login.php');
    exit;
}

// Pastikan ID praktikum ada di URL
if (!isset($_GET['id'])) {
    header('Location: praktikum_saya.php');
    exit;
}

$id_praktikum = $_GET['id'];
$id_mahasiswa = $_SESSION['user_id'];

// --- KONEKSI DATABASE ---
$host = 'localhost';
$dbname = 'db_simpraks'; // Nama database baru yang konsisten
$user = 'root';
$pass = '';
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Koneksi ke database gagal: " . $e->getMessage());
}

try {
    // Keamanan: Pastikan mahasiswa ini terdaftar di praktikum yang akan diakses
    $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM pendaftaran_praktikum WHERE id_mahasiswa = :id_mahasiswa AND id_praktikum = :id_praktikum");
    $stmt_check->execute([':id_mahasiswa' => $id_mahasiswa, ':id_praktikum' => $id_praktikum]);
    if ($stmt_check->fetchColumn() == 0) {
        // Jika tidak terdaftar, lempar kembali ke halaman praktikum saya
        header('Location: praktikum_saya.php');
        exit;
    }

    // Ambil detail mata praktikum
    $stmt_praktikum = $pdo->prepare("SELECT nama_praktikum FROM mata_praktikum WHERE id = :id");
    $stmt_praktikum->execute([':id' => $id_praktikum]);
    $praktikum = $stmt_praktikum->fetch(PDO::FETCH_ASSOC);

    // Ambil semua modul untuk praktikum ini
    $stmt_modul = $pdo->prepare("SELECT * FROM modul WHERE id_praktikum = :id_praktikum ORDER BY id ASC");
    $stmt_modul->execute([':id_praktikum' => $id_praktikum]);
    $modul_list = $stmt_modul->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}

// Persiapan untuk header template
$pageTitle = 'Detail Praktikum';
$activePage = 'praktikum_saya';
// require_once 'templates/header_mahasiswa.php'; 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle . ' - ' . htmlspecialchars($praktikum['nama_praktikum']) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>

    <div class="container mx-auto mt-4 p-4">
        <div class="bg-gradient-to-r from-blue-500 to-cyan-400 text-white p-8 rounded-xl shadow-lg mb-8">
            <a href="praktikum_saya.php" class="text-sm opacity-90 hover:underline mb-2 inline-block">&larr; Kembali ke Praktikum Saya</a>
            <h1 class="text-3xl font-bold"><?= htmlspecialchars($praktikum['nama_praktikum']) ?></h1>
            <p class="mt-2 opacity-90">Daftar modul, materi, dan pengumpulan laporan.</p>
        </div>

        <div class="space-y-6">
            <?php if (empty($modul_list)): ?>
                <div class="bg-white p-6 rounded-xl shadow-md text-center">
                    <p class="text-gray-500">Belum ada modul yang ditambahkan oleh asisten untuk praktikum ini.</p>
                </div>
            <?php else: ?>
                <?php foreach ($modul_list as $modul): ?>
                    <?php
                    // Untuk setiap modul, cek apakah mahasiswa sudah mengumpulkan laporan
                    $stmt_laporan = $pdo->prepare("SELECT * FROM pengumpulan_laporan WHERE id_modul = :id_modul AND id_mahasiswa = :id_mahasiswa");
                    $stmt_laporan->execute([':id_modul' => $modul['id'], ':id_mahasiswa' => $id_mahasiswa]);
                    $laporan = $stmt_laporan->fetch(PDO::FETCH_ASSOC);
                    ?>
                    <div class="bg-white p-6 rounded-xl shadow-md">
                        <h3 class="text-2xl font-bold text-gray-800 mb-4"><?= htmlspecialchars($modul['judul_modul']) ?></h3>
                        
                        <div class="mb-4 pb-4 border-b border-gray-200">
                            <h4 class="text-lg font-semibold text-gray-700 mb-2">Materi Praktikum</h4>
                            <?php if (!empty($modul['file_materi'])): ?>
                                <a href="asisten/<?= htmlspecialchars($modul['file_materi']) ?>" download class="inline-flex items-center px-4 py-2 bg-blue-100 text-blue-800 rounded-lg hover:bg-blue-200 text-sm font-medium">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                    Unduh Materi
                                </a>
                            <?php else: ?>
                                <p class="text-gray-500 text-sm">Materi belum tersedia.</p>
                            <?php endif; ?>
                        </div>

                        <div>
                            <h4 class="text-lg font-semibold text-gray-700 mb-2">Laporan & Penilaian</h4>
                            <?php if ($laporan): // Jika sudah mengumpulkan laporan ?>
                                <div class="bg-green-50 border-l-4 border-green-400 text-green-800 p-4 rounded-r-lg">
                                    <p class="font-bold">Laporan Telah Dikumpulkan</p>
                                    <p class="text-sm">File Anda: <a href="<?= htmlspecialchars($laporan['file_laporan']) ?>" target="_blank" class="underline"><?= basename($laporan['file_laporan']) ?></a></p>
                                </div>
                                <div class="mt-4 grid grid-cols-2 gap-4">
                                    <div>
                                        <h5 class="font-semibold text-gray-600">Nilai:</h5>
                                        <p class="text-3xl font-bold <?= $laporan['nilai'] !== null ? 'text-blue-600' : 'text-gray-400' ?>">
                                            <?= $laporan['nilai'] ?? '...' ?>
                                        </p>
                                    </div>
                                    <div>
                                        <h5 class="font-semibold text-gray-600">Feedback Asisten:</h5>
                                        <p class="text-gray-600 italic text-sm">
                                            <?= !empty($laporan['feedback']) ? htmlspecialchars($laporan['feedback']) : 'Belum ada feedback.' ?>
                                        </p>
                                    </div>
                                </div>
                            <?php else: // Jika belum, tampilkan form upload ?>
                                <form action="kumpul_laporan.php" method="POST" enctype="multipart/form-data">
                                    <input type="hidden" name="id_modul" value="<?= $modul['id'] ?>">
                                    <input type="hidden" name="id_praktikum" value="<?= $id_praktikum ?>">
                                    <div>
                                        <label for="file_laporan_<?= $modul['id'] ?>" class="block text-sm font-medium text-gray-700">Unggah File Laporan Anda:</label>
                                        <input type="file" name="file_laporan" id="file_laporan_<?= $modul['id'] ?>" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200" required>
                                    </div>
                                    <button type="submit" class="mt-3 px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 text-sm font-medium">Kumpulkan Laporan</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

<?php 
// require_once 'templates/footer_mahasiswa.php'; 
?>
</body>
</html>