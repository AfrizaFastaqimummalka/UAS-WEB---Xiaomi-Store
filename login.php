<?php
session_start();
require_once 'includes/koneksi_database.php';

// Redirect jika sudah login
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

$error = '';
$success = '';

// Proses Login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = clean_input($_POST['username']);
    $password = $_POST['password'];
    
    if (empty($username) || empty($password)) {
        $error = "Username dan password harus diisi!";
    } else {
        $stmt = $conn->prepare("SELECT id, username, password FROM admin_users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            
            // Verifikasi password
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                header("Location: dashboard.php");
                exit;
            } else {
                $error = "Username atau password salah!";
            }
        } else {
            $error = "Username atau password salah!";
        }
        $stmt->close();
    }
}

// Proses Register (opsional - bisa dihapus jika tidak diperlukan)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $username = clean_input($_POST['reg_username']);
    $password = $_POST['reg_password'];
    $email = clean_input($_POST['reg_email']);
    
    if (empty($username) || empty($password) || empty($email)) {
        $error = "Semua field harus diisi!";
    } else {
        // Cek apakah username sudah ada
        $stmt = $conn->prepare("SELECT id FROM admin_users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = "Username sudah digunakan!";
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert user baru
            $stmt = $conn->prepare("INSERT INTO admin_users (username, password, email) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $username, $hashed_password, $email);
            
            if ($stmt->execute()) {
                $success = "Registrasi berhasil! Silakan login.";
            } else {
                $error = "Terjadi kesalahan saat registrasi!";
            }
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Xiaomi Store</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <div class="login-logo">
                <div class="logo-circle">Mi</div>
                <h1 style="color: var(--orange); font-size: 28px; margin-bottom: 5px;">XIAOMI STORE</h1>
                <p style="color: #999; font-size: 14px;">Admin Panel</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?= $success ?></div>
            <?php endif; ?>
            
            <div id="login-form-container">
                <form method="POST" action="">
                    <h2 style="margin-bottom: 20px;">Login</h2>
                    
                    <label>Username</label>
                    <input type="text" name="username" placeholder="Masukkan username" required>
                    
                    <label>Password</label>
                    <input type="password" name="password" placeholder="Masukkan password" required>
                    
                    <button type="submit" name="login" class="btn" style="width: 100%; margin-top: 20px;">
                        LOGIN
                    </button>
                </form>
                
                <div class="text-center mt-2">
                    <p style="color: #999; font-size: 14px;">
                        Belum punya akun? 
                        <a href="#" onclick="toggleForm()" style="color: var(--orange);">Daftar di sini</a>
                    </p>
                </div>
            </div>
            
            <div id="register-form-container" style="display: none;">
                <form method="POST" action="">
                    <h2 style="margin-bottom: 20px;">Registrasi</h2>
                    
                    <label>Username</label>
                    <input type="text" name="reg_username" placeholder="Pilih username" required>
                    
                    <label>Email</label>
                    <input type="email" name="reg_email" placeholder="Email anda" required>
                    
                    <label>Password</label>
                    <input type="password" name="reg_password" placeholder="Buat password" required>
                    
                    <button type="submit" name="register" class="btn" style="width: 100%; margin-top: 20px;">
                        DAFTAR
                    </button>
                </form>
                
                <div class="text-center mt-2">
                    <p style="color: #999; font-size: 14px;">
                        Sudah punya akun? 
                        <a href="#" onclick="toggleForm()" style="color: var(--orange);">Login di sini</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        function toggleForm() {
            const loginForm = document.getElementById('login-form-container');
            const registerForm = document.getElementById('register-form-container');
            
            if (loginForm.style.display === 'none') {
                loginForm.style.display = 'block';
                registerForm.style.display = 'none';
            } else {
                loginForm.style.display = 'none';
                registerForm.style.display = 'block';
            }
        }
    </script>
</body>
</html>
<?php $conn->close(); ?>