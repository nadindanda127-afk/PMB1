<?php
$host = 'localhost';
$dbname = 'pembe';
$user = 'root';
$pass = '';

$conn = mysqli_connect($host, $user, $pass, $dbname);
$result = mysqli_query($conn, "SELECT id, nama_lengkap, foto FROM daftar_ulang");

echo "<h2>Cek Lokasi File Foto</h2>";
echo "<table border='1' cellpadding='10'>";
echo "<tr><th>ID</th><th>Nama</th><th>Foto di DB</th><th>Cek di folder</th><th>Status</th></tr>";

while ($row = mysqli_fetch_assoc($result)) {
    echo "<tr>";
    echo "<td>{$row['id']}</td>";
    echo "<td>{$row['nama_lengkap']}</td>";
    echo "<td>{$row['foto']}</td>";
    echo "<td>";
    
    $found = false;
    $paths = [
        '../uploads/' . $row['foto'],
        'uploads/' . $row['foto'],
        '../../uploads/' . $row['foto'],
        $_SERVER['DOCUMENT_ROOT'] . '/pembe/uploads/' . $row['foto'],
        $_SERVER['DOCUMENT_ROOT'] . '/pembe/admin/uploads/' . $row['foto']
    ];
    
    foreach ($paths as $path) {
        if (file_exists($path)) {
            echo "✅ Ditemukan di: " . $path . "<br>";
            echo "<img src='" . $path . "' width='50' height='50' style='border-radius:50%'>";
            $found = true;
            break;
        }
    }
    
    if (!$found) {
        echo "❌ FILE TIDAK DITEMUKAN<br>";
        echo "Coba cek di folder: <br>";
        echo "- pembe/uploads/<br>";
        echo "- pembe/admin/uploads/<br>";
    }
    
    echo "</td>";
    echo "<td>" . ($found ? "✅ OK" : "❌ MISSING") . "</td>";
    echo "</tr>";
}
echo "</table>";
?>