<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Konfigurasi Database MySQL (Default Laragon)
$host = "localhost";
$user = "root";
$pass = "";
$db   = "db_rekam_medis";

$conn = mysqli_connect($host, $user, $pass, $db);
if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

define('AES_CIPHER', 'aes-256-cbc');

// 2. Pencari otomatis file openssl.cnf pada Windows / Laragon
function getOpenSSLConfig() {
    $kandidat = [
        getenv('OPENSSL_CONF'),
        dirname(PHP_BINARY) . '/extras/ssl/openssl.cnf',
        dirname(PHP_BINARY) . '/openssl.cnf',
    ];

    $laragonPaths = glob('C:/laragon/bin/php/*/extras/ssl/openssl.cnf');
    if ($laragonPaths) {
        $kandidat = array_merge($kandidat, $laragonPaths);
    }

    foreach ($kandidat as $path) {
        if ($path && file_exists($path)) {
            return $path;
        }
    }
    return null;
}

// =========================================================================
// 3. FUNGSI KRIPTOGRAFI SIMETRIS (AES-256-CBC) UNTUK DATA REKAM MEDIS
// =========================================================================
function aes_encrypt($plaintext, $aesKey) {
    if ($plaintext === '' || $plaintext === null) {
        return '';
    }
    $keyHash   = hash('sha256', trim($aesKey), true);
    $ivLength  = openssl_cipher_iv_length(AES_CIPHER);
    $iv        = openssl_random_pseudo_bytes($ivLength);
    $encrypted = openssl_encrypt((string)$plaintext, AES_CIPHER, $keyHash, OPENSSL_RAW_DATA, $iv);

    if ($encrypted === false) {
        return false;
    }
    // Gabungkan IV (16 byte) + Ciphertext lalu encode Base64
    return base64_encode($iv . $encrypted);
}

function aes_decrypt($ciphertextBase64, $aesKey) {
    if ($ciphertextBase64 === '' || $ciphertextBase64 === null) {
        return '';
    }
    $raw = base64_decode($ciphertextBase64, true);
    $ivLength = openssl_cipher_iv_length(AES_CIPHER);

    if ($raw === false || strlen($raw) <= $ivLength) {
        return false;
    }

    $keyHash       = hash('sha256', trim($aesKey), true);
    $iv            = substr($raw, 0, $ivLength);
    $encryptedData = substr($raw, $ivLength);

    return openssl_decrypt($encryptedData, AES_CIPHER, $keyHash, OPENSSL_RAW_DATA, $iv);
}

// =========================================================================
// 4. FUNGSI KRIPTOGRAFI ASIMETRIS (RSA-2048) UNTUK MENGAMANKAN KUNCI AES
// =========================================================================
function rsa_encrypt_key($aesKeyPlain, $publicKey) {
    $encrypted = '';
    $sukses = openssl_public_encrypt(trim($aesKeyPlain), $encrypted, $publicKey, OPENSSL_PKCS1_OAEP_PADDING);
    return $sukses ? base64_encode($encrypted) : false;
}

function rsa_decrypt_key($encryptedAesKeyBase64, $privateKey) {
    $privateKeyClean = str_replace(["\r\n", "\r"], "\n", trim($privateKey));
    $resPriv = @openssl_pkey_get_private($privateKeyClean);
    if (!$resPriv) {
        return false;
    }

    $rawCipher = base64_decode($encryptedAesKeyBase64, true);
    if ($rawCipher === false) {
        return false;
    }

    $decryptedAesKey = '';
    $sukses = openssl_private_decrypt($rawCipher, $decryptedAesKey, $resPriv, OPENSSL_PKCS1_OAEP_PADDING);
    return $sukses ? $decryptedAesKey : false;
}

// 5. Verifikasi Gabungan (Hybrid): RSA Private Key membuka Kunci AES, lalu dicocokkan
function verifikasi_kunci_hybrid($inputAesKey, $inputPrivateKey, $encryptedAesKeyBase64) {
    $inputAesClean = trim($inputAesKey);
    if ($inputAesClean === '' || trim($inputPrivateKey) === '') {
        return false;
    }

    // Tahap Asimetris: Dekripsi kunci AES dari database menggunakan RSA Private Key
    $serverAesKey = rsa_decrypt_key($encryptedAesKeyBase64, $inputPrivateKey);
    if ($serverAesKey === false) {
        return false;
    }

    // Tahap Simetris: Cocokkan Kunci Simetris yang diinput dengan hasil dekripsi RSA
    if (hash_equals($serverAesKey, $inputAesClean)) {
        return $serverAesKey;
    }

    return false;
}

