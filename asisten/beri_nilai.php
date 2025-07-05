<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'asisten') {
    header('Location: ../login.php');
    exit;
}

$host = 'localhost'; $dbname = 'db_simpraks'; $user = 'root'; $pass = '';
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) { die("Koneksi gagal: " . $e->getMessage()); }

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: dashboard.php?page=laporan_masuk');
    exit;
}

// Ambil data laporan lengkap, termasuk catatan asisten
$stmt = $pdo->prepare("
    SELECT pl.id, pl.file_laporan, pl.tanggal_upload, pl.nilai, pl.catatan_asisten,
           u.nama AS nama_mahasiswa,
           m.judul_modul,
           m.deskripsi, 
           mp.nama_praktikum
    FROM pengumpulan_laporan pl
    JOIN users u ON pl.id_mahasiswa = u.id
    JOIN modul m ON pl.id_modul = m.id
    JOIN mata_praktikum mp ON m.id_praktikum = mp.id
    WHERE pl.id = ?
");
$stmt->execute([$id]);
$laporan = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$laporan) {
    header('Location: dashboard.php?page=laporan_masuk');
    exit;
}

$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nilai = $_POST['nilai'];
    // Ambil data catatan dari form
    $catatan_asisten = trim($_POST['catatan_asisten']);

    if (!is_numeric($nilai) || $nilai < 0 || $nilai > 100) {
        $error_message = 'Nilai harus berupa angka antara 0 dan 100.';
    } else {
        // Update query untuk menyimpan nilai dan catatan
        $sql = "UPDATE pengumpulan_laporan SET nilai = ?, catatan_asisten = ? WHERE id = ?";
        try {
            $update_stmt = $pdo->prepare($sql);
            $update_stmt->execute([$nilai, $catatan_asisten, $id]);
            $success_message = 'Nilai dan catatan berhasil disimpan! Mengarahkan kembali...';
            header("refresh:2;url=dashboard.php?page=laporan_masuk");
        } catch (PDOException $e) {
            $error_message = "Gagal menyimpan data: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beri Nilai Laporan</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <style> body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #F0F4F8; } </style>
</head>
<body>
    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="w-full max-w-xl">
            <div class="bg-white p-8 rounded-xl shadow-lg border border-gray-200">
                <div class="flex items-center space-x-3 mb-6">
                    <div class="p-2 bg-yellow-100 rounded-lg text-yellow-600"><i class="ph-bold ph-star text-2xl"></i></div>
                    <h1 class="text-2xl font-bold text-gray-800">Beri Nilai Laporan</h1>
                </div>

                <?php if ($error_message): ?>
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert"><p><?= $error_message ?></p></div>
                <?php endif; ?>
                <?php if ($success_message): ?>
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert"><p><?= $success_message ?></p></div>
                <?php else: ?>
                <div class="space-y-4 mb-6 p-4 bg-gray-50 rounded-lg border">
                    <div>
                        <label class="text-sm font-medium text-gray-500">Mahasiswa</label>
                        <p class="font-semibold text-gray-800"><?= htmlspecialchars($laporan['nama_mahasiswa']) ?></p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-500">Modul</label>
                        <p class="font-semibold text-gray-800"><?= htmlspecialchars($laporan['judul_modul']) ?></p>
                    </div>
                     <div>
                        <label class="text-sm font-medium text-gray-500">File Laporan</label>
                        <a href="../uploads/laporan/<?= htmlspecialchars($laporan['file_laporan']) ?>" target="_blank" class="inline-flex items-center text-blue-600 hover:text-blue-800 hover:underline mt-1">
                            <i class="ph-bold ph-file-arrow-down mr-2"></i>Lihat/Unduh File
                        </a>
                    </div>
                </div>

                <form action="beri_nilai.php?id=<?= $id ?>" method="POST">
                    <div class="space-y-4">
                        <div>
                            <label for="nilai" class="block text-sm font-medium text-gray-700 mb-1">Nilai (0-100)</label>
                            <input type="number" id="nilai" name="nilai" min="0" max="100" value="<?= htmlspecialchars($laporan['nilai'] ?? '') ?>" placeholder="Masukkan nilai angka" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        </div>
                        <div>
                            <label for="catatan_asisten" class="block text-sm font-medium text-gray-700 mb-1">Catatan / Feedback (Opsional)</label>
                            <textarea id="catatan_asisten" name="catatan_asisten" rows="4" placeholder="Berikan feedback untuk mahasiswa..." class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"><?= htmlspecialchars($laporan['catatan_asisten'] ?? '') ?></textarea>
                        </div>
                    </div>
                    <div class="mt-6 flex items-center space-x-3">
                        <button type="submit" class="w-full px-4 py-2.5 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition">
                            <i class="ph-bold ph-floppy-disk mr-2"></i> Simpan Penilaian
                        </button>
                        <a href="dashboard.php?page=laporan_masuk" class="w-full text-center px-4 py-2.5 bg-gray-200 text-gray-800 font-semibold rounded-lg hover:bg-gray-300 transition">Batal</a>
                    </div>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>