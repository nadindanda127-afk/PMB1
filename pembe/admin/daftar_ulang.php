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
if (!$conn) die("Koneksi gagal: " . mysqli_connect_error());

// Proses hapus daftar ulang
if (isset($_GET['hapus'])) {
    $id = intval($_GET['hapus']);
    $query = "DELETE FROM daftar_ulang WHERE id = $id";
    mysqli_query($conn, $query);
    header('Location: daftar_ulang.php');
    exit();
}

// ========== QUERY UNTUK AMBIL DATA ==========
$query = "SELECT du.*, 
          u.email, 
          u.no_test, 
          u.status_test, 
          u.nim,
          u.nama_lengkap,
          u.jurusan,
          u.foto
          FROM daftar_ulang du 
          LEFT JOIN users u ON du.id_peserta = u.id 
          ORDER BY du.tanggal_daftar_ulang DESC";

$result = mysqli_query($conn, $query);

if (!$result || mysqli_num_rows($result) == 0) {
    $query2 = "SELECT du.*, 
               p.email, 
               p.no_test, 
               p.status_test, 
               p.nim,
               p.nama_lengkap,
               p.jurusan,
               p.foto
               FROM daftar_ulang du 
               LEFT JOIN peserta p ON du.id_peserta = p.id 
               ORDER BY du.tanggal_daftar_ulang DESC";
    $result = mysqli_query($conn, $query2);
}

if (!$result) {
    die("Query Error: " . mysqli_error($conn) . "<br>Query: " . $query);
}

$daftar_ulang_list = [];
if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $daftar_ulang_list[] = $row;
    }
}

function getStatusBadge($status) {
    switch(strtolower($status)) {
        case 'lulus': return '<span class="badge badge-success">Lulus</span>';
        case 'pending': return '<span class="badge badge-warning">Pending</span>';
        case 'selesai': return '<span class="badge badge-success">Selesai</span>';
        case 'menunggu': return '<span class="badge badge-warning">Menunggu</span>';
        case 'sudah daftar ulang': return '<span class="badge badge-success">Sudah Daftar Ulang</span>';
        default: return '<span class="badge badge-secondary">' . htmlspecialchars($status) . '</span>';
    }
}

function getTestStatusBadge($status) {
    switch(strtolower($status)) {
        case 'lulus': return '<span class="badge badge-test-lulus">Lulus</span>';
        case 'tidak_lulus': return '<span class="badge badge-test-gagal">Tidak Lulus</span>';
        case 'proses': return '<span class="badge badge-test-proses">Proses</span>';
        default: return '<span class="badge badge-secondary">' . htmlspecialchars($status) . '</span>';
    }
}

// ========== FUNGSI FOTO YANG DIPERBAIKI ==========
function getInitialsHtml($nama) {
    $inisial = '';
    if (!empty($nama) && $nama != '-') {
        $words = explode(' ', $nama);
        $inisial = strtoupper(substr($words[0], 0, 1));
        if (isset($words[1])) {
            $inisial .= strtoupper(substr($words[1], 0, 1));
        }
        return '<div class="foto-profile default-foto" style="background: #D4A017; color: white; font-weight: bold; font-size: 18px; display: flex; align-items: center; justify-content: center; cursor: pointer; width: 50px; height: 50px; border-radius: 50%;" onclick="showFoto(null, \'' . htmlspecialchars($nama) . '\')">' . $inisial . '</div>';
    }
    return '<div class="foto-profile default-foto" style="display: flex; align-items: center; justify-content: center; cursor: pointer; width: 50px; height: 50px; border-radius: 50%;" onclick="showFoto(null, \'' . htmlspecialchars($nama) . '\')"><i class="fas fa-user-graduate"></i></div>';
}

// Fungsi untuk menampilkan foto (Langsung muncul dari upload user)
function tampilkanFoto($foto, $nama = '') {
    // Jika foto tidak kosong
    if (!empty($foto)) {
        // Cek berbagai kemungkinan path
        $paths_to_check = [
            '../uploads/' . $foto,      // admin/uploads/
            'uploads/' . $foto,         // uploads/ (selevel)
            '../foto/' . $foto,
            'foto/' . $foto,
            '../' . $foto,
            $foto
        ];
        
        foreach ($paths_to_check as $path) {
            if (file_exists($path)) {
                return '<img src="' . htmlspecialchars($path) . '" alt="Foto" class="foto-profile" style="cursor: pointer; object-fit: cover; width: 50px; height: 50px; border-radius: 50%;" onclick="showFoto(\'' . htmlspecialchars($path) . '\', \'' . htmlspecialchars($nama) . '\')">';
            }
        }
        
        // Jika file tidak ditemukan, coba langsung tampilkan dari URL (asumsi di uploads)
        $test_path = 'uploads/' . $foto;
        return '<img src="' . $test_path . '" alt="Foto" class="foto-profile" style="cursor: pointer; object-fit: cover; width: 50px; height: 50px; border-radius: 50%;" onclick="showFoto(\'' . $test_path . '\', \'' . htmlspecialchars($nama) . '\')" onerror="this.onerror=null; this.parentElement.innerHTML=\'' . addslashes(getInitialsHtml($nama)) . '\'">';
    }
    
    return getInitialsHtml($nama);
}

