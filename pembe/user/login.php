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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];
    
    $query = "SELECT * FROM users WHERE email = '$email' AND role = 'user'";
    $result = mysqli_query($conn, $query);
    
    if (!$result) {
        $error = "Error query: " . mysqli_error($conn);
    } else {
        $user_data = mysqli_fetch_assoc($result);
        
        if ($user_data) {
            if (password_verify($password, $user_data['password'])) {
                $_SESSION['user'] = [
                    'id' => $user_data['id'],
                    'nama' => $user_data['nama_lengkap'],
                    'email' => $user_data['email'],
                    'role' => $user_data['role'],
                    'jurusan' => $user_data['jurusan'],
                    'no_test' => $user_data['no_test'],
                    'status_test' => $user_data['status_test']
                ];
                
                header('Location: dashboard.php');
                exit();
            } else {
                $error = "Password salah!";
            }
        } else {
            $error = "Email tidak ditemukan!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login User | PMB System</title>
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

        .login-card {
            background: white;
            border-radius: 28px;
            box-shadow: 0 20px 35px -10px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 450px;
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
            animation: shake 0.3s ease-out;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }

        .alert-danger {
            background: #FEF2F2;
            border-left: 4px solid #EF4444;
            color: #DC2626;
        }

        .alert-danger i {
            color: #EF4444;
        }

        .form-group {
            margin-bottom: 24px;
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

        .input-field input {
            width: 100%;
            padding: 14px 16px 14px 48px;
            border: 1.5px solid #E2E8F0;
            border-radius: 14px;
            font-size: 14px;
            font-family: 'Inter', sans-serif;
            background: white;
            transition: all 0.3s;
        }

        .input-field input:focus {
            outline: none;
            border-color: #D4A017;
            box-shadow: 0 0 0 3px rgba(212, 160, 23, 0.1);
        }

        .input-field input:focus + i.input-icon {
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

        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 28px;
        }

        .checkbox {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
        }

        .checkbox input {
            width: 16px;
            height: 16px;
            cursor: pointer;
            accent-color: #D4A017;
        }

        .checkbox span {
            font-size: 13px;
            color: #5a4a2a;
        }

        .forgot-link {
            font-size: 13px;
            color: #B8860B;
            text-decoration: none;
            font-weight: 500;
        }

        .forgot-link:hover {
            text-decoration: underline;
        }

        .btn-login {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #D4A017, #B8860B);
            border: none;
            border-radius: 40px;
            font-size: 16px;
            font-weight: 600;
            color: white;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-top: 10px;
            box-shadow: 0 4px 15px rgba(212, 160, 23, 0.3);
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(212, 160, 23, 0.4);
        }

        .divider {
            text-align: center;
            margin: 28px 0 20px;
            position: relative;
        }

        .divider::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background: #F0E6D2;
        }

        .divider span {
            background: white;
            padding: 0 16px;
            position: relative;
            color: #9CA3AF;
            font-size: 12px;
        }

        .register-link {
            text-align: center;
            margin-top: 20px;
        }

        .register-link p {
            font-size: 13px;
            color: #6c757d;
        }

        .register-link a {
            color: #B8860B;
            text-decoration: none;
            font-weight: 600;
        }

        .register-link a:hover {
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
    <div class="login-card">
        <div class="card-header">
            <div class="icon-wrapper">
                <i class="fas fa-user-graduate"></i>
            </div>
            <h2>Login User</h2>
            <p>Masuk ke portal mahasiswa PMB</p>
        </div>
        
        <div class="card-body">
            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><?php echo $error; ?></span>
                </div>
            <?php endif; ?>
            
            <form method="POST" id="loginForm">
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <div class="input-field">
                        <i class="fas fa-envelope input-icon"></i>
                        <input type="email" name="email" placeholder="Masukkan email" required autofocus>
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
                
                <div class="form-options">
                    <label class="checkbox">
                        <input type="checkbox" name="remember_me">
                        <span>Ingat saya</span>
                    </label>
                    <a href="#" class="forgot-link">Lupa password?</a>
                </div>
                
                <button type="submit" class="btn-login" id="loginBtn">
                    <i class="fas fa-arrow-right-to-bracket"></i> Login
                </button>
            </form>
            
            <div class="divider">
                <span>Belum punya akun?</span>
            </div>
            
            <div class="register-link">
                <p><a href="register.php">Daftar disini</a> untuk mengikuti seleksi PMB</p>
            </div>
        </div>
        
        <div class="card-footer">
            <p>&copy; <?php echo date('Y'); ?> PMB System. Protected by <i class="fas fa-shield-alt"></i></p>
        </div>
    </div>

    <script>
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
        
        const loginForm = document.getElementById('loginForm');
        const loginBtn = document.getElementById('loginBtn');
        
        if (loginForm) {
            loginForm.addEventListener('submit', function() {
                loginBtn.classList.add('loading');
                loginBtn.innerHTML = '<i class="fas fa-spinner fa-pulse"></i><span>Memproses...</span>';
                loginBtn.disabled = true;
            });
        }
        
        const rememberCheckbox = document.querySelector('input[name="remember_me"]');
        const emailInput = document.querySelector('input[name="email"]');
        
        if (localStorage.getItem('remembered_email') && rememberCheckbox) {
            emailInput.value = localStorage.getItem('remembered_email');
            rememberCheckbox.checked = true;
        }
        
        if (loginForm) {
            loginForm.addEventListener('submit', function() {
                if (rememberCheckbox && rememberCheckbox.checked) {
                    localStorage.setItem('remembered_email', emailInput.value);
                } else if (rememberCheckbox) {
                    localStorage.removeItem('remembered_email');
                }
            });
        }
    </script>
    <style>
        .btn-login.loading {
            opacity: 0.7;
            cursor: not-allowed;
        }
    </style>
</body>
</html>