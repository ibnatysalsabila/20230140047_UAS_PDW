<?php
session_start(); // Mulai session untuk mengaksesnya

// Hapus semua variabel session
session_unset();

// Hancurkan session
session_destroy();

// --- BAGIAN PENTING ---
// Tentukan nama folder proyek Anda.
// Pastikan namanya sama persis dengan nama folder di htdocs.
$project_folder = 'SistemPengumpulanTugas'; 

// Arahkan ke halaman login yang benar, lengkap dengan nama folder proyek
header("Location: /" . $project_folder . "/login.php");
exit(); // Pastikan script berhenti setelah redirect
?>