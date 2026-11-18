<?php
require_once 'koneksi.php';

$errorLogin = '';
$pesanNotif = '';
$tipeNotif  = '';

// 1. Proses Logout dari Dashboard kembali ke Halaman Login Admin
if (isset($_GET['aksi']) && $_GET['aksi'] === 'logout') {
    unset($_SESSION['admin_logged_in']);
    unset($_SESSION['mode_dekripsi']);
    unset($_SESSION['aes_key']);
    header("Location: admin.php");
    exit;
}

// 2. Proses Login Menggunakan Kunci Simetris (AES) + Kunci Asimetris (RSA Private Key)
if (isset($_POST['login_hybrid'])) {
    $inputAesKey     = trim($_POST['aes_key'] ?? '');
    $inputPrivateKey = trim($_POST['private_key'] ?? '');

    // Jika pengguna mengunggah file .pem untuk Private Key, baca isinya
    if (isset($_FILES['file_pem']) && $_FILES['file_pem']['error'] === UPLOAD_ERR_OK) {
        $inputPrivateKey = trim(file_get_contents($_FILES['file_pem']['tmp_name']));
    }

    if ($inputAesKey === '' || $inputPrivateKey === '') {
        $errorLogin = "Kunci Simetris (AES) dan Kunci Asimetris (RSA Private Key) wajib diisi keduanya! Atau gunakan tombol 'Login Tanpa Kunci'.";
    } else {
        $verifiedAesKey = verifikasi_kunci_hybrid($inputAesKey, $inputPrivateKey, $ENCRYPTED_AES_KEY);
        if ($verifiedAesKey !== false) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['mode_dekripsi']   = true;
            $_SESSION['aes_key']         = $verifiedAesKey;
            header("Location: admin.php");
            exit;
        } else {
            $errorLogin = "Autentikasi Hybrid Gagal! Kombinasi Kunci Simetris (AES) dan RSA Private Key tidak valid.";
        }
    }
}

// 3. Proses Login Tanpa Kunci (Masuk Dashboard Mode Read-Only Terenkripsi)
if (isset($_POST['login_tanpa_kunci'])) {
    $_SESSION['admin_logged_in'] = true;
    $_SESSION['mode_dekripsi']   = false;
    unset($_SESSION['aes_key']);
    header("Location: admin.php");
    exit;
}

// Cek status login admin
$sudahLogin = !empty($_SESSION['admin_logged_in']);

