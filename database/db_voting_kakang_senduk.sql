-- ====================================================================
-- DATABASE: db_voting_kakang_senduk
-- APLIKASI: Sistem Informasi Pemilihan Duta Wisata Kakang Senduk Ponorogo
-- ENGINE: InnoDB | CHARSET: utf8mb4 | COLLATION: utf8mb4_unicode_ci
-- ====================================================================

CREATE DATABASE IF NOT EXISTS `db_voting_kakang_senduk` 
DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE `db_voting_kakang_senduk`;

-- --------------------------------------------------------------------
-- 1. TABEL: users
-- --------------------------------------------------------------------
DROP TABLE IF EXISTS `activity_logs`;
DROP TABLE IF EXISTS `voting`;
DROP TABLE IF EXISTS `kontestan`;
DROP TABLE IF EXISTS `periode_voting`;
DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nama` VARCHAR(100) NOT NULL,
    `email` VARCHAR(100) NOT NULL UNIQUE,
    `no_hp` VARCHAR(20) NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `role` ENUM('admin', 'pemilih') NOT NULL DEFAULT 'pemilih',
    `status` ENUM('aktif', 'nonaktif') NOT NULL DEFAULT 'aktif',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_users_role_status` (`role`, `status`),
    INDEX `idx_users_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------------------
-- 2. TABEL: kontestan
-- --------------------------------------------------------------------
CREATE TABLE `kontestan` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nomor_peserta` VARCHAR(10) NOT NULL,
    `nama` VARCHAR(100) NOT NULL,
    `kategori` ENUM('Kakang', 'Senduk') NOT NULL,
    `tempat_lahir` VARCHAR(100) NULL,
    `tanggal_lahir` DATE NULL,
    `asal_instansi` VARCHAR(150) NOT NULL,
    `foto` VARCHAR(255) NOT NULL,
    `visi` TEXT NULL,
    `misi` TEXT NULL,
    `prestasi` TEXT NULL,
    `deskripsi` TEXT NULL,
    `status` ENUM('aktif', 'nonaktif') NOT NULL DEFAULT 'aktif',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_kontestan_kategori_status` (`kategori`, `status`),
    INDEX `idx_kontestan_nomor` (`nomor_peserta`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------------------
-- 3. TABEL: periode_voting
-- --------------------------------------------------------------------
CREATE TABLE `periode_voting` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nama_periode` VARCHAR(100) NOT NULL,
    `tanggal_mulai` DATETIME NOT NULL,
    `tanggal_selesai` DATETIME NOT NULL,
    `status` ENUM('aktif', 'nonaktif') NOT NULL DEFAULT 'nonaktif',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_periode_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------------------
-- 4. TABEL: voting
-- --------------------------------------------------------------------
CREATE TABLE `voting` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `kontestan_id` INT NOT NULL,
    `periode_voting_id` INT NOT NULL,
    `kategori` ENUM('Kakang', 'Senduk') NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_voting_user` FOREIGN KEY (`user_id`) 
        REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `fk_voting_kontestan` FOREIGN KEY (`kontestan_id`) 
        REFERENCES `kontestan` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `fk_voting_periode` FOREIGN KEY (`periode_voting_id`) 
        REFERENCES `periode_voting` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `uq_user_kategori_periode` 
        UNIQUE (`user_id`, `kategori`, `periode_voting_id`),
    INDEX `idx_voting_rekap` (`periode_voting_id`, `kategori`, `kontestan_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------------------
-- 5. TABEL: activity_logs
-- --------------------------------------------------------------------
CREATE TABLE `activity_logs` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `aktivitas` VARCHAR(255) NOT NULL,
    `modul` VARCHAR(50) NOT NULL,
    `ip_address` VARCHAR(45) NOT NULL,
    `user_agent` TEXT NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_logs_user` FOREIGN KEY (`user_id`) 
        REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX `idx_logs_modul` (`modul`),
    INDEX `idx_logs_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================================================
-- SEED DATA AWAL (USERS, PERIODE, KONTESTAN, VOTING SAMPLE)
-- ====================================================================

