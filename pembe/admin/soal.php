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
// BUAT TABEL SOAL DENGAN JURUSAN
// ============================================
mysqli_query($conn, "CREATE TABLE IF NOT EXISTS soal (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    jurusan VARCHAR(50) NOT NULL,
    pertanyaan TEXT NOT NULL,
    pilihan_a VARCHAR(255) NOT NULL,
    pilihan_b VARCHAR(255) NOT NULL,
    pilihan_c VARCHAR(255) NOT NULL,
    pilihan_d VARCHAR(255) NOT NULL,
    jawaban_benar VARCHAR(1) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

// ============================================
// PROSES CRUD
// ============================================

// Tambah Soal
if (isset($_POST['tambah'])) {
    $jurusan = mysqli_real_escape_string($conn, $_POST['jurusan']);
    $pertanyaan = mysqli_real_escape_string($conn, $_POST['pertanyaan']);
    $a = mysqli_real_escape_string($conn, $_POST['a']);
    $b = mysqli_real_escape_string($conn, $_POST['b']);
    $c = mysqli_real_escape_string($conn, $_POST['c']);
    $d = mysqli_real_escape_string($conn, $_POST['d']);
    $jawaban = strtoupper(mysqli_real_escape_string($conn, $_POST['jawaban']));
    
    $query = "INSERT INTO soal (jurusan, pertanyaan, pilihan_a, pilihan_b, pilihan_c, pilihan_d, jawaban_benar) 
              VALUES ('$jurusan', '$pertanyaan', '$a', '$b', '$c', '$d', '$jawaban')";
    if (mysqli_query($conn, $query)) {
        $success = "Soal berhasil ditambahkan!";
    } else {
        $error = "Error: " . mysqli_error($conn);
    }
}

// Edit Soal
if (isset($_POST['edit'])) {
    $id = intval($_POST['id']);
    $jurusan = mysqli_real_escape_string($conn, $_POST['jurusan']);
    $pertanyaan = mysqli_real_escape_string($conn, $_POST['pertanyaan']);
    $a = mysqli_real_escape_string($conn, $_POST['a']);
    $b = mysqli_real_escape_string($conn, $_POST['b']);
    $c = mysqli_real_escape_string($conn, $_POST['c']);
    $d = mysqli_real_escape_string($conn, $_POST['d']);
    $jawaban = strtoupper(mysqli_real_escape_string($conn, $_POST['jawaban']));
    
    $query = "UPDATE soal SET 
              jurusan='$jurusan',
              pertanyaan='$pertanyaan', 
              pilihan_a='$a', 
              pilihan_b='$b', 
              pilihan_c='$c', 
              pilihan_d='$d', 
              jawaban_benar='$jawaban' 
              WHERE id=$id";
    if (mysqli_query($conn, $query)) {
        $success = "Soal berhasil diupdate!";
    } else {
        $error = "Error: " . mysqli_error($conn);
    }
}

// Hapus Soal
if (isset($_GET['hapus'])) {
    $id = intval($_GET['hapus']);
    mysqli_query($conn, "DELETE FROM soal WHERE id=$id");
    header('Location: soal.php');
    exit();
}

// ============================================
// AMBIL DATA SOAL PER JURUSAN
// ============================================

// Jurusan yang tersedia
$jurusan_list = ['Teknik Informatika', 'Sistem Informasi'];

// Ambil data soal per jurusan
$soal_by_jurusan = [];
foreach ($jurusan_list as $jurusan) {
    $result = mysqli_query($conn, "SELECT * FROM soal WHERE jurusan = '$jurusan' ORDER BY id ASC");
    $soal_by_jurusan[$jurusan] = [];
    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $soal_by_jurusan[$jurusan][] = $row;
        }
    }
}

// Data untuk edit
$edit_data = null;
if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $res = mysqli_query($conn, "SELECT * FROM soal WHERE id=$id");
    if ($res && mysqli_num_rows($res) > 0) {
        $edit_data = mysqli_fetch_assoc($res);
    }
}

// Insert data demo jika belum ada soal
$total_soal = 0;
foreach ($soal_by_jurusan as $soals) {
    $total_soal += count($soals);
}

