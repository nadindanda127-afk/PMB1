<?php
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

$nama_admin = $_SESSION['admin_nama'];
$email_admin = $_SESSION['admin_email'];

$host = 'localhost';
$dbname = 'pembe';
$user = 'root';
$pass = '';

$conn = mysqli_connect($host, $user, $pass, $dbname);
if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

// Ambil ID user dari URL
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Ambil data user berdasarkan ID
$query = "SELECT * FROM users WHERE id = $id";
$result = mysqli_query($conn, $query);
$user_data = mysqli_fetch_assoc($result);

if (!$user_data) {
    header('Location: maba.php');
    exit();
}

$error = '';
$success = '';

// Proses update data
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_lengkap = mysqli_real_escape_string($conn, $_POST['nama_lengkap']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $no_telegram = mysqli_real_escape_string($conn, $_POST['no_telegram']);
    $no_test = mysqli_real_escape_string($conn, $_POST['no_test']);
    $status_test = mysqli_real_escape_string($conn, $_POST['status_test']);
    $nim = mysqli_real_escape_string($conn, $_POST['nim']);
    $jurusan = mysqli_real_escape_string($conn, $_POST['jurusan']);
    
    $update = "UPDATE users SET 
                nama_lengkap = '$nama_lengkap',
                email = '$email',
                no_telegram = '$no_telegram',
                no_test = '$no_test',
                status_test = '$status_test',
                nim = '$nim',
                jurusan = '$jurusan'
                WHERE id = $id";
    
    if (mysqli_query($conn, $update)) {
        $success = "Data berhasil diupdate!";
        // Refresh data
        $result = mysqli_query($conn, "SELECT * FROM users WHERE id = $id");
        $user_data = mysqli_fetch_assoc($result);
    } else {
        $error = "Error: " . mysqli_error($conn);
    }
}

