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

// ============================================
// BUAT TABEL HASIL_TEST JIKA BELUM ADA
// ============================================
mysqli_query($conn, "CREATE TABLE IF NOT EXISTS hasil_test (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_peserta INT,
    nama_peserta VARCHAR(100),
    jurusan VARCHAR(50),
    total_nilai INT,
    jumlah_benar INT,
    jumlah_salah INT,
    persentase DECIMAL(5,2),
    status VARCHAR(20),
    tanggal_test TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

// ============================================
// AMBIL DATA HASIL TEST
// ============================================
$query = "SELECT * FROM hasil_test ORDER BY total_nilai DESC, tanggal_test DESC";
$result = mysqli_query($conn, $query);
$hasil_list = [];
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $hasil_list[] = $row;
    }
}

// ============================================
// HITUNG STATISTIK
// ============================================
$total_peserta = count($hasil_list);
$total_nilai = 0;
$lulus = 0;
$tidak_lulus = 0;

foreach ($hasil_list as $h) {
    $total_nilai += $h['total_nilai'];
    if ($h['status'] == 'lulus') {
        $lulus++;
    } else {
        $tidak_lulus++;
    }
}
$rata_rata = $total_peserta > 0 ? round($total_nilai / $total_peserta) : 0;

// Fungsi untuk badge status (warna mint/teal soft)
function getStatusBadge($status) {
    switch($status) {
        case 'lulus':
            return '<span class="badge badge-success"><i class="fas fa-check-circle"></i> Lulus</span>';
        case 'tidak_lulus':
            return '<span class="badge badge-danger"><i class="fas fa-times-circle"></i> Tidak Lulus</span>';
        default:
            return '<span class="badge badge-secondary">' . htmlspecialchars($status) . '</span>';
    }
}

// Fungsi untuk badge nilai (warna mint/teal soft)
function getNilaiBadge($nilai) {
    if ($nilai >= 80) {
        return '<span class="badge-nilai badge-nilai-a">A (Sangat Baik)</span>';
    } elseif ($nilai >= 70) {
        return '<span class="badge-nilai badge-nilai-b">B (Baik)</span>';
    } elseif ($nilai >= 60) {
        return '<span class="badge-nilai badge-nilai-c">C (Cukup)</span>';
    } elseif ($nilai >= 50) {
        return '<span class="badge-nilai badge-nilai-d">D (Kurang)</span>';
    } else {
        return '<span class="badge-nilai badge-nilai-e">E (Gagal)</span>';
    }
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
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }

        .stat-card h3 {
            font-size: 32px;
            font-weight: 800;
            color: #B8860B;
            margin-bottom: 5px;
        }

        .stat-card p {
            font-size: 13px;
            color: #8B7355;
            font-weight: 500;
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            background: rgba(212, 160, 23, 0.1);
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 15px;
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
            margin-bottom: 30px;
            overflow: hidden;
            border: 1px solid #F0E6D2;
        }

        .card-header {
            padding: 18px 25px;
            border-bottom: 1px solid #F0E6D2;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
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

        .card-body {
            padding: 0;
        }

        /* Table */
        .data-table {
            width: 100%;
            border-collapse: collapse;
        }

        .data-table th {
            text-align: left;
            padding: 15px 12px;
            background: #FFFDF5;
            font-weight: 600;
            font-size: 13px;
            color: #8B7355;
            border-bottom: 2px solid #F0E6D2;
        }

        .data-table td {
            padding: 12px;
            font-size: 13px;
            border-bottom: 1px solid #F0E6D2;
            vertical-align: middle;
            color: #050505;
        }

        .data-table tr:hover {
            background: #FFFDF5;
        }

        /* Badges - Warna Mint/Teal Soft */
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

        .badge-success i {
            color: #155724;
        }

        .badge-danger {
            background: #f8d7da;
            color: #721c24;
        }

        .badge-danger i {
            color: #dc3545;
        }

        .badge-secondary {
            background: #e9ecef;
            color: #6c757d;
        }

        /* Nilai Badge - Warna Mint/Teal Soft */
        .badge-nilai {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
        }

        .badge-nilai-a {
            background: #d4edda;
            color: #155724;
            
        }

        .badge-nilai-e {
            background: #FFCDD2;
            color: #c62828;
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
            .data-table {
                font-size: 11px;
            }
            .data-table th, .data-table td {
                padding: 8px;
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
            <a href="dashboard.php" class="menu-item">
                <i class="fas fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>
            <a href="maba.php" class="menu-item">
                <i class="fas fa-users"></i>
                <span>Data Maba</span>
            </a>
            <a href="soal.php" class="menu-item">
                <i class="fas fa-question-circle"></i>
                <span>Kelola Soal</span>
            </a>
            <a href="hasil.php" class="menu-item active">
                <i class="fas fa-chart-line"></i>
                <span>Hasil Test</span>
            </a>
            <a href="ranking.php" class="menu-item">
                <i class="fas fa-trophy"></i>
                <span>Ranking</span>
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
                <h2><i class="fas fa-chart-line"></i> Hasil Test</h2>
            </div>
            <a href="logout.php" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>

        <div class="container">
            <!-- Statistik -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3><?php echo $total_peserta; ?></h3>
                    <p>Total Peserta Test</p>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h3><?php echo $rata_rata; ?></h3>
                    <p>Rata-rata Nilai</p>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-check-circle" style="color:#D4A017 ;"></i>
                    </div>
                    <h3 style="color:#B8860B;"><?php echo $lulus; ?></h3>
                    <p>Lulus</p>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-times-circle" style="color:#dc3545;"></i>
                    </div>
                    <h3 style="color:#dc3545;"><?php echo $tidak_lulus; ?></h3>
                    <p>Tidak Lulus</p>
                </div>
            </div>

            <!-- Tabel Hasil Test -->
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-list"></i> Daftar Hasil Test Peserta</h3>
                </div>
                <div class="card-body">
                    <?php if (count($hasil_list) > 0): ?>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>NO</th>
                                    <th>Nama Peserta</th>
                                    <th>Jurusan</th>
                                    <th>Jumlah Benar</th>
                                    <th>Jumlah Salah</th>
                                    <th>Total Nilai</th>
                                    <th>Predikat</th>
                                    <th>Status</th>
                                    <th>Tanggal Test</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no = 1; foreach ($hasil_list as $h): ?>
                                <tr>
                                    <td><?php echo $no++; ?></td>
                                    <td><strong><?php echo htmlspecialchars($h['nama_peserta']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($h['jurusan']); ?></td>
                                    <td><?php echo $h['jumlah_benar']; ?></td>
                                    <td><?php echo $h['jumlah_salah']; ?></td>
                                    <td><strong style="font-size: 16px; color:#050505;"><?php echo $h['total_nilai']; ?></strong></td>
                                    <td><?php echo getNilaiBadge($h['total_nilai']); ?></td>
                                    <td><?php echo getStatusBadge($h['status']); ?></td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($h['tanggal_test'])); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-chart-line"></i>
                            <p>Belum ada data hasil test</p>
                            <small>Hasil test akan muncul setelah peserta mengerjakan ujian</small>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>