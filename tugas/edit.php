<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

$uid = $_SESSION['user_id'];
$id  = (int) ($_GET['id'] ?? 0);

if (!$id) {
    header('Location: main.php');
    exit;
}

// Ambil data tugas — pastikan milik user yang login
$stmt = $conn->prepare("SELECT * FROM tugas WHERE id = ? AND user_id = ?");
$stmt->bind_param('ii', $id, $uid);
$stmt->execute();
$tugas = $stmt->get_result()->fetch_assoc();

if (!$tugas) {
    header('Location: main.php');
    exit;
}

// Ambil lampiran yang sudah ada
$stmt2    = $conn->prepare("SELECT * FROM lampiran WHERE tugas_id = ?");
$stmt2->bind_param('i', $id);
$stmt2->execute();
$lampiran = $stmt2->get_result()->fetch_all(MYSQLI_ASSOC);

$error   = $_SESSION['error'] ?? '';
$success = $_SESSION['success'] ?? '';
unset($_SESSION['error'], $_SESSION['success']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Tugas – Task Archive</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">

  <!-- Navbar -->
  <nav class="bg-white shadow-sm px-6 py-4 flex items-center justify-between">
    <span class="text-blue-600 font-bold text-lg">Task Archive</span>
    <div class="flex items-center gap-6">
      <a href="../dashboard.php" class="text-sm text-gray-600 hover:text-blue-600">Dashboard</a>
      <a href="main.php" class="text-sm text-gray-600 hover:text-blue-600">Data Tugas</a>
      <a href="../auth/logout.php" class="text-sm text-red-500 hover:text-red-600">Logout</a>
    </div>
  </nav>

  <main class="max-w-2xl mx-auto px-4 py-8">

    <h2 class="text-xl font-semibold text-gray-800 mb-6">Edit Tugas</h2>

    <?php if ($error): ?>
      <div class="bg-red-50 border border-red-200 text-red-600 text-sm rounded-lg px-4 py-3 mb-5">
        <?= htmlspecialchars($error) ?>
      </div>
    <?php endif; ?>

    <form action="update.php" method="POST" enctype="multipart/form-data">
      <input type="hidden" name="id" value="<?= $tugas['id'] ?>">

      <div class="bg-white rounded-xl shadow-sm p-6 space-y-5">

        <!-- Judul Tugas -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Judul Tugas</label>
          <input type="text" name="judul_tugas" required
            value="<?= htmlspecialchars($tugas['judul_tugas']) ?>"
            class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <!-- Mata Kuliah & Dosen -->
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Mata Kuliah</label>
            <input type="text" name="mata_kuliah" required
              value="<?= htmlspecialchars($tugas['mata_kuliah']) ?>"
              class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Dosen</label>
            <input type="text" name="dosen" required
              value="<?= htmlspecialchars($tugas['dosen']) ?>"
              class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
          </div>
        </div>

        <!-- Semester & Tanggal -->
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Semester</label>
            <select name="semester" required
              class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
              <?php for ($s = 1; $s <= 8; $s++): ?>
                <option value="<?= $s ?>" <?= $tugas['semester'] == $s ? 'selected' : '' ?>>
                  Semester <?= $s ?>
                </option>
              <?php endfor; ?>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Pengumpulan</label>
            <input type="date" name="tanggal_pengumpulan" required
              value="<?= $tugas['tanggal_pengumpulan'] ?>"
              class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
          </div>
        </div>

        <!-- Deskripsi -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
          <textarea name="deskripsi" rows="3"
            class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"><?= htmlspecialchars($tugas['deskripsi'] ?? '') ?></textarea>
        </div>

      </div>

      <!-- Lampiran yang sudah ada -->
      <?php if (!empty($lampiran)): ?>
      <div class="bg-white rounded-xl shadow-sm p-6 mt-4">
        <h3 class="text-sm font-semibold text-gray-700 mb-3">Lampiran Tersimpan</h3>
        <ul class="space-y-2">
          <?php foreach ($lampiran as $l): ?>
            <li class="flex items-center justify-between text-sm bg-gray-50 rounded-lg px-4 py-2.5">
              <span class="text-gray-700 truncate max-w-xs"><?= htmlspecialchars($l['nama_file']) ?></span>
              <label class="flex items-center gap-1.5 text-red-500 cursor-pointer ml-4 shrink-0">
                <input type="checkbox" name="hapus_lampiran[]" value="<?= $l['id'] ?>" class="accent-red-500">
                <span class="text-xs">Hapus</span>
              </label>
            </li>
          <?php endforeach; ?>
        </ul>
        <p class="text-xs text-gray-400 mt-2">Centang untuk menghapus lampiran saat menyimpan.</p>
      </div>
      <?php endif; ?>

      <!-- Upload Lampiran Baru -->
      <div class="bg-white rounded-xl shadow-sm p-6 mt-4 space-y-4">
        <h3 class="text-sm font-semibold text-gray-700">Tambah Lampiran Baru <span class="text-gray-400 font-normal">(opsional)</span></h3>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Laporan PDF</label>
          <input type="file" name="lampiran_pdf[]" multiple accept=".pdf"
            class="w-full text-sm text-gray-500 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-600 hover:file:bg-blue-100">
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Source Code (ZIP)</label>
          <input type="file" name="lampiran_zip[]" multiple accept=".zip,.rar"
            class="w-full text-sm text-gray-500 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-600 hover:file:bg-blue-100">
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Screenshot</label>
          <input type="file" name="lampiran_img[]" multiple accept="image/*"
            class="w-full text-sm text-gray-500 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-600 hover:file:bg-blue-100">
        </div>
      </div>

      <!-- Canvas TTD -->
      <div class="bg-white rounded-xl shadow-sm p-6 mt-4">
        <h3 class="text-sm font-semibold text-gray-700 mb-3">Tanda Tangan Digital</h3>

        <!-- Preview TTD lama -->
        <?php if (!empty($tugas['ttd_digital'])): ?>
          <div class="mb-3">
            <p class="text-xs text-gray-500 mb-1">TTD tersimpan:</p>
            <img src="../assets/uploads/signatures/<?= htmlspecialchars($tugas['ttd_digital']) ?>"
              alt="TTD" class="border border-gray-200 rounded-lg max-h-24">
            <label class="flex items-center gap-2 mt-2 text-sm text-red-500 cursor-pointer">
              <input type="checkbox" name="hapus_ttd" value="1" class="accent-red-500">
              Hapus TTD lama
            </label>
          </div>
        <?php endif; ?>

        <p class="text-xs text-gray-500 mb-2">Gambar TTD baru (akan menggantikan yang lama):</p>
        <canvas id="ttdCanvas" width="600" height="160"
          class="w-full border border-gray-300 rounded-lg bg-white cursor-crosshair touch-none"></canvas>
        <button type="button" onclick="clearTTD()"
          class="mt-3 border border-gray-300 text-gray-600 text-sm px-4 py-2 rounded-lg hover:bg-gray-50 transition">
          Hapus TTD
        </button>
        <input type="hidden" name="ttd_data" id="ttdData">
      </div>

      <!-- Tombol -->
      <div class="flex gap-3 mt-6">
        <button type="submit" onclick="saveTTD()"
          class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-6 py-2.5 rounded-lg transition">
          Simpan Perubahan
        </button>
        <a href="main.php"
          class="text-sm text-gray-500 hover:text-gray-700 px-4 py-2.5 rounded-lg hover:bg-gray-100 transition">
          Batal
        </a>
      </div>

    </form>
  </main>

  <footer class="text-center text-xs text-gray-400 py-6">
    © 2026 Task Archive. All rights reserved.
  </footer>

  <script>
    const canvas = document.getElementById('ttdCanvas');
    const ctx    = canvas.getContext('2d');
    let drawing  = false;

    ctx.strokeStyle = '#1e3a5f';
    ctx.lineWidth   = 2;
    ctx.lineCap     = 'round';
    ctx.lineJoin    = 'round';

    function getPos(e) {
      const rect  = canvas.getBoundingClientRect();
      const scaleX = canvas.width  / rect.width;
      const scaleY = canvas.height / rect.height;
      const src   = e.touches ? e.touches[0] : e;
      return { x: (src.clientX - rect.left) * scaleX, y: (src.clientY - rect.top) * scaleY };
    }

    canvas.addEventListener('mousedown',  e => { drawing = true; ctx.beginPath(); const p = getPos(e); ctx.moveTo(p.x, p.y); });
    canvas.addEventListener('mousemove',  e => { if (!drawing) return; const p = getPos(e); ctx.lineTo(p.x, p.y); ctx.stroke(); });
    canvas.addEventListener('mouseup',    () => drawing = false);
    canvas.addEventListener('mouseleave', () => drawing = false);
    canvas.addEventListener('touchstart', e => { e.preventDefault(); drawing = true; ctx.beginPath(); const p = getPos(e); ctx.moveTo(p.x, p.y); }, { passive: false });
    canvas.addEventListener('touchmove',  e => { e.preventDefault(); if (!drawing) return; const p = getPos(e); ctx.lineTo(p.x, p.y); ctx.stroke(); }, { passive: false });
    canvas.addEventListener('touchend',   () => drawing = false);

    function clearTTD() { ctx.clearRect(0, 0, canvas.width, canvas.height); document.getElementById('ttdData').value = ''; }
    function saveTTD()  { document.getElementById('ttdData').value = canvas.toDataURL('image/png'); }
  </script>

</body>
</html>