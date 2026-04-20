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

// Ambil data daftar ulang
$query_du = "SELECT * FROM daftar_ulang WHERE id_peserta = $user_id ORDER BY id DESC LIMIT 1";
$result_du = mysqli_query($conn, $query_du);
$daftar_ulang = mysqli_fetch_assoc($result_du);
$nim = $daftar_ulang['nim'] ?? null;

// Ambil hasil test
$query_test = "SELECT * FROM hasil_test WHERE id_peserta = $user_id ORDER BY id DESC LIMIT 1";
$result_test = mysqli_query($conn, $query_test);
$hasil = mysqli_fetch_assoc($result_test);

$total_nilai = $hasil['total_nilai'] ?? 0;
$status = $hasil['status'] ?? 'belum';
$jumlah_benar = $hasil['jumlah_benar'] ?? 0;
$jumlah_salah = $hasil['jumlah_salah'] ?? 0;
$sudah_tes = ($status != 'belum');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard User | PMB System</title>
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

        /* Sidebar - Sama dengan Dashboard Admin */
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
            transition: all 0.3s;
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
            transition: all 0.3s;
            position: relative;
            z-index: 1;
        }

        /* Top Header - Tanpa user info */
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
            padding: 25px 30px;
        }

        /* Welcome Card */
        .welcome-card {
            background: white;
            border-radius: 20px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            border-left: 5px solid #D4A017;
            border: 1px solid #F0E6D2;
        }

        .welcome-card h3 {
            font-size: 22px;
            color: #B8860B;
            margin-bottom: 8px;
        }

        .welcome-card p {
            color: #8B7355;
            font-size: 14px;
        }

        .test-badge {
            display: inline-block;
            background: #FFFDF5;
            padding: 5px 15px;
            border-radius: 30px;
            margin-top: 15px;
            font-size: 13px;
            color: #B8860B;
            border: 1px solid #F0E6D2;
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            border-radius: 20px;
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            transition: all 0.3s;
            border: 1px solid #F0E6D2;
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }

        .stat-icon {
            width: 55px;
            height: 55px;
            background: rgba(212, 160, 23, 0.1);
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .stat-icon i {
            font-size: 26px;
            color: #D4A017;
        }

        .stat-info h3 {
            font-size: 28px;
            font-weight: 800;
            color: #B8860B;
        }

        .stat-info p {
            font-size: 13px;
            color: #000000;
        }

        /* Info Card */
        .info-card {
            background: white;
            border-radius: 20px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            border: 1px solid #F0E6D2;
        }

        .info-card h4 {
            font-size: 18px;
            font-weight: 700;
            color: #B8860B;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #F0E6D2;
        }

        .info-card h4 i {
            color: #D4A017;
            margin-right: 8px;
        }

        .info-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .info-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid #F0E6D2;
        }

        .info-label {
            color: #000000;
            font-size: 14px;
            font-weight: 500;
        }

        .info-value {
            font-weight: 600;
            color: #B8860B;
        }

        /* Action Card */
        .action-card {
            background: #FFFDF5;
            border-radius: 20px;
            padding: 25px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            border: 1px solid #F0E6D2;
        }

        .action-card h3 {
            font-size: 18px;
            color: #B8860B;
            margin-bottom: 15px;
        }

        .action-card h3 i {
            color: #D4A017;
            margin-right: 8px;
        }

        .action-card p {
            color: #000000;
            font-size: 14px;
            margin-bottom: 20px;
        }

        .btn-test {
            background: linear-gradient(135deg, #D4A017, #B8860B);
            color: white;
            padding: 12px 30px;
            border-radius: 30px;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
        }

        .btn-test:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(212, 160, 23, 0.4);
        }

        .btn-hasil {
            background: linear-gradient(135deg, #C49A0C, #A07608);
            color: white;
            padding: 12px 30px;
            border-radius: 30px;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
        }

        .btn-hasil:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(196, 154, 12, 0.4);
        }

        .badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .badge-success {
            background: #FEF3C7;
            color: #B8860B;
        }

        .badge-warning {
            background: #fff3cd;
            color: #856404;
        }

        .badge-danger {
            background: #f8d7da;
            color: #721c24;
        }

        .d-flex {
            display: flex;
        }

        .justify-content-between {
            justify-content: space-between;
        }

        .align-items-center {
            align-items: center;
        }

        .flex-wrap {
            flex-wrap: wrap;
        }

        .gap-15 {
            gap: 15px;
        }

        /* Responsive */
        @media (max-width: 992px) {
            .sidebar {
                width: 80px;
            }
            .sidebar-header h3, .sidebar-header p, .menu-item span {
                display: none;
            }
            .sidebar-header {
                text-align: center;
                padding: 20px 10px;
            }
            .menu-item {
                justify-content: center;
            }
            .menu-item i {
                width: auto;
                font-size: 20px;
            }
            .main-content {
                margin-left: 80px;
            }
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 0;
                transform: translateX(-100%);
            }
            .main-content {
                margin-left: 0;
            }
            .top-header {
                flex-direction: column;
                text-align: center;
                gap: 15px;
            }
            .info-item {
                flex-direction: column;
                text-align: center;
                gap: 5px;
            }
            .welcome-card .d-flex {
                flex-direction: column;
                text-align: center;
            }
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
            <a href="dashboard.php" class="menu-item active">
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
                <h2><i class="fas fa-chalkboard-user"></i> Dashboard User</h2>
            </div>
            <a href="logout.php" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>

        <div class="container">
            <!-- Welcome Card - Tombol NIM dihapus -->
            <div class="welcome-card">
                <div>
                    <h3>Selamat Datang, <?php echo htmlspecialchars($user['nama']); ?>! 👋</h3>
                    <p>Terima kasih telah mendaftar di Universitas Cakrawala Nusantara</p>
                    <div class="test-badge">
                        <i class="fas fa-ticket-alt"></i> Nomor Tes: <?php echo htmlspecialchars($user['no_test'] ?? 'PMB' . str_pad($user_id, 5, '0', STR_PAD_LEFT)); ?>
                    </div>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-id-card"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $nim ?: 'Belum'; ?></h3>
                        <p>NIM</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php 
                            if ($status == 'lulus') echo '<span class="badge badge-success">LULUS</span>';
                            elseif ($status == 'tidak_lulus') echo '<span class="badge badge-danger">TIDAK LULUS</span>';
                            else echo '<span class="badge badge-warning">BELUM TEST</span>';
                        ?></h3>
                        <p>Status Test</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-star"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $total_nilai ?: '0'; ?></h3>
                        <p>Nilai Test</p>
                    </div>
                </div>
            </div>

            <!-- Informasi Pendaftaran -->
            <div class="info-card">
                <h4><i class="fas fa-info-circle"></i> Informasi Pendaftaran</h4>
                <div class="info-list">
                    <div class="info-item">
                        <span class="info-label">Nama Lengkap</span>
                        <span class="info-value"><?php echo htmlspecialchars($user['nama']); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Email</span>
                        <span class="info-value"><?php echo htmlspecialchars($user['email']); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Jurusan</span>
                        <span class="info-value"><?php echo htmlspecialchars($user['jurusan'] ?? '-'); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Status Pengerjaan</span>
                        <span class="info-value"><?php echo $sudah_tes ? 'Sudah Mengerjakan' : 'Belum Tes'; ?></span>
                    </div>
                    <?php if ($sudah_tes): ?>
                    <div class="info-item">
                        <span class="info-label">Nilai Test</span>
                        <span class="info-value"><strong><?php echo $total_nilai; ?></strong></span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Status Pendaftaran -->
            <div class="info-card">
                <h4><i class="fas fa-clipboard-list"></i> Status Pendaftaran</h4>
                <div class="info-list">
                    <div class="info-item">
                        <span class="info-label">Nomor Tes</span>
                        <span class="info-value"><?php echo htmlspecialchars($user['no_test'] ?? 'PMB' . str_pad($user_id, 5, '0', STR_PAD_LEFT)); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Status Test</span>
                        <span class="info-value">
                            <?php if ($status == 'lulus'): ?>
                                <span class="badge badge-success">Lulus</span>
                            <?php elseif ($status == 'tidak_lulus'): ?>
                                <span class="badge badge-danger">Tidak Lulus</span>
                            <?php else: ?>
                                <span class="badge badge-warning">Belum Test</span>
                            <?php endif; ?>
                        </span>
                    </div>
                    <?php if ($nim): ?>
                    <div class="info-item">
                        <span class="info-label">NIM</span>
                        <span class="info-value"><strong><?php echo $nim; ?></strong></span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Action Card -->
            <div class="action-card">
                <?php if (!$sudah_tes): ?>
                    <h3><i class="fas fa-pen-alt"></i> Test Penerimaan</h3>
                    <p>Anda belum mengerjakan tes seleksi PMB. Klik tombol di bawah untuk mulai tes.</p>
                    <a href="test.php" class="btn-test">
                        <i class="fas fa-play"></i> Mulai Test Seleksi
                    </a>
                <?php elseif ($status == 'lulus' && !$nim): ?>
                    <h3><i class="fas fa-file-signature"></i> Daftar Ulang</h3>
                    <p>Anda dinyatakan LULUS. Silakan lengkapi data daftar ulang untuk mendapatkan NIM.</p>
                    <a href="daftar_ulang.php" class="btn-test">
                        <i class="fas fa-file-signature"></i> Daftar Ulang Sekarang
                    </a>
                <?php elseif ($status == 'lulus' && $nim): ?>
                    <h3><i class="fas fa-check-circle"></i> Pendaftaran Selesai</h3>
                    <p>Selamat! Anda telah terdaftar sebagai mahasiswa dengan NIM: <strong><?php echo $nim; ?></strong></p>
                    <a href="hasil_test.php" class="btn-hasil">
                        <i class="fas fa-chart-line"></i> Lihat Hasil Test
                    </a>
                <?php elseif ($status == 'tidak_lulus'): ?>
                    <h3><i class="fas fa-frown"></i> Maaf, Anda Tidak Lulus</h3>
                    <p>Anda dinyatakan TIDAK LULUS seleksi PMB. Tetap semangat dan coba lagi di tahun berikutnya.</p>
                    <a href="hasil_test.php" class="btn-hasil">
                        <i class="fas fa-chart-line"></i> Lihat Hasil Test
                    </a>
                <?php else: ?>
                    <h3><i class="fas fa-hourglass-half"></i> Menunggu Hasil</h3>
                    <p>Anda telah mengerjakan test. Hasil akan segera diumumkan.</p>
                    <a href="hasil_test.php" class="btn-hasil">
                        <i class="fas fa-chart-line"></i> Lihat Hasil Test
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        function copyNIM() {
            const nim = '<?php echo $nim; ?>';
            navigator.clipboard.writeText(nim);
            alert('NIM ' + nim + ' telah disalin ke clipboard!');
        }
    </script>
</body>
</html>