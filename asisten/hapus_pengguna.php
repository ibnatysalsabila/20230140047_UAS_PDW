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
    header('Location: dashboard.php?page=kelola_pengguna');
    exit;
}

// Keamanan: Mencegah asisten menghapus akunnya sendiri
if ($id == $_SESSION['user_id']) {
    // Mungkin bisa ditambahkan notifikasi error di session, tapi untuk sekarang redirect saja
    header('Location: dashboard.php?page=kelola_pengguna&error=self_delete');
    exit;
}

// Hapus record dari database
$stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
$stmt->execute([$id]);

// Redirect kembali ke halaman manajemen pengguna
header('Location: dashboard.php?page=kelola_pengguna');
exit;
?>