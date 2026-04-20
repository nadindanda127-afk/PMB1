<?php
session_start();

// Cek login admin
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

$nama = $_SESSION['admin_nama'];
$email = $_SESSION['admin_email'];
$role = $_SESSION['admin_role'];

// Koneksi database
$host = 'localhost';
$dbname = 'pembe';
$user = 'root';
$pass = '';

$conn = mysqli_connect($host, $user, $pass, $dbname);

if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

// ============================================
// AMBIL DATA STATISTIK
// ============================================

// Total Pendaftar (dari tabel users)
$query = "SELECT COUNT(*) as total FROM users WHERE role = 'user'";
$result = mysqli_query($conn, $query);
$total_pendaftar = mysqli_fetch_assoc($result)['total'] ?? 0;

// Terdaftar (status = proses atau belum)
$query = "SELECT COUNT(*) as total FROM users WHERE status_test = 'proses' OR status_test = 'belum'";
$result = mysqli_query($conn, $query);
$terdaftar = mysqli_fetch_assoc($result)['total'] ?? 0;

// Lulus Test
$query = "SELECT COUNT(*) as total FROM users WHERE status_test = 'lulus'";
$result = mysqli_query($conn, $query);
$lulus_test = mysqli_fetch_assoc($result)['total'] ?? 0;

// Tidak Lulus
$query = "SELECT COUNT(*) as total FROM users WHERE status_test = 'tidak_lulus'";
$result = mysqli_query($conn, $query);
$tidak_lulus = mysqli_fetch_assoc($result)['total'] ?? 0;

// ============================================
// AMBIL DATA PER JURUSAN (HANYA SI dan TI)
// ============================================
$jurusan_list = ['Teknik Informatika', 'Sistem Informasi'];
$jurusan_data = [];

foreach ($jurusan_list as $jurusan) {
    $query = "SELECT COUNT(*) as total FROM users WHERE jurusan = '$jurusan' AND role = 'user'";
    $result = mysqli_query($conn, $query);
    $jurusan_data[$jurusan] = mysqli_fetch_assoc($result)['total'] ?? 0;
}

// ============================================
// AMBIL DATA STATUS TEST PER JURUSAN
// ============================================
$status_data = [];
$status_list = ['lulus', 'tidak_lulus', 'proses'];

foreach ($jurusan_list as $jurusan) {
    foreach ($status_list as $status) {
        $query = "SELECT COUNT(*) as total FROM users WHERE jurusan = '$jurusan' AND status_test = '$status' AND role = 'user'";
        $result = mysqli_query($conn, $query);
        $status_data[$jurusan][$status] = mysqli_fetch_assoc($result)['total'] ?? 0;
    }
}

// ============================================
// AMBIL DATA DAFTAR ULANG TERBARU
// ============================================
$query = "SELECT nama_lengkap, jurusan, nim, tanggal_daftar_ulang, status FROM daftar_ulang ORDER BY id DESC LIMIT 10";
$result = mysqli_query($conn, $query);
$daftar_ulang_list = [];
if ($result) {
    $daftar_ulang_list = mysqli_fetch_all($result, MYSQLI_ASSOC);
}

