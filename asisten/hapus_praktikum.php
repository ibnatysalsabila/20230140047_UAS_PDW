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
    header('Location: dashboard.php?page=manajemen_praktikum');
    exit;
}

// PENTING: Cek apakah praktikum masih digunakan di tabel modul
$stmt_check = $pdo->prepare("SELECT COUNT(*) FROM modul WHERE id_praktikum = ?");
$stmt_check->execute([$id]);
if ($stmt_check->fetchColumn() > 0) {
    // Jika masih digunakan, jangan hapus. Beri notifikasi.
    // Anda bisa menggunakan session untuk menampilkan pesan error di halaman utama.
    $_SESSION['error_message'] = "Gagal menghapus! Mata praktikum masih digunakan oleh modul.";
    header('Location: dashboard.php?page=manajemen_praktikum');
    exit;
}

// Jika tidak ada relasi, lanjutkan proses hapus
$stmt = $pdo->prepare("DELETE FROM mata_praktikum WHERE id = ?");
$stmt->execute([$id]);

$_SESSION['success_message'] = "Mata praktikum berhasil dihapus.";
header('Location: dashboard.php?page=manajemen_praktikum');
exit;
?>