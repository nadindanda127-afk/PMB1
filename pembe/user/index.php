<?php session_start(); ?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>PMB | Universitas together</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
.btn-nav-daftar,
.btn-nav-login {
    color: #fff;
    border: 1px solid transparent;
    font-weight: 600;
    border-radius: 10px;
}

.btn-nav-daftar {
    background: #0b1220;
    border-color: #020617;
}

.btn-nav-daftar:hover {
    background: #020617;
    border-color: #010409;
    color: #fff;
}

.btn-nav-login {
    background: #172033;
    border-color: #0b1220;
}

.btn-nav-login:hover {
    background: #0b1220;
    border-color: #020617;
    color: #fff;
}

.btn-nav-daftar:focus,
.btn-nav-login:focus {
    color: #fff;
    box-shadow: 0 0 0 0.2rem rgba(17, 24, 39, 0.28);
}
</style>
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow">
<div class="container">
    <a class="navbar-brand fw-bold" href="#">Universitas Contoh</a>
    <button class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#nav">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="nav">
        <ul class="navbar-nav ms-auto">
            <li class="nav-item"><a class="nav-link" href="#">Beranda</a></li>
            <li class="nav-item"><a class="nav-link" href="#jalur">Jalur Masuk</a></li>
            <li class="nav-item"><a class="nav-link" href="#jadwal">Jadwal</a></li>
            <li class="nav-item"><a class="nav-link btn btn-nav-daftar ms-2" href="register.php">Daftar</a></li>
            <li class="nav-item"><a class="nav-link btn btn-nav-login ms-2" href="login.php">Login</a></li>
        </ul>
    </div>
</div>
</nav>

<!-- HERO -->
<section class="bg-light py-5">
<div class="container text-center">
    <h1 class="fw-bold">Penerimaan Mahasiswa Baru 2026</h1>
    <p class="lead text-muted">Wujudkan masa depan akademik bersama Universitas Contoh</p>
    <a href="register.php" class="btn btn-primary btn-lg mt-3">Daftar Sekarang</a>
</div>
</section>

<!-- JALUR -->
<section id="jalur" class="py-5">
<div class="container">
<h3 class="text-center mb-4">Jalur Pendaftaran</h3>
<div class="row g-4">
    <div class="col-md-4">
        <div class="card shadow-sm">
            <div class="card-body text-center">
                <h5>Jalur Prestasi</h5>
                <p class="text-muted">Nilai rapor & prestasi akademik</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm">
            <div class="card-body text-center">
                <h5>Jalur Tes</h5>
                <p class="text-muted">Ujian seleksi tertulis</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm">
            <div class="card-body text-center">
                <h5>Jalur Beasiswa</h5>
                <p class="text-muted">Bagi calon mahasiswa berprestasi</p>
            </div>
        </div>
    </div>
</div>
</div>
</section>

<!-- JADWAL -->
<section id="jadwal" class="bg-light py-5">
<div class="container text-center">
<h3>Jadwal PMB</h3>
<ul class="list-group list-group-flush mt-3">
    <li class="list-group-item">Pendaftaran: Maret – Juni 2026</li>
    <li class="list-group-item">Tes Seleksi: Juli 2026</li>
    <li class="list-group-item">Pengumuman: Juli 2026</li>
</ul>
</div>
</section>

<!-- FOOTER -->
<footer class="bg-primary text-white text-center py-3">
© 2026 Universitas Contoh | PMB Online
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
