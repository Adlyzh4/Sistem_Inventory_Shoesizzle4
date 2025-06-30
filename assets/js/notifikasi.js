document.addEventListener('DOMContentLoaded', () => {
  const notifBtn = document.getElementById('notifBtn');
  const notifContainer = document.getElementById('notifContainer');

  notifBtn.addEventListener('click', () => {
    fetch('/shoesizzle/api/get_notifikasi.php')

      .then(res => res.json())
      .then(data => {
        notifContainer.innerHTML = '';

        if (data.length === 0) {
          notifContainer.innerHTML = '<div class="text-center text-muted">Tidak ada notifikasi baru.</div>';
          return;
        }

        data.forEach(n => {
          const item = document.createElement('div');
          item.classList.add('alert', `alert-${getAlertClass(n.jenis)}`, 'd-flex', 'justify-content-between', 'align-items-start');
          item.innerHTML = `
            <div>
              <strong>${n.judul}</strong><br>
              <small>${n.pesan}</small>
              <div class="text-muted mt-1"><small>${n.created_at}</small></div>
            </div>
          `;
          notifContainer.appendChild(item);
        });

        // Update status jadi sudah dibaca
        fetch('/shoesizzle/api/update_notifikasi_read.php', { method: 'POST' });
      })
      .catch(err => {
        notifContainer.innerHTML = '<div class="text-danger">Gagal memuat notifikasi.</div>';
        console.error(err);
      });
  });

  function getAlertClass(jenis) {
    switch (jenis) {
      case 'success': return 'success';
      case 'warning': return 'warning';
      case 'error': return 'danger';
      default: return 'info';
    }
  }
});
