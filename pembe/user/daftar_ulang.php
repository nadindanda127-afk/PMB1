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

// ============================================
// CEK APAKAH USER SUDAH MELAKUKAN TEST
// ============================================
$cek_test = mysqli_query($conn, "SELECT * FROM users WHERE id = $user_id");
$user_data = mysqli_fetch_assoc($cek_test);
$status_test = $user_data['status_test'] ?? 'belum';

// Jika belum test
$belum_test = ($status_test == 'belum' || $status_test == 'proses');

// Jika sudah test tapi tidak lulus
$tidak_lulus = ($status_test == 'tidak_lulus');

// Jika sudah lulus
$lulus = ($status_test == 'lulus');

// Buat folder uploads jika belum ada
if (!file_exists('../uploads')) {
    mkdir('../uploads', 0777, true);
}

// Buat folder uploads/dokumen jika belum ada
if (!file_exists('../uploads/dokumen')) {
    mkdir('../uploads/dokumen', 0777, true);
}

// Buat tabel daftar_ulang jika belum ada
mysqli_query($conn, "CREATE TABLE IF NOT EXISTS daftar_ulang (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_peserta INT NOT NULL,
    nama_lengkap VARCHAR(100),
    no_telepon VARCHAR(20),
    tanggal_lahir DATE,
    jenis_kelamin VARCHAR(20),
    alamat TEXT,
    email VARCHAR(100),
    nik VARCHAR(20),
    tempat_lahir VARCHAR(100),
    agama VARCHAR(20),
    nama_ayah VARCHAR(100),
    nama_ibu VARCHAR(100),
    pekerjaan_ayah VARCHAR(100),
    penghasilan_ortu VARCHAR(50),
    foto VARCHAR(255),
    ktp VARCHAR(255),
    ijazah VARCHAR(255),
    nilai_test INT,
    nim VARCHAR(20),
    status VARCHAR(20) DEFAULT 'pending',
    tanggal_daftar_ulang DATETIME DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

// Ambil nilai test
$nilai_test = 0;
$result_test = mysqli_query($conn, "SELECT total_nilai FROM hasil_test WHERE id_peserta = $user_id ORDER BY id DESC LIMIT 1");
if ($result_test && mysqli_num_rows($result_test) > 0) {
    $test = mysqli_fetch_assoc($result_test);
    $nilai_test = $test['total_nilai'] ?? 0;
}

// NIM otomatis
$kode = ($user['jurusan'] == 'Teknik Informatika') ? 'TI' : 'SI';
$nim_otomatis = date('Y') . $kode . str_pad($user_id, 5, '0', STR_PAD_LEFT);

// Ambil data alamat dan no telepon dari tabel users (jika ada)
$alamat_registrasi = $user_data['alamat'] ?? '';
$no_telp_registrasi = $user_data['no_telp'] ?? '';

$message = '';
$error = '';

// CEK APAKAH SUDAH PERNAH DAFTAR ULANG
$cek_du = mysqli_query($conn, "SELECT * FROM daftar_ulang WHERE id_peserta = $user_id");
$sudah_daftar = false;
$data_daftar = null;
if ($cek_du && mysqli_num_rows($cek_du) > 0) {
    $sudah_daftar = true;
    $data_daftar = mysqli_fetch_assoc($cek_du);
    // Jika sudah daftar, gunakan NIM yang sudah ada
    if (!empty($data_daftar['nim'])) {
        $nim_otomatis = $data_daftar['nim'];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $lulus && !$sudah_daftar) {
    $nama_lengkap = mysqli_real_escape_string($conn, $_POST['nama_lengkap']);
    $no_telepon = mysqli_real_escape_string($conn, $_POST['no_telepon']);
    $tanggal_lahir = mysqli_real_escape_string($conn, $_POST['tanggal_lahir']);
    $jenis_kelamin = mysqli_real_escape_string($conn, $_POST['jenis_kelamin']);
    $alamat = mysqli_real_escape_string($conn, $_POST['alamat']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $nik = mysqli_real_escape_string($conn, $_POST['nik']);
    $tempat_lahir = mysqli_real_escape_string($conn, $_POST['tempat_lahir']);
    $agama = mysqli_real_escape_string($conn, $_POST['agama']);
    $nama_ayah = mysqli_real_escape_string($conn, $_POST['nama_ayah']);
    $nama_ibu = mysqli_real_escape_string($conn, $_POST['nama_ibu']);
    $pekerjaan_ayah = mysqli_real_escape_string($conn, $_POST['pekerjaan_ayah']);
    $penghasilan_ortu = mysqli_real_escape_string($conn, $_POST['penghasilan_ortu']);
    $nim = $nim_otomatis;
    
    $foto_name = '';
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0 && $_FILES['foto']['size'] > 0) {
        $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
        $foto_name = time() . '_foto.' . $ext;
        move_uploaded_file($_FILES['foto']['tmp_name'], '../uploads/' . $foto_name);
    }
    
    $ktp_name = '';
    if (isset($_FILES['ktp']) && $_FILES['ktp']['error'] == 0 && $_FILES['ktp']['size'] > 0) {
        $ext = pathinfo($_FILES['ktp']['name'], PATHINFO_EXTENSION);
        $ktp_name = time() . '_ktp.' . $ext;
        move_uploaded_file($_FILES['ktp']['tmp_name'], '../uploads/' . $ktp_name);
    }
    
    // Upload Ijazah
    $ijazah_name = '';
    if (isset($_FILES['ijazah']) && $_FILES['ijazah']['error'] == 0 && $_FILES['ijazah']['size'] > 0) {
        $ext = pathinfo($_FILES['ijazah']['name'], PATHINFO_EXTENSION);
        $ijazah_name = time() . '_ijazah.' . $ext;
        move_uploaded_file($_FILES['ijazah']['tmp_name'], '../uploads/dokumen/' . $ijazah_name);
    }
    
    $query = "INSERT INTO daftar_ulang (
        id_peserta, nama_lengkap, no_telepon, tanggal_lahir, jenis_kelamin, alamat,
        email, nik, tempat_lahir, agama, nama_ayah, nama_ibu, pekerjaan_ayah,
        penghasilan_ortu, foto, ktp, ijazah, nilai_test, nim, status
    ) VALUES (
        '$user_id', '$nama_lengkap', '$no_telepon', '$tanggal_lahir', '$jenis_kelamin', '$alamat',
        '$email', '$nik', '$tempat_lahir', '$agama', '$nama_ayah', '$nama_ibu', '$pekerjaan_ayah',
        '$penghasilan_ortu', '$foto_name', '$ktp_name', '$ijazah_name', '$nilai_test', '$nim', 'pending'
    )";
    
    if (mysqli_query($conn, $query)) {
        $message = "✅ Daftar ulang BERHASIL! Silakan tunggu verifikasi dari admin.";
        $sudah_daftar = true;
        // Refresh data
        $cek_du = mysqli_query($conn, "SELECT * FROM daftar_ulang WHERE id_peserta = $user_id");
        if ($cek_du && mysqli_num_rows($cek_du) > 0) {
            $data_daftar = mysqli_fetch_assoc($cek_du);
        }
    } else {
        $error = "❌ Error: " . mysqli_error($conn);
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Ulang | PMB System</title>
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
            max-width: 1000px;
            margin: 30px auto;
            padding: 0 20px;
        }

        /* Alert - Warna Tema Emas/Coklat */
        .alert {
            padding: 14px 18px;
            border-radius: 12px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 13px;
            animation: slideInRight 0.4s ease-out;
        }

        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(30px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .alert-success {
            background: linear-gradient(135deg, #FFF8E1 0%, #FFECB3 100%);
            color: #B8860B;
            border-left: 5px solid #D4A017;
            box-shadow: 0 2px 8px rgba(212, 160, 23, 0.15);
        }

        .alert-success i {
            font-size: 18px;
            color: #D4A017;
        }

        .alert-danger {
            background: #FEF2F2;
            color: #C62828;
            border-left: 4px solid #dc3545;
        }

        .alert-info {
            background: #FEF3C7;
            color: #B8860B;
            border-left: 4px solid #D4A017;
        }

        .alert-info i {
            color: #D4A017;
        }

        /* Warning Card */
        .warning-card {
            background: white;
            border-radius: 24px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            text-align: center;
            padding: 50px 30px;
            border: 1px solid #F0E6D2;
        }

        .warning-icon {
            width: 85px;
            height: 85px;
            background: #FEF2F2;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 24px;
        }

        .warning-icon i {
            font-size: 42px;
            color: #dc3545;
        }

        .warning-card h3 {
            font-size: 24px;
            font-weight: 700;
            color: #B8860B;
            margin-bottom: 20px;
        }

        .warning-message {
            color: #B8860B;
            font-size: 14px;
            margin-bottom: 8px;
            line-height: 1.6;
        }

        .warning-card p {
            color: #5a4a2a;
            font-size: 14px;
            margin-bottom: 8px;
            line-height: 1.6;
        }

        .btn-test-warning {
            background: linear-gradient(135deg, #D4A017, #B8860B);
            color: white;
            padding: 12px 32px;
            border-radius: 40px;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
            margin-top: 20px;
        }

        .btn-test-warning:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(212, 160, 23, 0.3);
        }

        /* Daftar Card */
        .daftar-card {
            background: white;
            border-radius: 24px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            overflow: hidden;
            border: 1px solid #F0E6D2;
        }

        .card-header {
            background: linear-gradient(135deg, #D4A017, #B8860B);
            padding: 28px 24px;
            text-align: center;
            position: relative;
        }

        .card-header::after {
            content: '';
            position: absolute;
            bottom: -16px;
            left: 0;
            right: 0;
            height: 32px;
            background: white;
            border-radius: 50% 50% 0 0;
        }

        .icon-circle {
            width: 60px;
            height: 60px;
            background: rgba(255,255,255,0.2);
            border-radius: 16px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 12px;
        }

        .icon-circle i {
            font-size: 28px;
            color: white;
        }

        .card-header h2 {
            color: white;
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 4px;
        }

        .card-header p {
            color: rgba(255,255,255,0.85);
            font-size: 13px;
        }

        .card-body {
            padding: 32px 28px;
        }

        /* Form Grid */
        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-bottom: 24px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .form-group label {
            font-size: 12px;
            font-weight: 600;
            color: #5a4a2a;
            letter-spacing: 0.5px;
        }

        .form-group label i {
            width: 18px;
            color: #D4A017;
            margin-right: 5px;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            padding: 12px 14px;
            border: 1px solid #E2E8F0;
            border-radius: 12px;
            font-size: 14px;
            font-family: 'Inter', sans-serif;
            transition: all 0.3s;
            background: white;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #D4A017;
            box-shadow: 0 0 0 3px rgba(212, 160, 23, 0.1);
        }

        .form-group textarea {
            resize: vertical;
            min-height: 80px;
        }

        .full-width {
            grid-column: span 2;
        }

        /* Section Title */
        .section-title {
            font-size: 16px;
            font-weight: 700;
            color: #B8860B;
            margin: 24px 0 16px 0;
            padding-bottom: 8px;
            border-bottom: 2px solid #F0E6D2;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* Upload Area */
        .upload-area {
            border: 2px dashed #E2E8F0;
            border-radius: 16px;
            padding: 20px;
            text-align: center;
            background: #F8FAFC;
            cursor: pointer;
            transition: all 0.3s;
        }

        .upload-area:hover {
            border-color: #D4A017;
            background: #FFF9E6;
        }

        .upload-area i {
            font-size: 28px;
            color: #D4A017;
            margin-bottom: 8px;
        }

        .upload-area p {
            font-size: 12px;
            color: #64748B;
        }

        .upload-area small {
            font-size: 10px;
            color: #94A3B8;
        }

        .upload-area input {
            display: none;
        }

        /* Status Card */
        .status-card {
            background: #F8FAFC;
            border-radius: 16px;
            padding: 20px;
            margin-top: 20px;
            border: 1px solid #E2E8F0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 16px;
        }

        .status-item {
            text-align: center;
            flex: 1;
            min-width: 120px;
        }

        .status-label {
            font-size: 11px;
            color: #64748B;
            margin-bottom: 6px;
        }

        .status-value {
            font-size: 24px;
            font-weight: 800;
            color: #D4A017;
        }

        .status-nim {
            font-size: 18px;
            font-weight: 700;
            color: #D4A017;
            font-family: monospace;
            background: white;
            padding: 6px 14px;
            border-radius: 40px;
            display: inline-block;
        }

        .btn-submit {
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
            margin-top: 28px;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(212, 160, 23, 0.3);
        }

        /* Readonly input style */
        .form-group input[readonly] {
            background: #F8FAFC;
            cursor: not-allowed;
        }

        /* Responsive */
        @media (max-width: 992px) {
            .sidebar { width: 80px; }
            .sidebar-header h3, .sidebar-header p, .menu-item span { display: none; }
            .sidebar-header { text-align: center; padding: 20px 10px; }
            .menu-item { justify-content: center; }
            .menu-item i { width: auto; font-size: 20px; }
            .main-content { margin-left: 80px; }
        }

        @media (max-width: 768px) {
            .sidebar { width: 0; transform: translateX(-100%); }
            .main-content { margin-left: 0; }
            .top-header { flex-direction: column; text-align: center; gap: 15px; }
            .form-grid { grid-template-columns: 1fr; }
            .full-width { grid-column: span 1; }
            .card-body { padding: 24px 20px; }
            .status-card { flex-direction: column; text-align: center; }
            .warning-card { padding: 32px 20px; }
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
            <a href="test.php" class="menu-item">
                <i class="fas fa-pen-alt"></i>
                <span>Test Seleksi</span>
            </a>
            <a href="hasil_test.php" class="menu-item">
                <i class="fas fa-chart-line"></i>
                <span>Hasil Test</span>
            </a>
            <a href="daftar_ulang.php" class="menu-item active">
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
                <h2><i class="fas fa-file-signature"></i> Daftar Ulang</h2>
            </div>
            <a href="logout.php" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>

        <div class="container">
            <?php if ($belum_test): ?>
                <div class="warning-card">
                    <div class="warning-icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <h3>Maaf, Anda Belum Melakukan Test</h3>
                    <div class="warning-message">
                        <p>Anda belum mengikuti test seleksi PMB.</p>
                        <p>Daftar ulang hanya dapat dilakukan setelah Anda dinyatakan <strong>LULUS</strong> pada test seleksi.</p>
                        <p>Silakan selesaikan test terlebih dahulu untuk melanjutkan proses daftar ulang.</p>
                    </div>
                    <a href="test.php" class="btn-test-warning">
                        <i class="fas fa-play"></i> Mulai Test Sekarang
                    </a>
                </div>

            <?php elseif ($tidak_lulus): ?>
                <div class="warning-card">
                    <div class="warning-icon" style="background: #FEF2F2;">
                        <i class="fas fa-frown" style="color: #dc3545;"></i>
                    </div>
                    <h3>Maaf, Anda Tidak Lulus Test Seleksi</h3>
                    <div class="warning-message">
                        <p>Anda dinyatakan <strong>TIDAK LULUS</strong> pada test seleksi PMB.</p>
                        <p>Daftar ulang hanya dapat dilakukan oleh peserta yang dinyatakan <strong>LULUS</strong>.</p>
                        <p>Terima kasih telah berpartisipasi. Tetap semangat dan coba lagi di tahun berikutnya.</p>
                    </div>
                    <a href="hasil_test.php" class="btn-test-warning" style="background: linear-gradient(135deg, #6c757d, #5a6268);">
                        <i class="fas fa-chart-line"></i> Lihat Hasil Test
                    </a>
                </div>

            <?php elseif ($lulus): ?>
                <div class="daftar-card">
                    <div class="card-header">
                        <div class="icon-circle">
                            <i class="fas fa-file-signature"></i>
                        </div>
                        <h2>Daftar Ulang</h2>
                        <p>Lengkapi biodata untuk menyelesaikan pendaftaran</p>
                    </div>
                    
                    <div class="card-body">
                        <?php if ($message): ?>
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle"></i>
                                <span><?php echo $message; ?></span>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($error): ?>
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-circle"></i>
                                <span><?php echo $error; ?></span>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($sudah_daftar && $data_daftar): ?>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i>
                                <span>Anda sudah melakukan daftar ulang pada <?php echo date('d/m/Y H:i', strtotime($data_daftar['tanggal_daftar_ulang'])); ?></span>
                            </div>
                            <div class="status-card">
                                <div class="status-item">
                                    <div class="status-label">Nilai Test</div>
                                    <div class="status-value"><?php echo $data_daftar['nilai_test']; ?></div>
                                </div>
                                <div class="status-item">
                                    <div class="status-label">NIM</div>
                                    <div class="status-nim"><?php echo htmlspecialchars($data_daftar['nim']); ?></div>
                                </div>
                                <div class="status-item">
                                    <div class="status-label">Status</div>
                                    <div class="status-value" style="font-size: 16px;"><?php echo $data_daftar['status'] == 'pending' ? 'Menunggu Verifikasi' : 'Terverifikasi'; ?></div>
                                </div>
                            </div>
                            
                        <?php else: ?>
                            <form method="POST" enctype="multipart/form-data">
                                <!-- Data Pribadi -->
                                <div class="section-title">
                                    <i class="fas fa-user-circle"></i> Data Pribadi
                                </div>
                                <div class="form-grid">
                                    <div class="form-group">
                                        <label><i class="fas fa-user"></i> Nama Lengkap</label>
                                        <input type="text" name="nama_lengkap" value="<?php echo htmlspecialchars($user['nama']); ?>" placeholder="Masukkan nama lengkap" required>
                                    </div>
                                    <div class="form-group">
                                        <label><i class="fas fa-phone"></i> No Telepon</label>
                                        <input type="text" name="no_telepon" value="<?php echo htmlspecialchars($no_telp_registrasi); ?>" placeholder="Masukkan nomor telepon" required readonly>
                                    </div>
                                    <div class="form-group">
                                        <label><i class="fas fa-calendar"></i> Tanggal Lahir</label>
                                        <input type="date" name="tanggal_lahir" required>
                                    </div>
                                    <div class="form-group">
                                        <label><i class="fas fa-venus-mars"></i> Jenis Kelamin</label>
                                        <select name="jenis_kelamin" required>
                                            <option value="">Pilih Jenis Kelamin</option>
                                            <option value="Laki-laki">Laki-laki</option>
                                            <option value="Perempuan">Perempuan</option>
                                        </select>
                                    </div>
                                    <div class="form-group full-width">
                                        <label><i class="fas fa-map-marker-alt"></i> Alamat Lengkap</label>
                                        <textarea name="alamat" rows="2" placeholder="Masukkan alamat lengkap" required><?php echo htmlspecialchars($alamat_registrasi); ?></textarea>
                                    </div>
                                </div>

                                <!-- Data Lainnya -->
                                <div class="section-title">
                                    <i class="fas fa-id-card"></i> Data Lainnya
                                </div>
                                <div class="form-grid">
                                    <div class="form-group">
                                        <label><i class="fas fa-envelope"></i> Email</label>
                                        <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" readonly style="background:#F8FAFC;">
                                    </div>
                                    <div class="form-group">
                                        <label><i class="fas fa-id-card"></i> NIK (16 digit)</label>
                                        <input type="text" name="nik" maxlength="16" placeholder="Masukkan NIK" required>
                                    </div>
                                    <div class="form-group">
                                        <label><i class="fas fa-map-pin"></i> Tempat Lahir</label>
                                        <input type="text" name="tempat_lahir" placeholder="Masukkan kota lahir" required>
                                    </div>
                                    <div class="form-group">
                                        <label><i class="fas fa-pray"></i> Agama</label>
                                        <select name="agama" required>
                                            <option value="">Pilih Agama</option>
                                            <option value="Islam">Islam</option>
                                            <option value="Kristen">Kristen</option>
                                            <option value="Katolik">Katolik</option>
                                            <option value="Hindu">Hindu</option>
                                            <option value="Buddha">Buddha</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Data Orang Tua -->
                                <div class="section-title">
                                    <i class="fas fa-users"></i> Data Orang Tua
                                </div>
                                <div class="form-grid">
                                    <div class="form-group">
                                        <label><i class="fas fa-user"></i> Nama Ayah</label>
                                        <input type="text" name="nama_ayah" placeholder="Masukkan nama ayah" required>
                                    </div>
                                    <div class="form-group">
                                        <label><i class="fas fa-user"></i> Nama Ibu</label>
                                        <input type="text" name="nama_ibu" placeholder="Masukkan nama ibu" required>
                                    </div>
                                    <div class="form-group">
                                        <label><i class="fas fa-briefcase"></i> Pekerjaan Ayah</label>
                                        <input type="text" name="pekerjaan_ayah" placeholder="Masukkan pekerjaan ayah" required>
                                    </div>
                                    <div class="form-group">
                                        <label><i class="fas fa-money-bill"></i> Penghasilan Orang Tua</label>
                                        <select name="penghasilan_ortu" required>
                                            <option value="">Pilih Penghasilan</option>
                                            <option value="< 1.000.000">&lt; Rp 1.000.000</option>
                                            <option value="1.000.000 - 3.000.000">Rp 1.000.000 - Rp 3.000.000</option>
                                            <option value="3.000.000 - 5.000.000">Rp 3.000.000 - Rp 5.000.000</option>
                                            <option value="5.000.000 - 10.000.000">Rp 5.000.000 - Rp 10.000.000</option>
                                            <option value="> 10.000.000">&gt; Rp 10.000.000</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Upload Dokumen -->
                                <div class="section-title">
                                    <i class="fas fa-upload"></i> Upload Dokumen
                                </div>
                                <div class="form-grid">
                                    <div class="form-group">
                                        <label><i class="fas fa-camera"></i> Foto 3×4</label>
                                        <div class="upload-area" onclick="document.getElementById('foto').click()">
                                            <i class="fas fa-image"></i>
                                            <p>Klik untuk upload foto 3×4</p>
                                            <small>Format JPG/PNG, max 2MB</small>
                                            <input type="file" name="foto" id="foto" accept="image/jpeg,image/png">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label><i class="fas fa-id-card"></i> KTP/SIM</label>
                                        <div class="upload-area" onclick="document.getElementById('ktp').click()">
                                            <i class="fas fa-file-alt"></i>
                                            <p>Klik untuk upload KTP/SIM</p>
                                            <small>Format PDF/JPG/PNG, max 2MB</small>
                                            <input type="file" name="ktp" id="ktp" accept=".pdf,image/jpeg,image/png">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label><i class="fas fa-graduation-cap"></i> Ijazah</label>
                                        <div class="upload-area" onclick="document.getElementById('ijazah').click()">
                                            <i class="fas fa-file-pdf"></i>
                                            <p>Klik untuk upload Ijazah</p>
                                            <small>Format PDF/JPG/PNG, max 2MB</small>
                                            <input type="file" name="ijazah" id="ijazah" accept=".pdf,image/jpeg,image/png">
                                        </div>
                                    </div>
                                </div>

                                <!-- Status Pendaftaran -->
                                <div class="section-title">
                                    <i class="fas fa-chart-line"></i> Status Pendaftaran
                                </div>
                                <div class="status-card">
                                    <div class="status-item">
                                        <div class="status-label">Nilai Test</div>
                                        <div class="status-value"><?php echo $nilai_test; ?></div>
                                    </div>
                                    <div class="status-item">
                                        <div class="status-label">NIM</div>
                                        <div class="status-nim"><?php echo $nim_otomatis; ?></div>
                                    </div>
                                </div>

                                <button type="submit" class="btn-submit">
                                    <i class="fas fa-check-circle"></i> Konfirmasi Daftar Ulang
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        document.getElementById('foto')?.addEventListener('change', function(e) {
            if(e.target.files[0]) {
                let uploadArea = this.parentElement;
                uploadArea.querySelector('p').innerHTML = '<i class="fas fa-check-circle"></i> ' + e.target.files[0].name;
                uploadArea.querySelector('small').innerHTML = '';
            }
        });
        document.getElementById('ktp')?.addEventListener('change', function(e) {
            if(e.target.files[0]) {
                let uploadArea = this.parentElement;
                uploadArea.querySelector('p').innerHTML = '<i class="fas fa-check-circle"></i> ' + e.target.files[0].name;
                uploadArea.querySelector('small').innerHTML = '';
            }
        });
        document.getElementById('ijazah')?.addEventListener('change', function(e) {
            if(e.target.files[0]) {
                let uploadArea = this.parentElement;
                uploadArea.querySelector('p').innerHTML = '<i class="fas fa-check-circle"></i> ' + e.target.files[0].name;
                uploadArea.querySelector('small').innerHTML = '';
            }
        });
    </script>
</body>
</html>