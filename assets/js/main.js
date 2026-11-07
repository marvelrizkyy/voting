/**
 * VOTING KAKANG SENDUK PONOROGO — MAIN JS
 * Logika Filter Kontestan, Countdown Timer, dan Interaksi UI
 */

document.addEventListener('DOMContentLoaded', function () {
  // 1. Inisialisasi Countdown Timer Periode Voting
  initCountdownTimer();

  // 2. Inisialisasi Filter dan Pencarian Kontestan
  initContestantFilter();

  // 3. Navbar Scroll Effect
  initNavbarScroll();

  // 4. Auto Dismiss Alert Bootstrap setelah 5 detik
  setTimeout(function () {
    const alerts = document.querySelectorAll('.alert-dismissible');
    alerts.forEach(function (alert) {
      if (typeof bootstrap !== 'undefined' && bootstrap.Alert) {
        const bsAlert = new bootstrap.Alert(alert);
        bsAlert.close();
      }
    });
  }, 5000);
});

/**
 * Logika Hitung Mundur Periode Voting
 */
function initCountdownTimer() {
  const countdownEl = document.getElementById('votingCountdown');
  if (!countdownEl) return;

  const targetDateStr = countdownEl.getAttribute('data-target-date');
  if (!targetDateStr) return;

  const targetDate = new Date(targetDateStr).getTime();
  if (isNaN(targetDate)) return;

  const daysVal = document.getElementById('cdDays');
  const hoursVal = document.getElementById('cdHours');
  const minutesVal = document.getElementById('cdMinutes');
  const secondsVal = document.getElementById('cdSeconds');

  function updateTimer() {
    const now = new Date().getTime();
    const distance = targetDate - now;

    if (distance <= 0) {
      if (daysVal) daysVal.innerText = '00';
      if (hoursVal) hoursVal.innerText = '00';
      if (minutesVal) minutesVal.innerText = '00';
      if (secondsVal) secondsVal.innerText = '00';
      const statusBadge = document.getElementById('cdStatusBadge');
      if (statusBadge) {
        statusBadge.innerText = 'Voting Ditutup';
        statusBadge.className = 'badge bg-danger text-white px-3 py-2';
      }
      return;
    }

    const days = Math.floor(distance / (1000 * 60 * 60 * 24));
    const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
    const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
    const seconds = Math.floor((distance % (1000 * 60)) / 1000);

    if (daysVal) daysVal.innerText = String(days).padStart(2, '0');
    if (hoursVal) hoursVal.innerText = String(hours).padStart(2, '0');
    if (minutesVal) minutesVal.innerText = String(minutes).padStart(2, '0');
    if (secondsVal) secondsVal.innerText = String(seconds).padStart(2, '0');
  }

  updateTimer();
  setInterval(updateTimer, 1000);
}

/**
 * Filter Kategori dan Pencarian Kontestan secara Live
 */
function initContestantFilter() {
  const filterButtons = document.querySelectorAll('.filter-tab-btn');
  const searchInput = document.getElementById('searchContestant');
  const contestantCards = document.querySelectorAll('.contestant-item');
  const emptyState = document.getElementById('contestantEmptyState');

  if (filterButtons.length === 0 && !searchInput) return;

  let currentCategory = 'all';
  let currentKeyword = '';

  function applyFilters() {
    let visibleCount = 0;

    contestantCards.forEach(function (card) {
      const category = (card.getAttribute('data-kategori') || '').toLowerCase();
      const name = (card.getAttribute('data-nama') || '').toLowerCase();
      const instansi = (card.getAttribute('data-instansi') || '').toLowerCase();
      const nomor = (card.getAttribute('data-nomor') || '').toLowerCase();

      const matchCategory = currentCategory === 'all' || category === currentCategory;
      const matchSearch =
        currentKeyword === '' ||
        name.includes(currentKeyword) ||
        instansi.includes(currentKeyword) ||
        nomor.includes(currentKeyword);

      if (matchCategory && matchSearch) {
        card.style.display = '';
        visibleCount++;
      } else {
        card.style.display = 'none';
      }
    });

    if (emptyState) {
      emptyState.style.display = visibleCount === 0 ? 'block' : 'none';
    }
  }

  filterButtons.forEach(function (btn) {
    btn.addEventListener('click', function () {
      filterButtons.forEach(b => b.classList.remove('active'));
      this.classList.add('active');
      currentCategory = (this.getAttribute('data-filter') || 'all').toLowerCase();
      applyFilters();
    });
  });

  if (searchInput) {
    searchInput.addEventListener('input', function () {
      currentKeyword = this.value.trim().toLowerCase();
      applyFilters();
    });
  }
}

/**
 * Efek Navbar pada saat scroll
 */
function initNavbarScroll() {
  const navbar = document.querySelector('.main-navbar');
  if (!navbar) return;

  window.addEventListener('scroll', function () {
    if (window.scrollY > 40) {
      navbar.classList.add('shadow-lg');
      navbar.style.backgroundColor = 'rgba(16, 27, 53, 0.98)';
    } else {
      navbar.classList.remove('shadow-lg');
      navbar.style.backgroundColor = 'rgba(16, 27, 53, 0.95)';
    }
  });
}

