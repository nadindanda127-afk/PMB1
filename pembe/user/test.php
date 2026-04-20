<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit();
}

$user = $_SESSION['user'];
$user_id = $user['id'];

$host = 'localhost';
$dbname = 'pembe';
$user_db = 'root';
$pass = '';

$conn = mysqli_connect($host, $user_db, $pass, $dbname);
if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

// Ambil nomor test user dari database
$query = "SELECT no_test, status_test FROM users WHERE id = $user_id";
$result = mysqli_query($conn, $query);
$user_data = mysqli_fetch_assoc($result);
$no_test_db = $user_data['no_test'] ?? '';
$status_test = $user_data['status_test'] ?? 'belum';

// Ambil data daftar ulang untuk cek apakah sudah daftar ulang
$query_du = "SELECT * FROM daftar_ulang WHERE id_peserta = $user_id";
$result_du = mysqli_query($conn, $query_du);
$daftar_ulang = mysqli_fetch_assoc($result_du);
$nim = $daftar_ulang['nim'] ?? null;

// Jika sudah pernah test
if ($status_test == 'lulus' || $status_test == 'tidak_lulus') {
    $sudah_test = true;
    $sudah_lulus = ($status_test == 'lulus');
} else {
    $sudah_test = false;
}

$error = '';
$step = 'input_nomor';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['verifikasi'])) {
        $no_test_input = mysqli_real_escape_string($conn, $_POST['no_test']);
        
        if ($no_test_input == $no_test_db) {
            $_SESSION['test_verified'] = true;
            $step = 'test';
        } else {
            $error = "Nomor Test yang Anda masukkan salah!";
        }
    } elseif (isset($_POST['mulai_test'])) {
        if (isset($_SESSION['test_verified']) && $_SESSION['test_verified'] === true) {
            header('Location: mulai_test.php');
            exit();
        } else {
            $error = "Silakan verifikasi nomor test terlebih dahulu!";
            $step = 'input_nomor';
        }
    }
}

