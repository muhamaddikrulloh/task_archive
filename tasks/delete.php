<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

$uid = $_SESSION['user_id'];

if (!isset($_POST['id'])) {
    header('Location: index.php');
    exit;
}

$id = (int) $_POST['id'];

// Ambil data tugas — pastikan milik user yang login
$stmt = $conn->prepare("SELECT ttd_digital FROM tugas WHERE id = ? AND user_id = ?");
$stmt->bind_param('ii', $id, $uid);
$stmt->execute();
$tugas = $stmt->get_result()->fetch_assoc();

if (!$tugas) {
    header('Location: index.php');
    exit;
}

// Ambil semua lampiran untuk dihapus dari disk
$stmt2 = $conn->prepare("SELECT path_file FROM lampiran WHERE tugas_id = ?");
$stmt2->bind_param('i', $id);
$stmt2->execute();
$lampirans = $stmt2->get_result()->fetch_all(MYSQLI_ASSOC);

// Hapus file lampiran dari disk
foreach ($lampirans as $l) {
    @unlink('../' . $l['path_file']);
}

// Hapus file TTD dari disk
if (!empty($tugas['ttd_digital'])) {
    @unlink('../assets/uploads/signatures/' . $tugas['ttd_digital']);
}

// Hapus dari DB — lampiran terhapus otomatis karena ON DELETE CASCADE
$del = $conn->prepare("DELETE FROM tugas WHERE id = ? AND user_id = ?");
$del->bind_param('ii', $id, $uid);
$del->execute();

$_SESSION['success'] = 'Tugas berhasil dihapus.';
header('Location: index.php');
exit;