-- Akun Administrator Utama (Password: AdminPonorogo2026!)
-- Akun Pemilih Sample (Password: Pemilih2026!)
INSERT INTO `users` (`id`, `nama`, `email`, `no_hp`, `password`, `role`, `status`, `created_at`) VALUES
(1, 'Administrator Ponorogo', 'admin@kakangsudukan.ponorogo.go.id', '081234567890', '$2y$10$.v.yazRe6j06XncbYaqBbuuGUxurWg/sN7YaRYsEepN7GElKEffKi', 'admin', 'aktif', NOW()),
(2, 'Bima Nugroho', 'bima.nugroho@gmail.com', '082198765432', '$2y$10$BDhGQE2D9gcCOkAfcFLyfuMRermga0dwHG1Tt0rE84T0rfS5MDFea', 'pemilih', 'aktif', NOW()),
(3, 'Siti Rahmawati', 'siti.rahmawati@gmail.com', '082233445566', '$2y$10$BDhGQE2D9gcCOkAfcFLyfuMRermga0dwHG1Tt0rE84T0rfS5MDFea', 'pemilih', 'aktif', NOW()),
(4, 'Dimas Pratama', 'dimas.pratama@gmail.com', '085712345678', '$2y$10$BDhGQE2D9gcCOkAfcFLyfuMRermga0dwHG1Tt0rE84T0rfS5MDFea', 'pemilih', 'aktif', NOW()),
(5, 'Anisa Permata', 'anisa.permata@gmail.com', '087812349988', '$2y$10$BDhGQE2D9gcCOkAfcFLyfuMRermga0dwHG1Tt0rE84T0rfS5MDFea', 'pemilih', 'aktif', NOW()),
(6, 'Rizki Firmansyah', 'rizki.firmansyah@gmail.com', '089611223344', '$2y$10$BDhGQE2D9gcCOkAfcFLyfuMRermga0dwHG1Tt0rE84T0rfS5MDFea', 'pemilih', 'aktif', NOW());

-- Periode Pemilihan Aktif 2026
INSERT INTO `periode_voting` (`id`, `nama_periode`, `tanggal_mulai`, `tanggal_selesai`, `status`, `created_at`) VALUES
(1, 'Pemilihan Duta Wisata Kakang Senduk Ponorogo 2026', '2026-10-01 00:00:00', '2026-12-31 23:59:59', 'aktif', NOW());

-- Data Kontestan Kakang (8 Peserta)
INSERT INTO `kontestan` (`id`, `nomor_peserta`, `nama`, `kategori`, `tempat_lahir`, `tanggal_lahir`, `asal_instansi`, `foto`, `visi`, `misi`, `prestasi`, `deskripsi`, `status`, `created_at`) VALUES
(1, 'K-01', 'Raditya Arya Mahendra', 'Kakang', 'Ponorogo', '2004-05-14', 'Universitas Muhammadiyah Ponorogo', 'kakang_01.jpg', 
 'Menjadikan kebudayaan Reog Ponorogo sebagai ikon pariwisata unggulan kelas dunia yang berdaya saing global melalui transformasi ekonomi kreatif berbasis generasi muda.',
 '1. Mengintegrasikan teknologi digital dan media sosial interaktif dalam mempromosikan destinasi wisata Ponorogo.\n2. Mengedukasi generasi Z untuk bangga dan aktif melestarikan kearifan lokal Reyog Ponorogo.\n3. Membangun kolaborasi sinergis antara pegiat seni, UMKM lokal, dan pemerintah daerah.',
 'Juara 1 Lomba Orasi Kebangsaan Jawa Timur 2023, Duta Kampus Berprestasi Unmuh Ponorogo 2024, Delegasi Pemuda Pelopor Ponorogo 2025.',
 'Mahasiswa Ilmu Komunikasi yang aktif menggerakkan komunitas literasi budaya dan pelestari kesenian Reyog gaya muda di Kabupaten Ponorogo.', 'aktif', NOW()),

