<?php
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

$nama = $_SESSION['admin_nama'];
$email = $_SESSION['admin_email'];

$host = 'localhost';
$dbname = 'pembe';
$user = 'root';
$pass = '';

$conn = mysqli_connect($host, $user, $pass, $dbname);
if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

// Cek struktur tabel hasil_test
$check_table = mysqli_query($conn, "SHOW TABLES LIKE 'hasil_test'");
if (mysqli_num_rows($check_table) == 0) {
    // Tabel tidak ada, buat tabel dengan struktur lengkap
    $create_table = "CREATE TABLE hasil_test (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nama_peserta VARCHAR(100) NOT NULL,
        jurusan VARCHAR(50) NOT NULL,
        jawaban_benar INT DEFAULT 0,
        total_soal INT DEFAULT 0,
        total_nilai INT DEFAULT 0,
        status VARCHAR(20) DEFAULT 'tidak lulus',
        tanggal_test DATETIME DEFAULT CURRENT_TIMESTAMP
    )";
    
    if (!mysqli_query($conn, $create_table)) {
        die("Gagal membuat tabel: " . mysqli_error($conn));
    }
} else {
    // Cek apakah kolom total_nilai ada
    $check_column = mysqli_query($conn, "SHOW COLUMNS FROM hasil_test LIKE 'total_nilai'");
    if (mysqli_num_rows($check_column) == 0) {
        // Tambahkan kolom total_nilai jika belum ada
        $add_column = "ALTER TABLE hasil_test ADD COLUMN total_nilai INT DEFAULT 0 AFTER total_soal";
        if (!mysqli_query($conn, $add_column)) {
            die("Gagal menambah kolom: " . mysqli_error($conn));
        }
    }
    
    // Cek apakah kolom jawaban_benar ada
    $check_column2 = mysqli_query($conn, "SHOW COLUMNS FROM hasil_test LIKE 'jawaban_benar'");
    if (mysqli_num_rows($check_column2) == 0) {
        $add_column2 = "ALTER TABLE hasil_test ADD COLUMN jawaban_benar INT DEFAULT 0 AFTER jurusan";
        if (!mysqli_query($conn, $add_column2)) {
            die("Gagal menambah kolom: " . mysqli_error($conn));
        }
    }
    
    // Cek apakah kolom total_soal ada
    $check_column3 = mysqli_query($conn, "SHOW COLUMNS FROM hasil_test LIKE 'total_soal'");
    if (mysqli_num_rows($check_column3) == 0) {
        $add_column3 = "ALTER TABLE hasil_test ADD COLUMN total_soal INT DEFAULT 0 AFTER jawaban_benar";
        if (!mysqli_query($conn, $add_column3)) {
            die("Gagal menambah kolom: " . mysqli_error($conn));
        }
    }
}

// Ambil ranking berdasarkan nilai tertinggi
$query = "SELECT * FROM hasil_test ORDER BY total_nilai DESC, tanggal_test ASC";
$result = mysqli_query($conn, $query);

// Periksa apakah query berhasil
if (!$result) {
    die("Query Error: " . mysqli_error($conn) . "<br>Query: " . $query);
}

// Ambil data
$ranking_list = [];
if (mysqli_num_rows($result) > 0) {
    $ranking_list = mysqli_fetch_all($result, MYSQLI_ASSOC);
}

// Hitung statistik
$total_peserta = count($ranking_list);
$total_lulus = 0;
$nilai_tertinggi = 0;
$rata_rata = 0;

foreach ($ranking_list as $r) {
    if ($r['status'] == 'lulus') {
        $total_lulus++;
    }
    if ($r['total_nilai'] > $nilai_tertinggi) {
        $nilai_tertinggi = $r['total_nilai'];
    }
    $rata_rata += $r['total_nilai'];
}

