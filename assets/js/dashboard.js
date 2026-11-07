/**
 * VOTING KAKANG SENDUK PONOROGO — ADMIN DASHBOARD JS
 * Inisialisasi Chart.js dan Interaktivitas Dashboard
 */

document.addEventListener('DOMContentLoaded', function () {
  // Toggle Mobile Sidebar
  const sidebarToggle = document.getElementById('sidebarToggle');
  const adminSidebar = document.querySelector('.admin-sidebar');
  if (sidebarToggle && adminSidebar) {
    sidebarToggle.addEventListener('click', function () {
      adminSidebar.classList.toggle('show');
    });
  }

  // Inisialisasi Grafik Chart.js jika data tersedia
  if (typeof Chart !== 'undefined' && window.dashboardChartData) {
    initDashboardCharts(window.dashboardChartData);
  }
});

function initDashboardCharts(data) {
  // 1. Grafik Batang Perolehan Suara Kakang
  const kakangCanvas = document.getElementById('chartKakang');
  if (kakangCanvas && data.kakang) {
    new Chart(kakangCanvas, {
      type: 'bar',
      data: {
        labels: data.kakang.labels || [],
        datasets: [{
          label: 'Jumlah Suara',
          data: data.kakang.votes || [],
          backgroundColor: 'rgba(36, 63, 115, 0.85)',
          borderColor: '#243F73',
          borderWidth: 1.5,
          borderRadius: 8,
          hoverBackgroundColor: '#D6AE58'
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { display: false },
          tooltip: {
            callbacks: {
              label: function (context) {
                return ` Suara: ${context.raw} vote`;
              }
            }
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: { precision: 0 }
          }
        }
      }
    });
  }

  // 2. Grafik Batang Perolehan Suara Senduk
  const sendukCanvas = document.getElementById('chartSenduk');
  if (sendukCanvas && data.senduk) {
    new Chart(sendukCanvas, {
      type: 'bar',
      data: {
        labels: data.senduk.labels || [],
        datasets: [{
          label: 'Jumlah Suara',
          data: data.senduk.votes || [],
          backgroundColor: 'rgba(214, 174, 88, 0.85)',
          borderColor: '#A67C2E',
          borderWidth: 1.5,
          borderRadius: 8,
          hoverBackgroundColor: '#243F73'
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { display: false },
          tooltip: {
            callbacks: {
              label: function (context) {
                return ` Suara: ${context.raw} vote`;
              }
            }
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: { precision: 0 }
          }
        }
      }
    });
  }
}