if ($total_soal == 0 && !isset($_POST['tambah']) && !isset($_GET['edit'])) {
    $demo_soals = [
        ['jurusan' => 'Teknik Informatika', 'pertanyaan' => 'Apa kepanjangan dari HTML?', 'a' => 'Hyper Text Markup Language', 'b' => 'High Tech Modern Language', 'c' => 'Hyper Transfer Markup Language', 'd' => 'Home Tool Markup Language', 'jawaban' => 'A'],
        ['jurusan' => 'Teknik Informatika', 'pertanyaan' => 'CSS digunakan untuk...', 'a' => 'Membuat struktur halaman', 'b' => 'Mengatur tampilan halaman', 'c' => 'Membuat database', 'd' => 'Mengelola server', 'jawaban' => 'B'],
        ['jurusan' => 'Sistem Informasi', 'pertanyaan' => 'Apa itu sistem informasi?', 'a' => 'Kombinasi hardware dan software', 'b' => 'Sistem yang mengolah data menjadi informasi', 'c' => 'Jaringan komputer', 'd' => 'Program aplikasi', 'jawaban' => 'B'],
        ['jurusan' => 'Sistem Informasi', 'pertanyaan' => 'Database MySQL menggunakan bahasa query...', 'a' => 'HTML', 'b' => 'CSS', 'c' => 'JavaScript', 'd' => 'SQL', 'jawaban' => 'D'],
    ];
    
    foreach ($demo_soals as $demo) {
        $q = "INSERT INTO soal (jurusan, pertanyaan, pilihan_a, pilihan_b, pilihan_c, pilihan_d, jawaban_benar) 
              VALUES ('{$demo['jurusan']}', '{$demo['pertanyaan']}', '{$demo['a']}', '{$demo['b']}', '{$demo['c']}', '{$demo['d']}', '{$demo['jawaban']}')";
        mysqli_query($conn, $q);
    }
    
    // Refresh data
    foreach ($jurusan_list as $jurusan) {
        $result = mysqli_query($conn, "SELECT * FROM soal WHERE jurusan = '$jurusan' ORDER BY id ASC");
        $soal_by_jurusan[$jurusan] = [];
        if ($result && mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $soal_by_jurusan[$jurusan][] = $row;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Soal | PMB System</title>
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

        /* Cards */
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
            color: #8B7355;
            margin-bottom: 8px;
        }

        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 12px 15px;
            border: 1.5px solid #F0E6D2;
            border-radius: 12px;
            font-size: 14px;
            font-family: inherit;
            transition: all 0.3s;
        }

        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: #D4A017;
            box-shadow: 0 0 0 3px rgba(212, 160, 23, 0.1);
        }

        .form-group textarea {
            resize: vertical;
            min-height: 80px;
        }

        .form-row {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }

        /* Buttons */
        .btn-submit {
            background: linear-gradient(135deg, #D4A017, #B8860B);
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
            box-shadow: 0 5px 15px rgba(212, 160, 23, 0.3);
        }

        .btn-cancel {
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
        }

        .btn-cancel:hover {
            background: #5a6268;
        }

        /* Jurusan Section */
        .jurusan-section {
            margin-bottom: 30px;
        }

        .jurusan-title {
            background: linear-gradient(135deg, #D4A017, #B8860B);
            color: white;
            padding: 12px 20px;
            border-radius: 12px;
            margin-bottom: 15px;
            font-size: 16px;
            font-weight: 600;
        }

        .jurusan-title i {
            margin-right: 10px;
        }

        /* Table */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 12px;
            overflow: hidden;
        }

        .data-table th {
            text-align: left;
            padding: 12px 15px;
            background: #FFFDF5;
            font-weight: 600;
            font-size: 13px;
            color: #8B7355;
            border-bottom: 2px solid #F0E6D2;
        }

        .data-table td {
            padding: 12px 15px;
            font-size: 13px;
            border-bottom: 1px solid #F0E6D2;
            vertical-align: top;
            color: #5a4a2a;
        }

        .data-table tr:hover {
            background: #FFFDF5;
        }

        /* Badge */
        .badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            background: #e9ecef;
            color: #495057;
        }

        .badge-primary {
            background: #D4A017;
            color: white;
        }

        /* Action Buttons */
        .action-btn {
            padding: 6px 12px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 12px;
            display: inline-block;
            margin: 2px;
            transition: all 0.2s;
        }

        .btn-edit {
            background: #ffc107;
            color: #212529;
        }

        .btn-delete {
            background: #dc3545;
            color: white;
        }

        .btn-edit:hover, .btn-delete:hover {
            transform: translateY(-2px);
            opacity: 0.9;
        }

        .jawaban-badge {
            background: #28a745;
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-weight: bold;
            display: inline-block;
            font-size: 12px;
        }

        /* Alert Styles - Warna Kuning/Emas */
        .alert-success {
            background: linear-gradient(135deg, #FFF8E1 0%, #FFECB3 100%);
            color: #B8860B;
            padding: 14px 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            border-left: 5px solid #D4A017;
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 500;
            box-shadow: 0 2px 8px rgba(212, 160, 23, 0.15);
            animation: slideInRight 0.4s ease-out;
        }

        .alert-success i {
            font-size: 20px;
            color: #D4A017;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            padding: 12px 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            border-left: 4px solid #dc3545;
            display: flex;
            align-items: center;
            gap: 10px;
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

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 30px;
            color: #8B7355;
        }

        .empty-state i {
            font-size: 40px;
            margin-bottom: 10px;
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
            <a href="soal.php" class="menu-item active">
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
                <h2><i class="fas fa-question-circle"></i> Kelola Soal Test</h2>
            </div>
            <a href="logout.php" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>

        <div class="container">
            <?php if (isset($success)): ?>
                <div class="alert-success">
                    <i class="fas fa-check-circle"></i> 
                    <span><?php echo $success; ?></span>
                </div>
            <?php endif; ?>
            <?php if (isset($error)): ?>
                <div class="alert-error">
                    <i class="fas fa-exclamation-circle"></i> 
                    <span><?php echo $error; ?></span>
                </div>
            <?php endif; ?>

            <!-- Form Tambah/Edit Soal -->
            <div class="card">
                <div class="card-header">
                    <h3>
                        <i class="fas <?php echo $edit_data ? 'fa-edit' : 'fa-plus-circle'; ?>"></i>
                        <?php echo $edit_data ? 'Edit Soal' : 'Tambah Soal Baru'; ?>
                    </h3>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <?php if ($edit_data): ?>
                            <input type="hidden" name="id" value="<?php echo $edit_data['id']; ?>">
                        <?php endif; ?>

                        <div class="form-group">
                            <label><i class="fas fa-building"></i> Jurusan</label>
                            <select name="jurusan" required>
                                <option value="">-- Pilih Jurusan --</option>
                                <option value="Teknik Informatika" <?php echo $edit_data && $edit_data['jurusan'] == 'Teknik Informatika' ? 'selected' : ''; ?>>Teknik Informatika</option>
                                <option value="Sistem Informasi" <?php echo $edit_data && $edit_data['jurusan'] == 'Sistem Informasi' ? 'selected' : ''; ?>>Sistem Informasi</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label><i class="fas fa-question"></i> Soal</label>
                            <textarea name="pertanyaan" rows="3" placeholder="Masukkan pertanyaan soal..." required><?php echo $edit_data ? htmlspecialchars($edit_data['pertanyaan']) : ''; ?></textarea>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label><i class="fas fa-circle" style="color:#28a745;"></i> A. Pilihan A</label>
                                <input type="text" name="a" placeholder="Pilihan A" value="<?php echo $edit_data ? htmlspecialchars($edit_data['pilihan_a']) : ''; ?>" required>
                            </div>
                            <div class="form-group">
                                <label><i class="fas fa-circle" style="color:#ffc107;"></i> B. Pilihan B</label>
                                <input type="text" name="b" placeholder="Pilihan B" value="<?php echo $edit_data ? htmlspecialchars($edit_data['pilihan_b']) : ''; ?>" required>
                            </div>
                            <div class="form-group">
                                <label><i class="fas fa-circle" style="color:#17a2b8;"></i> C. Pilihan C</label>
                                <input type="text" name="c" placeholder="Pilihan C" value="<?php echo $edit_data ? htmlspecialchars($edit_data['pilihan_c']) : ''; ?>" required>
                            </div>
                            <div class="form-group">
                                <label><i class="fas fa-circle" style="color:#dc3545;"></i> D. Pilihan D</label>
                                <input type="text" name="d" placeholder="Pilihan D" value="<?php echo $edit_data ? htmlspecialchars($edit_data['pilihan_d']) : ''; ?>" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label><i class="fas fa-check-circle"></i> Jawaban Benar</label>
                            <select name="jawaban" required>
                                <option value="">-- Pilih Jawaban Benar --</option>
                                <option value="A" <?php echo $edit_data && $edit_data['jawaban_benar'] == 'A' ? 'selected' : ''; ?>>A</option>
                                <option value="B" <?php echo $edit_data && $edit_data['jawaban_benar'] == 'B' ? 'selected' : ''; ?>>B</option>
                                <option value="C" <?php echo $edit_data && $edit_data['jawaban_benar'] == 'C' ? 'selected' : ''; ?>>C</option>
                                <option value="D" <?php echo $edit_data && $edit_data['jawaban_benar'] == 'D' ? 'selected' : ''; ?>>D</option>
                            </select>
                        </div>

                        <button type="submit" name="<?php echo $edit_data ? 'edit' : 'tambah'; ?>" class="btn-submit">
                            <i class="fas fa-save"></i> <?php echo $edit_data ? 'Update Soal' : 'Simpan Soal'; ?>
                        </button>
                        <?php if ($edit_data): ?>
                            <a href="soal.php" class="btn-cancel">
                                <i class="fas fa-times"></i> Batal
                            </a>
                        <?php endif; ?>
                    </form>
                </div>
            </div>

            <!-- Daftar Soal Per Jurusan -->
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-list"></i> Daftar Soal Test Per Jurusan</h3>
                </div>
                <div class="card-body">
                    <?php foreach ($jurusan_list as $jurusan): ?>
                        <div class="jurusan-section">
                            <div class="jurusan-title">
                                <i class="fas fa-folder-open"></i> <?php echo $jurusan; ?> (<?php echo count($soal_by_jurusan[$jurusan]); ?> Soal)
                            </div>
                            
                            <?php if (count($soal_by_jurusan[$jurusan]) > 0): ?>
                                <table class="data-table">
                                    <thead>
                                        <tr>
                                            <th width="50">NO</th>
                                            <th width="300">SOAL</th>
                                            <th>PILIHAN</th>
                                            <th width="80">JAWABAN</th>
                                            <th width="100">AKSI</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $no = 1; foreach ($soal_by_jurusan[$jurusan] as $s): ?>
                                        <tr>
                                            <td><?php echo $no++; ?></td>
                                            <td><strong><?php echo htmlspecialchars($s['pertanyaan']); ?></strong></td>
                                            <td style="font-size: 12px;">
                                                <span style="color:#28a745;">A.</span> <?php echo htmlspecialchars(substr($s['pilihan_a'], 0, 50)); ?><br>
                                                <span style="color:#ffc107;">B.</span> <?php echo htmlspecialchars(substr($s['pilihan_b'], 0, 50)); ?><br>
                                                <span style="color:#17a2b8;">C.</span> <?php echo htmlspecialchars(substr($s['pilihan_c'], 0, 50)); ?><br>
                                                <span style="color:#dc3545;">D.</span> <?php echo htmlspecialchars(substr($s['pilihan_d'], 0, 50)); ?>
                                            </td>
                                            <td><span class="jawaban-badge"><?php echo $s['jawaban_benar']; ?></span></td>
                                            <td>
                                                <a href="?edit=<?php echo $s['id']; ?>" class="action-btn btn-edit">
                                                    <i class="fas fa-edit"></i> Edit
                                                </a>
                                                <a href="?hapus=<?php echo $s['id']; ?>" class="action-btn btn-delete" onclick="return confirm('Yakin hapus soal ini?')">
                                                    <i class="fas fa-trash"></i> Hapus
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php else: ?>
                                <div class="empty-state">
                                    <i class="fas fa-inbox"></i>
                                    <p>Belum ada soal untuk jurusan <?php echo $jurusan; ?></p>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>