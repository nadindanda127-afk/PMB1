<?php
// Mulai session hanya sekali di awal
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

$host = 'localhost';
$dbname = 'pembe';
$user = 'root';
$pass = '';

$conn = mysqli_connect($host, $user, $pass, $dbname);
if (!$conn) die("Koneksi gagal: " . mysqli_connect_error());

// ========== KODE HAPUS OTOMATIS DARI SEMUA TABEL ==========
if (isset($_GET['hapus'])) {
    $id = intval($_GET['hapus']);
    
    // Hapus dari tabel daftar_ulang
    mysqli_query($conn, "DELETE FROM daftar_ulang WHERE id_peserta = $id");
    
    // Hapus dari tabel hasil_test
    mysqli_query($conn, "DELETE FROM hasil_test WHERE id_peserta = $id");
    
    // Hapus dari tabel ranking (jika ada)
    mysqli_query($conn, "DELETE FROM ranking WHERE id_peserta = $id");
    
    // Hapus dari tabel users (utama)
    if (mysqli_query($conn, "DELETE FROM users WHERE id = $id")) {
        $_SESSION['success'] = "Data mahasiswa berhasil dihapus dari semua tabel!";
    } else {
        $_SESSION['error'] = "Gagal menghapus data: " . mysqli_error($conn);
    }
    
    header('Location: maba.php');
    exit();
}

// Ambil data admin
$nama = $_SESSION['admin_nama'];
$email_admin = $_SESSION['admin_email'];

// Ambil semua data user (mahasiswa)
$query = "SELECT * FROM users WHERE role = 'user' ORDER BY id DESC";
$result = mysqli_query($conn, $query);
$users_list = [];
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $users_list[] = $row;
    }
}

// Fungsi untuk badge status
function getStatusBadge($status) {
    if (empty($status)) $status = 'proses';
    switch($status) {
        case 'lulus':
            return '<span class="badge badge-success">Lulus</span>';
        case 'proses':
            return '<span class="badge badge-warning">Proses</span>';
        case 'diterima':
            return '<span class="badge badge-info">Diterima</span>';
        case 'tidak_lulus':
            return '<span class="badge badge-danger">Tidak Lulus</span>';
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
    <title>Data Maba | PMB System</title>
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
        }

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
        }

        .sidebar-header h3 {
            font-size: 20px;
            font-weight: 700;
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

        .main-content {
            margin-left: 280px;
            min-height: 100vh;
        }

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
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .logout-btn:hover {
            background: #c82333;
            transform: translateY(-2px);
        }

        .container {
            padding: 25px 30px;
        }

        .alert {
            padding: 14px 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 500;
            animation: slideInRight 0.4s ease-out;
        }

        .alert-success {
            background: linear-gradient(135deg, #FFF8E1 0%, #FFECB3 100%);
            color: #B8860B;
            border-left: 5px solid #D4A017;
        }

        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border-left: 5px solid #dc3545;
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
            color: #5a4a2a;
        }

        .data-table tr:hover {
            background: #FFFDF5;
        }

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

        .badge-secondary {
            background: #e9ecef;
            color: #6c757d;
        }

        .btn-delete {
            background: #dc3545;
            color: white;
            padding: 6px 12px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 12px;
            display: inline-block;
            transition: all 0.2s;
        }

        .btn-delete:hover {
            transform: translateY(-2px);
            opacity: 0.9;
            background: #c82333;
        }

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

        .alamat-text {
            max-width: 200px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            display: block;
        }

        @media (max-width: 992px) {
            .sidebar {
                width: 80px;
            }
            .sidebar-header h3, .sidebar-header p, .menu-item span {
                display: none;
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
            <a href="maba.php" class="menu-item active"><i class="fas fa-users"></i><span>Data Maba</span></a>
            <a href="soal.php" class="menu-item"><i class="fas fa-question-circle"></i><span>Kelola Soal</span></a>
            <a href="hasil.php" class="menu-item"><i class="fas fa-chart-line"></i><span>Hasil Test</span></a>
            <a href="ranking.php" class="menu-item"><i class="fas fa-trophy"></i><span>Ranking</span></a>
            <a href="daftar_ulang.php" class="menu-item"><i class="fas fa-file-signature"></i><span>Daftar Ulang</span></a>
            <div class="menu-divider"></div>
            <a href="logout.php" class="menu-item"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a>
        </div>
    </div>

    <div class="main-content">
        <div class="top-header">
            <div class="page-title">
                <h2><i class="fas fa-users"></i> Data Mahasiswa Baru</h2>
            </div>
            <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>

        <div class="container">
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i> <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-list"></i> Daftar Peserta</h3>
                </div>
                <div class="card-body">
                    <?php if (count($users_list) > 0): ?>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>NO</th>
                                    <th>Nama Lengkap</th>
                                    <th>Email</th>
                                    <th>No Telepon</th>
                                    <th>No Test</th>
                                    <th>Status Test</th>
                                    <th>Alamat</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no = 1; foreach ($users_list as $u): ?>
                                <tr>
                                    <td><?php echo $no++; ?>
                                    <td><strong><?php echo htmlspecialchars($u['nama_lengkap'] ?? '-'); ?></strong></td>
                                    <td><?php echo htmlspecialchars($u['email']); ?></td>
                                    <td><?php echo htmlspecialchars($u['no_telp'] ?? '-'); ?></td>
                                    <td><?php echo htmlspecialchars($u['no_test'] ?? '-'); ?></td>
                                    <td><?php echo getStatusBadge($u['status_test'] ?? 'proses'); ?></td>
                                    <td>
                                        <span class="alamat-text" title="<?php echo htmlspecialchars($u['alamat'] ?? '-'); ?>">
                                            <?php echo htmlspecialchars($u['alamat'] ?? '-'); ?>
                                        </span>
                                     </div>
                                    <td>
                                        <a href="?hapus=<?php echo $u['id']; ?>" class="btn-delete" onclick="return confirm('Yakin ingin menghapus data <?php echo htmlspecialchars($u['nama_lengkap']); ?>?\n\nData akan dihapus dari:\n- Data Maba\n- Hasil Test\n- Ranking\n- Daftar Ulang')">
                                            <i class="fas fa-trash"></i> Hapus
                                        </a>
                                     </div>
                                
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-database"></i>
                            <p>Belum ada data peserta</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>