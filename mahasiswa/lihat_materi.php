<?php
session_start();
// Siapapun yang sudah login (asisten/mahasiswa) bisa melihat materi
if (!isset($_SESSION['user_id'])) {
    die("Akses ditolak. Silakan login terlebih dahulu.");
}

// Ambil ID modul dari URL
$id_modul = $_GET['id_modul'] ?? null;
if (!$id_modul) {
    die("Error: ID Modul tidak ditemukan.");
}

// Koneksi ke database
$host = 'localhost';
$dbname = 'db_simpraks';
$user = 'root';
$pass = '';
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Koneksi database gagal: " . $e->getMessage());
}

// Ambil nama file dari database berdasarkan ID modul
$stmt = $pdo->prepare("SELECT file_materi FROM modul WHERE id = ?");
$stmt->execute([$id_modul]);
$file = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$file || empty($file['file_materi'])) {
    die("Maaf, file materi tidak ditemukan di database.");
}

$filename = $file['file_materi'];
$filepath = 'uploads/materi/' . $filename;

// Cek apakah file benar-benar ada di server
if (file_exists($filepath)) {
    // Atur header untuk menampilkan file PDF di browser
    header('Content-Type: application/pdf');
    header('Content-Disposition: inline; filename="' . basename($filepath) . '"');
    header('Content-Transfer-Encoding: binary');
    header('Accept-Ranges: bytes');
    header('Content-Length: ' . filesize($filepath));
    
    // Baca dan tampilkan file
    readfile($filepath);
    exit;
} else {
    die("Maaf, file tidak dapat ditemukan di server. Path: " . $filepath);
}
?>