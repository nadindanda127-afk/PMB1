<?php
session_start();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PMB System | Universitas Cakrawala Nusantara</title>
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

        /* Background pattern seperti dashboard */
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

        /* Navbar */
        .navbar {
            background: linear-gradient(135deg, #D4A017 0%, #B8860B 100%);
            padding: 15px 0;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 24px;
        }

        .nav-wrapper {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
        }

        .logo h2 {
            color: white;
            font-size: 24px;
            font-weight: 800;
            letter-spacing: -0.5px;
        }

        .logo p {
            color: rgba(255,255,255,0.8);
            font-size: 11px;
        }

        .nav-buttons {
            display: flex;
            gap: 12px;
        }

        .btn-nav {
            padding: 10px 22px;
            border-radius: 40px;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.3s;
        }

        .btn-user {
            background: rgba(255,255,255,0.2);
            color: white;
            border: 1px solid rgba(255,255,255,0.3);
        }

        .btn-user:hover {
            background: rgba(255,255,255,0.3);
            transform: translateY(-2px);
        }

        .btn-admin {
            background: #8B4513;
            color: white;
        }

        .btn-admin:hover {
            background: #6B3410;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(139, 69, 19, 0.3);
        }

        /* Hero Section */
        .hero {
            background: linear-gradient(135deg, #FFF9E6 0%, #FFECB3 100%);
            color: #5a4a2a;
            padding: 80px 0;
            position: relative;
            overflow: hidden;
        }

        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320"><path fill="rgba(255,215,0,0.1)" fill-opacity="1" d="M0,96L48,112C96,128,192,160,288,160C384,160,480,128,576,122.7C672,117,768,139,864,154.7C960,171,1056,181,1152,170.7C1248,160,1344,128,1392,112L1440,96L1440,0L1392,0C1344,0,1248,0,1152,0C1056,0,960,0,864,0C768,0,672,0,576,0C480,0,384,0,288,0C192,0,96,0,48,0L0,0Z"></path></svg>') no-repeat bottom;
            background-size: cover;
        }

        .hero-wrapper {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 50px;
            position: relative;
            z-index: 2;
        }

        .hero-content {
            flex: 1;
            min-width: 300px;
        }

        .hero-content h1 {
            font-size: 48px;
            font-weight: 800;
            margin-bottom: 20px;
            line-height: 1.2;
            color: #B8860B;
        }

        .hero-content h1 span {
            color: #B8860B;
        }

        .hero-content p {
            font-size: 16px;
            color: #6b5a3a;
            margin-bottom: 30px;
            line-height: 1.6;
        }

        .btn-hero {
            background: linear-gradient(135deg, #D4A017, #B8860B);
            color: white;
            padding: 14px 35px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            font-size: 16px;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s;
            box-shadow: 0 4px 15px rgba(212, 160, 23, 0.3);
        }

        .btn-hero:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 30px rgba(212, 160, 23, 0.4);
        }

        .hero-image {
            flex: 1;
            min-width: 280px;
            text-align: center;
        }

        .hero-image i {
            font-size: 200px;
            color: rgba(212, 160, 23, 0.3);
        }

        /* Alur Section */
        .alur-section {
            padding: 80px 0;
            background: white;
        }

        .section-title {
            text-align: center;
            font-size: 32px;
            font-weight: 800;
            color: #B8860B;
            margin-bottom: 15px;
        }

        .section-subtitle {
            text-align: center;
            color: #6b5a3a;
            margin-bottom: 50px;
            font-size: 16px;
        }

        .alur-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
        }

        .alur-card {
            background: #FFFDF5;
            border-radius: 24px;
            padding: 35px 25px;
            text-align: center;
            transition: all 0.3s;
            border: 1px solid #F0E6D2;
        }

        .alur-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.08);
            border-color: #D4A017;
        }

        .alur-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #D4A017, #B8860B);
            border-radius: 30px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 25px;
        }

        .alur-icon i {
            font-size: 36px;
            color: white;
        }

        .alur-card h3 {
            font-size: 20px;
            color: #B8860B;
            margin-bottom: 12px;
            font-weight: 700;
        }

        .alur-card p {
            color: #6b5a3a;
            font-size: 14px;
            line-height: 1.6;
        }

        .alur-number {
            display: inline-block;
            background: #FFF0D0;
            color: #B8860B;
            font-size: 12px;
            font-weight: 700;
            padding: 4px 12px;
            border-radius: 20px;
            margin-top: 15px;
        }

        /* Statistik */
        .stats-section {
            background: linear-gradient(135deg, #D4A017 0%, #B8860B 100%);
            padding: 60px 0;
            color: white;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 30px;
            text-align: center;
        }

        .stat-item h2 {
            font-size: 40px;
            font-weight: 800;
            margin-bottom: 10px;
        }

        .stat-item p {
            font-size: 14px;
            opacity: 0.9;
        }

        /* Jadwal */
        .jadwal-section {
            padding: 80px 0;
            background: #FFFDF5;
        }

        .jadwal-list {
            max-width: 800px;
            margin: 0 auto;
        }

        .jadwal-item {
            background: white;
            border-radius: 20px;
            padding: 20px 25px;
            margin-bottom: 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            transition: all 0.3s;
            border: 1px solid #F0E6D2;
        }

        .jadwal-item:hover {
            transform: translateX(5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            border-color: #D4A017;
        }

        .jadwal-date {
            font-weight: 800;
            color: #B8860B;
            font-size: 16px;
        }

        .jadwal-event {
            color: #6b5a3a;
            font-weight: 500;
        }

        .jadwal-badge {
            background: linear-gradient(135deg, #D4A017, #B8860B);
            color: white;
            padding: 6px 18px;
            border-radius: 40px;
            font-size: 12px;
            font-weight: 600;
        }

        /* CTA */
        .cta-section {
            background: linear-gradient(135deg, #FFF9E6 0%, #FFECB3 100%);
            color: #5a4a2a;
            padding: 60px 0;
            text-align: center;
        }

        .cta-section h2 {
            font-size: 32px;
            font-weight: 800;
            margin-bottom: 15px;
            color: #B8860B;
        }

        .cta-section p {
            font-size: 16px;
            color: #6b5a3a;
            margin-bottom: 30px;
        }

        .btn-cta {
            background: linear-gradient(135deg, #D4A017, #B8860B);
            color: white;
            padding: 14px 40px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            font-size: 16px;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s;
        }

        .btn-cta:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(212, 160, 23, 0.4);
        }

        /* Footer */
        .footer {
            background: #5a4a2a;
            color: #E8E0D0;
            padding: 40px 0;
            text-align: center;
        }

        .footer p {
            font-size: 13px;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .hero-content h1 {
                font-size: 32px;
            }
            .section-title {
                font-size: 28px;
            }
            .nav-wrapper {
                flex-direction: column;
                text-align: center;
            }
            .jadwal-item {
                flex-direction: column;
                text-align: center;
            }
            .stats-grid {
                grid-template-columns: 1fr 1fr;
            }
        }

        @media (max-width: 480px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar">
    <div class="container">
        <div class="nav-wrapper">
            <div class="logo">
                <h2><i class="fas fa-graduation-cap"></i> UCAN</h2>
                <p>Universitas Cakrawala Nusantara</p>
            </div>
            <div class="nav-buttons">
                <a href="user/login.php" class="btn-nav btn-user">
                    <i class="fas fa-user"></i> Akses Mahasiswa
                </a>
                <a href="admin/login.php" class="btn-nav btn-admin">
                    <i class="fas fa-shield-alt"></i> Admin Panel
                </a>
            </div>
        </div>
    </div>
</nav>

<!-- HERO SECTION -->
<section class="hero">
    <div class="container">
        <div class="hero-wrapper">
            <div class="hero-content">
                <h1>Penerimaan Mahasiswa Baru <span>2026/2027</span></h1>
                <p>Wujudkan masa depan akademik bersama Universitas Cakrawala Nusantara. Bergabunglah dengan ratusan mahasiswa berprestasi.</p>
                <a href="user/register.php" class="btn-hero">
                    <i class="fas fa-arrow-right"></i> Daftar Sekarang
                </a>
            </div>
            <div class="hero-image">
                <i class="fas fa-graduation-cap"></i>
            </div>
        </div>
    </div>
</section>

<!-- ALUR PENDAFTARAN -->
<section class="alur-section">
    <div class="container">
        <h2 class="section-title">Alur Pendaftaran</h2>
        <p class="section-subtitle">Pilih jalur yang sesuai dengan prestasi dan minat Anda</p>
        <div class="alur-grid">
            <div class="alur-card">
                <div class="alur-icon">
                    <i class="fas fa-trophy"></i>
                </div>
                <h3>Jalur Prestasi</h3>
                <p>Untuk calon mahasiswa dengan prestasi akademik maupun non-akademik yang gemilang.</p>
                <span class="alur-number">Tanpa Tes</span>
            </div>
            <div class="alur-card">
                <div class="alur-icon">
                    <i class="fas fa-pen-fancy"></i>
                </div>
                <h3>Jalur Tes</h3>
                <p>Seleksi berdasarkan hasil ujian tertulis yang diselenggarakan secara online.</p>
                <span class="alur-number">Ujian Online</span>
            </div>
            <div class="alur-card">
                <div class="alur-icon">
                    <i class="fas fa-gem"></i>
                </div>
                <h3>Jalur Beasiswa</h3>
                <p>Kesempatan bagi calon mahasiswa berprestasi untuk mendapatkan beasiswa penuh.</p>
                <span class="alur-number">Full Scholarship</span>
            </div>
        </div>
    </div>
</section>

<!-- STATISTIK -->
<section class="stats-section">
    <div class="container">
        <div class="stats-grid">
            <div class="stat-item">
                <h2>500+</h2>
                <p>Mahasiswa Aktif</p>
            </div>
            <div class="stat-item">
                <h2>200+</h2>
                <p>Dosen Profesional</p>
            </div>
            <div class="stat-item">
                <h2>98%</h2>
                <p>Kelulusan Tepat Waktu</p>
            </div>
            <div class="stat-item">
                <h2>400+</h2>
                <p>Program Kerjasama</p>
            </div>
        </div>
    </div>
</section>

<!-- JADWAL PMB -->
<section class="jadwal-section">
    <div class="container">
        <h2 class="section-title">Jadwal PMB 2026</h2>
        <p class="section-subtitle">Catat tanggal-tanggal penting berikut</p>
        <div class="jadwal-list">
            <div class="jadwal-item">
                <span class="jadwal-date"><i class="fas fa-calendar-alt"></i> Maret - Juni 2026</span>
                <span class="jadwal-event">Pendaftaran Online</span>
                <span class="jadwal-badge">Aktif</span>
            </div>
            <div class="jadwal-item">
                <span class="jadwal-date"><i class="fas fa-calendar-alt"></i> Juli 2026</span>
                <span class="jadwal-event">Tes Seleksi</span>
                <span class="jadwal-badge">Segera</span>
            </div>
            <div class="jadwal-item">
                <span class="jadwal-date"><i class="fas fa-calendar-alt"></i> Juli 2026</span>
                <span class="jadwal-event">Pengumuman Hasil</span>
                <span class="jadwal-badge">Info</span>
            </div>
            <div class="jadwal-item">
                <span class="jadwal-date"><i class="fas fa-calendar-alt"></i> Agustus 2026</span>
                <span class="jadwal-event">Daftar Ulang Mahasiswa Baru</span>
                <span class="jadwal-badge">Info</span>
            </div>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="cta-section">
    <div class="container">
        <h2>Mulai Perjalanan Akademik Anda</h2>
        <p>Daftarkan diri Anda sekarang dan raih masa depan cerah bersama Universitas Cakrawala Nusantara</p>
        <a href="user/register.php" class="btn-cta">
            <i class="fas fa-user-plus"></i> Daftar Sekarang
        </a>
    </div>
</section>

<!-- FOOTER -->
<footer class="footer">
    <div class="container">
        <p>&copy; 2026 PMB UCAN - Universitas Cakrawala Nusantara. All rights reserved.</p>
    </div>
</footer>

</body>
</html>