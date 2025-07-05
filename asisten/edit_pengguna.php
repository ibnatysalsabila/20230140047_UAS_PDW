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
if (!$id) { header('Location: dashboard.php?page=kelola_pengguna'); exit; }

// Ambil data pengguna yang akan diedit
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$id]);
$user_data = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$user_data) { header('Location: dashboard.php?page=kelola_pengguna'); exit; }

$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama']);
    $email = trim($_POST['email']);
    $password = $_POST['password']; // Password baru (opsional)
    $role = $_POST['role'];

    if (empty($nama) || empty($email) || empty($role)) {
        $error_message = 'Nama, Email, dan Role wajib diisi.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = 'Format email tidak valid.';
    } else {
        // Cek duplikasi email (kecuali email pengguna itu sendiri)
        $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ? AND id != ?");
        $stmt_check->execute([$email, $id]);
        if ($stmt_check->fetchColumn() > 0) {
            $error_message = 'Email sudah terdaftar oleh pengguna lain.';
        } else {
            // Logika update password: hanya jika diisi
            if (!empty($password)) {
                $hashed_password = password_hash($password, PASSWORD_BCRYPT);
                $sql = "UPDATE users SET nama = ?, email = ?, password = ?, role = ? WHERE id = ?";
                $params = [$nama, $email, $hashed_password, $role, $id];
            } else {
                // Jika password tidak diisi, jangan update password
                $sql = "UPDATE users SET nama = ?, email = ?, role = ? WHERE id = ?";
                $params = [$nama, $email, $role, $id];
            }

            if (empty($error_message)) {
                try {
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute($params);
                    $success_message = 'Data pengguna berhasil diperbarui! Mengarahkan kembali...';
                    header("refresh:2;url=dashboard.php?page=kelola_pengguna");
                } catch (PDOException $e) {
                    $error_message = "Gagal memperbarui data: " . $e->getMessage();
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Pengguna</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <style> body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #F0F4F8; } </style>
</head>
<body>
     <div class="min-h-screen flex items-center justify-center p-4">
        <div class="w-full max-w-md">
            <div class="bg-white p-8 rounded-xl shadow-lg border border-gray-200">
                <div class="flex items-center space-x-3 mb-6">
                    <div class="p-2 bg-yellow-100 rounded-lg text-yellow-600"><i class="ph-bold ph-user-circle-gear text-2xl"></i></div>
                    <h1 class="text-2xl font-bold text-gray-800">Edit Pengguna</h1>
                </div>

                <?php if ($error_message): ?>
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert"><p><?= $error_message ?></p></div>
                <?php endif; ?>
                <?php if ($success_message): ?>
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert"><p><?= $success_message ?></p></div>
                <?php else: ?>
                <form action="edit_pengguna.php?id=<?= $id ?>" method="POST">
                    <div class="space-y-4">
                        <div>
                            <label for="nama" class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
                            <input type="text" id="nama" name="nama" value="<?= htmlspecialchars($user_data['nama']) ?>" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        </div>
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                            <input type="email" id="email" name="email" value="<?= htmlspecialchars($user_data['email']) ?>" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        </div>
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password Baru (Opsional)</label>
                            <input type="password" id="password" name="password" placeholder="Isi hanya jika ingin ganti password" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label for="role" class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                            <select id="role" name="role" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                                <option value="asisten" <?= ($user_data['role'] == 'asisten') ? 'selected' : '' ?>>Asisten</option>
                                <option value="mahasiswa" <?= ($user_data['role'] == 'mahasiswa') ? 'selected' : '' ?>>Mahasiswa</option>
                            </select>
                        </div>
                    </div>
                    <div class="mt-6 flex items-center space-x-3">
                        <button type="submit" class="w-full px-4 py-2.5 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition">
                            <i class="ph-bold ph-floppy-disk mr-2"></i> Simpan Perubahan
                        </button>
                         <a href="dashboard.php?page=kelola_pengguna" class="w-full text-center px-4 py-2.5 bg-gray-200 text-gray-800 font-semibold rounded-lg hover:bg-gray-300 transition">Batal</a>
                    </div>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>