if ($total_peserta > 0) {
    $rata_rata = round($rata_rata / $total_peserta, 2);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Ranking | PMB System</title>
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
            padding: 25px 30px;
        }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            border-radius: 20px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            transition: all 0.3s;
            border: 1px solid #F0E6D2;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }

        .stat-info h4 {
            font-size: 13px;
            color: #8B7355;
            font-weight: 500;
            margin-bottom: 8px;
        }

        .stat-number {
            font-size: 32px;
            font-weight: 800;
            color: #B8860B;
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            background: rgba(212, 160, 23, 0.1);
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .stat-icon i {
            font-size: 24px;
            color: #D4A017;
        }

        /* Card */
        .card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            overflow: hidden;
            border: 1px solid #F0E6D2;
        }

        .card-header {
            padding: 18px 25px;
            border-bottom: 1px solid #F0E6D2;
            background: #FFFDF5;
        }

        .card-header h3 {
            font-size: 18px;
            font-weight: 700;
            color: #B8860B;
        }

        .card-header h3 i {
            margin-right: 10px;
            color: #D4A017;
        }

        /* Ranking Table */
        .ranking-table {
            width: 100%;
            border-collapse: collapse;
        }

        .ranking-table th {
            text-align: left;
            padding: 15px 12px;
            background: #FFFDF5;
            font-weight: 600;
            font-size: 13px;
            color: #8B7355;
            border-bottom: 2px solid #F0E6D2;
        }

        .ranking-table td {
            padding: 12px;
            font-size: 13px;
            border-bottom: 1px solid #F0E6D2;
            vertical-align: middle;
            color: #5a4a2a;
        }

        .ranking-table tr:hover {
            background: #FFFDF5;
        }

        /* Rank Styles */
        .rank-1 {
            background: linear-gradient(135deg, #FFF9E6, #FFF3C4);
        }

        .rank-2 {
            background: linear-gradient(135deg, #FFF8F0, #FFEFE0);
        }

        .rank-3 {
            background: linear-gradient(135deg, #FDF8E8, #F5EDD6);
        }

        .medal {
            font-size: 20px;
            margin-right: 5px;
        }

        /* Badges */
        .badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
        }

        .badge-success {
            background: #d4edda;
            color: #155724;
        }

        .badge-danger {
            background: #f8d7da;
            color: #721c24;
        }

        .badge-jurusan {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            display: inline-block;
        }

        .badge-si {
            background: #E8F4FD;
            color: #0288D1;
        }

        .badge-ti {
            background: #FFF3E0;
            color: #E65100;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 50px;
            color: #8B7355;
        }

        .empty-state i {
            font-size: 48px;
            margin-bottom: 15px;
            opacity: 0.5;
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
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
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
            .stats-grid {
                grid-template-columns: 1fr;
            }
            .ranking-table {
                font-size: 11px;
            }
            .ranking-table th, .ranking-table td {
                padding: 8px;
            }
        }

        /* Print Styles */
        @media print {
            .sidebar, .top-header, .logout-btn, .stats-grid {
                display: none;
            }
            .main-content {
                margin-left: 0;
            }
            .container {
                padding: 0;
            }
            .card {
                box-shadow: none;
            }
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-header">
            <h3><i class="fas fa-graduation-cap"></i> UCAN</h3>
            <p>Universitas Cakrawala Nusantara</p>
        </div>
        <div class="sidebar-menu">
            <a href="dashboard.php" class="menu-item"><i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a>
            <a href="maba.php" class="menu-item"><i class="fas fa-users"></i><span>Data Maba</span></a>
            <a href="soal.php" class="menu-item"><i class="fas fa-question-circle"></i><span>Kelola Soal</span></a>
            <a href="hasil.php" class="menu-item"><i class="fas fa-chart-line"></i><span>Hasil Test</span></a>
            <a href="ranking.php" class="menu-item active"><i class="fas fa-trophy"></i><span>Ranking</span></a>
            <a href="daftar_ulang.php" class="menu-item"><i class="fas fa-file-signature"></i><span>Daftar Ulang</span></a>
            <div class="menu-divider"></div>
            <a href="logout.php" class="menu-item"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a>
        </div>
    </div>

    <div class="main-content">
        <div class="top-header">
            <div class="page-title">
                <h2><i class="fas fa-trophy"></i> Ranking Peserta</h2>
            </div>
            <a href="logout.php" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
        <div class="container">
            <!-- Statistik Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-info">
                        <h4>Total Peserta</h4>
                        <div class="stat-number"><?php echo $total_peserta; ?></div>
                    </div>
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-info">
                        <h4>Total Lulus</h4>
                        <div class="stat-number"><?php echo $total_lulus; ?></div>
                    </div>
                    <div class="stat-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-info">
                        <h4>Nilai Tertinggi</h4>
                        <div class="stat-number"><?php echo $nilai_tertinggi; ?></div>
                    </div>
                    <div class="stat-icon">
                        <i class="fas fa-trophy"></i>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-info">
                        <h4>Rata-rata Nilai</h4>
                        <div class="stat-number"><?php echo $rata_rata; ?></div>
                    </div>
                    <div class="stat-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                </div>
            </div>
            
            <!-- Ranking Table -->
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-trophy"></i> Peringkat Nilai Tertinggi</h3>
                </div>
                <?php if (count($ranking_list) > 0): ?>
                <div style="overflow-x: auto;">
                    <table class="ranking-table">
                        <thead>
                             <tr>
                                <th>Rank</th>
                                <th>Nama Peserta</th>
                                <th>Jurusan</th>
                                <th>Jawaban Benar</th>
                                <th>Total Nilai</th>
                                <th>Status</th>
                             </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($ranking_list as $index => $r): ?>
                            <tr class="<?php echo $index == 0 ? 'rank-1' : ($index == 1 ? 'rank-2' : ($index == 2 ? 'rank-3' : '')); ?>">
                                <td>
                                    <?php if ($index == 0): ?>
                                        <span class="medal">🥇</span> 1
                                    <?php elseif ($index == 1): ?>
                                        <span class="medal">🥈</span> 2
                                    <?php elseif ($index == 2): ?>
                                        <span class="medal">🥉</span> 3
                                    <?php else: ?>
                                        <?php echo $index + 1; ?>
                                    <?php endif; ?>
                                </td>
                                <td><strong><?php echo htmlspecialchars($r['nama_peserta']); ?></strong></td>
                                <td>
                                    <span class="badge-jurusan <?php echo $r['jurusan'] == 'Sistem Informasi' ? 'badge-si' : 'badge-ti'; ?>">
                                        <i class="fas <?php echo $r['jurusan'] == 'Sistem Informasi' ? 'fa-laptop-code' : 'fa-microchip'; ?>"></i>
                                        <?php echo htmlspecialchars($r['jurusan']); ?>
                                    </span>
                                </td>
                                <td><?php echo $r['jawaban_benar']; ?> / <?php echo $r['total_soal']; ?></td>
                                <td><strong style="font-size:16px; color:#B8860B;"><?php echo $r['total_nilai']; ?></strong></td>
                                <td>
                                    <span class="badge <?php echo $r['status'] == 'lulus' ? 'badge-success' : 'badge-danger'; ?>">
                                        <?php echo $r['status'] == 'lulus' ? 'Lulus' : 'Tidak Lulus'; ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <p>Belum ada data ranking.</p>
                    <p style="font-size: 12px; margin-top: 10px;">Silakan lakukan test terlebih dahulu</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>