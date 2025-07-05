<?php
require_once 'config.php'; // Pastikan file config.php ada dan berisi koneksi database

$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama = trim($_POST['nama']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $role = trim($_POST['role']);

    // Validasi sederhana
    if (empty($nama) || empty($email) || empty($password) || empty($role)) {
        $message = "Semua field harus diisi!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Format email tidak valid!";
    } elseif (!in_array($role, ['mahasiswa', 'asisten'])) {
        $message = "Peran tidak valid!";
    } else {
        // Cek apakah email sudah terdaftar
        $sql = "SELECT id FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $message = "Email sudah terdaftar. Silakan gunakan email lain.";
        } else {
            // Hash password untuk keamanan
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);

            // Simpan ke database
            $sql_insert = "INSERT INTO users (nama, email, password, role) VALUES (?, ?, ?, ?)";
            $stmt_insert = $conn->prepare($sql_insert);
            $stmt_insert->bind_param("ssss", $nama, $email, $hashed_password, $role);

            if ($stmt_insert->execute()) {
                header("Location: login.php?status=registered");
                exit();
            } else {
                $message = "Terjadi kesalahan. Silakan coba lagi.";
            }
            $stmt_insert->close();
        }
        $stmt->close();
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi - Sistem Akademik</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .glass-effect {
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        .input-focus {
            transition: all 0.3s ease;
        }
        .input-focus:focus {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
        .btn-hover {
            transition: all 0.3s ease;
        }
        .btn-hover:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        }
        .role-option {
            transition: all 0.3s ease;
        }
        .role-option:hover {
            background: rgba(255, 255, 255, 0.2);
        }
        .role-option.selected {
            background: rgba(255, 255, 255, 0.3);
            border-color: rgba(255, 255, 255, 0.6);
        }
    </style>
</head>
<body class="min-h-screen gradient-bg flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <!-- Header -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-white rounded-full shadow-lg mb-4">
                <i class="fas fa-user-plus text-2xl text-indigo-600"></i>
            </div>
            <h1 class="text-3xl font-bold text-white mb-2">Bergabung dengan Kami</h1>
            <p class="text-white/80">Buat akun baru untuk mengakses sistem</p>
        </div>

        <!-- Registration Form -->
        <div class="glass-effect rounded-2xl p-8 shadow-2xl">
            <h2 class="text-2xl font-bold text-center text-white mb-6">Registrasi</h2>
            
            <?php if (!empty($message)): ?>
                <div class="bg-red-500/20 border border-red-400 text-red-100 px-4 py-3 rounded-lg mb-4 text-center">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <form action="register.php" method="post" class="space-y-6">
                <!-- Nama Lengkap Field -->
                <div>
                    <label for="nama" class="block text-white text-sm font-medium mb-2">
                        <i class="fas fa-user mr-2"></i>Nama Lengkap
                    </label>
                    <input type="text" 
                           id="nama" 
                           name="nama" 
                           class="input-focus w-full px-4 py-3 bg-white/10 border border-white/20 rounded-lg text-white placeholder-white/60 focus:outline-none focus:ring-2 focus:ring-white/50 focus:border-transparent" 
                           placeholder="Masukkan nama lengkap Anda"
                           value="<?php echo isset($_POST['nama']) ? htmlspecialchars($_POST['nama']) : ''; ?>"
                           required>
                </div>

                <!-- Email Field -->
                <div>
                    <label for="email" class="block text-white text-sm font-medium mb-2">
                        <i class="fas fa-envelope mr-2"></i>Email
                    </label>
                    <input type="email" 
                           id="email" 
                           name="email" 
                           class="input-focus w-full px-4 py-3 bg-white/10 border border-white/20 rounded-lg text-white placeholder-white/60 focus:outline-none focus:ring-2 focus:ring-white/50 focus:border-transparent" 
                           placeholder="Masukkan email Anda"
                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                           required>
                </div>

                <!-- Password Field -->
                <div>
                    <label for="password" class="block text-white text-sm font-medium mb-2">
                        <i class="fas fa-lock mr-2"></i>Password
                    </label>
                    <div class="relative">
                        <input type="password" 
                               id="password" 
                               name="password" 
                               class="input-focus w-full px-4 py-3 bg-white/10 border border-white/20 rounded-lg text-white placeholder-white/60 focus:outline-none focus:ring-2 focus:ring-white/50 focus:border-transparent pr-12" 
                               placeholder="Buat password yang kuat"
                               required>
                        <button type="button" 
                                onclick="togglePassword()" 
                                class="absolute right-3 top-1/2 transform -translate-y-1/2 text-white/60 hover:text-white">
                            <i class="fas fa-eye" id="toggleIcon"></i>
                        </button>
                    </div>
                    <div class="mt-2 text-white/60 text-xs">
                        <i class="fas fa-info-circle mr-1"></i>
                        Password minimal 6 karakter
                    </div>
                </div>

                <!-- Role Selection -->
                <div>
                    <label class="block text-white text-sm font-medium mb-3">
                        <i class="fas fa-user-tag mr-2"></i>Daftar Sebagai
                    </label>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="role-option border border-white/20 rounded-lg p-4 text-center cursor-pointer" 
                             onclick="selectRole('mahasiswa')">
                            <input type="radio" id="mahasiswa" name="role" value="mahasiswa" class="hidden" 
                                   <?php echo (isset($_POST['role']) && $_POST['role'] == 'mahasiswa') ? 'checked' : ''; ?>>
                            <i class="fas fa-user-graduate text-2xl text-white mb-2 block"></i>
                            <span class="text-white text-sm font-medium">Mahasiswa</span>
                        </div>
                        <div class="role-option border border-white/20 rounded-lg p-4 text-center cursor-pointer" 
                             onclick="selectRole('asisten')">
                            <input type="radio" id="asisten" name="role" value="asisten" class="hidden"
                                   <?php echo (isset($_POST['role']) && $_POST['role'] == 'asisten') ? 'checked' : ''; ?>>
                            <i class="fas fa-chalkboard-teacher text-2xl text-white mb-2 block"></i>
                            <span class="text-white text-sm font-medium">Asisten</span>
                        </div>
                    </div>
                </div>

                <!-- Register Button -->
                <button type="submit" 
                        class="btn-hover w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-6 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500/50">
                    <i class="fas fa-user-plus mr-2"></i>
                    Daftar Sekarang
                </button>
            </form>

            <!-- Login Link -->
            <div class="text-center mt-6 pt-6 border-t border-white/20">
                <p class="text-white/80">
                    Sudah punya akun? 
                    <a href="login.php" class="text-white font-medium hover:text-white/80 transition-colors">
                        <i class="fas fa-sign-in-alt mr-1"></i>
                        Login di sini
                    </a>
                </p>
            </div>
        </div>

        <!-- Footer -->
        <div class="text-center mt-8">
            <p class="text-white/60 text-sm">
                © 2025 Sistem Akademik. All rights reserved.
            </p>
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }

        function selectRole(role) {
            // Remove selected class from all options
            document.querySelectorAll('.role-option').forEach(option => {
                option.classList.remove('selected');
            });
            
            // Add selected class to clicked option
            event.currentTarget.classList.add('selected');
            
            // Check the corresponding radio button
            document.getElementById(role).checked = true;
        }

        // Initialize role selection on page load
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('.glass-effect');
            form.style.animation = 'fadeInUp 0.6s ease-out';
            
            // Check if there's a pre-selected role
            const selectedRole = document.querySelector('input[name="role"]:checked');
            if (selectedRole) {
                selectRole(selectedRole.value);
            }
        });

        // Add CSS animation
        const style = document.createElement('style');
        style.textContent = `
            @keyframes fadeInUp {
                from {
                    opacity: 0;
                    transform: translateY(30px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>