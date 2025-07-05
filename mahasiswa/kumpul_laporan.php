<?php
session_start();

// =======================================================================
// SELURUH LOGIKA PHP DIPINDAHKAN KE ATAS, SEBELUM HTML DIMULAI
// =======================================================================

// --- KONEKSI DATABASE ---
$host = 'localhost'; $dbname = 'db_simpraks'; $user = 'root'; $pass = '';
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Koneksi gagal: " . $e->getMessage());
}

// --- INISIALISASI VARIABEL & VALIDASI AWAL ---
$id_modul = $_GET['id_modul'] ?? null;
$id_mahasiswa = $_SESSION['user_id'] ?? null;
$error_message = '';
$success_message = '';

// Jika user atau modul tidak teridentifikasi, langsung redirect
if (!$id_mahasiswa) {
    header('Location: ../login.php');
    exit;
}
if (!$id_modul) {
    header('Location: praktikum_saya.php');
    exit;
}

// --- LOGIKA PEMROSESAN FORM (POST REQUEST) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Cek dulu apakah sudah pernah submit, untuk mencegah submit ulang via inspect element
    $stmt_check_submit = $pdo->prepare("SELECT COUNT(*) FROM pengumpulan_laporan WHERE id_modul = ? AND id_mahasiswa = ?");
    $stmt_check_submit->execute([$id_modul, $id_mahasiswa]);
    if ($stmt_check_submit->fetchColumn() == 0) {
        if (isset($_FILES['file_laporan']) && $_FILES['file_laporan']['error'] == 0) {
            $target_dir = "../uploads/laporan/";
            
            // Membuat nama file yang lebih aman dan unik
            $original_filename = basename($_FILES["file_laporan"]["name"]);
            $file_type = strtolower(pathinfo($original_filename, PATHINFO_EXTENSION));
            $file_name = "laporan_" . $id_mahasiswa . "_" . $id_modul . "_" . time() . "." . $file_type;
            $target_file = $target_dir . $file_name;

            $allowed_types = ['pdf', 'doc', 'docx'];

            if (!in_array($file_type, $allowed_types)) {
                $error_message = "Hanya file dengan format PDF, DOC, atau DOCX yang diizinkan.";
            } elseif ($_FILES["file_laporan"]["size"] > 5000000) { // Batas 5MB
                $error_message = "Ukuran file terlalu besar. Maksimal 5MB.";
            } else {
                // Pastikan folder uploads ada
                if (!is_dir($target_dir)) {
                    mkdir($target_dir, 0755, true);
                }
                
                if (move_uploaded_file($_FILES["file_laporan"]["tmp_name"], $target_file)) {
                    $sql = "INSERT INTO pengumpulan_laporan (id_mahasiswa, id_modul, file_laporan, tanggal_upload) VALUES (?, ?, ?, NOW())";
                    try {
                        $stmt = $pdo->prepare($sql);
                        $stmt->execute([$id_mahasiswa, $id_modul, $file_name]);
                        
                        // Set session untuk pesan sukses dan redirect
                        $_SESSION['success_flash'] = 'Laporan berhasil dikumpulkan!';
                        header("Location: praktikum_saya.php");
                        exit;
                    } catch (PDOException $e) {
                        $error_message = "Gagal menyimpan data ke database: " . $e->getMessage();
                    }
                } else {
                    $error_message = "Terjadi kesalahan saat mengupload file Anda.";
                }
            }
        } else {
            $error_message = "Anda harus memilih file untuk diupload.";
        }
    }
}

// --- AMBIL DATA UNTUK DITAMPILKAN DI HALAMAN ---
$stmt_modul = $pdo->prepare("
    SELECT m.judul_modul, mp.nama_praktikum 
    FROM modul m 
    JOIN mata_praktikum mp ON m.id_praktikum = mp.id 
    WHERE m.id = ?
");
$stmt_modul->execute([$id_modul]);
$modul = $stmt_modul->fetch(PDO::FETCH_ASSOC);

if (!$modul) {
    header('Location: praktikum_saya.php');
    exit;
}

// Cek apakah mahasiswa sudah pernah mengumpulkan laporan untuk modul ini
$stmt_check = $pdo->prepare("SELECT COUNT(*) FROM pengumpulan_laporan WHERE id_modul = ? AND id_mahasiswa = ?");
$stmt_check->execute([$id_modul, $id_mahasiswa]);
$already_submitted = $stmt_check->fetchColumn() > 0;


// =======================================================================
// BARU MULAI TAMPILKAN HTML SETELAH SEMUA LOGIKA SELESAI
// =======================================================================
include 'templates/header_mahasiswa.php';
?>

<div class="flex justify-center my-8">
    <div class="w-full max-w-2xl">
        <div class="bg-white p-8 rounded-xl shadow-lg border border-gray-200">
            <div class="flex items-center space-x-4 mb-6">
                <div class="p-3 bg-blue-100 rounded-lg text-blue-600">
                    <i class="ph-bold ph-arrow-fat-up text-2xl"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">Kumpul Laporan</h1>
                    <p class="text-gray-500"><?= htmlspecialchars($modul['nama_praktikum']) ?>: <?= htmlspecialchars($modul['judul_modul']) ?></p>
                </div>
            </div>

            <?php if ($already_submitted): ?>
                <div class="bg-green-100 border-l-4 border-green-500 text-green-800 p-4 rounded-r-lg" role="alert">
                    <p class="font-bold">Berhasil!</p>
                    <p>Anda sudah mengumpulkan laporan untuk modul ini. Anda akan diarahkan kembali.</p>
                </div>
                <script>
                    setTimeout(function() {
                        window.location.href = 'praktikum_saya.php';
                    }, 3000); // Redirect setelah 3 detik
                </script>
            <?php else: ?>
                <?php if ($error_message): ?>
                    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-r-lg" role="alert">
                        <p class="font-bold">Gagal</p>
                        <p><?= $error_message ?></p>
                    </div>
                <?php endif; ?>

                <form action="kumpul_laporan.php?id_modul=<?= $id_modul ?>" method="POST" enctype="multipart/form-data">
                    <div class="mb-6">
                        <label for="file_laporan" class="block text-sm font-medium text-gray-700 mb-2">Pilih File Laporan (PDF, DOC, DOCX, maks 5MB)</label>
                        <input type="file" id="file_laporan" name="file_laporan" class="w-full text-sm text-gray-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 transition-colors" accept=".pdf,.doc,.docx" required>
                    </div>
                    <div class="mt-6 flex items-center space-x-3">
                        <button type="submit" class="w-full px-4 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200">
                            <i class="ph-bold ph-upload-simple mr-2"></i>Kumpul Laporan
                        </button>
                        <a href="praktikum_saya.php" class="w-full text-center px-4 py-3 bg-gray-200 text-gray-800 font-semibold rounded-lg hover:bg-gray-300 transition-all duration-200">
                            Batal
                        </a>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'templates/footer_mahasiswa.php'; ?>