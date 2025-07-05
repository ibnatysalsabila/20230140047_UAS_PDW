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
if (!$id) { header('Location: dashboard.php?page=manajemen_modul'); exit; }

// Ambil data modul yang akan diedit
$stmt = $pdo->prepare("SELECT * FROM modul WHERE id = ?");
$stmt->execute([$id]);
$modul = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$modul) { header('Location: dashboard.php?page=manajemen_modul'); exit; }

$praktikum_options = $pdo->query("SELECT id, nama_praktikum FROM mata_praktikum ORDER BY nama_praktikum")->fetchAll(PDO::FETCH_ASSOC);

$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul_modul = trim($_POST['judul_modul']);
    $deskripsi = trim($_POST['deskripsi']);
    $id_praktikum = $_POST['id_praktikum'];
    $old_file_materi = $_POST['old_file_materi'];
    $file_materi_name = $old_file_materi; // Default ke file lama

    if (empty($judul_modul) || empty($deskripsi) || empty($id_praktikum)) {
        $error_message = 'Semua field wajib diisi.';
    } else {
        // Proses upload file baru jika ada
        if (isset($_FILES['file_materi']) && $_FILES['file_materi']['error'] == 0) {
            $target_dir = "../uploads/materi/";
            $file_materi_name = uniqid() . '-' . basename($_FILES["file_materi"]["name"]);
            $target_file = $target_dir . $file_materi_name;
            $file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

            if ($file_type != "pdf") {
                $error_message = "Hanya file PDF yang diizinkan.";
            } elseif ($_FILES["file_materi"]["size"] > 5000000) {
                $error_message = "Ukuran file terlalu besar (maks 5MB).";
            } else {
                if (move_uploaded_file($_FILES["file_materi"]["tmp_name"], $target_file)) {
                    // Hapus file lama jika upload berhasil
                    if (!empty($old_file_materi) && file_exists($target_dir . $old_file_materi)) {
                        unlink($target_dir . $old_file_materi);
                    }
                } else {
                    $error_message = "Terjadi kesalahan saat mengupload file baru.";
                    $file_materi_name = $old_file_materi; // Gagal upload, kembali ke file lama
                }
            }
        }

        if (empty($error_message)) {
            $sql = "UPDATE modul SET judul_modul = ?, deskripsi = ?, file_materi = ?, id_praktikum = ? WHERE id = ?";
            try {
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$judul_modul, $deskripsi, $file_materi_name, $id_praktikum, $id]);
                $success_message = 'Modul berhasil diperbarui! Mengarahkan kembali...';
                header("refresh:2;url=dashboard.php?page=manajemen_modul");
            } catch (PDOException $e) {
                $error_message = "Gagal memperbarui data: " . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Modul</title>
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
                    <div class="p-2 bg-yellow-100 rounded-lg text-yellow-600"><i class="ph-bold ph-pencil-simple text-2xl"></i></div>
                    <h1 class="text-2xl font-bold text-gray-800">Edit Modul</h1>
                </div>

                <?php if ($error_message): ?>
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert"><p><?= $error_message ?></p></div>
                <?php endif; ?>
                <?php if ($success_message): ?>
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert"><p><?= $success_message ?></p></div>
                <?php else: ?>
                <form action="edit_modul.php?id=<?= $id ?>" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="old_file_materi" value="<?= htmlspecialchars($modul['file_materi']) ?>">
                    <div class="space-y-4">
                        <div>
                            <label for="id_praktikum" class="block text-sm font-medium text-gray-700 mb-1">Mata Praktikum</label>
                            <select id="id_praktikum" name="id_praktikum" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                                <?php foreach ($praktikum_options as $p): ?>
                                <option value="<?= $p['id'] ?>" <?= ($p['id'] == $modul['id_praktikum']) ? 'selected' : '' ?>><?= htmlspecialchars($p['nama_praktikum']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label for="judul_modul" class="block text-sm font-medium text-gray-700 mb-1">Judul Modul</label>
                            <input type="text" id="judul_modul" name="judul_modul" value="<?= htmlspecialchars($modul['judul_modul']) ?>" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        </div>
                        <div>
                            <label for="deskripsi" class="block text-sm font-medium text-gray-700 mb-1">Deskripsi Singkat</label>
                            <textarea id="deskripsi" name="deskripsi" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required><?= htmlspecialchars($modul['deskripsi']) ?></textarea>
                        </div>
                        <div>
                            <label for="file_materi" class="block text-sm font-medium text-gray-700 mb-1">Ganti File Materi (Opsional)</label>
                            <p class="text-xs text-gray-500 mb-2">File saat ini: <a href="../uploads/materi/<?= htmlspecialchars($modul['file_materi']) ?>" target="_blank" class="text-blue-600 hover:underline"><?= htmlspecialchars($modul['file_materi']) ?></a></p>
                            <input type="file" id="file_materi" name="file_materi" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" accept=".pdf">
                        </div>
                    </div>
                    <div class="mt-6 flex items-center space-x-3">
                        <button type="submit" class="w-full px-4 py-2.5 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition">
                            <i class="ph-bold ph-floppy-disk mr-2"></i> Simpan Perubahan
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