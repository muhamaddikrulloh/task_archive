<?php
session_start();
require_once 'includes/db.php';

$page = basename($_SERVER['PHP_SELF']);

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$uid = $_SESSION['user_id'];
$nama = $_SESSION['nama'];

$total_tugas    = $conn->query("SELECT COUNT(*) FROM tugas WHERE user_id = $uid")->fetch_row()[0];
$total_matkul   = $conn->query("SELECT COUNT(DISTINCT mata_kuliah) FROM tugas WHERE user_id = $uid")->fetch_row()[0];
$total_lampiran = $conn->query("SELECT COUNT(*) FROM lampiran l JOIN tugas t ON l.tugas_id = t.id WHERE t.user_id = $uid")->fetch_row()[0];
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard – Task Archive</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">

  <!-- Navbar -->
  <?php include 'includes/navbar.php'; ?>

  <main class="max-w-4xl mx-auto px-4 py-8">
    <h2 class="text-xl font-semibold text-gray-800 mb-1">
      Selamat datang, <?= htmlspecialchars($nama) ?>
    </h2>
    <p class="text-sm text-gray-500 mb-8">Kelola arsip tugas kuliah kamu di sini.</p>

    <!-- Statistik -->
    <div class="grid grid-cols-3 gap-4 mb-8">
      <div class="bg-white rounded-xl shadow-sm p-5 text-center">
        <p class="text-3xl font-bold text-blue-600"><?= $total_tugas ?></p>
        <p class="text-sm text-gray-500 mt-1">Total Tugas</p>
      </div>
      <div class="bg-white rounded-xl shadow-sm p-5 text-center">
        <p class="text-3xl font-bold text-blue-600"><?= $total_matkul ?></p>
        <p class="text-sm text-gray-500 mt-1">Mata Kuliah</p>
      </div>
      <div class="bg-white rounded-xl shadow-sm p-5 text-center">
        <p class="text-3xl font-bold text-blue-600"><?= $total_lampiran ?></p>
        <p class="text-sm text-gray-500 mt-1">Total Lampiran</p>
      </div>
    </div>

    <!-- Quick Action -->
    <div class="bg-white rounded-xl shadow-sm p-6 mb-8">
      <h3 class="text-sm font-semibold text-gray-700 mb-4">Quick Action</h3>
      <div class="flex gap-3">
        <a href="tasks/create.php"
          class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-5 py-2.5 rounded-lg transition">
          + Tambah Tugas
        </a>
        <a href="tasks/list.php"
          class="border border-gray-300 hover:border-blue-500 hover:text-blue-600 text-gray-600 text-sm font-medium px-5 py-2.5 rounded-lg transition">
          Lihat Semua Tugas
        </a>
      </div>
    </div>

    <!-- Video -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-sm font-semibold text-gray-700 mb-4">Video Tutorial Penggunaan Website</h3>
        <div class="rounded-lg overflow-hidden aspect-video">
            <video controls class="w-full h-full rounded-lg bg-black">
            <source src="assets/uploads/videos/tutorial.mp4" type="video/mp4">
            Browser kamu tidak mendukung pemutar video.
            </video>
        </div>
    </div>
  </main>

  <footer class="text-center text-xs text-gray-400 py-6">
  © 2026 Task Archive. All rights reserved.
  </footer>

</body>
</html>
