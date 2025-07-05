<?php
$host = 'localhost'; $dbname = 'db_simpraks'; $user = 'root'; $pass = '';
try { $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass); $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); } catch (PDOException $e) { die("Koneksi gagal: " . $e->getMessage()); }

$total_modul = $pdo->query("SELECT COUNT(*) FROM modul")->fetchColumn();
$total_laporan = $pdo->query("SELECT COUNT(*) FROM pengumpulan_laporan")->fetchColumn();
$laporan_belum_dinilai = $pdo->query("SELECT COUNT(*) FROM pengumpulan_laporan WHERE nilai IS NULL")->fetchColumn();
$total_pengguna = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
?>
<div>
    <h2 class="text-xl font-semibold text-gray-700 mb-6">Ringkasan Sistem</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-gray-500 text-sm font-medium">Total Modul</p>
                    <p class="text-3xl font-bold text-gray-800 mt-2"><?= $total_modul ?></p>
                </div>
                <div class="p-3 rounded-full bg-blue-100 text-blue-600"><i class="ph-bold ph-books text-2xl"></i></div>
            </div>
        </div>
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200">
             <div class="flex justify-between items-start">
                <div>
                    <p class="text-gray-500 text-sm font-medium">Laporan Masuk</p>
                    <p class="text-3xl font-bold text-gray-800 mt-2"><?= $total_laporan ?></p>
                </div>
                <div class="p-3 rounded-full bg-green-100 text-green-600"><i class="ph-bold ph-paper-plane-tilt text-2xl"></i></div>
            </div>
        </div>
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-gray-500 text-sm font-medium">Belum Dinilai</p>
                    <p class="text-3xl font-bold text-gray-800 mt-2"><?= $laporan_belum_dinilai ?></p>
                </div>
                <div class="p-3 rounded-full bg-yellow-100 text-yellow-600"><i class="ph-bold ph-clock text-2xl"></i></div>
            </div>
        </div>
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-gray-500 text-sm font-medium">Total Pengguna</p>
                    <p class="text-3xl font-bold text-gray-800 mt-2"><?= $total_pengguna ?></p>
                </div>
                <div class="p-3 rounded-full bg-purple-100 text-purple-600"><i class="ph-bold ph-users-three text-2xl"></i></div>
            </div>
        </div>
    </div>
</div>