(2, 'K-02', 'Bagas Satria Wijaya', 'Kakang', 'Ponorogo', '2005-08-20', 'SMA Negeri 1 Ponorogo', 'kakang_02.jpg',
 'Mewujudkan pariwisata Ponorogo yang ramah lingkungan, inklusif, dan mengangkat potensi ekowisata alam lereng Gunung Wilis dan Telaga Ngebel.',
 '1. Mengembangkan kampanye pariwisata hijau (Green Tourism) di destinasi unggulan Telaga Ngebel.\n2. Memberdayakan pemuda desa wisata sebagai pramuwisata profesional bersertifikasi.\n3. Mendorong program zero-waste pada setiap festival seni dan budaya di Ponorogo.',
 'Juara 2 Debat Bahasa Inggris Tingkat Karesidenan Madiun 2024, Finalis Olimpiade Geografi Nasional 2024.',
 'Siswa berdedikasi tinggi yang gemar menjelajah alam dan memiliki kemampuan public speaking bilingual yang sangat fasih.', 'aktif', NOW()),

(3, 'K-03', 'Fajar Ramadhan Kusuma', 'Kakang', 'Madiun', '2003-11-10', 'IAIN Ponorogo', 'kakang_03.jpg',
 'Menumbuhkan kesadaran kolektif generasi muda sebagai garda terdepan diplomasi pariwisata religi dan sejarah peninggalan Bumi Reog.',
 '1. Mendokumentasikan dan mempublikasikan sejarah babad Ponorogo dalam format sinematik digital.\n2. Menggagas festival santri berbudaya yang mengkolaborasikan seni Islam dan tradisi lokal.\n3. Memperluas jejaring promosi wisata religi makam Bathara Katong.',
 'Terbaik 1 Duta Bahasa Kabupaten Ponorogo 2024, Penulis Buku Antologi Puisi Budaya Ponorogo 2023.',
 'Pribadi yang santun, berwawasan sejarah mendalam, serta aktif mengabdikan diri di sanggar seni budaya tradisional.', 'aktif', NOW()),

(4, 'K-04', 'Gilang Pratama Putra', 'Kakang', 'Ponorogo', '2004-02-18', 'Kecamatan Babadan / Sanggar Singo Menggolo', 'kakang_04.jpg',
 'Membawa seni pertunjukan Reyog Ponorogo diakui secara abadi oleh UNESCO melalui diplomasi budaya aktif dan pemberdayaan seniman muda.',
 '1. Membina generasi muda dalam penguasaan tari jathil, warok, dan pembarong berkualitas prima.\n2. Mengadakan workshop tari tradisional terbuka untuk wisatawan domestik dan mancanegara.\n3. Menguatkan ekosistem pertunjukan panggung bulanan di Alun-Alun Ponorogo.',
 'Juara 1 Festival Reyog Nasional Kategori Pelajar 2023, Pembarong Muda Terbaik Festival Grebeg Suro 2024.',
 'Seniman tari muda berbakat yang memiliki fisik prima dan kepiawaian memainkan Dadak Merak seberat puluhan kilogram dengan penuh karisma.', 'aktif', NOW()),

(5, 'K-05', 'Daffa Ihsan Fadillah', 'Kakang', 'Surabaya', '2005-01-25', 'SMA Negeri 2 Ponorogo', 'kakang_05.jpg',
 'Menjadikan kuliner khas Ponorogo (Sate Ayam Ponorogo, Dawet Jabung) sebagai magnet gastronomi pariwisata terkemuka di tingkat nasional.',
 '1. Membuat panduan digital interaktif destinasi wisata kuliner legendaris Ponorogo.\n2. Mengedukasi higienitas dan packaging premium bagi para pelaku UMKM kuliner tradisional.\n3. Mengadakan festival tahunan Kuliner Nusantara di Ponorogo.',
 'Juara 1 Business Plan Competition Tingkat Provinsi 2024, Duta Kesehatan Remaja 2023.',
 'Pemuda inovatif berjiwa wirausaha yang gigih memperkenalkan cita rasa otentik kuliner warisan leluhur Ponorogo ke kancah digital.', 'aktif', NOW()),

(6, 'K-06', 'Mochammad Ilham Syahputra', 'Kakang', 'Ponorogo', '2003-09-05', 'Dinas Pariwisata Ponorogo / Alumni Unair', 'kakang_06.jpg',
 'Menciptakan pariwisata Ponorogo yang berbasis data (*smart tourism*) dengan infrastruktur digital modern untuk kepuasan wisatawan.',
 '1. Merancang aplikasi panduan wisata virtual Ponorogo berbasis augmented reality.\n2. Mengoptimalkan analisis tren media sosial untuk strategi promosi destinasi wisata.\n3. Menyediakan layanan informasi terintegrasi bagi para pelancong mancanegara.',
 'Lulusan Terbaik Fakultas Ilmu Budaya 2024, Juara Hackathon Tourism Tech Challenge 2023.',
 'Pemuda visioner dengan keahlian perpaduan riset kebudayaan dan teknologi informasi mutakhir.', 'aktif', NOW()),

