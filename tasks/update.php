<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

$uid = $_SESSION['user_id'];
$id  = (int) ($_POST['id'] ?? 0);

if ($id <= 0) {
    header('Location: list.php');
    exit;
}

// Validasi data input
$judul_tugas         = trim($_POST['judul_tugas']);
$mata_kuliah         = trim($_POST['mata_kuliah']);
$dosen               = trim($_POST['dosen']);
$semester            = (int) $_POST['semester'];
$tanggal_pengumpulan = $_POST['tanggal_pengumpulan'];
$deskripsi           = trim($_POST['deskripsi'] ?? '');

if (
    empty($judul_tugas) || empty($mata_kuliah) || empty($dosen) || empty($tanggal_pengumpulan)
) {
    $_SESSION['error'] = 'Semua field wajib diisi.';
    header("Location: edit.php?id=$id");
    exit;
}


// Cek apakah tugas dengan ID tersebut milik user yang sedang login
$stmt = $conn->prepare(
    "SELECT * FROM tugas WHERE id = ? AND user_id = ?"
);

$stmt->bind_param('ii', $id, $uid);
$stmt->execute();

$tugas = $stmt->get_result()->fetch_assoc();

if (!$tugas) {
    header('Location: list.php');
    exit;
}

// Update data tugas
$stmt = $conn->prepare("
    UPDATE tugas
    SET
        judul_tugas = ?,
        mata_kuliah = ?,
        dosen = ?,
        semester = ?,
        tanggal_pengumpulan = ?,
        deskripsi = ?
    WHERE id = ? AND user_id = ?
");

$stmt->bind_param(
    'sssissii',
    $judul_tugas,
    $mata_kuliah,
    $dosen,
    $semester,
    $tanggal_pengumpulan,
    $deskripsi,
    $id,
    $uid
);

$stmt->execute();

// Hapus Lampiran
if (!empty($_POST['hapus_lampiran'])) {
    foreach ($_POST['hapus_lampiran'] as $lampiran_id) {

        $lampiran_id = (int) $lampiran_id;
        $stmt = $conn->prepare("SELECT * FROM lampiran WHERE id = ? AND tugas_id = ?");

        $stmt->bind_param('ii', $lampiran_id, $id);
        $stmt->execute();

        $lampiran = $stmt->get_result()->fetch_assoc();

        if ($lampiran) {
            $filePath = '../assets/uploads/' . $lampiran['nama_file'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }

            $stmtDelete = $conn->prepare("DELETE FROM lampiran WHERE id = ?");
            $stmtDelete->bind_param('i', $lampiran_id);
            $stmtDelete->execute();
        }
    }
}

// Upload lampiran baru
$uploadDir = '../assets/uploads/';

function uploadFiles($files, $tugas_id, $conn, $uploadDir)
{
    if (empty($files['name'][0])) {
        return;
    }

    foreach ($files['name'] as $key => $name) {

        if ($files['error'][$key] !== 0) {
            continue;
        }

        $namaBaru = time() . '_' . uniqid() . '_' . basename($name);

        move_uploaded_file(
            $files['tmp_name'][$key],
            $uploadDir . $namaBaru
        );

        $stmt = $conn->prepare(
            "INSERT INTO lampiran (tugas_id, nama_file)
            VALUES (?, ?)"
        );

        $stmt->bind_param(
            'is',
            $tugas_id,
            $namaBaru
        );

        $stmt->execute();
    }
}

uploadFiles($_FILES['lampiran_pdf'], $id, $conn, $uploadDir);
uploadFiles($_FILES['lampiran_zip'], $id, $conn, $uploadDir);
uploadFiles($_FILES['lampiran_img'], $id, $conn, $uploadDir);

// Hapus TTD jika diminta
if (!empty($_POST['hapus_ttd'])) {
    if (!empty($tugas['ttd_digital'])) {
        $oldTTD = '../assets/uploads/signatures/' . $tugas['ttd_digital'];

        if (file_exists($oldTTD)) {
            unlink($oldTTD);
        }

        $stmt = $conn->prepare("UPDATE tugas SET ttd_digital = NULL WHERE id = ?"
        );

        $stmt->bind_param('i', $id);
        $stmt->execute();
    }
}

// Simpan TTD baru jika ada
if (
    !empty($_POST['ttd_data']) &&
    str_contains($_POST['ttd_data'], 'base64')
) {

    $folderTTD = '../assets/uploads/signatures/';

    if (!is_dir($folderTTD)) {
        mkdir($folderTTD, 0777, true);
    }

    $data = $_POST['ttd_data'];

    $data = str_replace(
        'data:image/png;base64,',
        '',
        $data
    );

    $data = base64_decode($data);
    $namaTTD = 'ttd_' . time() . '.png';

    file_put_contents(
        $folderTTD . $namaTTD,
        $data
    );

    if (!empty($tugas['ttd_digital'])) {
        $oldTTD = '../assets/uploads/signatures/' . $tugas['ttd_digital'];

        if (file_exists($oldTTD)) {
            unlink($oldTTD);
        }
    }

    $stmt = $conn->prepare("UPDATE tugas SET ttd_digital = ? WHERE id = ?"
    );

    $stmt->bind_param(
        'si',
        $namaTTD,
        $id
    );

    $stmt->execute();
}

$_SESSION['success'] = 'Data tugas berhasil diperbarui.';
header('Location: list.php');
exit;