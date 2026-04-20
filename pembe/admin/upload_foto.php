<?php
session_start();
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

// Ambil data users
$query = "SELECT id, nama_lengkap, email, foto FROM users WHERE role = 'user' OR role IS NULL ORDER BY id";
$result = mysqli_query($conn, $query);

// Proses upload
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['foto'])) {
    $user_id = intval($_POST['user_id']);
    
    if (!file_exists('uploads')) {
        mkdir('uploads', 0777, true);
    }
    
    $file = $_FILES['foto'];
    $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $file['name']);
    $target = 'uploads/' . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $target)) {
        $update = "UPDATE users SET foto = '$target' WHERE id = $user_id";
        mysqli_query($conn, $update);
        $success = "Foto berhasil diupload!";
        header("Location: upload_foto.php?success=1");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Upload Foto Mahasiswa</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #FFF9E6 0%, #FFF3C4 100%);
            padding: 30px;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            padding: 25px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        h2 { color: #B8860B; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #F0E6D2; }
        th { background: #FFFDF5; color: #8B7355; }
        .foto-preview { width: 50px; height: 50px; border-radius: 50%; object-fit: cover; }
        .btn-upload { background: #D4A017; color: white; border: none; padding: 8px 16px; border-radius: 8px; cursor: pointer; }
        .btn-upload:hover { background: #B8860B; }
        .alert-success { background: #d4edda; color: #155724; padding: 12px; border-radius: 8px; margin-bottom: 20px; }
        .back-link { color: #D4A017; margin-bottom: 20px; display: inline-block; text-decoration: none; }
        .back-link:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="container">
        <h2><i class="fas fa-camera"></i> Upload Foto Mahasiswa</h2>
        <a href="daftar_ulang.php" class="back-link">← Kembali ke Daftar Ulang</a>
        
        <?php if (isset($_GET['success'])): ?>
            <div class="alert-success">✓ Foto berhasil diupload!</div>
        <?php endif; ?>
        
        <table>
            <thead>
                <tr><th>ID</th><th>Nama Lengkap</th><th>Email</th><th>Foto Saat Ini</th><th>Upload Foto</th></tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo htmlspecialchars($row['nama_lengkap']); ?></td>
                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                    <td>
                        <?php if (!empty($row['foto']) && file_exists($row['foto'])): ?>
                            <img src="<?php echo $row['foto']; ?>" class="foto-preview">
                        <?php else: ?>
                            <span style="color: #999;">Belum ada foto</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <form method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="user_id" value="<?php echo $row['id']; ?>">
                            <input type="file" name="foto" accept="image/*" required>
                            <button type="submit" class="btn-upload">Upload</button>
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>