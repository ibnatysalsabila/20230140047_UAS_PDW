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
    header('Location: dashboard.php?page=manajemen_modul');
    exit;
}

// 1. Ambil nama file materi dari database sebelum dihapus
$stmt = $pdo->prepare("SELECT file_materi FROM modul WHERE id = ?");
$stmt->execute([$id]);
$modul = $stmt->fetch(PDO::FETCH_ASSOC);

if ($modul) {
    // 2. Hapus file fisik dari server
    $file_path = '../uploads/materi/' . $modul['file_materi'];
    if (file_exists($file_path)) {
        unlink($file_path);
    }

    // 3. Hapus record dari database
    $stmt_delete = $pdo->prepare("DELETE FROM modul WHERE id = ?");
    $stmt_delete->execute([$id]);
}

// 4. Redirect kembali ke halaman manajemen modul
header('Location: dashboard.php?page=manajemen_modul');
exit;
?>