// =========================================================================
// BAGIAN A: JIKA BELUM LOGIN -> TAMPILKAN HALAMAN LOGIN HYBRID ADMIN
// =========================================================================
if (!$sudahLogin):
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin Hybrid — E-Rekam Medis Klinik</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 24px; color: #1e293b; }
        .login-card { background: #fff; width: 100%; max-width: 550px; border-radius: 14px; padding: 34px; box-shadow: 0 20px 40px rgba(0,0,0,0.35); border-top: 5px solid #0f766e; }
        .top-link { display: inline-block; text-decoration: none; color: #0f766e; font-size: 13px; font-weight: 600; margin-bottom: 16px; }
        .top-link:hover { text-decoration: underline; }
        .badge { display: inline-block; background: #ccfbf1; color: #0f766e; font-size: 11.5px; font-weight: 700; padding: 4px 10px; border-radius: 999px; margin-bottom: 10px; }
        h1 { font-size: 23px; color: #0f172a; margin-bottom: 6px; }
        .desc { font-size: 13.5px; color: #64748b; margin-bottom: 20px; line-height: 1.5; }
        .alert-error { background: #fef2f2; border: 1px solid #fecaca; color: #b91c1c; padding: 12px 14px; border-radius: 8px; font-size: 13.5px; margin-bottom: 18px; }
        label { display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 6px; }
        input[type="password"], input[type="text"], textarea { width: 100%; padding: 11px 12px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-family: 'Courier New', monospace; font-size: 12.5px; background: #f8fafc; }
        textarea { resize: vertical; font-size: 11.5px; }
        input:focus, textarea:focus { outline: none; border-color: #0f766e; background: #fff; }
        input[type="file"] { width: 100%; padding: 8px 12px; border: 1.5px dashed #cbd5e1; border-radius: 8px; font-size: 12.5px; background: #f8fafc; }
        .field-group { margin-bottom: 15px; }
        .btn { width: 100%; padding: 12px 18px; border: none; border-radius: 8px; font-weight: 700; font-size: 14px; cursor: pointer; transition: 0.2s; }
        .btn-primary { background: #0f766e; color: #fff; margin-bottom: 10px; }
        .btn-primary:hover { background: #115e59; }
        .divider { text-align: center; font-size: 12px; color: #94a3b8; margin: 10px 0; }
        .btn-secondary { background: #fffbeb; color: #b45309; border: 1.5px solid #f59e0b; }
        .btn-secondary:hover { background: #fef3c7; }
        .note-footer { margin-top: 18px; padding-top: 14px; border-top: 1px solid #e2e8f0; font-size: 12.5px; color: #64748b; line-height: 1.5; }
    </style>
</head>
<body>

<div class="login-card">
    <a href="index.php" class="top-link">&larr; Kembali ke Landing Page</a>
    <div><span class="badge">AUTENTIKASI HYBRID: SIMETRIS AES-256 + ASIMETRIS RSA-2048</span></div>
    <h1>Login Dashboard Admin</h1>
    <p class="desc">Masukkan kombinasi <strong>Kunci Simetris (AES)</strong> dan <strong>Kunci Asimetris (RSA Private Key)</strong> untuk mendekripsi data pasien serta membuka hak akses CRUD.</p>

    <?php if ($errorLogin !== ''): ?>
        <div class="alert-error"><?= htmlspecialchars($errorLogin) ?></div>
    <?php endif; ?>

    <form method="POST" action="" enctype="multipart/form-data">
        <div class="field-group">
            <label for="aes_key">1. Kunci Simetris (Secret Key AES-256):</label>
            <input type="password" id="aes_key" name="aes_key" placeholder="Masukkan Kunci Simetris AES klinik...">
        </div>

        <div class="field-group">
            <label for="private_key">2. Kunci Asimetris (RSA Private Key .PEM):</label>
            <textarea id="private_key" name="private_key" rows="6" placeholder="-----BEGIN PRIVATE KEY-----&#10;Tempel RSA Private Key Anda di sini...&#10;-----END PRIVATE KEY-----"></textarea>
        </div>

        <div class="field-group">
            <label for="file_pem">Atau Unggah File RSA Private Key (private_key.pem):</label>
            <input type="file" id="file_pem" name="file_pem" accept=".pem,.txt,.key">
        </div>

        <button type="submit" name="login_hybrid" class="btn btn-primary">
            Login dengan Kunci Simetris &amp; Asimetris (Akses Penuh &amp; Dekripsi)
        </button>

        <div class="divider">atau masuk tanpa verifikasi kunci</div>

        <button type="submit" name="login_tanpa_kunci" class="btn btn-secondary">
            Login Tanpa Kunci (Lihat Data Terenkripsi)
        </button>
    </form>

</div>

</body>
</html>
<?php
exit;
endif;

// =========================================================================
// BAGIAN B: JIKA SUDAH LOGIN -> TAMPILKAN HALAMAN DASHBOARD ADMIN
// =========================================================================

// Validasi sesi dekripsi hybrid
$modeDekripsi = (!empty($_SESSION['mode_dekripsi']) && !empty($_SESSION['aes_key']));

// -------------------------------------------------------------------------
// OPERASI CRUD (HANYA DIIZINKAN JIKA $modeDekripsi === true)
// -------------------------------------------------------------------------

// CREATE: Tambah Rekam Medis Baru (Dienkripsi dengan AES-256-CBC)
if (isset($_POST['simpan_rekam_medis'])) {
    if (!$modeDekripsi) {
        $pesanNotif = "Akses ditolak! Anda harus login menggunakan Kunci Simetris dan Asimetris untuk menambah data.";
        $tipeNotif  = "error";
    } else {
        $no_rm    = mysqli_real_escape_string($conn, trim($_POST['no_rm']));
        $nik      = trim($_POST['nik_pasien']);
        $nama     = trim($_POST['nama_pasien']);
        $keluhan  = trim($_POST['keluhan']);
        $diagnosa = trim($_POST['diagnosa']);
        $resep    = trim($_POST['resep_obat']);

        $aesKey      = $_SESSION['aes_key'];
        $encNik      = aes_encrypt($nik, $aesKey);
        $encNama     = aes_encrypt($nama, $aesKey);
        $encKeluhan  = aes_encrypt($keluhan, $aesKey);
        $encDiagnosa = aes_encrypt($diagnosa, $aesKey);
        $encResep    = aes_encrypt($resep, $aesKey);

        if ($encNik && $encNama && $encKeluhan && $encDiagnosa && $encResep) {
            $sql = "INSERT INTO tb_rekam_medis (no_rm, nik_pasien, nama_pasien, keluhan, diagnosa, resep_obat)
                    VALUES (
                        '$no_rm',
                        '" . mysqli_real_escape_string($conn, $encNik) . "',
                        '" . mysqli_real_escape_string($conn, $encNama) . "',
                        '" . mysqli_real_escape_string($conn, $encKeluhan) . "',
                        '" . mysqli_real_escape_string($conn, $encDiagnosa) . "',
                        '" . mysqli_real_escape_string($conn, $encResep) . "'
                    )";
            if (mysqli_query($conn, $sql)) {
                $pesanNotif = "Data rekam medis baru berhasil dienkripsi menggunakan AES-256-CBC dan disimpan ke database!";
                $tipeNotif  = "sukses";
            }
        }
    }
}

// UPDATE: Simpan Perubahan Data Rekam Medis (Enkripsi Ulang dengan AES-256-CBC)
if (isset($_POST['update_rekam_medis'])) {
    if (!$modeDekripsi) {
        $pesanNotif = "Akses ditolak! Anda harus login menggunakan Kunci Simetris dan Asimetris untuk mengubah data.";
        $tipeNotif  = "error";
    } else {
        $idUpdate = (int)$_POST['id_rm'];
        $no_rm    = mysqli_real_escape_string($conn, trim($_POST['no_rm']));
        $nik      = trim($_POST['nik_pasien']);
        $nama     = trim($_POST['nama_pasien']);
        $keluhan  = trim($_POST['keluhan']);
        $diagnosa = trim($_POST['diagnosa']);
        $resep    = trim($_POST['resep_obat']);

        $aesKey      = $_SESSION['aes_key'];
        $encNik      = aes_encrypt($nik, $aesKey);
        $encNama     = aes_encrypt($nama, $aesKey);
        $encKeluhan  = aes_encrypt($keluhan, $aesKey);
        $encDiagnosa = aes_encrypt($diagnosa, $aesKey);
        $encResep    = aes_encrypt($resep, $aesKey);

        if ($encNik && $encNama && $encKeluhan && $encDiagnosa && $encResep) {
            $sql = "UPDATE tb_rekam_medis SET 
                        no_rm       = '$no_rm',
                        nik_pasien  = '" . mysqli_real_escape_string($conn, $encNik) . "',
                        nama_pasien = '" . mysqli_real_escape_string($conn, $encNama) . "',
                        keluhan     = '" . mysqli_real_escape_string($conn, $encKeluhan) . "',
                        diagnosa    = '" . mysqli_real_escape_string($conn, $encDiagnosa) . "',
                        resep_obat  = '" . mysqli_real_escape_string($conn, $encResep) . "'
                    WHERE id = $idUpdate";
            if (mysqli_query($conn, $sql)) {
                $pesanNotif = "Data rekam medis berhasil diperbarui dan dienkripsi ulang dengan AES-256-CBC!";
                $tipeNotif  = "sukses";
            }
        }
    }
}

// DELETE: Hapus Data Rekam Medis
if (isset($_GET['hapus'])) {
    if ($modeDekripsi) {
        $idHapus = (int)$_GET['hapus'];
        mysqli_query($conn, "DELETE FROM tb_rekam_medis WHERE id = $idHapus");
    }
    header("Location: admin.php");
    exit;
}

// Ambil data untuk form EDIT jika parameter ?edit=ID aktif dan modeDekripsi valid
$dataEdit = null;
if ($modeDekripsi && isset($_GET['edit'])) {
    $idEdit = (int)$_GET['edit'];
    $resEdit = mysqli_query($conn, "SELECT * FROM tb_rekam_medis WHERE id = $idEdit");
    if ($rowEdit = mysqli_fetch_assoc($resEdit)) {
        $aesKey = $_SESSION['aes_key'];
        $dataEdit = [
            'id'       => $rowEdit['id'],
            'no_rm'    => $rowEdit['no_rm'],
            'nik'      => aes_decrypt($rowEdit['nik_pasien'], $aesKey),
            'nama'     => aes_decrypt($rowEdit['nama_pasien'], $aesKey),
            'keluhan'  => aes_decrypt($rowEdit['keluhan'], $aesKey),
            'diagnosa' => aes_decrypt($rowEdit['diagnosa'], $aesKey),
            'resep'    => aes_decrypt($rowEdit['resep_obat'], $aesKey),
        ];
    }
}

// Ambil seluruh data rekam medis dari MySQL
$queryData  = mysqli_query($conn, "SELECT * FROM tb_rekam_medis ORDER BY id DESC");
$jumlahData = mysqli_num_rows($queryData);
$autoNoRM   = "RM-2026-" . str_pad($jumlahData + 1, 3, "0", STR_PAD_LEFT);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin Hybrid — E-Rekam Medis</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background: #f1f5f9; color: #1e293b; }
        .navbar { background: #0f172a; color: #fff; padding: 16px 32px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; }
        .navbar-left { display: flex; align-items: center; gap: 14px; }
        .btn-home { color: #cbd5e1; text-decoration: none; font-size: 13px; border: 1px solid #334155; padding: 6px 12px; border-radius: 6px; }
        .btn-home:hover { background: #1e293b; color: #fff; }
        .navbar h1 { font-size: 18px; }
        .navbar-right { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; }
        .badge-unlocked { background: #16a34a; color: #fff; padding: 6px 14px; border-radius: 20px; font-size: 12.5px; font-weight: 700; }
        .badge-locked { background: #d97706; color: #fff; padding: 6px 14px; border-radius: 20px; font-size: 12.5px; font-weight: 700; }
        .btn-logout { background: #ef4444; color: #fff; text-decoration: none; padding: 7px 14px; border-radius: 6px; font-size: 13px; font-weight: 600; }
        .btn-logout:hover { background: #dc2626; }
        .container { max-width: 1280px; margin: 26px auto; padding: 0 20px; }
        .banner-info { padding: 14px 18px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; line-height: 1.5; }
        .banner-green { background: #dcfce7; border: 1px solid #86efac; color: #166534; }
        .banner-amber { background: #fef3c7; border: 1px solid #fcd34d; color: #92400e; }
        .notif-sukses { background: #dbeafe; border: 1px solid #93c5fd; color: #1e40af; padding: 13px 18px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; }
        .notif-error { background: #fee2e2; border: 1px solid #fca5a5; color: #991b1b; padding: 13px 18px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; }
        .card { background: #fff; border-radius: 10px; padding: 24px; box-shadow: 0 2px 12px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; margin-bottom: 24px; }
        .card h2 { font-size: 17.5px; margin-bottom: 8px; color: #0f172a; }
        .sub-text { font-size: 13.5px; color: #64748b; margin-bottom: 18px; }
        label { display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px; color: #334155; }
        textarea, input[type="text"] { width: 100%; padding: 10px 12px; border: 1.5px solid #cbd5e1; border-radius: 7px; font-size: 13.5px; }
        textarea:focus, input:focus { outline: none; border-color: #0f766e; }
        .btn-teal { background: #0f766e; color: #fff; padding: 11px 20px; border: none; border-radius: 7px; font-weight: 600; font-size: 14px; cursor: pointer; margin-top: 16px; }
        .btn-teal:hover { background: #115e59; }
        .btn-cancel { display: inline-block; background: #e2e8f0; color: #334155; padding: 11px 18px; border-radius: 7px; font-weight: 600; font-size: 14px; text-decoration: none; margin-top: 16px; margin-left: 8px; }
        .btn-cancel:hover { background: #cbd5e1; }
        .form-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; }
        @media (max-width: 900px) { .form-grid { grid-template-columns: 1fr; } }
        .table-wrap { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; font-size: 13.5px; }
        th { background: #0f766e; color: #fff; text-align: left; padding: 12px; white-space: nowrap; }
        td { padding: 12px; border-bottom: 1px solid #e2e8f0; vertical-align: top; }
        tr:hover { background: #f8fafc; }
        .cipher-box { font-family: 'Courier New', monospace; font-size: 11px; color: #b45309; background: #fffbeb; padding: 6px 8px; border-radius: 5px; border: 1px dashed #f59e0b; word-break: break-all; max-width: 220px; display: block; }
        .aksi-group { display: flex; gap: 10px; white-space: nowrap; }
        .btn-edit { color: #0284c7; text-decoration: none; font-weight: 600; font-size: 12.5px; }
        .btn-edit:hover { text-decoration: underline; }
        .btn-del { color: #dc2626; text-decoration: none; font-weight: 600; font-size: 12.5px; }
        .btn-del:hover { text-decoration: underline; }
    </style>
</head>
<body>

<div class="navbar">
    <div class="navbar-left">
        <a href="index.php" class="btn-home">&larr; Landing Page</a>
        <h1>Dashboard Admin — E-Rekam Medis Hybrid</h1>
    </div>
    <div class="navbar-right">
        <?php if ($modeDekripsi): ?>
            <span class="badge-unlocked">STATUS: TERDEKRIPSI (HYBRID KEY VALID &amp; CRUD AKTIF)</span>
        <?php else: ?>
            <span class="badge-locked">STATUS: TERENKRIPSI (READ-ONLY)</span>
        <?php endif; ?>
        <a href="admin.php?aksi=logout" class="btn-logout">Logout</a>
    </div>
</div>

<div class="container">

    <?php if ($modeDekripsi): ?>

    <?php else: ?>

    <?php endif; ?>

    <?php if ($pesanNotif !== ''): ?>
        <div class="<?= $tipeNotif === 'sukses' ? 'notif-sukses' : 'notif-error' ?>">
            <?= htmlspecialchars($pesanNotif) ?>
        </div>
    <?php endif; ?>

    <!-- FORM CRUD (CREATE & UPDATE): HANYA MUNCUL JIKA LOGIN DENGAN KUNCI HYBRID -->
    <?php if ($modeDekripsi): ?>
    <div class="card">
        <?php if ($dataEdit): ?>
            <h2>Edit Data Rekam Medis Pasien (Enkripsi Ulang Simetris AES-256-CBC)</h2>
            <p class="sub-text">Perubahan data akan langsung dienkripsi ulang menggunakan <strong>AES-256-CBC</strong> dengan IV acak baru sebelum diperbarui ke database.</p>
            <form method="POST" action="admin.php">
                <input type="hidden" name="id_rm" value="<?= $dataEdit['id'] ?>">
                <div class="form-grid">
                    <div>
                        <label>No. Rekam Medis</label>
                        <input type="text" name="no_rm" value="<?= htmlspecialchars($dataEdit['no_rm']) ?>" required>
                    </div>
                    <div>
                        <label>NIK Pasien</label>
                        <input type="text" name="nik_pasien" value="<?= htmlspecialchars($dataEdit['nik']) ?>" required>
                    </div>
                    <div>
                        <label>Nama Lengkap Pasien</label>
                        <input type="text" name="nama_pasien" value="<?= htmlspecialchars($dataEdit['nama']) ?>" required>
                    </div>
                    <div>
                        <label>Keluhan Utama</label>
                        <textarea name="keluhan" rows="2" required><?= htmlspecialchars($dataEdit['keluhan']) ?></textarea>
                    </div>
                    <div>
                        <label>Diagnosa Dokter</label>
                        <textarea name="diagnosa" rows="2" required><?= htmlspecialchars($dataEdit['diagnosa']) ?></textarea>
                    </div>
                    <div>
                        <label>Resep Obat &amp; Tindakan</label>
                        <textarea name="resep_obat" rows="2" required><?= htmlspecialchars($dataEdit['resep']) ?></textarea>
                    </div>
                </div>
                <button type="submit" name="update_rekam_medis" class="btn-teal">Simpan Perubahan (Enkripsi Ulang)</button>
                <a href="admin.php" class="btn-cancel">Batal Edit</a>
            </form>
        <?php else: ?>
            <h2>Tambah Data Rekam Medis Pasien (Enkripsi Otomatis AES-256-CBC)</h2>
            <p class="sub-text">Setiap data baru yang disimpan akan dienkripsi menggunakan <strong>Kunci Simetris AES-256</strong> sebelum masuk ke database MySQL.</p>
            <form method="POST" action="admin.php">
                <div class="form-grid">
                    <div>
                        <label>No. Rekam Medis</label>
                        <input type="text" name="no_rm" value="<?= $autoNoRM ?>" required>
                    </div>
                    <div>
                        <label>NIK Pasien</label>
                        <input type="text" name="nik_pasien" placeholder="Contoh: 3202110908010002" required>
                    </div>
                    <div>
                        <label>Nama Lengkap Pasien</label>
                        <input type="text" name="nama_pasien" placeholder="Contoh: Ahmad Fauzi" required>
                    </div>
                    <div>
                        <label>Keluhan Utama</label>
                        <textarea name="keluhan" rows="2" placeholder="Masukkan keluhan pasien..." required></textarea>
                    </div>
                    <div>
                        <label>Diagnosa Dokter</label>
                        <textarea name="diagnosa" rows="2" placeholder="Masukkan hasil diagnosa..." required></textarea>
                    </div>
                    <div>
                        <label>Resep Obat &amp; Tindakan</label>
                        <textarea name="resep_obat" rows="2" placeholder="Masukkan resep obat..." required></textarea>
                    </div>
                </div>
                <button type="submit" name="simpan_rekam_medis" class="btn-teal">Enkripsi &amp; Simpan ke Database</button>
            </form>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- TABEL DATA REKAM MEDIS -->
    <div class="card">
        <h2>Daftar Rekam Medis Pasien — <?= $modeDekripsi ? 'Tampilan Asli (Hasil Dekripsi Hybrid)' : 'Tampilan Terenkripsi (Ciphertext)' ?></h2>
        <p class="sub-text">Sumber data: tabel <code>tb_rekam_medis</code> pada database <code>db_rekam_medis</code>.</p>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>No. RM</th>
                        <th>Waktu Periksa</th>
                        <th>NIK Pasien</th>
                        <th>Nama Pasien</th>
                        <th>Keluhan</th>
                        <th>Diagnosa</th>
                        <th>Resep Obat</th>
                        <?php if ($modeDekripsi): ?>
                            <th>Aksi</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $no = 1;
                    while ($row = mysqli_fetch_assoc($queryData)):
                        if ($modeDekripsi) {
                            $aesKey   = $_SESSION['aes_key'];
                            $nik      = aes_decrypt($row['nik_pasien'], $aesKey);
                            $nama     = aes_decrypt($row['nama_pasien'], $aesKey);
                            $keluhan  = aes_decrypt($row['keluhan'], $aesKey);
                            $diagnosa = aes_decrypt($row['diagnosa'], $aesKey);
                            $resep    = aes_decrypt($row['resep_obat'], $aesKey);
                        } else {
                            $nik      = $row['nik_pasien'];
                            $nama     = $row['nama_pasien'];
                            $keluhan  = $row['keluhan'];
                            $diagnosa = $row['diagnosa'];
                            $resep    = $row['resep_obat'];
                        }
                    ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><strong><?= htmlspecialchars($row['no_rm']) ?></strong></td>
                        <td><?= htmlspecialchars($row['tanggal_periksa']) ?></td>
                        <td>
                            <?php if ($modeDekripsi): ?>
                                <?= htmlspecialchars($nik) ?>
                            <?php else: ?>
                                <span class="cipher-box"><?= htmlspecialchars(substr($nik, 0, 75)) ?>...</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($modeDekripsi): ?>
                                <strong><?= htmlspecialchars($nama) ?></strong>
                            <?php else: ?>
                                <span class="cipher-box"><?= htmlspecialchars(substr($nama, 0, 75)) ?>...</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($modeDekripsi): ?>
                                <?= htmlspecialchars($keluhan) ?>
                            <?php else: ?>
                                <span class="cipher-box"><?= htmlspecialchars(substr($keluhan, 0, 75)) ?>...</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($modeDekripsi): ?>
                                <?= htmlspecialchars($diagnosa) ?>
                            <?php else: ?>
                                <span class="cipher-box"><?= htmlspecialchars(substr($diagnosa, 0, 75)) ?>...</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($modeDekripsi): ?>
                                <?= htmlspecialchars($resep) ?>
                            <?php else: ?>
                                <span class="cipher-box"><?= htmlspecialchars(substr($resep, 0, 75)) ?>...</span>
                            <?php endif; ?>
                        </td>
                        <?php if ($modeDekripsi): ?>
                        <td>
                            <div class="aksi-group">
                                <a href="admin.php?edit=<?= $row['id'] ?>" class="btn-edit">Edit</a>
                                <a href="admin.php?hapus=<?= $row['id'] ?>" class="btn-del" onclick="return confirm('Hapus data rekam medis ini?')">Hapus</a>
                            </div>
                        </td>
                        <?php endif; ?>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

</body>
</html>