<?php
session_start();
require_once '../includes/db.php';

$page = basename($_SERVER['PHP_SELF']);

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Tambah Tugas – Task Archive</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">

  <!-- Navbar -->
  <?php include '../includes/navbar.php'; ?>

  <main class="max-w-2xl mx-auto px-4 py-8">

    <h2 class="text-xl font-semibold text-gray-800 mb-6">Tambah Tugas</h2>

    <!-- PHP: action mengarah ke store.php -->
    <form action="store.php" method="POST" enctype="multipart/form-data">

      <div class="bg-white rounded-xl shadow-sm p-6 space-y-5">

        <!-- Judul Tugas -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Judul Tugas</label>
          <input type="text" name="judul_tugas" required placeholder="Contoh: Laporan Praktikum Basis Data"
            class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <!-- Mata Kuliah & Dosen -->
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Mata Kuliah</label>
            <input type="text" name="mata_kuliah" required placeholder="Basis Data"
              class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Dosen</label>
            <input type="text" name="dosen" required placeholder="Nama Dosen"
              class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
          </div>
        </div>

        <!-- Semester & Tanggal -->
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Semester</label>
            <select name="semester" required
              class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
              <option value="" disabled selected>Pilih semester</option>
              <?php for ($s = 1; $s <= 8; $s++): ?>
                <option value="<?= $s ?>">Semester <?= $s ?></option>
              <?php endfor; ?>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Pengumpulan</label>
            <input type="date" name="tanggal_pengumpulan" required
              class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
          </div>
        </div>

        <!-- Deskripsi -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
          <textarea name="deskripsi" rows="3" placeholder="Deskripsi singkat tugas..."
            class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"></textarea>
        </div>

      </div>

      <!-- Upload Lampiran -->
      <div class="bg-white rounded-xl shadow-sm p-6 mt-4 space-y-4">
        <h3 class="text-sm font-semibold text-gray-700">Upload Lampiran</h3>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Laporan PDF</label>
          <input type="file" name="lampiran_pdf[]" multiple accept=".pdf"
            class="w-full text-sm text-gray-500 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-600 hover:file:bg-blue-100">
          <p class="text-xs text-gray-400 mt-1">Bisa pilih lebih dari satu file .pdf</p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Source Code (ZIP)</label>
          <input type="file" name="lampiran_zip[]" multiple accept=".zip,.rar"
            class="w-full text-sm text-gray-500 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-600 hover:file:bg-blue-100">
          <p class="text-xs text-gray-400 mt-1">Format .zip atau .rar</p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Screenshot</label>
          <input type="file" name="lampiran_img[]" multiple accept="image/*"
            class="w-full text-sm text-gray-500 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-600 hover:file:bg-blue-100">
          <p class="text-xs text-gray-400 mt-1">Format gambar (jpg, png, dll)</p>
        </div>
      </div>

      <!-- Canvas Tanda Tangan -->
      <div class="bg-white rounded-xl shadow-sm p-6 mt-4">
        <h3 class="text-sm font-semibold text-gray-700 mb-3">Tanda Tangan Digital</h3>
        <canvas id="ttdCanvas" width="600" height="160"
          class="w-full border border-gray-300 rounded-lg bg-white cursor-crosshair touch-none"></canvas>
        <div class="flex gap-3 mt-3">
          <button type="button" onclick="clearTTD()"
            class="border border-gray-300 text-gray-600 text-sm px-4 py-2 rounded-lg hover:bg-gray-50 transition">
            Hapus TTD
          </button>
        </div>
        <!-- Input hidden untuk menyimpan data TTD sebagai base64 -->
        <input type="hidden" name="ttd_data" id="ttdData">
      </div>

      <!-- Tombol -->
      <div class="flex gap-3 mt-6">
        <button type="submit" onclick="saveTTD()"
          class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-6 py-2.5 rounded-lg transition">
          Simpan
        </button>
        <button type="reset" onclick="clearTTD()"
          class="border border-gray-300 text-gray-600 text-sm font-medium px-6 py-2.5 rounded-lg hover:bg-gray-50 transition">
          Reset
        </button>
        <a href="index.php"
          class="text-sm text-gray-500 hover:text-red-500 px-4 py-2.5 rounded-lg hover:bg-gray-100 transition">
          Batal
        </a>
      </div>

    </form>
  </main>

  <script>
    // Canvas TTD
    const canvas  = document.getElementById('ttdCanvas');
    const ctx     = canvas.getContext('2d');
    let drawing   = false;

    ctx.strokeStyle = '#1e3a5f';
    ctx.lineWidth   = 2;
    ctx.lineCap     = 'round';
    ctx.lineJoin    = 'round';

    function getPos(e) {
      const rect = canvas.getBoundingClientRect();
      const scaleX = canvas.width  / rect.width;
      const scaleY = canvas.height / rect.height;
      const src = e.touches ? e.touches[0] : e;
      return {
        x: (src.clientX - rect.left) * scaleX,
        y: (src.clientY - rect.top)  * scaleY
      };
    }

    canvas.addEventListener('mousedown',  e => { drawing = true; ctx.beginPath(); const p = getPos(e); ctx.moveTo(p.x, p.y); });
    canvas.addEventListener('mousemove',  e => { if (!drawing) return; const p = getPos(e); ctx.lineTo(p.x, p.y); ctx.stroke(); });
    canvas.addEventListener('mouseup',    () => drawing = false);
    canvas.addEventListener('mouseleave', () => drawing = false);

    canvas.addEventListener('touchstart',  e => { e.preventDefault(); drawing = true; ctx.beginPath(); const p = getPos(e); ctx.moveTo(p.x, p.y); }, { passive: false });
    canvas.addEventListener('touchmove',   e => { e.preventDefault(); if (!drawing) return; const p = getPos(e); ctx.lineTo(p.x, p.y); ctx.stroke(); }, { passive: false });
    canvas.addEventListener('touchend',    () => drawing = false);

    function clearTTD() {
      ctx.clearRect(0, 0, canvas.width, canvas.height);
      document.getElementById('ttdData').value = '';
    }

    function saveTTD() {
      // Simpan TTD ke input hidden sebelum form disubmit
      document.getElementById('ttdData').value = canvas.toDataURL('image/png');
    }
  </script>

</body>
</html>