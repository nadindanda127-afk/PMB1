<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = 'localhost';
$dbname = 'pembe';
$user = 'root';
$pass = '';

$conn = mysqli_connect($host, $user, $pass, $dbname);
if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

$error = '';
$success = '';

// Simpan data yang diinput untuk ditampilkan kembali jika ada error
$old_nama = '';
$old_email = '';
$old_alamat = '';
$old_telepon = '';
$old_jurusan = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_lengkap = mysqli_real_escape_string($conn, $_POST['nama_lengkap']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $alamat = mysqli_real_escape_string($conn, $_POST['alamat']);
    $no_telp = mysqli_real_escape_string($conn, $_POST['no_telp']);
    $jurusan = mysqli_real_escape_string($conn, $_POST['jurusan']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    $old_nama = $nama_lengkap;
    $old_email = $email;
    $old_alamat = $alamat;
    $old_telepon = $no_telp;
    $old_jurusan = $jurusan;
    
    if (empty($nama_lengkap) || empty($email) || empty($alamat) || empty($no_telp) || empty($jurusan) || empty($password)) {
        $error = "Semua field harus diisi!";
    } elseif ($password !== $confirm_password) {
        $error = "Password dan Konfirmasi Password tidak cocok!";
    } else {
        // Cek apakah email sudah terdaftar
        $check = mysqli_query($conn, "SELECT id FROM users WHERE email = '$email'");
        
        if (!$check) {
            $error = "Error query: " . mysqli_error($conn);
        } elseif (mysqli_num_rows($check) > 0) {
            $error = "Email sudah terdaftar!";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $no_test = "PMB" . date('Y') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
            
            $query = "INSERT INTO users (nama_lengkap, email, alamat, no_telp, jurusan, password, no_test, role, status_test) 
                      VALUES ('$nama_lengkap', '$email', '$alamat', '$no_telp', '$jurusan', '$hashed_password', '$no_test', 'user', 'proses')";
            
            if (mysqli_query($conn, $query)) {
                $success = "Pendaftaran berhasil! Mengalihkan ke halaman login...";
                echo '<script>setTimeout(function(){ window.location.href = "login.php"; }, 2000);</script>';
                $old_nama = $old_email = $old_alamat = $old_telepon = $old_jurusan = '';
                $_POST = array();
            } else {
                $error = "Error: " . mysqli_error($conn);
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi | PMB System</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #FFF9E6 0%, #FFF3C4 50%, #FFECB3 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
            position: relative;
        }

        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320"><path fill="rgba(255,215,0,0.08)" fill-opacity="1" d="M0,96L48,112C96,128,192,160,288,160C384,160,480,128,576,122.7C672,117,768,139,864,154.7C960,171,1056,181,1152,170.7C1248,160,1344,128,1392,112L1440,96L1440,0L1392,0C1344,0,1248,0,1152,0C1056,0,960,0,864,0C768,0,672,0,576,0C480,0,384,0,288,0C192,0,96,0,48,0L0,0Z"></path></svg>') no-repeat bottom;
            background-size: cover;
            pointer-events: none;
        }

        .register-card {
            background: white;
            border-radius: 28px;
            box-shadow: 0 20px 35px -10px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 550px;
            overflow: hidden;
            animation: slideUp 0.5s ease-out;
            position: relative;
            z-index: 1;
            border: 1px solid #F0E6D2;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .card-header {
            background: linear-gradient(135deg, #D4A017, #B8860B);
            padding: 32px 24px;
            text-align: center;
            position: relative;
        }

        .card-header::after {
            content: '';
            position: absolute;
            bottom: -20px;
            left: 0;
            right: 0;
            height: 40px;
            background: white;
            border-radius: 50% 50% 0 0;
        }

        .icon-wrapper {
            width: 70px;
            height: 70px;
            background: rgba(255,255,255,0.2);
            border-radius: 20px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
        }

        .icon-wrapper i {
            font-size: 36px;
            color: white;
        }

        .card-header h2 {
            color: white;
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .card-header p {
            color: rgba(255,255,255,0.9);
            font-size: 13px;
        }

        .card-body {
            padding: 32px 28px;
        }

        .alert {
            padding: 12px 16px;
            border-radius: 12px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 13px;
        }

        .alert-danger {
            background: #FEF2F2;
            border-left: 4px solid #EF4444;
            color: #DC2626;
        }

        .alert-success {
            background: #E8F5E9;
            border-left: 4px solid #28a745;
            color: #2E7D32;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #5a4a2a;
            margin-bottom: 8px;
        }

        .input-field {
            position: relative;
            width: 100%;
        }

        .input-field i.input-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #C4A35A;
            font-size: 18px;
            z-index: 2;
        }

        .input-field input,
        .input-field select {
            width: 100%;
            padding: 14px 16px 14px 48px;
            border: 1.5px solid #E2E8F0;
            border-radius: 14px;
            font-size: 14px;
            font-family: 'Inter', sans-serif;
            background: white;
            transition: all 0.3s;
        }

        .input-field textarea {
            width: 100%;
            padding: 14px 16px 14px 48px;
            border: 1.5px solid #E2E8F0;
            border-radius: 14px;
            font-size: 14px;
            font-family: 'Inter', sans-serif;
            background: white;
            resize: vertical;
            min-height: 80px;
            line-height: 1.5;
        }

        .input-field textarea + i.input-icon {
            top: 24px;
        }

        .input-field input:focus,
        .input-field select:focus,
        .input-field textarea:focus {
            outline: none;
            border-color: #D4A017;
            box-shadow: 0 0 0 3px rgba(212, 160, 23, 0.1);
        }

        .input-field input:focus + i.input-icon,
        .input-field select:focus + i.input-icon,
        .input-field textarea:focus + i.input-icon {
            color: #D4A017;
        }

        .password-field {
            position: relative;
            width: 100%;
        }

        .password-field i.lock-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #C4A35A;
            font-size: 18px;
            z-index: 2;
        }

        .password-field input {
            width: 100%;
            padding: 14px 48px 14px 48px;
            border: 1.5px solid #E2E8F0;
            border-radius: 14px;
            font-size: 14px;
            font-family: 'Inter', sans-serif;
            background: white;
            transition: all 0.3s;
        }

        .password-field input:focus {
            outline: none;
            border-color: #D4A017;
            box-shadow: 0 0 0 3px rgba(212, 160, 23, 0.1);
        }

        .toggle-eye {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #C4A35A;
            font-size: 18px;
            z-index: 2;
            transition: color 0.2s;
        }

        .toggle-eye:hover {
            color: #D4A017;
        }

        .btn-register {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #D4A017, #B8860B);
            color: white;
            border: none;
            border-radius: 40px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-top: 10px;
        }

        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(212, 160, 23, 0.3);
        }

        .login-link {
            text-align: center;
            margin-top: 24px;
            padding-top: 20px;
            border-top: 1px solid #F0E6D2;
        }

        .login-link p {
            font-size: 13px;
            color: #6c757d;
        }

        .login-link a {
            color: #B8860B;
            text-decoration: none;
            font-weight: 600;
        }

        .login-link a:hover {
            text-decoration: underline;
        }

        .card-footer {
            background: #FFFDF5;
            padding: 16px 28px;
            text-align: center;
            border-top: 1px solid #F0E6D2;
        }

        .card-footer p {
            font-size: 11px;
            color: #9CA3AF;
        }

        @media (max-width: 550px) {
            .card-body {
                padding: 24px 20px;
            }
        }
    </style>
</head>
<body>
    <div class="register-card">
        <div class="card-header">
            <div class="icon-wrapper">
                <i class="fas fa-user-plus"></i>
            </div>
            <h2>Registrasi Calon Mahasiswa</h2>
            <p>Daftarkan diri Anda untuk mengikuti seleksi PMB</p>
        </div>
        
        <div class="card-body">
            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><?php echo $error; ?></span>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <span><?php echo $success; ?></span>
                </div>
            <?php endif; ?>
            
            <form method="POST" id="registerForm">
                <div class="form-group">
                    <label class="form-label">Nama Lengkap</label>
                    <div class="input-field">
                        <i class="fas fa-user input-icon"></i>
                        <input type="text" name="nama_lengkap" placeholder="Masukkan nama lengkap" value="<?php echo htmlspecialchars($old_nama); ?>" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <div class="input-field">
                        <i class="fas fa-envelope input-icon"></i>
                        <input type="email" name="email" placeholder="Masukkan email" value="<?php echo htmlspecialchars($old_email); ?>" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Alamat</label>
                    <div class="input-field">
                        <i class="fas fa-map-marker-alt input-icon"></i>
                        <textarea name="alamat" rows="3" placeholder="Masukkan alamat lengkap"><?php echo htmlspecialchars($old_alamat); ?></textarea>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">No. Telepon</label>
                    <div class="input-field">
                        <i class="fas fa-phone input-icon"></i>
                        <input type="tel" name="no_telp" placeholder="Masukkan nomor telepon" value="<?php echo htmlspecialchars($old_telepon); ?>" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Pilih Jurusan</label>
                    <div class="input-field">
                        <i class="fas fa-graduation-cap input-icon"></i>
                        <select name="jurusan" required>
                            <option value="">-- Pilih Jurusan --</option>
                            <option value="Teknik Informatika" <?php echo $old_jurusan == 'Teknik Informatika' ? 'selected' : ''; ?>>Teknik Informatika</option>
                            <option value="Sistem Informasi" <?php echo $old_jurusan == 'Sistem Informasi' ? 'selected' : ''; ?>>Sistem Informasi</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Password</label>
                    <div class="password-field">
                        <i class="fas fa-lock lock-icon"></i>
                        <input type="password" name="password" id="password" placeholder="Masukkan password" required>
                        <i class="fas fa-eye toggle-eye" id="togglePassword"></i>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Konfirmasi Password</label>
                    <div class="password-field">
                        <i class="fas fa-lock lock-icon"></i>
                        <input type="password" name="confirm_password" id="confirm_password" placeholder="Konfirmasi password" required>
                        <i class="fas fa-eye toggle-eye" id="toggleConfirmPassword"></i>
                    </div>
                </div>
                
                <button type="submit" class="btn-register" id="registerBtn">
                    <i class="fas fa-user-plus"></i> Daftar
                </button>
            </form>
            
            <div class="login-link">
                <p>Sudah punya akun? <a href="login.php">Login disini</a></p>
            </div>
        </div>
        
        <div class="card-footer">
            <p>&copy; <?php echo date('Y'); ?> PMB System. Protected by <i class="fas fa-shield-alt"></i></p>
        </div>
    </div>

    <script>
        // Toggle Password
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');
        
        if (togglePassword) {
            togglePassword.addEventListener('click', function() {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                this.classList.toggle('fa-eye');
                this.classList.toggle('fa-eye-slash');
            });
        }
        
        // Toggle Confirm Password
        const toggleConfirmPassword = document.getElementById('toggleConfirmPassword');
        const confirmPasswordInput = document.getElementById('confirm_password');
        
        if (toggleConfirmPassword) {
            toggleConfirmPassword.addEventListener('click', function() {
                const type = confirmPasswordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                confirmPasswordInput.setAttribute('type', type);
                this.classList.toggle('fa-eye');
                this.classList.toggle('fa-eye-slash');
            });
        }
        
        // Loading state
        const registerForm = document.getElementById('registerForm');
        const registerBtn = document.getElementById('registerBtn');
        
        if (registerForm) {
            registerForm.addEventListener('submit', function() {
                registerBtn.classList.add('loading');
                registerBtn.innerHTML = '<i class="fas fa-spinner fa-pulse"></i><span>Memproses...</span>';
                registerBtn.disabled = true;
            });
        }
    </script>
    <style>
        .btn-register.loading {
            opacity: 0.7;
            cursor: not-allowed;
        }
    </style>
</body>
</html>