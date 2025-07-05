<?php
session_start();

// 1. Pastikan pengguna sudah login dan merupakan mahasiswa
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'mahasiswa') {
    // Jika tidak, arahkan ke halaman login
    header('Location: login.php');
    exit;
}

// Pastikan ID praktikum ada di URL
if (!isset($_GET['id'])) {
    // Jika tidak ada, kembalikan ke katalog
    header('Location: katalog_praktikum.php');
    exit;
}

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

$id_praktikum = $_GET['id'];
$id_mahasiswa = $_SESSION['user_id'];

try {
    // 2. Cek apakah mahasiswa sudah terdaftar sebelumnya untuk menghindari data ganda
    $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM pendaftaran_praktikum WHERE id_mahasiswa = :id_mahasiswa AND id_praktikum = :id_praktikum");
    $stmt_check->execute([
        ':id_mahasiswa' => $id_mahasiswa,
        ':id_praktikum' => $id_praktikum
    ]);
    
    // 3. Jika belum terdaftar (hasil COUNT = 0), maka daftarkan
    if ($stmt_check->fetchColumn() == 0) {
        $stmt_insert = $pdo->prepare("INSERT INTO pendaftaran_praktikum (id_mahasiswa, id_praktikum) VALUES (:id_mahasiswa, :id_praktikum)");
        $stmt_insert->execute([
            ':id_mahasiswa' => $id_mahasiswa,
            ':id_praktikum' => $id_praktikum
        ]);
    }

    // 4. Arahkan ke halaman 'Praktikum Saya'
    header('Location: praktikum_saya.php');
    exit;

} catch (PDOException $e) {
    // Jika terjadi error (misal: ID praktikum tidak valid), kembalikan ke katalog
    header('Location: katalog_praktikum.php?error=failed');
    exit;
}
?>