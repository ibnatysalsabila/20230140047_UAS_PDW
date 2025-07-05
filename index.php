<?php require 'templates/header.php'; ?>

<h1 class="text-3xl font-bold text-gray-800 mb-6">Katalog Mata Praktikum</h1>
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
    <?php
    $result = mysqli_query($conn, "SELECT * FROM mata_praktikum ORDER BY nama_matkum ASC");
    if (mysqli_num_rows($result) > 0) {
        while ($matkum = mysqli_fetch_assoc($result)) {
    ?>
        <div class="bg-white p-6 rounded-lg shadow-lg hover:shadow-xl transition-shadow duration-300">
            <h2 class="text-xl font-semibold mb-2"><?php echo htmlspecialchars($matkum['nama_matkum']); ?></h2>
            <p class="text-gray-600 mb-2 font-mono"><?php echo htmlspecialchars($matkum['kode_matkum']); ?></p>
            <p class="text-gray-700 mb-4"><?php echo nl2br(htmlspecialchars($matkum['deskripsi'])); ?></p>
            
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'mahasiswa'): ?>
                <a href="/Sistem_Pengumpulan_Tugas/mahasiswa/daftar_praktikum.php?id=<?php echo $matkum['id']; ?>" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded">
                    Daftar Praktikum
                </a>
            <?php endif; ?>
        </div>
    <?php
        }
    } else {
        echo "<p class='text-gray-500'>Belum ada mata praktikum yang tersedia.</p>";
    }
    ?>
</div>

<?php require 'templates/footer.php'; ?>