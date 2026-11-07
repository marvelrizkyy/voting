/**
 * VOTING KAKANG SENDUK PONOROGO — VOTING LOGIC JS
 * Dialog Konfirmasi Modal, Validasi Client, dan Proteksi Double-Submit
 */

document.addEventListener('DOMContentLoaded', function () {
  const voteButtons = document.querySelectorAll('.btn-vote-trigger');
  const confirmModalEl = document.getElementById('voteConfirmModal');
  const voteForm = document.getElementById('formVote');
  const btnSubmitVote = document.getElementById('btnSubmitVote');

  let confirmModal = null;
  if (confirmModalEl && typeof bootstrap !== 'undefined') {
    confirmModal = new bootstrap.Modal(confirmModalEl);
  }

  // Bind click pada setiap tombol "Pilih" kontestan
  voteButtons.forEach(function (btn) {
    btn.addEventListener('click', function (e) {
      e.preventDefault();

      const kontestanId = this.getAttribute('data-id');
      const kontestanNama = this.getAttribute('data-nama');
      const kontestanKategori = this.getAttribute('data-kategori');
      const kontestanNomor = this.getAttribute('data-nomor');
      const kontestanFoto = this.getAttribute('data-foto');

      // Isi elemen modal
      const modalNama = document.getElementById('modalContestantNama');
      const modalKategori = document.getElementById('modalContestantKategori');
      const modalNomor = document.getElementById('modalContestantNomor');
      const modalFoto = document.getElementById('modalContestantFoto');
      const inputId = document.getElementById('inputKontestanId');
      const inputKategori = document.getElementById('inputKategori');

      if (modalNama) modalNama.textContent = kontestanNama;
      if (modalKategori) modalKategori.textContent = 'Kategori ' + kontestanKategori;
      if (modalNomor) modalNomor.textContent = kontestanNomor;
      if (modalFoto) modalFoto.src = kontestanFoto;
      if (inputId) inputId.value = kontestanId;
      if (inputKategori) inputKategori.value = kontestanKategori;

      if (confirmModal) {
        confirmModal.show();
      }
    });
  });

  // Proteksi Double-Submit pada Form Voting
  if (voteForm) {
    voteForm.addEventListener('submit', function () {
      if (btnSubmitVote) {
        btnSubmitVote.disabled = true;
        btnSubmitVote.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Memproses Suara...';
      }
    });
  }
});

