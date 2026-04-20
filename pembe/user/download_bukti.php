<?php
session_start();
include '../config/database.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'user') {
    header('Location: ../login.php');
    exit;
}

$user_id = (int) $_SESSION['user']['id'];

$data = mysqli_fetch_assoc(
    mysqli_query($conn, "
        SELECT
            COALESCE(NULLIF(u.nama, ''), u.nama_lengkap, 'User') AS nama_tampil,
            u.email,
            COALESCE(u.jurusan, '-') AS jurusan,
            COALESCE(u.no_tes, '-') AS no_tes,
            COALESCE(u.status_test, 'proses') AS status_test,
            COALESCE(u.status_daftar_ulang, 'belum') AS status_daftar_ulang,
            d.alamat_lengkap,
            d.asal_sekolah,
            d.bukti_pembayaran,
            d.updated_at
        FROM users u
        LEFT JOIN daftar_ulang d ON d.user_id = u.id
        WHERE u.id='$user_id'
        LIMIT 1
    ")
);

if (!$data || empty($data['alamat_lengkap'])) {
    header('Location: daftar_ulang.php');
    exit;
}

$filename = 'bukti-daftar-ulang-' . $user_id . '.html';
header('Content-Type: text/html; charset=UTF-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Bukti Daftar Ulang</title>
<style>
body{font-family:Arial,sans-serif;padding:24px;line-height:1.5;color:#222}
.box{border:1px solid #333;padding:18px;border-radius:8px;max-width:700px}
h2{margin-top:0}
.row{margin:6px 0}
.label{display:inline-block;width:170px;font-weight:bold}
</style>
</head>
<body>
<div class="box">
    <h2>Bukti Daftar Ulang PMB</h2>
    <p>Dokumen ini adalah bukti bahwa calon mahasiswa telah mengisi data daftar ulang.</p>

    <div class="row"><span class="label">Nama</span>: <?= htmlspecialchars($data['nama_tampil']); ?></div>
    <div class="row"><span class="label">Gmail</span>: <?= htmlspecialchars($data['email']); ?></div>
    <div class="row"><span class="label">Jurusan</span>: <?= htmlspecialchars($data['jurusan']); ?></div>
    <div class="row"><span class="label">Nomor Tes</span>: <?= htmlspecialchars($data['no_tes']); ?></div>
    <div class="row"><span class="label">Status Tes</span>: <?= strtoupper(htmlspecialchars($data['status_test'])); ?></div>
    <div class="row"><span class="label">Status Daftar Ulang</span>: <?= htmlspecialchars($data['status_daftar_ulang']); ?></div>
    <div class="row"><span class="label">Alamat Lengkap</span>: <?= htmlspecialchars($data['alamat_lengkap']); ?></div>
    <div class="row"><span class="label">Asal Sekolah</span>: <?= htmlspecialchars($data['asal_sekolah']); ?></div>
    <div class="row"><span class="label">No. Bukti Pembayaran</span>: <?= htmlspecialchars($data['bukti_pembayaran']); ?></div>
    <div class="row"><span class="label">Waktu Update</span>: <?= htmlspecialchars($data['updated_at']); ?></div>
</div>
</body>
</html>
