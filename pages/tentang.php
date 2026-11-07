<?php
/**
 * Halaman Tentang Kegiatan & Sejarah Kakang Senduk
 * Sistem Informasi Pemilihan Duta Wisata Kakang Senduk Ponorogo
 */

$page_title = 'Tentang Kakang Senduk';
$active_nav = 'tentang';

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<!-- Header Banner -->
<div class="py-5" style="background: radial-gradient(circle at 80% 20%, #243F73 0%, #101B35 70%); color: white;">
  <div class="container py-3">
    <div class="row align-items-center">
      <div class="col-lg-8">
        <span class="badge" style="background: rgba(214, 174, 88, 0.2); color: #F2D07C; border: 1px solid rgba(214, 174, 88, 0.4);">
          Mengenal Duta Wisata Bumi Reog
        </span>
        <h1 class="font-serif text-white mt-2 mb-2">Tentang Kakang Senduk Ponorogo</h1>
        <p class="text-light opacity-75 mb-0">Tradisi, kearifan luhur, dan komitmen memajukan potensi pariwisata Kabupaten Ponorogo.</p>
      </div>
    </div>
  </div>
</div>

<div class="container py-5">
  <div class="row g-5 align-items-center mb-5">
    <div class="col-lg-6">
      <span class="text-gold fw-bold text-uppercase small tracking-wider">Sejarah & Filosofi</span>
      <h2 class="font-serif text-navy mt-1 mb-3">Warisan Luhur Gerbang Kebudayaan Ponorogo</h2>
      <p class="text-secondary leading-relaxed">
        Pemilihan Kakang Senduk Ponorogo merupakan ajang pemilihan Duta Wisata resmi Kabupaten Ponorogo yang diselenggarakan oleh Pemerintah Kabupaten Ponorogo melalui Dinas Kebudayaan, Pariwisata, Pemuda dan Olahraga.
      </p>
      <p class="text-secondary leading-relaxed">
        Sebutan <strong>"Kakang"</strong> dan <strong>"Senduk"</strong> berakar dari dialek keakraban masyarakat Ponorogo, di mana <em>Kakang</em> merepresentasikan figur kesatria muda pelindung yang berani, berwibawa, dan berbudi pekerti luhur sebagaimana falsafah Warok. Sementara <em>Senduk</em> adalah panggilan penuh kasih bagi putri daerah yang anggun, cerdas, berhati mulia, dan menjunjung tinggi adat ketimuran sebagaimana keelokan putri Dewi Songgolangit.
      </p>
    </div>

    <div class="col-lg-6">
      <div class="p-4 rounded-4 shadow-sm" style="background: linear-gradient(135deg, #101B35, #243F73); color: white;">
        <h4 class="font-serif text-gold mb-3"><i class="bi bi-gem me-2"></i>Tiga Pilar Karakter Duta</h4>
        <div class="d-flex flex-column gap-3">
          <div class="d-flex gap-3">
            <div class="fs-4 text-gold"><i class="bi bi-1-circle-fill"></i></div>
            <div>
              <h6 class="fw-bold text-light mb-1">Brain (Kecerdasan & Wawasan)</h6>
              <p class="small text-light opacity-75 mb-0">Memiliki wawasan mendalam tentang sejarah kebudayaan Reog Ponorogo, geografi ekowisata, dan potensi ekonomi kreatif.</p>
            </div>
          </div>
          <div class="d-flex gap-3">
            <div class="fs-4 text-gold"><i class="bi bi-2-circle-fill"></i></div>
            <div>
              <h6 class="fw-bold text-light mb-1">Beauty / Behavior (Keanggunan & Karakter)</h6>
              <p class="small text-light opacity-75 mb-0">Menunjukkan etika santun, tata krama Jawa luhur, dan pembawaan diri yang memancarkan pesona khas daerah.</p>
            </div>
          </div>
          <div class="d-flex gap-3">
            <div class="fs-4 text-gold"><i class="bi bi-3-circle-fill"></i></div>
            <div>
              <h6 class="fw-bold text-light mb-1">Brave (Keberanian & Kepeloporan)</h6>
              <p class="small text-light opacity-75 mb-0">Berani menjadi duta terdepan, memimpin inovasi kepemudaan, dan mempromosikan Ponorogo hingga kancah internasional.</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Destinasi Unggulan Wisata Ponorogo -->
  <div class="text-center max-w-700 mx-auto my-5">
    <span class="text-gold fw-bold text-uppercase small tracking-wider">Potensi Pariwisata</span>
    <h2 class="font-serif text-navy mt-1">Pesona Bumi Reog yang Dipromosikan</h2>
    <p class="text-secondary small">Para duta terpilih bertugas memperkenalkan magnet pariwisata Ponorogo ke penjuru nusantara.</p>
  </div>

  <div class="row g-4 mb-5">
    <div class="col-md-4">
      <div class="card border-0 rounded-4 shadow-sm h-100 p-4 bg-white">
        <div class="fs-1 text-gold mb-3"><i class="bi bi-water"></i></div>
        <h5 class="font-serif text-navy">Telaga Ngebel</h5>
        <p class="small text-secondary mb-0">Danau alami di lereng Gunung Wilis berketinggian 734 mdpl dengan hawa sejuk, pemandangan memukau, dan tradisi Larung Sesaji 1 Suro yang melegenda.</p>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card border-0 rounded-4 shadow-sm h-100 p-4 bg-white">
        <div class="fs-1 text-gold mb-3"><i class="bi bi-stars"></i></div>
        <h5 class="font-serif text-navy">Seni Reog Ponorogo</h5>
        <p class="small text-secondary mb-0">Mahakarya seni tari pertunjukan adiluhung dengan topeng Dadak Merak seberat 50 kg yang telah diakui dunia dan menjadi identitas kebanggaan bangsa.</p>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card border-0 rounded-4 shadow-sm h-100 p-4 bg-white">
        <div class="fs-1 text-gold mb-3"><i class="bi bi-cup-hot"></i></div>
        <h5 class="font-serif text-navy">Kuliner Khas Legendaris</h5>
        <p class="small text-secondary mb-0">Kelezatan Sate Ayam Ponorogo dengan bumbu kacang lembut, Dawet Jabung khas Kauman, dan Jenang Mirah yang manis menggoda selera para pelancong.</p>
      </div>
    </div>
  </div>

  <!-- Call to action -->
  <div class="p-5 rounded-4 text-center bg-navy text-white">
    <h3 class="font-serif text-gold mb-3">Mari Sukseskan Pemilihan Kakang Senduk 2026</h3>
    <p class="text-light opacity-75 max-w-700 mx-auto mb-4">
      Partisipasi Anda sebagai pemilih sangat berharga untuk melahirkan duta wisata yang kredibel, aspiratif, dan membanggakan masyarakat Ponorogo.
    </p>
    <a href="<?= BASE_URL ?>pages/voting.php" class="btn btn-gold btn-lg px-4">
      <i class="bi bi-check2-circle me-2"></i> Ikut Memberikan Suara
    </a>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

