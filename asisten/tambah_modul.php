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

$praktikum_options = $pdo->query("SELECT id, nama_praktikum FROM mata_praktikum ORDER BY nama_praktikum")->fetchAll(PDO::FETCH_ASSOC);

$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul_modul = trim($_POST['judul_modul']);
    $deskripsi = trim($_POST['deskripsi']);
    $id_praktikum = $_POST['id_praktikum'];
    $file_materi_name = '';

    if (empty($judul_modul) || empty($deskripsi) || empty($id_praktikum)) {
        $error_message = 'Semua field wajib diisi.';
    } else {
        if (isset($_FILES['file_materi']) && $_FILES['file_materi']['error'] == 0) {
            $target_dir = "../uploads/materi/";
            // HANYA AMBIL NAMA FILE, BUKAN PATH
            $file_materi_name = uniqid() . '-' . basename($_FILES["file_materi"]["name"]);
            $target_file = $target_dir . $file_materi_name;
            
            if (move_uploaded_file($_FILES["file_materi"]["tmp_name"], $target_file)) {
                $sql = "INSERT INTO modul (judul_modul, deskripsi, file_materi, id_praktikum) VALUES (?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                // SIMPAN HANYA NAMA FILE KE DB
                $stmt->execute([$judul_modul, $deskripsi, $file_materi_name, $id_praktikum]);
                $success_message = 'Modul berhasil ditambahkan! Mengarahkan kembali...';
                header("refresh:2;url=dashboard.php?page=manajemen_modul");
            } else {
                $error_message = "Terjadi kesalahan saat mengupload file.";
            }
        } else {
             $error_message = "File materi wajib diupload.";
        }
    }
}
// Bagian HTML Form tetap sama, tidak perlu diubah.
// ... (Salin bagian HTML dari file tambah_modul.php sebelumnya) ...
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Modul</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <style> body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #F0F4F8; } </style>
</head>
<body>
    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="w-full max-w-2xl">
            <div class="bg-white p-8 rounded-xl shadow-lg border border-gray-200">
                <div class="flex items-center space-x-3 mb-6">
                    <div class="p-2 bg-blue-100 rounded-lg text-blue-600"><i class="ph-bold ph-books text-2xl"></i></div>
                    <h1 class="text-2xl font-bold text-gray-800">Tambah Modul Baru</h1>
                </div>

                <?php if ($error_message): ?>
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert"><p><?= $error_message ?></p></div>
                <?php endif; ?>
                <?php if ($success_message): ?>
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert"><p><?= $success_message ?></p></div>
                <?php else: ?>
                <form action="tambah_modul.php" method="POST" enctype="multipart/form-data">
                    <div class="space-y-4">
                        <div>
                            <label for="id_praktikum" class="block text-sm font-medium text-gray-700 mb-1">Mata Praktikum</label>
                            <select id="id_praktikum" name="id_praktikum" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                                <option value="">Pilih Praktikum</option>
                                <?php foreach ($praktikum_options as $p): ?>
                                <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['nama_praktikum']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label for="judul_modul" class="block text-sm font-medium text-gray-700 mb-1">Judul Modul</label>
                            <input type="text" id="judul_modul" name="judul_modul" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        </div>
                        <div>
                            <label for="deskripsi" class="block text-sm font-medium text-gray-700 mb-1">Deskripsi Singkat</label>
                            <textarea id="deskripsi" name="deskripsi" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required></textarea>
                        </div>
                        <div>
                            <label for="file_materi" class="block text-sm font-medium text-gray-700 mb-1">File Materi (PDF, Docx, dll)</label>
                            <input type="file" id="file_materi" name="file_materi" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" required>
                        </div>
                    </div>
                    <div class="mt-6 flex items-center space-x-3">
                        <button type="submit" class="w-full px-4 py-2.5 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition">
                            <i class="ph-bold ph-plus mr-2"></i> Tambah Modul
                        </button>
                        <a href="dashboard.php?page=manajemen_modul" class="w-full text-center px-4 py-2.5 bg-gray-200 text-gray-800 font-semibold rounded-lg hover:bg-gray-300 transition">Batal</a>
                    </div>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>