<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit();
}

// Cek apakah sudah diverifikasi
if (!isset($_SESSION['test_verified']) || $_SESSION['test_verified'] !== true) {
    header('Location: test.php');
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

// Ambil jurusan user
$jurusan = $user['jurusan'];

// Ambil soal berdasarkan jurusan user
$query = "SELECT * FROM soal WHERE jurusan = '$jurusan' ORDER BY id ASC";
$result = mysqli_query($conn, $query);
$soal_list = [];

if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $soal_list[] = $row;
    }
}

if (count($soal_list) == 0) {
    echo "<h3>Belum ada soal untuk jurusan $jurusan. Silakan hubungi admin.</h3>";
    echo "<a href='dashboard.php'>Kembali ke Dashboard</a>";
    exit();
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
            width: calc(100% - 280px);
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
            max-width: 900px;
            margin: 30px auto;
            padding: 0 20px;
        }

        /* Info Card */
        .info-card {
            background: white;
            border-radius: 20px;
            padding: 20px 25px;
            margin-bottom: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
            border: 1px solid #F0E6D2;
        }

        .info-card p {
            margin: 0;
            font-size: 14px;
            color: #000000;
        }

        .info-card strong {
            color: #B8860B;
        }

        .info-card i {
            color: #D4A017;
            margin-right: 5px;
        }

        /* Question Card */
        .question-card {
            background: white;
            border-radius: 20px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            transition: all 0.3s;
            border: 1px solid #F0E6D2;
        }

        .question-card:hover {
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }

        .question-number {
            font-weight: 700;
            color: #B8860B;
            margin-bottom: 12px;
            font-size: 16px;
            display: inline-block;
            background: #FFFDF5;
            padding: 5px 15px;
            border-radius: 20px;
            border: 1px solid #F0E6D2;
        }

        .question-text {
            font-size: 16px;
            font-weight: 500;
            margin-bottom: 20px;
            color: #000000;
            line-height: 1.5;
        }

        .options {
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin-left: 20px;
        }

        .options label {
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            padding: 8px 12px;
            border-radius: 10px;
            transition: background 0.2s;
        }

        .options label:hover {
            background: #FFFDF5;
        }

        .options input {
            width: 18px;
            height: 18px;
            cursor: pointer;
            accent-color: #D4A017;
        }

        .options span {
            font-size: 14px;
            color: #000000;
        }

        /* Submit Button */
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
            margin-top: 20px;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(212, 160, 23, 0.3);
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
            .top-header { flex-direction: column; text-align: center; gap: 15px; }
            .info-card { flex-direction: column; text-align: center; }
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
            <!-- Info Card -->
            <div class="info-card">
                <p><i class="fas fa-graduation-cap"></i> <strong>Jurusan:</strong> <?php echo htmlspecialchars($jurusan); ?></p>
                <p><i class="fas fa-question-circle"></i> <strong>Jumlah Soal:</strong> <?php echo count($soal_list); ?></p>
                <p><i class="fas fa-check-circle"></i> <strong>Nilai Minimal:</strong> 70</p>
            </div>

            <!-- Form Test -->
            <form method="POST" action="test_proses.php" id="testForm">
                <?php $no = 1; foreach ($soal_list as $soal): ?>
                <div class="question-card">
                    <div class="question-number">Soal <?php echo $no++; ?></div>
                    <div class="question-text"><?php echo htmlspecialchars($soal['pertanyaan']); ?></div>
                    <div class="options">
                        <label>
                            <input type="radio" name="jawaban_<?php echo $soal['id']; ?>" value="A" required>
                            <span>A. <?php echo htmlspecialchars($soal['pilihan_a']); ?></span>
                        </label>
                        <label>
                            <input type="radio" name="jawaban_<?php echo $soal['id']; ?>" value="B">
                            <span>B. <?php echo htmlspecialchars($soal['pilihan_b']); ?></span>
                        </label>
                        <label>
                            <input type="radio" name="jawaban_<?php echo $soal['id']; ?>" value="C">
                            <span>C. <?php echo htmlspecialchars($soal['pilihan_c']); ?></span>
                        </label>
                        <label>
                            <input type="radio" name="jawaban_<?php echo $soal['id']; ?>" value="D">
                            <span>D. <?php echo htmlspecialchars($soal['pilihan_d']); ?></span>
                        </label>
                    </div>
                </div>
                <?php endforeach; ?>

                <button type="submit" class="btn-submit">
                    <i class="fas fa-paper-plane"></i> Kirim Jawaban
                </button>
            </form>
        </div>
    </div>
</body>
</html>