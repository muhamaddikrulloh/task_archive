# TaskArchive

TaskArchive adalah website untuk mahasiswa yang ingin menyimpan dan mengelola riwayat tugas kuliah di satu tempat. Setiap tugas dapat dicatat lengkap beserta mata kuliah, dosen, semester, tanggal pengumpulan, lampiran file, hingga tanda tangan digital. Aplikasi ini memudahkan mahasiswa melacak tugas yang pernah dikerjakan tanpa harus mencari-cari file yang tersebar.

---

## Tech Stack

- **Backend:** PHP (Native), MySQL
- **Frontend:** Tailwind CSS, DataTables, jsPDF
- **Databse:** MariaDB

---

## Fitur

**Login**
- Autentikasi menggunakan email dan password dengan `password_verify()`
- Sesi dikelola menggunakan PHP Session
- Semua halaman dilindungi, pengguna yang belum login diarahkan ke halaman login otomatis

**CRUD + Upload Multiple File**
- Tambah, lihat, edit, dan hapus data tugas
- Upload lampiran lebih dari satu file dalam tiga kategori PDF, screenshot (JPG/PNG), dan ZIP/RAR

**Pencarian Data & DataTables**
- Daftar tugas ditampilkan dengan library DataTables lengkap fitur pencarian
- Data dapat diekspor ke PDF menggunakan jsPDF dan plugin AutoTable

**Canvas untuk TTD Digital**
- Tanda tangan digambar langsung di browser menggunakan elemen `<canvas>`
- Tanda tangan disimpan sebagai file PNG

**Video / Animasi dan Audio**
- Halaman dashboard menyediakan video tutorial yang diputar langsung menggunakan elemen `<video>`

**Penggunaan Modal**
- Detail tugas ditampilkan dalam modal

---

## Struktur Proyek

```
TaskArchive/
├── auth/
│   ├── login_process.php
│   └── logout.php
├── tasks/
│   ├── index.php        # Daftar tugas
│   ├── create.php       # Form tambah tugas
│   ├── store.php        # Proses simpan tugas
│   ├── edit.php         # Form edit tugas
│   ├── update.php       # Proses update tugas
│   └── delete.php       # Proses hapus tugas
├── includes/
│   ├── db.php           # Koneksi database
│   ├── navbar.php       # Komponen navigasi
│   └── task_archive.sql # Skema database
├── assets/
│   └── uploads/
│       ├── pdf/
│       ├── screenshots/
│       ├── signatures/
│       ├── zip/
│       └── videos/
├── dashboard.php
└── login.php
```

---
## Preview Website
![Preview](assets/uploads/screenshots/preview.png)
---

## Disusun Oleh
```
NAMA    : MUHAMAD DIKRULLOH
NIM     : 2430511076
KELAS   : 4B
```
---