(7, 'K-07', 'Aditya Eka Prayoga', 'Kakang', 'Ponorogo', '2005-04-12', 'SMK Negeri 1 Ponorogo', 'kakang_07.jpg',
 'Membangun generasi muda Ponorogo yang berkarakter luhur, berbudaya kuat, dan terampil dalam industri hospitality pariwisata.',
 '1. Menyelenggarakan pelatihan standar pelayanan prima bagi pegiat homestay desa wisata.\n2. Mempromosikan sentra kerajinan reyog dan cenderamata khas Ponorogo.\n3. Aktif mengkampanyekan Sapta Pesona di seluruh objek rekreasi Ponorogo.',
 'Siswa Berprestasi Jurusan Perhotelan 2024, Finalis Lomba Cerdas Cermat Pariwisata Jawa Timur.',
 'Memiliki kepribadian ramah, berdedikasi tinggi di bidang perhotelan dan pariwisata ramah keluarga.', 'aktif', NOW()),

(8, 'K-08', 'Tegar Wahyu Wardana', 'Kakang', 'Ponorogo', '2004-12-03', 'Kecamatan Siman', 'kakang_08.jpg',
 'Mengoptimalkan potensi seni bela diri Pencak Silat dan Warok Ponorogo sebagai daya tarik wisata minat khusus yang membanggakan.',
 '1. Mengembangkan paket wisata edukasi silat tradisional Ponorogo.\n2. Membangun sport tourism yang memadukan keindahan alam bukit dan tantangan fisik.\n3. Mengedukasi filosofi luhur Warok sebagai simbol kesatria budiman.',
 'Juara 1 Kejurda Pencak Silat Tingkat Remaja 2023, Juara Umum Festival Budaya Warok 2024.',
 'Atlet muda berprestasi dengan jiwa kepemimpinan tegas dan cinta mendalam pada nilai-nilai kearifan lokal Ponorogo.', 'aktif', NOW());

-- Data Kontestan Senduk (8 Peserta)
INSERT INTO `kontestan` (`id`, `nomor_peserta`, `nama`, `kategori`, `tempat_lahir`, `tanggal_lahir`, `asal_instansi`, `foto`, `visi`, `misi`, `prestasi`, `deskripsi`, `status`, `created_at`) VALUES
(9, 'S-01', 'Naurah Aliyah Zahra', 'Senduk', 'Ponorogo', '2005-03-15', 'SMA Negeri 1 Ponorogo', 'senduk_01.jpg',
 'Menjadi representasi perempuan Ponorogo yang anggun, cerdas, berbudaya, serta mampu menjadi duta pariwisata yang menginspirasi dunia internasional.',
 '1. Mempromosikan keanggunan tari Senduk dan batik motif Reyog Ponorogo di tingkat nasional dan mancanegara.\n2. Mengedukasi kaum perempuan muda mengenai peran strategis pelestarian warisan budaya bangsa.\n3. Menginisiasi program podcast pariwisata inspiratif \'Senduk Bercerita\'.',
 'Juara 1 Duta Genre (Generasi Berencana) Ponorogo 2024, Best Presentation Model United Nations 2024, Juara Pidato Bahasa Inggris 2023.',
 'Gadis cerdas, komunikatif, dan berprestasi akademik gemilang dengan kefasihan berbahasa Inggris dan pembawaan anggun khas putri daerah.', 'aktif', NOW()),

(10, 'S-02', 'Clarissa Putri Maharani', 'Senduk', 'Ponorogo', '2004-07-22', 'Universitas Muhammadiyah Ponorogo', 'senduk_02.jpg',
 'Mewujudkan Ponorogo sebagai destinasi wisata budaya dan kriya unggulan yang mandiri secara ekonomi dan berkelanjutan.',
 '1. Memberdayakan perajin batik canting Ponorogo melalui pemasaran marketplace dan pameran kreatif.\n2. Menjalin kemitraan dengan desainer muda untuk memodernisasi busana adat Ponorogo.\n3. Membina sanggar-sanggar tari anak di pelosok desa.',
 'Juara 1 Festival Tari Tradisional Kreasi Jawa Timur 2023, Putri Kampus Berbakat 2024.',
 'Penari tradisional yang telah menekuni tari jathil dan tari kreasi sejak belia serta aktif mempopulerkan busana batik Ponorogo.', 'aktif', NOW()),

