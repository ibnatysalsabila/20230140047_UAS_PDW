<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'asisten') {
    header('Location: ../login.php');
    exit;
}

$host = 'localhost';
$dbname = 'db_simpraks';
$user = 'root';
$pass = '';
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Koneksi gagal: " . $e->getMessage());
}

$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kode_praktikum = trim($_POST['kode_praktikum']);
    $nama_praktikum = trim($_POST['nama_praktikum']);

    if (empty($kode_praktikum) || empty($nama_praktikum)) {
        $error_message = 'Semua field wajib diisi.';
    } else {
        // Cek apakah kode praktikum sudah ada
        $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM mata_praktikum WHERE kode_praktikum = ?");
        $stmt_check->execute([$kode_praktikum]);
        if ($stmt_check->fetchColumn() > 0) {
            $error_message = 'Kode Praktikum sudah ada. Harap gunakan kode yang lain.';
        } else {
            // Jika semua validasi lolos, masukkan data ke database
            $sql = "INSERT INTO mata_praktikum (kode_praktikum, nama_praktikum) VALUES (?, ?)";
            try {
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$kode_praktikum, $nama_praktikum]);
                $success_message = 'Mata praktikum berhasil ditambahkan! Mengarahkan kembali...';
                header("refresh:2;url=dashboard.php?page=manajemen_praktikum");
            } catch (PDOException $e) {
                $error_message = "Gagal menyimpan data: " . $e->getMessage();
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
    <title>Tambah Mata Praktikum</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <style> body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #F0F4F8; } </style>
</head>
<body>
    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="w-full max-w-md">
            <div class="bg-white p-8 rounded-xl shadow-lg border border-gray-200">
                <div class="flex items-center space-x-3 mb-6">
                    <div class="p-2 bg-blue-100 rounded-lg text-blue-600"><i class="ph-bold ph-flask text-2xl"></i></div>
                    <h1 class="text-2xl font-bold text-gray-800">Tambah Praktikum</h1>
                </div>

                <?php if ($error_message): ?>
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert"><p><?= $error_message ?></p></div>
                <?php endif; ?>
                <?php if ($success_message): ?>
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert"><p><?= $success_message ?></p></div>
                <?php else: ?>
                <form action="tambah_praktikum.php" method="POST">
                    <div class="space-y-4">
                        <div>
                            <label for="kode_praktikum" class="block text-sm font-medium text-gray-700 mb-1">Kode Praktikum</label>
                            <input type="text" id="kode_praktikum" name="kode_praktikum" placeholder="" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        </div>
                        <div>
                            <label for="nama_praktikum" class="block text-sm font-medium text-gray-700 mb-1">Nama Praktikum</label>
                            <input type="text" id="nama_praktikum" name="nama_praktikum" placeholder="" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        </div>
                    </div>
                    <div class="mt-6 flex items-center space-x-3">
                        <button type="submit" class="w-full px-4 py-2.5 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition">
                            <i class="ph-bold ph-plus mr-2"></i> Tambah
                        </button>
                        <a href="dashboard.php?page=manajemen_praktikum" class="w-full text-center px-4 py-2.5 bg-gray-200 text-gray-800 font-semibold rounded-lg hover:bg-gray-300 transition">Batal</a>
                    </div>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>