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

// Ambil hasil test terbaru
$query = "SELECT * FROM hasil_test WHERE id_peserta = $user_id ORDER BY id DESC LIMIT 1";
$result = mysqli_query($conn, $query);
$hasil = mysqli_fetch_assoc($result);

if (!$hasil) {
    header('Location: dashboard.php');
    exit();
}

$total_nilai = $hasil['total_nilai'];
$status = $hasil['status'];
$jumlah_benar = $hasil['jumlah_benar'];
$jumlah_salah = $hasil['jumlah_salah'];
$persentase = $hasil['persentase'] ?? round(($total_nilai / 100) * 100);

// Tentukan grade
if ($total_nilai >= 90) {
    $grade = 'A+';
    $grade_desc = 'Sangat Baik';
} elseif ($total_nilai >= 80) {
    $grade = 'A';
    $grade_desc = 'Baik Sekali';
} elseif ($total_nilai >= 70) {
    $grade = 'B';
    $grade_desc = 'Baik';
} elseif ($total_nilai >= 60) {
    $grade = 'C';
    $grade_desc = 'Cukup';
} elseif ($total_nilai >= 50) {
    $grade = 'D';
    $grade_desc = 'Kurang';
} else {
    $grade = 'E';
    $grade_desc = 'Gagal';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hasil Test | PMB System</title>
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

        /* Sidebar - Dashboard Admin Color */
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

        .result-card {
            background: white;
            border-radius: 24px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            overflow: hidden;
            border: 1px solid #F0E6D2;
        }

        .result-header {
            background: linear-gradient(135deg, #D4A017, #B8860B);
            padding: 25px;
            text-align: center;
            color: white;
        }

        .result-header h3 {
            font-size: 20px;
            font-weight: 700;
        }

        .result-header p {
            font-size: 14px;
            margin-top: 5px;
            opacity: 0.9;
        }

        .score-container {
            text-align: center;
            margin: 25px 0;
        }

        .score-value {
            font-size: 56px;
            font-weight: 800;
            color: #B8860B;
        }

        .grade-badge {
            display: inline-block;
            padding: 5px 20px;
            border-radius: 30px;
            font-weight: 700;
            background: #D4A017;
            color: white;
            margin: 10px 0;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin: 20px 0;
        }

        .stat-card {
            background: #FFFDF5;
            padding: 15px;
            border-radius: 16px;
            text-align: center;
            border: 1px solid #F0E6D2;
        }

        .stat-card i {
            font-size: 28px;
            margin-bottom: 8px;
        }

        .stat-card.benar i { color: #28a745; }
        .stat-card.salah i { color: #dc3545; }

        .stat-value {
            font-size: 28px;
            font-weight: 800;
            color: #B8860B;
        }

        .stat-card div:last-child {
            color: #000000;
        }

        .status-lulus {
            background: #FEF3C7;
            color: #B8860B;
            padding: 12px;
            border-radius: 12px;
            text-align: center;
            margin: 20px 0;
            border-left: 4px solid #D4A017;
        }

        .status-tidak {
            background: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-radius: 12px;
            text-align: center;
            margin: 20px 0;
            border-left: 4px solid #dc3545;
        }

        .btn-daftar {
            background: linear-gradient(135deg, #D4A017, #B8860B);
            color: white;
            padding: 12px 25px;
            border-radius: 30px;
            text-decoration: none;
            display: inline-block;
            margin: 10px 0;
            transition: all 0.3s;
            text-align: center;
        }

        .btn-daftar:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(212, 160, 23, 0.4);
        }

        .button-group {
            display: flex;
            flex-direction: column;
            gap: 15px;
            justify-content: center;
            align-items: center;
            margin-top: 20px;
        }

        .button-group .btn-daftar {
            width: auto;
            min-width: 200px;
        }

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
            .stats-grid { grid-template-columns: 1fr; }
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
            <a href="hasil_test.php" class="menu-item active">
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
                <h2><i class="fas fa-chart-line"></i> Hasil Test Seleksi</h2>
            </div>
            <a href="logout.php" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>

        <div class="container">
            <div class="result-card">
                <div class="result-header">
                    <h3><i class="fas fa-clipboard-list"></i> Hasil Test</h3>
                    <p>Terima kasih telah mengikuti ujian masuk Pendaftaran Mahasiswa Baru Univeritas Ckrawala Nusantara</p>
                </div>
                
                <div style="padding: 25px;">
                    <?php if ($status == 'lulus'): ?>
                        <div class="status-lulus">
                            <i class="fas fa-trophy"></i> Selamat! Anda LULUS dengan nilai <?php echo $total_nilai; ?>
                        </div>
                    <?php else: ?>
                        <div class="status-tidak">
                            <i class="fas fa-frown"></i> Maaf, Anda TIDAK LULUS dengan nilai <?php echo $total_nilai; ?>
                        </div>
                    <?php endif; ?>

                    <div class="score-container">
                        <div class="score-value"><?php echo $total_nilai; ?></div>
                        <div class="grade-badge"><?php echo $grade; ?> | <?php echo $grade_desc; ?></div>
                    </div>

                    <div class="stats-grid">
                        <div class="stat-card benar">
                            <i class="fas fa-check-circle"></i>
                            <div class="stat-value"><?php echo $jumlah_benar; ?></div>
                            <div>Jawaban Benar</div>
                        </div>
                        <div class="stat-card salah">
                            <i class="fas fa-times-circle"></i>
                            <div class="stat-value"><?php echo $jumlah_salah; ?></div>
                            <div>Jawaban Salah</div>
                        </div>
                    </div>

                    <div class="button-group">
                        <?php if ($status == 'lulus'): ?>
                            <a href="daftar_ulang.php" class="btn-daftar">
                                <i class="fas fa-file-signature"></i> Daftar Ulang Sekarang
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>