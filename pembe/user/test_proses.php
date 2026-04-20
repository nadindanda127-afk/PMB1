<?php
session_start();

// Cek apakah sudah diverifikasi
if (!isset($_SESSION['test_verified']) || $_SESSION['test_verified'] !== true) {
    header('Location: test.php');
    exit();
}

// Hapus session verifikasi setelah test selesai
unset($_SESSION['test_verified']);

session_start();

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
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

// Ambil semua soal
$query = "SELECT * FROM soal WHERE jurusan = '{$user['jurusan']}' ORDER BY id ASC";
$result = mysqli_query($conn, $query);
$soal_list = [];
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $soal_list[] = $row;
    }
}

$jumlah_benar = 0;
$jumlah_salah = 0;
$total_soal = count($soal_list);

foreach ($soal_list as $soal) {
    $id_soal = $soal['id'];
    $jawaban_user = isset($_POST['jawaban_' . $id_soal]) ? $_POST['jawaban_' . $id_soal] : '';
    $jawaban_benar = $soal['jawaban_benar'];
    
    if ($jawaban_user == $jawaban_benar) {
        $jumlah_benar++;
    } else {
        $jumlah_salah++;
    }
}

// Hitung nilai (skala 100)
$total_nilai = round(($jumlah_benar / $total_soal) * 100);
$status = ($total_nilai >= 70) ? 'lulus' : 'tidak_lulus';

// Simpan ke tabel hasil_test
$query = "INSERT INTO hasil_test (id_peserta, nama_peserta, jurusan, total_nilai, jumlah_benar, jumlah_salah, persentase, status) 
          VALUES ($user_id, '{$user['nama']}', '{$user['jurusan']}', $total_nilai, $jumlah_benar, $jumlah_salah, $total_nilai, '$status')";
mysqli_query($conn, $query);

// Update status user
$query = "UPDATE users SET status_test = '$status' WHERE id = $user_id";
mysqli_query($conn, $query);

// Redirect ke halaman hasil
header('Location: hasil_test.php');
exit();
?>