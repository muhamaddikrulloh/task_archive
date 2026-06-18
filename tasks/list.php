<?php
session_start();
require_once '../includes/db.php';

$page = basename($_SERVER['PHP_SELF']);

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

$success = $_SESSION['success'] ?? '';
unset($_SESSION['success']);

$uid = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT t.*, (SELECT COUNT(*) FROM lampiran WHERE tugas_id = t.id) AS jml_lampiran FROM tugas t WHERE t.user_id = ? ORDER BY t.tanggal_pengumpulan DESC");
$stmt->bind_param('i', $uid);
$stmt->execute();
$tugas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Data Tugas – Task Archive</title>
  <script src="https://cdn.tailwindcss.com"></script>

  <!-- DataTables -->
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
  <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

  <!-- Export PDF -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.31/jspdf.plugin.autotable.min.js"></script>

  <style>
    table.dataTable thead th { background-color: #f9fafb; color: #374151; font-weight: 600; }
    .dataTables_wrapper .dataTables_filter input { border: 1px solid #d1d5db; border-radius: 0.5rem; padding: 0.35rem 0.75rem; font-size: 0.875rem; outline: none; }
    .dataTables_wrapper .dataTables_filter input:focus { border-color: #3b82f6; box-shadow: 0 0 0 2px #bfdbfe; }
    .dataTables_wrapper .dataTables_length select { border: 1px solid #d1d5db; border-radius: 0.5rem; padding: 0.25rem 0.5rem; font-size: 0.875rem; }
    .dataTables_wrapper .dataTables_info, .dataTables_wrapper .dataTables_paginate { font-size: 0.8rem; color: #6b7280; margin-top: 0.75rem; }
    .dataTables_wrapper .dataTables_paginate .paginate_button.current { background: #3b82f6 !important; color: white !important; border-radius: 0.375rem; border: none !important; }
    .dataTables_wrapper .dataTables_paginate .paginate_button:hover { background: #eff6ff !important; color: #3b82f6 !important; border-radius: 0.375rem; border: none !important; }
  </style>
</head>
<body class="bg-gray-100 min-h-screen">

  <!-- Navbar -->
  <?php include '../includes/navbar.php'; ?>

  <main class="max-w-6xl mx-auto px-4 py-8">

    <h2 class="text-xl font-semibold text-gray-800 mb-6">Data Tugas</h2>

    <!-- Tombol -->
    <div class="flex flex-wrap gap-3 mb-6">
      <a href="create.php"
        class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2.5 rounded-lg transition">
        Tambah Tugas
      </a>
      <button onclick="exportPDF()"
        class="bg-red-500 hover:bg-red-600 text-white text-sm font-medium px-4 py-2.5 rounded-lg transition">
        Export PDF
      </button>

      <?php if ($success): ?>
      <div class="ml-auto bg-green-50 border border-green-200 text-green-600 text-sm px-3 py-2 rounded-lg">
        <?= htmlspecialchars($success) ?>
      </div>
      <?php endif; ?>


    </div>

    <!-- Tabel -->
    <div class="bg-white rounded-xl shadow-sm p-6 overflow-x-auto">
      <table id="tabelTugas" class="w-full text-sm text-left">
        <thead>
          <tr class="border-b border-gray-200">
            <th class="px-4 py-3">No</th>
            <th class="px-4 py-3">Judul Tugas</th>
            <th class="px-4 py-3">Mata Kuliah</th>
            <th class="px-4 py-3">Semester</th>
            <th class="px-4 py-3">Tanggal</th>
            <th class="px-4 py-3 text-center">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($tugas as $i => $t): ?>
            <tr class="border-b border-gray-100 hover:bg-gray-50">
              <td class="px-4 py-3 text-gray-500"><?= $i + 1 ?></td>
              <td class="px-4 py-3 font-medium text-gray-800"><?= htmlspecialchars($t['judul_tugas']) ?></td>
              <td class="px-4 py-3 text-gray-600"><?= htmlspecialchars($t['mata_kuliah']) ?></td>
              <td class="px-4 py-3 text-gray-600"><?= htmlspecialchars($t['semester']) ?></td>
              <td class="px-4 py-3 text-gray-600"><?= date('d M Y', strtotime($t['tanggal_pengumpulan'])) ?></td>
              <td class="px-4 py-3 text-center">
                <div class="flex justify-center gap-2">

                  <button onclick="openDetail(<?= htmlspecialchars(json_encode($t), ENT_QUOTES) ?>)"
                    class="bg-blue-50 hover:bg-blue-100 text-blue-600 text-xs font-medium px-3 py-1.5 rounded-lg transition">
                    Detail
                  </button>

                  <a href="edit.php?id=<?= $t['id'] ?>"
                    class="bg-yellow-50 hover:bg-yellow-100 text-yellow-600 text-xs font-medium px-3 py-1.5 rounded-lg transition">
                    Edit
                  </a>

                  <button onclick="confirmHapus(<?= $t['id'] ?>)"
                    class="bg-red-50 hover:bg-red-100 text-red-600 text-xs font-medium px-3 py-1.5 rounded-lg transition">
                    Hapus
                  </button>

              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

  </main>

  <!-- Modal Detail -->
  <div id="modalDetail" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 hidden px-4">
    <div class="bg-white rounded-2xl shadow-lg w-full max-w-md p-6 max-h-[100vh] overflow-y-auto">
      <div class="mb-4 text-center">
        <h3 class="text-1xl font-bold text-blue-600">Detail Tugas</h3>
      </div>
      <div class="space-y-3 text-sm">
        <div><p class="font-semibold text-gray-500">Judul Tugas</p><p id="d-judul" class="text-gray-700 mt-0.5"></p></div>
        <div><p class="font-semibold text-gray-500">Mata Kuliah</p><p id="d-matkul" class="text-gray-700 mt-0.5"></p></div>
        <div><p class="font-semibold text-gray-500">Dosen</p><p id="d-dosen" class="text-gray-700 mt-0.5"></p></div>
        <div><p class="font-semibold text-gray-500">Semester</p><p id="d-semester" class="text-gray-700 mt-0.5"></p></div>
        <div><p class="font-semibold text-gray-500">Tanggal Pengumpulan</p><p id="d-tanggal" class="text-gray-700 mt-0.5"></p></div>
        <div><p class="font-semibold text-gray-500">Deskripsi</p><p id="d-deskripsi" class="text-gray-700 mt-0.5 whitespace-pre-wrap"></p></div>
        <div><p class="font-semibold text-gray-500">Jumlah Lampiran</p><p id="d-lampiran" class="text-gray-700 mt-0.5"></p></div>
        <div id="ttd-wrapper" class="hidden">
          <p class="font-semibold text-gray-500">Tanda Tangan Digital</p>
          <img id="d-ttd" src="" alt="TTD" class="border border-gray-200 rounded-lg max-h-32">
        </div>
      </div>
      <button onclick="closeDetail()"
        class="mt-6 w-full bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium py-2.5 rounded-lg transition">
        Tutup
      </button>
    </div>
  </div>

  <!-- Form hapus tersembunyi -->
  <form id="formHapus" action="delete.php" method="POST" class="hidden">
    <input type="hidden" name="id" id="hapus-id">
  </form>

  <script>
    $(document).ready(function () {
      $('#tabelTugas').DataTable({
        columnDefs: [{ targets: '_all', defaultContent: '-' }],
        language: {
          search: 'Cari:',
          lengthMenu: 'Tampilkan _MENU_ data',
          info: 'Menampilkan _START_–_END_ dari _TOTAL_ data',
          paginate: { previous: '‹', next: '›' },
          emptyTable: 'Belum ada data tugas.'
        }
      });
    });

    function openDetail(data) {
      document.getElementById('d-judul').textContent = data.judul_tugas || '-';
      document.getElementById('d-matkul').textContent = data.mata_kuliah || '-';
      document.getElementById('d-dosen').textContent = data.dosen || '-';
      document.getElementById('d-semester').textContent = 'Semester ' + (data.semester || '-');
      document.getElementById('d-tanggal').textContent  = data.tanggal_pengumpulan || '-';
      document.getElementById('d-deskripsi').textContent = data.deskripsi || '-';
      document.getElementById('d-lampiran').textContent = data.jml_lampiran + ' file';

      const ttdWrapper = document.getElementById('ttd-wrapper');
      if (data.ttd_digital) {
        document.getElementById('d-ttd').src = '../assets/uploads/signatures/' + data.ttd_digital;
        ttdWrapper.classList.remove('hidden');
      } else {
        ttdWrapper.classList.add('hidden');
      }
      document.getElementById('modalDetail').classList.remove('hidden');
    }

    function closeDetail() {
      document.getElementById('modalDetail').classList.add('hidden');
    }

    document.getElementById('modalDetail').addEventListener('click', function (e) {
      if (e.target === this) closeDetail();
    });

    function confirmHapus(id) {
      if (confirm('Yakin ingin menghapus tugas ini?')) {
        document.getElementById('hapus-id').value = id;
        document.getElementById('formHapus').submit();
      }
    }

    function exportPDF() {
      const { jsPDF } = window.jspdf;
      const doc = new jsPDF();
      doc.text('Data Tugas – Task Archive', 14, 15);
      doc.autoTable({
        startY: 22,
        head: [['No', 'Judul Tugas', 'Mata Kuliah', 'Semester', 'Tanggal']],
        body: Array.from(document.querySelectorAll('#tabelTugas tbody tr')).map((row, i) => {
          const cols = row.querySelectorAll('td');
          return [i + 1, cols[1]?.textContent.trim(), cols[2]?.textContent.trim(), cols[3]?.textContent.trim(), cols[4]?.textContent.trim()];
        })
      });
      doc.save('data-tugas.pdf');
    }

  </script>

</body>
</html>