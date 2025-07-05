<?php
require '../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $matkum_id = $_POST['matkum_id'];
    $judul_modul = $_POST['judul_modul'];
    $deskripsi = $_POST['deskripsi'];
    
    // Proses upload file
    $file_name = $_FILES['file_materi']['name'];
    $file_tmp = $_FILES['file_materi']['tmp_name'];
    $target_dir = "../uploads/materi/";
    $unique_file_name = time() . '_' . $file_name;
    
    if (move_uploaded_file($file_tmp, $target_dir . $unique_file_name)) {
        // Jika upload berhasil, insert ke DB
        $stmt = mysqli_prepare($conn, "INSERT INTO modul (matkum_id, judul_modul, deskripsi, file_materi) VALUES (?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "isss", $matkum_id, $judul_modul, $deskripsi, $unique_file_name);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        header("Location: kelola_modul.php?matkum_id=" . $matkum_id);
    } else {
        echo "Gagal mengunggah file.";
    }
}
exit();
?>