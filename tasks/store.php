<?php
session_start();
require_once '../includes/db.php';

$page = basename($_SERVER['PHP_SELF']);

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: create.php');
    exit;
}

$uid                 = $_SESSION['user_id'];
$judul_tugas         = trim($_POST['judul_tugas'] ?? '');
$mata_kuliah         = trim($_POST['mata_kuliah'] ?? '');
$dosen               = trim($_POST['dosen'] ?? '');
$semester            = (int) ($_POST['semester'] ?? 0);
$tanggal_pengumpulan = $_POST['tanggal_pengumpulan'] ?? '';
$deskripsi           = trim($_POST['deskripsi'] ?? '');
$ttd_data            = $_POST['ttd_data'] ?? '';

// Validasi field wajib
if (!$judul_tugas || !$mata_kuliah || !$dosen || !$semester || !$tanggal_pengumpulan) {
    $_SESSION['error'] = 'Semua field wajib diisi.';
    header('Location: create.php');
    exit;
}

// Simpan TTD
$ttd_filename = null;
if (!empty($ttd_data) && str_starts_with($ttd_data, 'data:image/png;base64,')) {
    $ttd_binary   = base64_decode(explode(',', $ttd_data)[1]);
    $ttd_filename = uniqid('ttd_') . '.png';
    file_put_contents('../assets/uploads/signatures/' . $ttd_filename, $ttd_binary);
}

// Insert tugas
$stmt = $conn->prepare("INSERT INTO tugas (user_id, judul_tugas, mata_kuliah, dosen, semester, tanggal_pengumpulan, deskripsi, ttd_digital) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param('isssisss', $uid, $judul_tugas, $mata_kuliah, $dosen, $semester, $tanggal_pengumpulan, $deskripsi, $ttd_filename);

if (!$stmt->execute()) {
    $_SESSION['error'] = 'Gagal menyimpan tugas. Coba lagi.';
    header('Location: create.php');
    exit;
}

$tugas_id = $conn->insert_id;

// Upload lampiran
// Mapping: nama input => [folder tujuan, tipe_file, ekstensi yang diizinkan]
$upload_groups = [
    'lampiran_pdf' => ['pdf',         'pdf',   ['pdf']],
    'lampiran_zip' => ['zip',         'zip',   ['zip', 'rar']],
    'lampiran_img' => ['screenshots', 'image', ['jpg', 'jpeg', 'png', 'gif', 'webp']],
];

foreach ($upload_groups as $input_name => [$folder, $tipe, $allowed_ext]) {
    if (empty($_FILES[$input_name]['name'][0])) continue;

    $files = $_FILES[$input_name];
    $count = count($files['name']);

    for ($i = 0; $i < $count; $i++) {
        if ($files['error'][$i] !== UPLOAD_ERR_OK) continue;

        $original_name = basename($files['name'][$i]);
        $ext           = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));

        // Validasi ekstensi
        if (!in_array($ext, $allowed_ext)) continue;

        $new_filename = uniqid() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $original_name);
        $dest_path    = '../assets/uploads/' . $folder . '/' . $new_filename;

        if (!move_uploaded_file($files['tmp_name'][$i], $dest_path)) continue;

        $ukuran = $files['size'][$i];
        $path   = 'assets/uploads/' . $folder . '/' . $new_filename;

        $stmt2 = $conn->prepare("INSERT INTO lampiran (tugas_id, nama_file, path_file, tipe_file, ukuran_file) VALUES (?, ?, ?, ?, ?)");
        $stmt2->bind_param('isssi', $tugas_id, $original_name, $path, $tipe, $ukuran);
        $stmt2->execute();
    }
}

$_SESSION['success'] = 'Tugas berhasil disimpan.';
header('Location: index.php');
exit;