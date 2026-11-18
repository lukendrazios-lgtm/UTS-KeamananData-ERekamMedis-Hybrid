<?php
require_once 'koneksi.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Klinik Lukendra Sehat Sentosa — E-Rekam Medis Hybrid (AES-256 &amp; RSA-2048)</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background: #f8fafc; color: #1e293b; line-height: 1.6; }
        .navbar { background: #0f766e; color: #fff; padding: 18px 40px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 10px rgba(0,0,0,0.08); }
        .brand { font-size: 20px; font-weight: 700; letter-spacing: 0.3px; }
        .brand small { display: block; font-size: 12px; font-weight: 400; color: #ccfbf1; }
        .btn-nav { background: #fff; color: #0f766e; text-decoration: none; padding: 10px 20px; border-radius: 8px; font-weight: 700; font-size: 14px; transition: 0.2s; }
        .btn-nav:hover { background: #f0fdfa; transform: translateY(-1px); }
        .hero { background: linear-gradient(135deg, #0f766e 0%, #115e59 100%); color: #fff; padding: 72px 24px; text-align: center; }
        .hero-inner { max-width: 840px; margin: 0 auto; }
        .pill { display: inline-block; background: rgba(255,255,255,0.15); border: 1px solid rgba(255,255,255,0.3); padding: 5px 14px; border-radius: 999px; font-size: 12.5px; margin-bottom: 18px; }
        .hero h1 { font-size: 35px; line-height: 1.25; margin-bottom: 16px; }
        .hero p { font-size: 16px; color: #e2e8f0; margin-bottom: 30px; }
        .hero-actions { display: flex; justify-content: center; gap: 14px; flex-wrap: wrap; }
        .btn-cta { background: #f59e0b; color: #0f172a; text-decoration: none; padding: 14px 28px; border-radius: 8px; font-weight: 700; font-size: 15px; transition: 0.2s; }
        .btn-cta:hover { background: #fbbf24; }
        .btn-outline { border: 1.5px solid #fff; color: #fff; text-decoration: none; padding: 13px 24px; border-radius: 8px; font-weight: 600; font-size: 15px; }
        .btn-outline:hover { background: rgba(255,255,255,0.1); }
        .section { max-width: 1100px; margin: 54px auto; padding: 0 24px; }
        .section-title { text-align: center; margin-bottom: 34px; }
        .section-title h2 { font-size: 26px; color: #0f172a; margin-bottom: 8px; }
        .section-title p { color: #64748b; font-size: 15px; }
        .grid-3 { display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; }
        @media (max-width: 850px) { .grid-3 { grid-template-columns: 1fr; } .hero h1 { font-size: 27px; } }
        .card { background: #fff; padding: 26px; border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: 0 4px 12px rgba(15,23,42,0.04); }
        .card h3 { font-size: 18px; color: #0f766e; margin-bottom: 10px; }
        .card p { font-size: 14.5px; color: #475569; }
        .security-box { background: #fff; border-left: 5px solid #0f766e; padding: 28px; border-radius: 10px; border-top: 1px solid #e2e8f0; border-right: 1px solid #e2e8f0; border-bottom: 1px solid #e2e8f0; }
        .security-box h3 { font-size: 20px; margin-bottom: 10px; color: #0f172a; }
        .security-box p { color: #475569; font-size: 14.5px; margin-bottom: 12px; }
        .footer { background: #0f172a; color: #94a3b8; text-align: center; padding: 24px; font-size: 13.5px; margin-top: 60px; }
    </style>
</head>
<body>

<nav class="navbar">
    <div class="brand">
        Klinik Lukendra` Sehat Sentosa
        <small>Layanan Kesehatan Terpadu &amp; Rekam Medis Digital</small>

</nav>

<header class="hero">
    <div class="hero-inner">
        <span class="pill">Kriptografi Hybrid: Simetris AES-256-CBC + Asimetris RSA-2048</span>
        <h1>Pelayanan Kesehatan Profesional dengan Perlindungan Rekam Medis Terenkripsi</h1>
        <p>Seluruh catatan medis pasien diamankan menggunakan kombinasi kecepatan enkripsi simetris <strong>AES-256</strong> dan perlindungan pertukaran kunci asimetris <strong>RSA-2048</strong>.</p>
        <div class="hero-actions">
            <a href="admin.php" class="btn-cta">Buka Halaman Admin Rekam Medis</a>
        </div>
    </div>
</header>

<section class="section">
    <div class="section-title">
        <h2>Layanan Poliklinik Kami</h2>
        <p>Pelayanan medis rawat jalan dengan pencatatan rekam medis elektronik terintegrasi</p>
    </div>
    <div class="grid-3">
        <div class="card">
            <h3>Poli Umum</h3>
            <p>Pemeriksaan kesehatan menyeluruh, penanganan penyakit akut maupun kronis, serta konsultasi medis bersama dokter umum berpengalaman.</p>
        </div>
        <div class="card">
            <h3>Poli Gigi &amp; Mulut</h3>
            <p>Perawatan kesehatan gigi, pembersihan karang gigi, penambalan, serta tindakan medis gigi dengan peralatan steril dan modern.</p>
        </div>
        <div class="card">
            <h3>Laboratorium Klinik</h3>
            <p>Pemeriksaan darah lengkap, cek gula darah, kolesterol, dan asam urat dengan hasil terenkripsi langsung ke rekam medis pasien.</p>
        </div>
    </div>
</section>

<section class="section" id="keamanan">

</section>

<footer class="footer">
    &copy; 2026 Klinik Pratama Sehat Sentosa — Sistem Informasi E-Rekam Medis Hybrid (AES-256 &amp; RSA-2048)
</footer>

</body>
</html>