// =========================================================================
// 6. AUTO-GENERATE KUNCI HYBRID & DATA CONTOH PASIEN SAAT PERTAMA JALAN
// =========================================================================
$cekKunci = mysqli_query($conn, "SELECT * FROM tb_kunci_hybrid WHERE id = 1");
if (mysqli_num_rows($cekKunci) === 0) {
    $configArgs = [
        "private_key_bits" => 2048,
        "private_key_type" => OPENSSL_KEYTYPE_RSA,
    ];
    $cnfPath = getOpenSSLConfig();
    if ($cnfPath) {
        $configArgs["config"] = $cnfPath;
    }

    $resKey = openssl_pkey_new($configArgs);
    if (!$resKey) {
        die("Gagal membangkitkan kunci RSA OpenSSL: " . openssl_error_string());
    }

    openssl_pkey_export($resKey, $generatedPrivateKey, null, $configArgs);
    $keyDetails = openssl_pkey_get_details($resKey);
    $generatedPublicKey = $keyDetails["key"];

    // Bangkitkan Kunci Simetris AES-256
    $generatedAesKey = "KLINIK-AES256-" . strtoupper(bin2hex(random_bytes(8)));

    // Bungkus Kunci Simetris AES menggunakan RSA Public Key (Digital Envelope)
    $encryptedAesKey = rsa_encrypt_key($generatedAesKey, $generatedPublicKey);

    $pubEsc    = mysqli_real_escape_string($conn, $generatedPublicKey);
    $encAesEsc = mysqli_real_escape_string($conn, $encryptedAesKey);

    mysqli_query($conn, "INSERT INTO tb_kunci_hybrid (id, public_key, encrypted_aes_key) VALUES (1, '$pubEsc', '$encAesEsc')");

    // Simpan ke-3 file kunci di folder proyek untuk pengujian & lampiran laporan
    @file_put_contents(__DIR__ . '/public_key.pem', $generatedPublicKey);
    @file_put_contents(__DIR__ . '/private_key.pem', $generatedPrivateKey);
    @file_put_contents(__DIR__ . '/kunci_simetris_aes.txt', $generatedAesKey);

    // Isi 2 data contoh pasien yang dienkripsi menggunakan Kunci Simetris AES-256-CBC
    $contohPasien = [
        [
            'no_rm'    => 'RM-2026-001',
            'nik'      => '3202114508990001',
            'nama'     => 'Siti Rahmawati',
            'keluhan'  => 'Demam tinggi 3 hari, nyeri sendi, dan pusing berat',
            'diagnosa' => 'Suspek Demam Berdarah Dengue (DBD) Grade I',
            'resep'    => 'Paracetamol 500mg (3x1), Cairan Elektrolit, Observasi Trombosit'
        ],
        [
            'no_rm'    => 'RM-2026-002',
            'nik'      => '3202111204950004',
            'nama'     => 'Budi Santoso',
            'keluhan'  => 'Nyeri ulu hati disertai mual setelah makan pedas',
            'diagnosa' => 'Gastritis Akut / Dispepsia Fungsional',
            'resep'    => 'Omeprazole 20mg (2x1 sebelum makan), Antasida Doen (3x1)'
        ]
    ];

    foreach ($contohPasien as $p) {
        $encNik      = mysqli_real_escape_string($conn, aes_encrypt($p['nik'], $generatedAesKey));
        $encNama     = mysqli_real_escape_string($conn, aes_encrypt($p['nama'], $generatedAesKey));
        $encKeluhan  = mysqli_real_escape_string($conn, aes_encrypt($p['keluhan'], $generatedAesKey));
        $encDiagnosa = mysqli_real_escape_string($conn, aes_encrypt($p['diagnosa'], $generatedAesKey));
        $encResep    = mysqli_real_escape_string($conn, aes_encrypt($p['resep'], $generatedAesKey));

        mysqli_query($conn, "INSERT INTO tb_rekam_medis (no_rm, nik_pasien, nama_pasien, keluhan, diagnosa, resep_obat) 
                             VALUES ('{$p['no_rm']}', '$encNik', '$encNama', '$encKeluhan', '$encDiagnosa', '$encResep')");
    }
}

// Ambil Public Key & Kunci AES Terenkripsi dari database
$dataKunci         = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM tb_kunci_hybrid WHERE id = 1"));
$PUBLIC_KEY        = $dataKunci['public_key'];
$ENCRYPTED_AES_KEY = $dataKunci['encrypted_aes_key'];
?>