(11, 'S-03', 'Alya Safitri Nurfadilah', 'Senduk', 'Madiun', '2005-10-09', 'SMA Negeri 2 Ponorogo', 'senduk_03.jpg',
 'Membangun pariwisata ramah anak dan keluarga yang edukatif dengan mengedepankan legenda dan folklor lokal Ponorogo.',
 '1. Mengemas cerita rakyat Klono Sewandono dan Dewi Songgolangit menjadi bahan ajar dongeng interaktif di sekolah.\n2. Mengembangkan tur edukasi museum dan sanggar seni bagi siswa sekolah dasar.\n3. Menjadi figur teladan dalam kampanye anti-kekerasan anak dan perempuan.',
 'Juara 1 Lomba Storytelling Tingkat Karesidenan 2024, Duta Literasi Pelajar 2023.',
 'Sosok berhati hangat dengan kepiawaian bercerita yang memikat serta dedikasi luar biasa dalam bidang pendidikan anak usia dini.', 'aktif', NOW()),

(12, 'S-04', 'Dinda Ayu Sekar Wangi', 'Senduk', 'Ponorogo', '2004-04-18', 'Kecamatan Kauman / Sanggar Seni Budaya', 'senduk_04.jpg',
 'Mengangkat keindahan lanskap alam Ponorogo dan kearifan seni tradisional melalui visual storytelling dan sinematografi kreatif.',
 '1. Memproduksi konten video cinematic mengenai pesona Telaga Ngebel, Air Terjun Pletuk, dan Gunung Beruk.\n2. Mengajak para konten kreator muda untuk berkolaborasi mengangkat pariwisata lokal.\n3. Memperkenalkan etika berwisata yang sopan dan bertanggung jawab.',
 'Juara Video Kreatif Pesona Wisata Daerah 2024, Juara 2 Fotografi Budaya Ponorogo 2023.',
 'Kreator konten estetis yang memiliki kepekaan visual tinggi serta menguasai seni tari Jawa klasik dengan gerak gemulai yang mempesona.', 'aktif', NOW()),

(13, 'S-05', 'Tiara Putri Dewanti', 'Senduk', 'Ponorogo', '2003-12-30', 'IAIN Ponorogo', 'senduk_05.jpg',
 'Meneguhkan citra Ponorogo sebagai Kota Santri yang kaya toleransi, luhur dalam budi pekerti, dan kaya akan keindahan destinasi wisata rohani.',
 '1. Membina pramuwisata santri untuk menyambut peziarah dan wisatawan religi dari berbagai pelosok negeri.\n2. Menggelar pentas seni kaligrafi dan musik hadrah bernuansa gamelan Ponoragan.\n3. Mendorong sertifikasi halal bagi seluruh pusat oleh-oleh kuliner Ponorogo.',
 'Juara Musabaqah Syarhil Quran Tingkat Provinsi 2023, Duta Kerukunan Umat Beragama 2024.',
 'Pribadi bersahaja, berakhlak mulia, dengan kecerdasan emosional dan intelektual yang menyejukkan.', 'aktif', NOW()),

(14, 'S-06', 'Rania Zahrani Kusuma', 'Senduk', 'Ponorogo', '2005-02-14', 'SMA Negeri 1 Babadan', 'senduk_06.jpg',
 'Menjadikan desa-desa wisata di Ponorogo sebagai destinasi favorit pelancong melalui inovasi atraksi wisata dan kearifan lokal.',
 '1. Menggali potensi agrowisata buah naga dan durian khas lereng Wilis Ponorogo.\n2. Menyelenggarakan festival layang-layang tradisional bertema karakter Reog di area persawahan terpadu.\n3. Membimbing pemuda desa dalam mengelola akun media sosial desa wisata.',
 'Juara 1 Duta Lingkungan Hidup Tingkat Pelajar 2024, Finalis Olimpiade Biologi Karesidenan.',
 'Gadis enerjik yang penuh inisiatif sosial dan aktif dalam berbagai gerakan pemuda pelestari lingkungan.', 'aktif', NOW()),