// Fungsi untuk badge status daftar ulang
function getDaftarUlangBadge($status) {
    switch($status) {
        case 'pending':
            return '<span class="badge badge-warning">Menunggu</span>';
        case 'selesai':
        case 'terverifikasi':
            return '<span class="badge badge-success">Selesai</span>';
        default:
            return '<span class="badge badge-secondary">' . htmlspecialchars($status) . '</span>';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin | PMB System</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
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
            padding: 25px 30px;
        }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            border-radius: 20px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all 0.3s;
            border: 1px solid #F0E6D2;
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }

        .stat-info h3 {
            font-size: 28px;
            font-weight: 800;
            color: #D4A017;
            margin-bottom: 5px;
        }

        .stat-info p {
            font-size: 13px;
            color: #6c757d;
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
        }

        .stat-icon i {
            font-size: 24px;
            color: #D4A017;
        }

        /* Charts Container */
        .charts-container {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 25px;
            margin-bottom: 30px;
        }

        .chart-card {
            background: white;
            border-radius: 20px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            border: 1px solid #F0E6D2;
        }

        .chart-card h3 {
            font-size: 16px;
            font-weight: 700;
            color: #B8860B;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #F0E6D2;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .chart-card h3 i {
            color: #D4A017;
        }

        .chart-canvas {
            max-height: 300px;
            width: 100%;
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

        .card-header a {
            font-size: 13px;
            color: #B8860B;
            text-decoration: none;
            font-weight: 500;
        }

        .card-header a:hover {
            text-decoration: underline;
        }

        .card-body {
            padding: 0 25px 20px 25px;
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
            color: #5a4a2a;
            border-bottom: 2px solid #F0E6D2;
        }

        .data-table td {
            padding: 12px;
            font-size: 14px;
            border-bottom: 1px solid #F0E6D2;
            color: #5a4a2a;
        }

        .data-table tr:hover {
            background: #FFFDF5;
        }

        /* Badges */
        .badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
        }

        .badge-success {
            background: #d4edda;
            color: #155724;
        }

        .badge-warning {
            background: #fff3cd;
            color: #856404;
        }

        .badge-danger {
            background: #f8d7da;
            color: #721c24;
        }

        .badge-info {
            background: #d1ecf1;
            color: #0c5460;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #6c757d;
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
                padding: 12px;
            }
            .menu-item i {
                width: auto;
                font-size: 20px;
            }
            .main-content {
                margin-left: 80px;
            }
            .charts-container {
                grid-template-columns: 1fr;
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
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            .top-header {
                flex-direction: column;
                text-align: center;
                gap: 10px;
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
            <a href="maba.php" class="menu-item">
                <i class="fas fa-users"></i>
                <span>Data Maba</span>
            </a>
            <a href="soal.php" class="menu-item">
                <i class="fas fa-question-circle"></i>
                <span>Kelola Soal</span>
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
        <!-- Top Header -->
        <div class="top-header">
            <div class="page-title">
                <h2><i class="fas fa-chalkboard-user"></i> Dashboard Admin</h2>
            </div>
            <a href="logout.php" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>

        <div class="container">
            <!-- Statistics Cards (Tanpa Total Daftar Ulang, Menunggu Verifikasi, Selesai/Terverifikasi) -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-info">
                        <h3><?php echo $total_pendaftar; ?></h3>
                        <p>Total Pendaftar</p>
                    </div>
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-info">
                        <h3><?php echo $terdaftar; ?></h3>
                        <p>Terdaftar</p>
                    </div>
                    <div class="stat-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-info">
                        <h3><?php echo $lulus_test; ?></h3>
                        <p>Lulus Test</p>
                    </div>
                    <div class="stat-icon">
                        <i class="fas fa-trophy"></i>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-info">
                        <h3><?php echo $tidak_lulus; ?></h3>
                        <p>Tidak Lulus</p>
                    </div>
                    <div class="stat-icon">
                        <i class="fas fa-times-circle"></i>
                    </div>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="charts-container">
                <!-- Chart 1: Pendaftar per Jurusan -->
                <div class="chart-card">
                    <h3><i class="fas fa-chart-bar"></i> Pendaftar per Jurusan</h3>
                    <div class="chart-canvas">
                        <canvas id="jurusanChart"></canvas>
                    </div>
                </div>

                <!-- Chart 2: Status Test per Jurusan -->
                <div class="chart-card">
                    <h3><i class="fas fa-chart-bar"></i> Status Test per Jurusan</h3>
                    <div class="chart-canvas">
                        <canvas id="statusChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Daftar Ulang Terbaru Table -->
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-file-signature"></i> Daftar Ulang Terbaru</h3>
                    <a href="daftar_ulang.php">Lihat Semua <i class="fas fa-arrow-right"></i></a>
                </div>
                <div class="card-body">
                    <?php if (count($daftar_ulang_list) > 0): ?>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>NO</th>
                                    <th>NAMA LENGKAP</th>
                                    <th>JURUSAN</th>
                                    <th>NIM</th>
                                    <th>TANGGAL DAFTAR</th>
                                    <th>STATUS</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no = 1; foreach ($daftar_ulang_list as $du): ?>
                                <tr>
                                    <td><?php echo $no++; ?></td>
                                    <td><?php echo htmlspecialchars($du['nama_lengkap']); ?></td>
                                    <td><?php echo htmlspecialchars($du['jurusan']); ?></td>
                                    <td><?php echo htmlspecialchars($du['nim']); ?></td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($du['tanggal_daftar_ulang'])); ?></td>
                                    <td><?php echo getDaftarUlangBadge($du['status']); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-inbox"></i>
                            <p>Belum ada mahasiswa yang melakukan daftar ulang</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Data untuk Chart 1: Pendaftar per Jurusan
        const jurusanLabels = <?php echo json_encode(array_keys($jurusan_data)); ?>;
        const jurusanValues = <?php echo json_encode(array_values($jurusan_data)); ?>;

        const ctx1 = document.getElementById('jurusanChart').getContext('2d');
        new Chart(ctx1, {
            type: 'bar',
            data: {
                labels: jurusanLabels,
                datasets: [{
                    label: 'Jumlah Pendaftar',
                    data: jurusanValues,
                    backgroundColor: ['rgba(212, 160, 23, 0.7)', 'rgba(184, 134, 11, 0.7)'],
                    borderColor: ['#D4A017', '#B8860B'],
                    borderWidth: 1,
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        backgroundColor: '#fff',
                        titleColor: '#B8860B',
                        bodyColor: '#5a4a2a',
                        borderColor: '#D4A017',
                        borderWidth: 1
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: '#F0E6D2' },
                        ticks: { stepSize: 1 }
                    },
                    x: { grid: { display: false } }
                }
            }
        });

        // Data untuk Chart 2: Status Test per Jurusan
        const jurusanList = <?php echo json_encode($jurusan_list); ?>;
        const statusList = ['lulus', 'tidak_lulus', 'proses'];
        const statusColors = {
            'lulus': '#28a745',
            'tidak_lulus': '#dc3545',
            'proses': '#ffc107'
        };
        const statusLabels = { 'lulus': 'Lulus', 'tidak_lulus': 'Tidak Lulus', 'proses': 'Proses' };

        const datasets = statusList.map(status => {
            const data = jurusanList.map(jurusan => {
                return <?php echo json_encode($status_data); ?>[jurusan][status] || 0;
            });
            return {
                label: statusLabels[status],
                data: data,
                backgroundColor: statusColors[status],
                borderRadius: 8
            };
        });

        const ctx2 = document.getElementById('statusChart').getContext('2d');
        new Chart(ctx2, {
            type: 'bar',
            data: {
                labels: jurusanList,
                datasets: datasets
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: { position: 'top' },
                    tooltip: {
                        backgroundColor: '#fff',
                        titleColor: '#B8860B',
                        bodyColor: '#5a4a2a',
                        borderColor: '#D4A017',
                        borderWidth: 1
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: '#F0E6D2' },
                        ticks: { stepSize: 1 }
                    },
                    x: { grid: { display: false } }
                }
            }
        });
    </script>
</body>
</html>