if (isset($_SESSION['test_verified']) && $_SESSION['test_verified'] === true) {
    $step = 'test';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Seleksi | PMB System</title>
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

        /* Sidebar */
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: 280px;
            height: 100vh;
            background: linear-gradient(180deg, #D4A017 0%, #B8860B 100%);
            color: white;
            overflow-y: auto;
            z-index: 100;
        }

        .sidebar-header {
            padding: 25px 20px;
            border-bottom: 1px solid rgba(255,255,255,0.2);
            margin-bottom: 20px;
        }

        .sidebar-header h3 {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .sidebar-header h3 i {
            margin-right: 8px;
        }

        .sidebar-header p {
            font-size: 11px;
            opacity: 0.8;
        }

        .sidebar-menu {
            padding: 0 15px;
        }

        .menu-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 15px;
            margin: 5px 0;
            border-radius: 12px;
            color: rgba(255,255,255,0.9);
            text-decoration: none;
            transition: all 0.3s;
        }

        .menu-item i {
            width: 24px;
            font-size: 18px;
        }

        .menu-item span {
            font-size: 14px;
            font-weight: 500;
        }

        .menu-item:hover, .menu-item.active {
            background: rgba(255,255,255,0.15);
            color: white;
        }

        .menu-divider {
            height: 1px;
            background: rgba(255,255,255,0.2);
            margin: 15px 0;
        }

        /* Main Content */
        .main-content {
            margin-left: 280px;
            min-height: 100vh;
            position: relative;
            z-index: 1;
        }

        /* Top Header */
        .top-header {
            background: white;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            position: sticky;
            top: 0;
            z-index: 99;
            border-bottom: 1px solid #F0E6D2;
        }

        .page-title h2 {
            font-size: 22px;
            font-weight: 700;
            color: #B8860B;
        }

        .logout-btn {
            background: #dc3545;
            color: white;
            padding: 8px 18px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 13px;
            font-weight: 500;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .logout-btn:hover {
            background: #c82333;
            transform: translateY(-2px);
        }

        /* Container */
        .container {
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
            padding: 25px 20px;
        }

        /* Alert Info - Warna Emas/Coklat */
        .alert-info {
            background: #FEF3C7;
            color: #B8860B;
            padding: 16px 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            border-left: 5px solid #D4A017;
            display: flex;
            align-items: center;
            gap: 12px;
            box-shadow: 0 2px 8px rgba(212, 160, 23, 0.15);
        }

        .alert-info i {
            font-size: 20px;
            color: #D4A017;
        }

        .alert-info a {
            color: #D4A017;
            font-weight: 600;
            text-decoration: none;
        }

        .alert-info a:hover {
            text-decoration: underline;
        }

        .alert-warning {
            background: #FFF3E0;
            color: #E65100;
            padding: 16px 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            border-left: 5px solid #FF9800;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .alert-warning i {
            font-size: 20px;
            color: #FF9800;
        }

        .alert-warning a {
            color: #E65100;
            font-weight: 600;
            text-decoration: none;
        }

        .alert-success-custom {
            background: #FEF3C7;
            color: #B8860B;
            padding: 16px 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            border-left: 5px solid #D4A017;
            display: flex;
            align-items: center;
            gap: 12px;
            box-shadow: 0 2px 8px rgba(212, 160, 23, 0.15);
        }

        .alert-success-custom i {
            font-size: 20px;
            color: #D4A017;
        }

        /* Card */
        .test-card {
            background: white;
            border-radius: 28px;
            box-shadow: 0 20px 35px -10px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            animation: fadeInUp 0.5s ease-out;
            border: 1px solid #F0E6D2;
        }

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

        /* Header */
        .card-header {
            background: linear-gradient(135deg, #D4A017, #B8860B);
            padding: 30px 25px;
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

        .icon-circle {
            width: 70px;
            height: 70px;
            background: rgba(255,255,255,0.12);
            border-radius: 20px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 18px;
        }

        .icon-circle i {
            font-size: 34px;
            color: white;
        }

        .card-header h2 {
            color: white;
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 6px;
        }

        .card-header p {
            color: rgba(255,255,255,0.85);
            font-size: 13px;
        }

        /* Body */
        .card-body {
            padding: 32px 28px;
        }

        /* Success Alert */
        .success-alert {
            background: #FFFDF5;
            border-left: 4px solid #D4A017;
            padding: 18px;
            border-radius: 16px;
            margin-bottom: 25px;
            border: 1px solid #F0E6D2;
        }

        .success-alert .title {
            font-weight: 700;
            color: #B8860B;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .info-detail {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        /* LABEL (Nomor test:, Nama:, Jurusan:) - WARNA COKLAT/EMAS */
        .info-label {
            color: #B8860B;
            font-weight: 600;
        }

        /* VALUE (PMB20264114, Kirana Putri, dll) - WARNA HITAM (sama seperti 10 soal pilihan ganda) */
        .info-value {
            color: #302222;
            font-weight: 500;
        }

        .info-row {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 14px;
        }

        .info-row i {
            width: 20px;
            color: #D4A017;
        }

        /* Rules Card */
        .rules-card {
            background: #FFFDF5;
            border-radius: 20px;
            padding: 20px;
            margin-bottom: 25px;
            border: 1px solid #F0E6D2;
        }

        .rules-title {
            font-weight: 700;
            color: #B8860B;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 16px;
        }

        .rules-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .rule-item {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 13px;
        }

        /* LABEL RULE (Jumlah Soal:, Nilai Minimal:, Perhatian:) - WARNA COKLAT/EMAS */
        .rule-label {
            color: #B8860B;
            font-weight: 600;
        }

        /* VALUE RULE - WARNA HITAM */
        .rule-value {
            color: #000000;
        }

        .rule-item i {
            width: 20px;
            color: #D4A017;
        }

        /* Button */
        .btn-start {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #D4A017, #B8860B);
            border: none;
            border-radius: 40px;
            font-size: 16px;
            font-weight: 600;
            color: white;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            transition: all 0.3s;
        }

        .btn-start:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(212, 160, 23, 0.3);
        }

        /* Error Alert */
        .error-alert {
            background: #FEF2F2;
            border-left: 4px solid #dc3545;
            padding: 14px;
            border-radius: 12px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 13px;
            color: #dc3545;
        }

        /* Form */
        .form-group {
            margin-bottom: 24px;
        }

        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #8B7355;
            margin-bottom: 8px;
        }

        .form-group input {
            width: 100%;
            padding: 14px 16px;
            border: 1.5px solid #F0E6D2;
            border-radius: 14px;
            font-size: 14px;
            text-align: center;
            letter-spacing: 1px;
            transition: all 0.3s;
        }

        .form-group input:focus {
            outline: none;
            border-color: #D4A017;
            box-shadow: 0 0 0 3px rgba(212, 160, 23, 0.1);
        }

        .btn-verify {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #D4A017, #B8860B);
            border: none;
            border-radius: 40px;
            font-size: 16px;
            font-weight: 600;
            color: white;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            transition: all 0.3s;
        }

        .btn-verify:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(212, 160, 23, 0.3);
        }

        .info-box {
            background: #FFFDF5;
            border-radius: 20px;
            padding: 20px;
            text-align: center;
            margin-bottom: 25px;
            border: 1px solid #F0E6D2;
        }

        .info-box p {
            margin: 5px 0;
            font-size: 14px;
            color: #000000;
        }

        .info-box strong {
            font-size: 18px;
            color: #B8860B;
        }

        .info-box a {
            color: #D4A017;
            text-decoration: none;
        }

        .info-box a:hover {
            text-decoration: underline;
        }

        /* Responsive */
        @media (max-width: 992px) {
            .sidebar { width: 80px; }
            .sidebar-header h3, .sidebar-header p, .menu-item span { display: none; }
            .sidebar-header { text-align: center; padding: 20px 10px; }
            .menu-item { justify-content: center; }
            .menu-item i { width: auto; font-size: 20px; }
            .main-content { margin-left: 80px; width: calc(100% - 80px); }
        }

        @media (max-width: 768px) {
            .sidebar { width: 0; transform: translateX(-100%); }
            .main-content { margin-left: 0; width: 100%; }
            .card-body { padding: 25px 20px; }
            .top-header { flex-direction: column; text-align: center; gap: 10px; }
        }
    </style>
</head>
<body>
    <!-- SIDEBAR -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h3><i class="fas fa-graduation-cap"></i> UCAN</h3>
            <p>Universitas Cakrawala Nusantara</p>
        </div>
        <div class="sidebar-menu">
            <a href="dashboard.php" class="menu-item">
                <i class="fas fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>
            <a href="test.php" class="menu-item active">
                <i class="fas fa-pen-alt"></i>
                <span>Test Seleksi</span>
            </a>
            <a href="hasil_test.php" class="menu-item">
                <i class="fas fa-chart-line"></i>
                <span>Hasil Test</span>
            </a>
            <a href="daftar_ulang.php" class="menu-item">
                <i class="fas fa-file-signature"></i>
                <span>Daftar Ulang</span>
            </a>
            <div class="menu-divider"></div>
            <a href="logout.php" class="menu-item">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </div>
    </div>

    <!-- MAIN CONTENT -->
    <div class="main-content">
        <div class="top-header">
            <div class="page-title">
                <h2><i class="fas fa-pen-alt"></i> Test Seleksi PMB</h2>
            </div>
            <a href="logout.php" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>

        <div class="container">
            <!-- NOTIFIKASI JIKA SUDAH TEST - WARNA EMAS -->
            <?php if ($sudah_test): ?>
                <?php if ($sudah_lulus && !$nim): ?>
                    <div class="alert-info">
                        <i class="fas fa-info-circle"></i>
                        <span>Anda sudah melakukan test seleksi pendaftaran mahasiswa baru, silakan <a href="daftar_ulang.php">melakukan daftar ulang</a> untuk menyelesaikan proses pendaftaran.</span>
                    </div>
                <?php elseif ($sudah_lulus && $nim): ?>
                    <div class="alert-success-custom">
                        <i class="fas fa-check-circle"></i>
                        <span>Selamat! Anda telah menyelesaikan seluruh proses pendaftaran. NIM Anda: <strong><?php echo $nim; ?></strong></span>
                    </div>
                <?php elseif (!$sudah_lulus): ?>
                    <div class="alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <span>Anda sudah melakukan test seleksi namun dinyatakan TIDAK LULUS. Silakan coba lagi di tahun berikutnya. <a href="hasil_test.php">Lihat hasil test</a></span>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <div class="test-card">
                <div class="card-header">
                    <div class="icon-circle">
                        <i class="fas fa-pen-alt"></i>
                    </div>
                    <h2>Test Seleksi PMB</h2>
                    <p>Verifikasi nomor test untuk memulai ujian</p>
                </div>
                
                <div class="card-body">
                    <?php if ($error): ?>
                        <div class="error-alert">
                            <i class="fas fa-exclamation-circle"></i>
                            <span><?php echo $error; ?></span>
                        </div>
                    <?php endif; ?>

                    <?php if ($sudah_test): ?>
                        <div class="info-box">
                            <p><i class="fas fa-info-circle"></i> Anda telah menyelesaikan test seleksi.</p>
                            <p>Status: <strong><?php echo $sudah_lulus ? 'LULUS' : 'TIDAK LULUS'; ?></strong></p>
                            <p>Silakan cek <a href="hasil_test.php">Hasil Test</a> untuk detail.</p>
                        </div>
                    <?php elseif ($step == 'input_nomor'): ?>
                        <div class="info-box">
                            <p><i class="fas fa-info-circle"></i> Nomor test Anda adalah:</p>
                            <p><strong><?php echo $no_test_db ?: 'Belum ada nomor test'; ?></strong></p>
                        </div>

                        <form method="POST">
                            <div class="form-group">
                                <label><i class="fas fa-ticket-alt"></i> Masukkan Nomor Test</label>
                                <input type="text" name="no_test" placeholder="Contoh: PMB20262010" required autofocus>
                            </div>
                            <button type="submit" name="verifikasi" class="btn-verify">
                                <i class="fas fa-check-circle"></i> Verifikasi Nomor Test
                            </button>
                        </form>

                    <?php else: ?>
                        <div class="success-alert">
                            <div class="title">
                                <i class="fas fa-check-circle"></i> Verifikasi Berhasil!
                            </div>
                            <div class="info-detail">
                                <div class="info-row">
                                    <i class="fas fa-qrcode"></i>
                                    <span><span class="info-label">Nomor test:</span> <span class="info-value"><?php echo $no_test_db; ?></span></span>
                                </div>
                                <div class="info-row">
                                    <i class="fas fa-user"></i>
                                    <span><span class="info-label">Nama:</span> <span class="info-value"><?php echo htmlspecialchars($user['nama']); ?></span></span>
                                </div>
                                <div class="info-row">
                                    <i class="fas fa-graduation-cap"></i>
                                    <span><span class="info-label">Jurusan:</span> <span class="info-value"><?php echo htmlspecialchars($user['jurusan']); ?></span></span>
                                </div>
                            </div>
                        </div>

                        <div class="rules-card">
                            <div class="rules-title">
                                <i class="fas fa-clipboard-list"></i> Petunjuk Test
                            </div>
                            <div class="rules-list">
                                <div class="rule-item">
                                    <i class="fas fa-question-circle"></i>
                                    <span><span class="rule-label">Jumlah Soal:</span> <span class="rule-value">10 soal pilihan ganda</span></span>
                                </div>
                                <div class="rule-item">
                                    <i class="fas fa-check-circle"></i>
                                    <span><span class="rule-label">Nilai Minimal:</span> <span class="rule-value">70 untuk LULUS</span></span>
                                </div>
                                <div class="rule-item">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    <span><span class="rule-label">Perhatian:</span> <span class="rule-value">Test hanya dapat dilakukan 1 kali</span></span>
                                </div>
                            </div>
                        </div>

                        <form method="POST">
                            <button type="submit" name="mulai_test" class="btn-start">
                                <i class="fas fa-play"></i> Mulai Test Sekarang
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>