// Fungsi untuk badge status
function getStatusBadge($status) {
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
    <title>Edit Data User | PMB System</title>
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
            background: #f5f7fa;
        }

        /* Sidebar */
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: 280px;
            height: 100vh;
            background: linear-gradient(180deg, #0B2B4A 0%, #0F2B40 100%);
            color: white;
            overflow-y: auto;
            z-index: 100;
        }

        .sidebar-header {
            padding: 25px 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            margin-bottom: 20px;
        }

        .sidebar-header h3 {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .sidebar-header p {
            font-size: 11px;
            opacity: 0.7;
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
            color: rgba(255,255,255,0.8);
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
            background: rgba(255,255,255,0.1);
            color: white;
        }

        .menu-divider {
            height: 1px;
            background: rgba(255,255,255,0.1);
            margin: 15px 0;
        }

        /* Main Content */
        .main-content {
            margin-left: 280px;
            min-height: 100vh;
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
        }

        .page-title h2 {
            font-size: 22px;
            font-weight: 700;
            color: #1A4D7A;
        }

        .page-title p {
            font-size: 13px;
            color: #6c757d;
            margin-top: 4px;
        }

        .user-section {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .user-info {
            text-align: right;
        }

        .user-name {
            font-weight: 600;
            font-size: 14px;
            color: #1A4D7A;
        }

        .user-email {
            font-size: 11px;
            color: #6c757d;
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

        /* Card */
        .card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            max-width: 800px;
            margin: 0 auto;
            overflow: hidden;
        }

        .card-header {
            padding: 20px 25px;
            border-bottom: 1px solid #e9ecef;
            background: #f8f9fa;
        }

        .card-header h3 {
            font-size: 18px;
            font-weight: 700;
            color: #1A4D7A;
        }

        .card-header h3 i {
            margin-right: 10px;
        }

        .card-body {
            padding: 25px;
        }

        /* Form */
        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #1A4D7A;
            margin-bottom: 8px;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 12px 15px;
            border: 1.5px solid #e9ecef;
            border-radius: 12px;
            font-size: 14px;
            font-family: inherit;
            transition: all 0.3s;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #1A4D7A;
            box-shadow: 0 0 0 3px rgba(26, 77, 122, 0.1);
        }

        .form-row {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }

        /* Buttons */
        .btn-submit {
            background: linear-gradient(135deg, #28a745, #218838);
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(40, 167, 69, 0.3);
        }

        .btn-back {
            background: #6c757d;
            color: white;
            padding: 12px 25px;
            border-radius: 12px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-left: 10px;
            transition: all 0.3s;
        }

        .btn-back:hover {
            background: #5a6268;
            transform: translateY(-2px);
        }

        /* Alert */
        .alert-success {
            background: #d4edda;
            color: #155724;
            padding: 12px 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            border-left: 4px solid #28a745;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            padding: 12px 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            border-left: 4px solid #dc3545;
        }

        /* Badge */
        .badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
        }

        .badge-warning {
            background: #fff3cd;
            color: #856404;
        }

        /* Responsive */
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
            .user-section {
                flex-direction: column;
            }
            .user-info {
                text-align: center;
            }
            .form-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- SIDEBAR -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h3><i class="fas fa-graduation-cap"></i> PMB System</h3>
            <p>Universitas SEI</p>
        </div>
        <div class="sidebar-menu">
            <a href="dashboard.php" class="menu-item">
                <i class="fas fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>
            <a href="maba.php" class="menu-item active">
                <i class="fas fa-users"></i>
                <span>Data Maba</span>
            </a>
            <a href="soal.php" class="menu-item">
                <i class="fas fa-question-circle"></i>
                <span>Kelola Soal</span>
            </a>
            <a href="hasil.php" class="menu-item">
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
                <h2><i class="fas fa-edit"></i> Edit Data Peserta</h2>
                <p>Edit informasi peserta PMB</p>
            </div>
            <div class="user-section">
                <div class="user-info">
                    <div class="user-name">Administrator</div>
                    <div class="user-email">admin@pembe.com</div>
                </div>
                <a href="logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>

        <div class="container">
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-user-edit"></i> Edit Data: <?php echo htmlspecialchars($user_data['nama_lengkap'] ?? $user_data['email']); ?></h3>
                </div>
                <div class="card-body">
                    <?php if ($success): ?>
                        <div class="alert-success">
                            <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                        </div>
                    <?php endif; ?>
                    <?php if ($error): ?>
                        <div class="alert-error">
                            <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="form-row">
                            <div class="form-group">
                                <label><i class="fas fa-user"></i> Nama Lengkap</label>
                                <input type="text" name="nama_lengkap" value="<?php echo htmlspecialchars($user_data['nama_lengkap'] ?? ''); ?>" required>
                            </div>
                            <div class="form-group">
                                <label><i class="fas fa-envelope"></i> Email</label>
                                <input type="email" name="email" value="<?php echo htmlspecialchars($user_data['email']); ?>" required>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label><i class="fas fa-phone"></i> No Telepon</label>
                                <input type="text" name="no_telegram" value="<?php echo htmlspecialchars($user_data['no_telegram'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label><i class="fas fa-building"></i> Jurusan</label>
                                <input type="text" name="jurusan" value="<?php echo htmlspecialchars($user_data['jurusan'] ?? ''); ?>" placeholder="Teknik Informatika / Sistem Informasi">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label><i class="fas fa-qrcode"></i> No Test</label>
                                <input type="text" name="no_test" value="<?php echo htmlspecialchars($user_data['no_test'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label><i class="fas fa-id-card"></i> NIM</label>
                                <input type="text" name="nim" value="<?php echo htmlspecialchars($user_data['nim'] ?? ''); ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <label><i class="fas fa-chart-line"></i> Status Test</label>
                            <select name="status_test">
                                <option value="proses" <?php echo ($user_data['status_test'] ?? '') == 'proses' ? 'selected' : ''; ?>>Proses</option>
                                <option value="lulus" <?php echo ($user_data['status_test'] ?? '') == 'lulus' ? 'selected' : ''; ?>>Lulus</option>
                                <option value="diterima" <?php echo ($user_data['status_test'] ?? '') == 'diterima' ? 'selected' : ''; ?>>Diterima</option>
                                <option value="tidak_lulus" <?php echo ($user_data['status_test'] ?? '') == 'tidak_lulus' ? 'selected' : ''; ?>>Tidak Lulus</option>
                            </select>
                        </div>

                        <div style="margin-top: 25px;">
                            <button type="submit" class="btn-submit">
                                <i class="fas fa-save"></i> Simpan Perubahan
                            </button>
                            <a href="maba.php" class="btn-back">
                                <i class="fas fa-arrow-left"></i> Kembali
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>