(15, 'S-07', 'Kezia Belinda Putri', 'Senduk', 'Ponorogo', '2004-09-17', 'SMK Negeri 1 Ponorogo', 'senduk_07.jpg',
 'Mempromosikan kriya busana tenun dan batik khas Ponorogo menjadi tren busana etnik modern di kalangan anak muda Indonesia.',
 '1. Mengadakan peragaan busana jalanan (Batik Street Carnival) yang meriah di pusat kota Ponorogo.\n2. Menjalin kerja sama dengan butik fashion ternama untuk memasukkan motif khas Ponorogo.\n3. Mengajarkan teknik membatik ramah lingkungan kepada para siswi kejuruan.',
 'Juara 1 Lomba Desain Busana Etnik Jawa Timur 2024, Juara Tata Rias Panggung Pageant 2023.',
 'Gadis berparas rupawan dengan keahlian tata busana, fashion design, dan rasa percaya diri panggung yang luar biasa.', 'aktif', NOW()),

(16, 'S-08', 'Fathia Rahma Azzahra', 'Senduk', 'Ponorogo', '2005-06-28', 'SMA Negeri 3 Ponorogo', 'senduk_08.jpg',
 'Membangun sinergi pemuda untuk melestarikan tradisi lisan, geguritan, dan tembang mocopat Jawa khas Ponoragan.',
 '1. Menginisiasi lomba geguritan dan macapat tingkat pelajar se-Ponorogo.\n2. Menyelenggarakan ruang bincang santai kebudayaan secara berkala di sanggar kota.\n3. Menyajikan informasi budaya dalam bentuk infografis informatif yang menarik bagi milenial.',
 'Juara 1 Lomba Geguritan Tingkat Pelajar Jawa Timur 2024, Juara Cerdas Cermat Bahasa Daerah 2023.',
 'Penyair muda dan pelestari bahasa daerah Jawa dengan artikulasi memikat serta kecintaan mendalam terhadap falsafah leluhur Ponorogo.', 'aktif', NOW());

-- Data Voting Sampel Awal (Sebagai Data Riil Pembuktian Klasemen dan Grafik Berfungsi Sempurna)
-- User 2 (Bima): Memilih Raditya (K-01) & Naurah (S-01)
-- User 3 (Siti): Memilih Raditya (K-01) & Clarissa (S-02)
-- User 4 (Dimas): Memilih Bagas (K-02) & Naurah (S-01)
-- User 5 (Anisa): Memilih Raditya (K-01) & Alya (S-03)
-- User 6 (Rizki): Memilih Gilang (K-04) & Naurah (S-01)
INSERT INTO `voting` (`user_id`, `kontestan_id`, `periode_voting_id`, `kategori`, `created_at`) VALUES
(2, 1, 1, 'Kakang', '2026-10-15 10:20:00'),
(2, 9, 1, 'Senduk', '2026-10-15 10:22:00'),
(3, 1, 1, 'Kakang', '2026-10-16 11:35:00'),
(3, 10, 1, 'Senduk', '2026-10-16 11:38:00'),
(4, 2, 1, 'Kakang', '2026-10-18 14:10:00'),
(4, 9, 1, 'Senduk', '2026-10-18 14:12:00'),
(5, 1, 1, 'Kakang', '2026-10-20 09:05:00'),
(5, 11, 1, 'Senduk', '2026-10-20 09:07:00'),
(6, 4, 1, 'Kakang', '2026-10-22 16:45:00'),
(6, 9, 1, 'Senduk', '2026-10-22 16:48:00');

-- Log Aktivitas Awal
INSERT INTO `activity_logs` (`user_id`, `aktivitas`, `modul`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, 'Inisialisasi sistem dan pembuatan periode pemilihan 2026', 'Periode', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)', NOW()),
(1, 'Unggah data kontestan Kakang dan Senduk 2026', 'Kontestan', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)', NOW()),
(1, 'Aktivasi periode pemilihan Duta Wisata 2026', 'Periode', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)', NOW());

