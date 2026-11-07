# 👑 VOTING KAKANG SENDUK PONOROGO
**Sistem Informasi Pemilihan Duta Wisata Daerah Kabupaten Ponorogo Berbasis Web**

Aplikasi web modern, aman, dan berintegritas tinggi yang dirancang khusus untuk memfasilitasi proses pemilihan favorit Duta Wisata (Kakang & Senduk) Kabupaten Ponorogo berbasis pilihan masyarakat (*People's Choice*).

---

## 🌟 FITUR UTAMA

### 1. Antarmuka Publik & Pemilih (Voters)
- **Desain Premium Editorial Pageant:** Visual bertema *Modern Government & Cultural Tourism* dengan palet warna Deep Navy (`#101B35`), Royal Blue (`#243F73`), dan Ponorogo Gold (`#D6AE58`), diperkaya ornamen Dadak Merak dan kebudayaan Reog Ponorogo.
- **Katalog & Filter Kontestan:** Pencarian instan dan filter kategori (Kakang / Senduk / Semua) secara *real-time*.
- **Profil Lengkap Kontestan:** Menampilkan foto resmi berformat 4:5, nomor peserta, biodata, visi, misi, rekam jejak prestasi, dan deskripsi inspiratif.
- **Bilik Voting Terpisah (Kakang & Senduk):**
  - Satu akun pemilih berhak memberikan tepat **1 suara Kakang** dan **1 suara Senduk**.
  - Pengecekan status hak suara real-time (*0/2, 1/2, 2/2 Suara*).
  - Modal konfirmasi bertingkat sebelum komit suara untuk mencegah kesalahan klik.
  - Suara bersifat permanen (tidak dapat diubah atau ditarik kembali).
- **Klasemen Sementara & Podium Juara:**
  - Visualisasi Podium Juara 1, 2, dan 3 berhias mahkota emas, perak, dan perunggu.
  - Tabel perolehan suara sah dan persentase dihitung otomatis via agregasi SQL.
- **Hitung Mundur Periode Voting:** Countdown timer interaktif menuju batas akhir pemungutan suara.

### 2. Panel Administrator (Admin)
- **Dashboard Statistik Real-Time:**
  - Metrik KPI: Total Kontestan, Pemilih Terdaftar, Total Suara Masuk, dan Status Periode.
  - Grafik interaktif **Chart.js** perolehan suara Kakang dan Senduk.
  - Audit Trail (Log aktivitas sistem terkini).
- **Manajemen Kontestan (CRUD & Upload Terproteksi):**
  - Tambah, lihat detail, perbarui data, dan arsipkan kontestan.
  - Validasi ketat upload foto (MIME type check, whitelist ekstensi JPG/PNG/WEBP, maksimal 2 MB, nama berkas terenkripsi).
  - **Proteksi Integritas Data:** Kontestan yang telah memiliki suara voting sah tidak dapat dihapus permanen, melainkan dinonaktifkan/diarsipkan.
- **Manajemen Akun Pemilih:**
  - Pemantauan partisipasi pemilih (status vote Kakang dan Senduk).
  - Fitur aktivasi dan pemblokiran akun pemilih bermasalah.
  - Password disimpan secara aman dalam bentuk hash BCRYPT (tidak menampilkan plain-text).
- **Manajemen Periode Pemilihan:**
  - Pengaturan tanggal mulai, tanggal selesai, dan status buka/tutup voting.
  - Validasi backend otomatis menolak voting sebelum tanggal buka atau setelah tanggal tutup.
  - Pencegahan otomatis tumpang tindih periode aktif.
- **Rekapitulasi Hasil & Ekspor Laporan:**
  - Filter hasil berdasarkan periode voting.
  - **Ekspor Dokumen PDF Resmi:** Format berita acara siap cetak lengkap dengan kop surat resmi Dinas Pariwisata Ponorogo dan kolom tanda tangan.
  - **Ekspor Spreadsheet Excel (.CSV):** Lembar kerja UTF-8 BOM siap analisis.
- **Audit Trail & Pengaturan Akun:** Riwayat lengkap aktivitas penting dan modul pergantian password admin.

---

## 🛡️ STANDAR KEAMANAN APLIKASI
1. **PDO Prepared Statements 100%:** Proteksi mutlak dari serangan SQL Injection.
2. **Double Voting Prevention:** Kombinasi `UNIQUE KEY (user_id, kategori, periode_voting_id)` dan transaksi database atomik PDO (`FOR UPDATE`, `beginTransaction`, `commit`, `rollBack`).
3. **Cross-Site Request Forgery (CSRF):** Token acak kriptografis 32-byte pada setiap form POST data.
4. **Cross-Site Scripting (XSS):** Sanitasi output menyeluruh dengan `htmlspecialchars(..., ENT_QUOTES, 'UTF-8')`.
5. **Session Security:** Pemanggilan `session_regenerate_id(true)` saat login untuk mitigasi Session Fixation & Session Hijacking.
6. **Upload Security:** Larangan eksekusi PHP pada direktori `uploads/` melalui berkas `.htaccess`.

---

## 💻 PERSYARATAN SISTEM
- **Web Server:** Apache (XAMPP / Laragon / Standalone Apache).
- **PHP Version:** PHP 8.0 / 8.1 / 8.2 atau yang lebih baru.
- **Database:** MySQL 5.7+ / MariaDB 10.4+.
- **Browser:** Google Chrome, Mozilla Firefox, Microsoft Edge, atau Safari (Desktop & Mobile).

---

## 🚀 PANDUAN INSTALASI & KONFIGURASI

### 1. Letak Folder Proyek
Pastikan folder aplikasi berada di direktori `htdocs` web server:
```
c:\xampp\htdocs\voting-kakang-senduk\
  atau
c:\xampp_baru\htdocs\voting-kakang-senduk\
```

### 2. Nyalakan Layanan XAMPP
Buka **XAMPP Control Panel**, lalu klik tombol **Start** pada modul:
- **Apache**
- **MySQL**

### 3. Pembuatan & Impor Basis Data
1. Buka peramban dan akses **phpMyAdmin**:
   ```
   http://localhost/phpmyadmin/
   ```
2. Buat database baru bernama:
   ```sql
   db_voting_kakang_senduk
   ```
3. Pilih database tersebut, lalu buka menu **Import**.
4. Klik **Choose File** dan pilih file SQL:
   ```
   voting-kakang-senduk/database/db_voting_kakang_senduk.sql
   ```
5. Klik tombol **Import** di bagian bawah. Seluruh tabel, relasi foreign key, dan data inisial akan otomatis terisi.

### 4. Konfigurasi Koneksi Database
Bila username/password MySQL XAMPP Anda menggunakan pengaturan selain default (`root` tanpa password), Anda dapat menyesuaikannya pada berkas `config/koneksi.php`:
```php
$db_host = '127.0.0.1';
$db_port = '3306';
$db_name = 'db_voting_kakang_senduk';
$db_user = 'root';
$db_pass = ''; // Sesuaikan jika ada password MySQL Anda
```

### 5. Menjalankan Aplikasi
Buka peramban Google Chrome dan akses URL berikut:
```
http://localhost/voting-kakang-senduk/
```

---

## 🔑 AKUN PENGGUNA DEFAULT (SEEDED DATA)

### A. Akun Administrator
| Informasi | Detail Akun |
| :--- | :--- |
| **Email Login** | `admin@kakangsudukan.ponorogo.go.id` |
| **Kata Sandi** | `AdminPonorogo2026!` |
| **Hak Akses** | Akses penuh dashboard admin, CRUD kontestan, buka/tutup periode, dan ekspor laporan |
| **URL Akses** | `http://localhost/voting-kakang-senduk/auth/login.php` |

### B. Akun Pemilih Contoh (Masyarakat)
| No. | Nama Pemilih | Email Login | Kata Sandi | Status Suara Awal |
| :---: | :--- | :--- | :--- | :--- |
| 1 | Bima Nugroho | `bima.nugroho@gmail.com` | `Pemilih2026!` | Sudah memilih Kakang & Senduk |
| 2 | Siti Rahmawati | `siti.rahmawati@gmail.com` | `Pemilih2026!` | Sudah memilih Kakang & Senduk |
| 3 | Dimas Pratama | `dimas.pratama@gmail.com` | `Pemilih2026!` | Sudah memilih Kakang & Senduk |
| 4 | Anisa Permata | `anisa.permata@gmail.com` | `Pemilih2026!` | Sudah memilih Kakang & Senduk |
| 5 | Rizki Firmansyah | `rizki.firmansyah@gmail.com` | `Pemilih2026!` | Sudah memilih Kakang & Senduk |

*(Masyarakat baru dapat mendaftar mandiri kapan saja melalui menu **Daftar Akun Baru** pada halaman registrasi)*.

---

## 📖 PANDUAN PENGGUNAAN

### Panduan untuk Administrator
1. Buka halaman login di `http://localhost/voting-kakang-senduk/auth/login.php` dan masuk dengan akun admin.
2. Anda akan otomatis diarahkan ke **Dashboard Administrator**.
3. **Mengelola Kontestan:**
   - Masuk ke menu **Data Kontestan**.
   - Klik **Tambah Kontestan Baru** untuk menambahkan peserta Kakang atau Senduk beserta foto profil resminya.
   - Anda dapat mengubah data melalui tombol **Edit**, atau menonaktifkan peserta melalui tombol **Hapus/Arsipkan**.
4. **Mengatur Periode Voting:**
   - Masuk ke menu **Periode Voting**.
   - Atur tanggal mulai dan selesai voting, lalu aktifkan status periode.
5. **Melihat Hasil & Rekapitulasi:**
   - Masuk ke menu **Hasil Voting** untuk meninjau perolehan suara per kontestan secara real-time.
6. **Mengekspor Laporan:**
   - Masuk ke menu **Laporan & Ekspor**.
   - Pilih periode pemilihan, lalu klik **Cetak / Ekspor PDF Resmi** untuk mencetak berita acara bertanda tangan atau klik **Unduh Berkas Excel** untuk spreadsheet.

### Panduan untuk Pemilih (Masyarakat)
1. Akses halaman utama di `http://localhost/voting-kakang-senduk/`.
2. Klik tombol **Daftar Akun Baru** jika belum memiliki akun, atau klik **Masuk** jika sudah terdaftar.
3. Setelah berhasil masuk, buka menu **Voting Sekarang** atau **Bilik Suara**.
4. Pada bilik suara:
   - Pilih tab **Bilik Kakang** dan tentukan kontestan pilihan Anda.
   - Dialog konfirmasi akan muncul. Tekan **Ya, Konfirmasi Suara**.
   - Lanjutkan ke tab **Bilik Senduk** dan tentukan pilihan Senduk favorit Anda.
5. Pantau klasemen perolehan suara sementara secara langsung pada menu **Klasemen**.

---

## 🏛️ STRUKTUR FOLDER PROYEK

```
voting-kakang-senduk/
├── .htaccess                       # Proteksi akses Apache & security headers
├── .gitignore                      # Berkas abaikan git
├── index.php                       # Beranda utama publik (Landing Page)
├── README.md                       # Dokumentasi resmi aplikasi
├── PROJECT_STATUS.md               # Catatan pelacakan progres pengembangan
│
├── admin/                          # Modul Panel Pengelola
│   ├── dashboard.php               # KPI metrik & grafik Chart.js
│   ├── kontestan.php               # Katalog & manajemen kontestan
│   ├── tambah-kontestan.php        # Formulir penambahan kontestan
│   ├── edit-kontestan.php          # Formulir pengubahan kontestan
│   ├── pemilih.php                 # Manajemen akun pemilih & hak suara
│   ├── periode.php                 # Penjadwalan & aktivasi periode
│   ├── hasil-voting.php            # Rekapitulasi perolehan suara
│   ├── laporan.php                 # Modul ekspor PDF & Excel
│   ├── pengaturan.php              # Ganti profil & kata sandi admin
│   └── logs.php                    # Audit trail aktivitas sistem
│
├── auth/                           # Modul Autentikasi Pengguna
│   ├── login.php                   # Halaman masuk admin & pemilih
│   ├── register.php                # Pendaftaran akun pemilih baru
│   └── logout.php                  # Penghancuran sesi aman
│
├── config/                         # Konfigurasi Sistem
│   ├── app.php                     # Konstanta, Base URL & sesi
│   └── koneksi.php                 # Inisialisasi PDO MySQL terproteksi
│
├── includes/                       # Komponen Modular & Middleware
│   ├── admin-footer.php            # Penutup layout & Chart.js
│   ├── admin-header.php            # Topbar & navigasi admin
│   ├── admin-sidebar.php           # Sidebar menu admin
│   ├── auth-check.php              # Guard middleware otorisasi RBAC
│   ├── csrf.php                    # Helper pencegah serangan CSRF
│   ├── footer.php                  # Footer publik bertema Reog Ponorogo
│   ├── functions.php               # Kumpulan utilitas, sanitasi & formatting
│   ├── header.php                  # Head HTML publik, Bootstrap & Font
│   └── navbar.php                  # Navigasi publik dengan status voting
│
├── pages/                          # Halaman Antarmuka Publik
│   ├── 403.php                     # Halaman galat akses ditolak (Forbidden)
│   ├── 404.php                     # Halaman galat data tidak ditemukan
│   ├── detail-kontestan.php        # Profil lengkap kontestan
│   ├── klasemen.php                # Klasemen real-time & podium juara
│   ├── kontestan.php               # Katalog kontestan & filter live
│   ├── tentang.php                 # Informasi sejarah Kakang Senduk
│   └── voting.php                  # Bilik suara digital interaktif
│
├── proses/                         # Endpoint Pemrosesan Data & Transaksi
│   ├── edit-kontestan.php          # Eksekusi update kontestan & ganti foto
│   ├── ekspor-excel.php            # Generator unduhan spreadsheet CSV/Excel
│   ├── ekspor-pdf.php              # Format cetak lembar keputusan resmi
│   ├── get-klasemen-live.php       # API JSON pembaruan klasemen live AJAX
│   ├── hapus-kontestan.php         # Hapus aman / arsip kontestan berintegritas
│   ├── simpan-periode.php          # Eksekusi simpan jadwal periode
│   ├── simpan-voting.php           # Transaksi atomic voting anti-double vote
│   ├── tambah-kontestan.php        # Eksekusi simpan kontestan & upload foto
│   ├── toggle-pemilih.php          # Aktivasi / blokir akun pemilih
│   └── toggle-periode.php          # Buka / tutup periode voting
│
├── assets/                         # Aset Desain & Skrip
│   ├── css/
│   │   ├── admin.css               # Desain dashboard admin modern
│   │   ├── auth.css                # Desain halaman login & register elegan
│   │   └── style.css               # Desain publik bertema Navy & Ponorogo Gold
│   ├── js/
│   │   ├── dashboard.js            # Inisialisasi grafik Chart.js admin
│   │   ├── main.js                 # Filter kontestan, countdown & navbar
│   │   └── voting.js               # Dialog konfirmasi modal & proteksi submit
│   └── img/
│       ├── kakang-placeholder.svg  # Avatar resmi Kakang dengan blangkon & selempang
│       ├── logo.svg                # Logo resmi Kakang Senduk Ponorogo
│       └── senduk-placeholder.svg  # Avatar resmi Senduk dengan sanggul & tiara
│
├── database/
│   └── db_voting_kakang_senduk.sql # Skema basis data lengkap & seeder siap impor
│
└── uploads/                        # Direktori Penyimpanan Berkas
    ├── .htaccess                   # Proteksi pemblokiran eksekusi skrip PHP
    └── kontestan/                  # Foto resmi peserta kontestan
```

---

## 🏛️ TIM PENGEMBANG
Dikembangkan secara profesional oleh gabungan peran:
1. *Senior System Analyst* — Analisis kebutuhan fungsional & non-fungsional.
2. *Senior Full-Stack PHP Developer* — Implementasi PHP Native & arsitektur modular.
3. *Database Engineer (MySQL)* — Skema normal 3NF, Foreign Key, dan Transaksi Atomic.
4. *UI/UX Designer* — Desain estetika pageant bernuansa Ponorogo Gold & Deep Navy.
5. *Software Architect* — Perancangan arsitektur direktori dan sirkulasi data.
6. *Quality Assurance Engineer* — Pengujian linting, alur validasi, dan kompatibilitas XAMPP.
7. *Application Security Engineer* — Penerapan proteksi SQLi, XSS, CSRF, dan RBAC.

&copy; 2026 Dinas Kebudayaan, Pariwisata, Pemuda dan Olahraga Kabupaten Ponorogo.