// Fungsi untuk mendapatkan URL dokumen dari database
function getDokumenUrl($file_path) {
    if (!empty($file_path) && file_exists($file_path)) {
        return $file_path;
    }
    if (!empty($file_path) && file_exists('../uploads/dokumen/' . $file_path)) {
        return '../uploads/dokumen/' . $file_path;
    }
    if (!empty($file_path) && file_exists('uploads/dokumen/' . $file_path)) {
        return 'uploads/dokumen/' . $file_path;
    }
    if (!empty($file_path) && file_exists('../uploads/' . $file_path)) {
        return '../uploads/' . $file_path;
    }
    if (!empty($file_path) && file_exists('uploads/' . $file_path)) {
        return 'uploads/' . $file_path;
    }
    return null;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Ulang | PMB System - Universitas Cakrawala Nusantara</title>
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

        .main-content {
            margin-left: 280px;
            min-height: 100vh;
            position: relative;
            z-index: 1;
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
            transition: all 0.3s;
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
            white-space: nowrap;
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

        .foto-profile {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
            background: #F0E6D2;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            color: #B8860B;
            cursor: pointer;
            transition: transform 0.2s;
        }

        .foto-profile:hover {
            transform: scale(1.05);
            opacity: 0.9;
        }

        .foto-profile img {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
        }

        .default-foto {
            background: #FEF3C7;
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

        .badge-info {
            background: #d1ecf1;
            color: #0c5460;
        }

        .badge-secondary {
            background: #e9ecef;
            color: #6c757d;
        }

        .badge-test-lulus {
            background: #d4edda;
            color: #155724;
        }

        .badge-test-gagal {
            background: #f8d7da;
            color: #721c24;
        }

        .badge-test-proses {
            background: #fff3cd;
            color: #856404;
        }

        .dokumen-link {
            color: #D4A017;
            text-decoration: none;
            font-size: 12px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .dokumen-link:hover {
            text-decoration: underline;
            color: #B8860B;
        }

        .btn-hapus {
            background: #dc3545;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 12px;
            transition: all 0.2s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .btn-hapus:hover {
            background: #c82333;
            transform: translateY(-1px);
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

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background: white;
            border-radius: 20px;
            max-width: 700px;
            width: 90%;
            padding: 25px;
            position: relative;
            animation: modalFadeIn 0.3s;
        }

        .modal-foto {
            max-width: 600px;
            text-align: center;
        }

        .modal-foto img {
            max-width: 100%;
            max-height: 500px;
            border-radius: 10px;
        }

        .dokumen-item {
            border: 1px solid #F0E6D2;
            border-radius: 12px;
            margin-bottom: 15px;
            overflow: hidden;
        }

        .dokumen-header {
            background: #FFFDF5;
            padding: 12px 15px;
            border-bottom: 1px solid #F0E6D2;
            font-weight: 600;
            color: #B8860B;
        }

        .dokumen-preview {
            padding: 20px;
            text-align: center;
            background: white;
        }

        .dokumen-preview img {
            max-width: 100%;
            max-height: 400px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .dokumen-preview iframe {
            width: 100%;
            height: 500px;
            border: none;
            border-radius: 10px;
        }

        .avatar-placeholder {
            width: 200px;
            height: 200px;
            border-radius: 50%;
            background: #D4A017;
            color: white;
            font-size: 80px;
            font-weight: bold;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto;
        }

        @keyframes modalFadeIn {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #F0E6D2;
        }

        .modal-header h4 {
            color: #B8860B;
            font-size: 18px;
        }

        .close-modal {
            cursor: pointer;
            font-size: 24px;
            color: #8B7355;
        }

        .close-modal:hover {
            color: #dc3545;
        }

        @media (max-width: 1200px) {
            .data-table {
                display: block;
                overflow-x: auto;
                white-space: nowrap;
            }
        }

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
            <a href="ranking.php" class="menu-item"><i class="fas fa-trophy"></i><span>Ranking</span></a>
            <a href="daftar_ulang.php" class="menu-item active"><i class="fas fa-file-signature"></i><span>Daftar Ulang</span></a>
            <div class="menu-divider"></div>
            <a href="logout.php" class="menu-item"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a>
        </div>
    </div>

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
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-list"></i> Data Daftar Ulang</h3>
                </div>
                <?php if (count($daftar_ulang_list) > 0): ?>
                <div style="overflow-x: auto;">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Foto</th>
                                <th>Nama Lengkap</th>
                                <th>Email</th>
                                <th>Jurusan</th>
                                <th>No Test</th>
                                <th>NIM</th>
                                <th>Status Test</th>
                                <th>Status</th>
                                <th>Dokumen</th>
                                <th>Tanggal Daftar</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = 1; foreach ($daftar_ulang_list as $du): ?>
                            <tr>
                                <td><?php echo $no++; ?></td>
                                <td><?php echo tampilkanFoto($du['foto'] ?? '', $du['nama_lengkap'] ?? ''); ?></td>
                                <td><strong><?php echo htmlspecialchars($du['nama_lengkap'] ?? '-'); ?></strong></td>
                                <td><?php echo htmlspecialchars($du['email'] ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($du['jurusan'] ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($du['no_test'] ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($du['nim'] ?? '-'); ?></td>
                                <td><?php echo getTestStatusBadge($du['status_test'] ?? 'proses'); ?></td>
                                <td><?php echo getStatusBadge($du['status'] ?? 'pending'); ?></td>
                                <td>
                                    <a href="#" class="dokumen-link" onclick="showDokumen(<?php echo $du['id']; ?>, '<?php echo htmlspecialchars($du['nama_lengkap'] ?? 'Mahasiswa'); ?>', '<?php echo htmlspecialchars($du['ijazah'] ?? ''); ?>', '<?php echo htmlspecialchars($du['ktp'] ?? ''); ?>'); return false;">
                                        <i class="fas fa-file-pdf"></i> Lihat Dokumen
                                    </a>
                                 </div>
                                <td><?php echo date('d/m/Y H:i', strtotime($du['tanggal_daftar_ulang'])); ?></div>
                                <td>
                                    <a href="?hapus=<?php echo $du['id']; ?>" class="btn-hapus" onclick="return confirm('Yakin ingin menghapus data daftar ulang dari <?php echo htmlspecialchars($du['nama_lengkap'] ?? 'Mahasiswa'); ?>?')">
                                        <i class="fas fa-trash"></i> Hapus
                                    </a>
                                 </div>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <p>Belum ada data daftar ulang</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Modal untuk lihat dokumen -->
    <div id="dokumenModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h4 id="modalTitle"><i class="fas fa-file-alt"></i> Dokumen Pendaftaran</h4>
                <span class="close-modal" onclick="closeModal()">&times;</span>
            </div>
            <div id="modalBody">
                <p style="color: #8B7355; text-align: center;">Memuat data dokumen...</p>
            </div>
        </div>
    </div>

    <!-- Modal untuk preview foto -->
    <div id="fotoModal" class="modal">
        <div class="modal-content modal-foto">
            <div class="modal-header">
                <h4 id="fotoModalTitle"><i class="fas fa-image"></i> Foto Mahasiswa</h4>
                <span class="close-modal" onclick="closeFotoModal()">&times;</span>
            </div>
            <div id="fotoModalBody" style="text-align: center; padding: 20px;">
                <img id="previewFoto" src="" alt="Foto" style="max-width: 100%; max-height: 500px; border-radius: 10px;">
            </div>
        </div>
    </div>

    <script>
        // FUNGSI SHOW DOKUMEN - LANGSUNG MENAMPILKAN GAMBAR IJAZAH DAN KTP
        function showDokumen(id, nama, fileIjazah, fileKtp) {
            const modal = document.getElementById('dokumenModal');
            const modalTitle = document.getElementById('modalTitle');
            const modalBody = document.getElementById('modalBody');
            
            modalTitle.innerHTML = '<i class="fas fa-file-alt"></i> Dokumen - ' + nama;
            
            let html = '';
            
            // Tampilkan Ijazah
            html += '<div class="dokumen-item">';
            html += '<div class="dokumen-header"><i class="fas fa-graduation-cap"></i> Ijazah / Surat Keterangan Lulus</div>';
            html += '<div class="dokumen-preview">';
            
            if (fileIjazah && fileIjazah !== '') {
                const ext = fileIjazah.split('.').pop().toLowerCase();
                if (['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(ext)) {
                    html += '<img src="' + fileIjazah + '" alt="Ijazah" style="max-width: 100%; max-height: 400px; border-radius: 10px;" onerror="this.src=\'data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 24 24\' fill=\'%23D4A017\'%3E%3Cpath d=\'M0 0h24v24H0z\' fill=\'none\'/%3E%3Cpath d=\'M6 2c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6H6zm7 7V3.5L18.5 9H13z\'/%3E%3C/svg%3E\')">';
                } else if (ext === 'pdf') {
                    html += '<iframe src="' + fileIjazah + '" style="width: 100%; height: 500px; border: none; border-radius: 10px;"></iframe>';
                } else {
                    html += '<a href="' + fileIjazah + '" target="_blank" class="dokumen-link"><i class="fas fa-download"></i> Download File</a>';
                }
            } else {
                html += '<p style="color: #999; text-align: center; padding: 40px;">Belum ada file Ijazah yang diunggah</p>';
            }
            html += '</div></div>';
            
            // Tampilkan KTP
            html += '<div class="dokumen-item">';
            html += '<div class="dokumen-header"><i class="fas fa-id-card"></i> Kartu Tanda Penduduk (KTP)</div>';
            html += '<div class="dokumen-preview">';
            
            if (fileKtp && fileKtp !== '') {
                const ext = fileKtp.split('.').pop().toLowerCase();
                if (['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(ext)) {
                    html += '<img src="' + fileKtp + '" alt="KTP" style="max-width: 100%; max-height: 400px; border-radius: 10px;" onerror="this.src=\'data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 24 24\' fill=\'%23D4A017\'%3E%3Cpath d=\'M0 0h24v24H0z\' fill=\'none\'/%3E%3Cpath d=\'M6 2c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6H6zm7 7V3.5L18.5 9H13z\'/%3E%3C/svg%3E\')">';
                } else if (ext === 'pdf') {
                    html += '<iframe src="' + fileKtp + '" style="width: 100%; height: 500px; border: none; border-radius: 10px;"></iframe>';
                } else {
                    html += '<a href="' + fileKtp + '" target="_blank" class="dokumen-link"><i class="fas fa-download"></i> Download File</a>';
                }
            } else {
                html += '<p style="color: #999; text-align: center; padding: 40px;">Belum ada file KTP yang diunggah</p>';
            }
            html += '</div></div>';
            
            modalBody.innerHTML = html;
            modal.style.display = 'flex';
        }

        function closeModal() {
            document.getElementById('dokumenModal').style.display = 'none';
        }

        // Fungsi untuk menampilkan foto modal
        function showFoto(fotoUrl, nama) {
            const modal = document.getElementById('fotoModal');
            const modalTitle = document.getElementById('fotoModalTitle');
            const previewImg = document.getElementById('previewFoto');
            const modalBody = document.getElementById('fotoModalBody');
            
            modalTitle.innerHTML = '<i class="fas fa-image"></i> Foto - ' + nama;
            
            // Hapus avatar placeholder jika ada
            const existingAvatar = document.querySelector('#fotoModalBody .avatar-placeholder');
            if (existingAvatar) existingAvatar.remove();
            
            if (fotoUrl && fotoUrl !== '') {
                previewImg.src = fotoUrl;
                previewImg.style.display = 'block';
            } else {
                // Jika tidak ada foto, tampilkan avatar dari inisial
                previewImg.style.display = 'none';
                const words = nama.split(' ');
                let inisial = words[0].charAt(0).toUpperCase();
                if (words[1]) inisial += words[1].charAt(0).toUpperCase();
                
                const avatarDiv = document.createElement('div');
                avatarDiv.className = 'avatar-placeholder';
                avatarDiv.innerHTML = inisial;
                modalBody.appendChild(avatarDiv);
            }
            
            modal.style.display = 'flex';
        }

        function closeFotoModal() {
            document.getElementById('fotoModal').style.display = 'none';
            // Hapus avatar placeholder jika ada
            const avatar = document.querySelector('#fotoModalBody .avatar-placeholder');
            if (avatar) avatar.remove();
            document.getElementById('previewFoto').style.display = 'block';
        }

        // Tutup modal jika klik di luar konten
        window.onclick = function(event) {
            const dokumenModal = document.getElementById('dokumenModal');
            const fotoModal = document.getElementById('fotoModal');
            if (event.target === dokumenModal) {
                dokumenModal.style.display = 'none';
            }
            if (event.target === fotoModal) {
                closeFotoModal();
            }
        }
    </script>
</body>
</html>