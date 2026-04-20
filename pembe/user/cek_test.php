<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['user'])) {
    die("Silakan login dulu");
}

$user = $_SESSION['user'];
$user_id = $user['id'];

$host = 'localhost';
$dbname = 'pembe';
$user_db = 'root';
$pass = '';

$conn = mysqli_connect($host, $user_db, $pass, $dbname);
if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

$query = "SELECT id, nama_lengkap, status_test, no_test FROM users WHERE id = $user_id";
$result = mysqli_query($conn, $query);
$data = mysqli_fetch_assoc($result);

echo "<h2>Data User:</h2>";
echo "ID: " . $data['id'] . "<br>";
echo "Nama: " . $data['nama_lengkap'] . "<br>";
echo "Status Test: '" . $data['status_test'] . "'<br>";
echo "No Test: " . $data['no_test'] . "<br>";

if ($data['status_test'] == 'belum' || $data['status_test'] == 'proses') {
    echo "<p style='color:orange;'>Status: BELUM TEST / PROSES</p>";
} elseif ($data['status_test'] == 'lulus') {
    echo "<p style='color:green;'>Status: LULUS</p>";
} elseif ($data['status_test'] == 'tidak_lulus') {
    echo "<p style='color:red;'>Status: TIDAK LULUS</p>";
} else {
    echo "<p style='color:red;'>Status tidak dikenal: " . $data['status_test'] . "</p>";
}
?>