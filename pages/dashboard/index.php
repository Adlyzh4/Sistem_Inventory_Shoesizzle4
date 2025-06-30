<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user_id'])) {
  header('Location: ../../auth/login.php');
  exit;
}

$bulanIniAwal = date('Y-m-01');
$hariIni = date('Y-m-d');

// Total Barang
$totalBarang = $pdo->query("SELECT COUNT(*) FROM barang")->fetchColumn();

// Stok Masuk bulan ini
$stokMasuk = $pdo->prepare("SELECT SUM(jumlah) FROM barang_masuk WHERE tanggal_masuk BETWEEN ? AND ?");
$stokMasuk->execute([$bulanIniAwal, $hariIni]);
$jumlahMasuk = $stokMasuk->fetchColumn() ?? 0;

// Stok Keluar bulan ini
$stokKeluar = $pdo->prepare("SELECT SUM(jumlah) FROM barang_keluar WHERE tanggal_keluar BETWEEN ? AND ?");
$stokKeluar->execute([$bulanIniAwal, $hariIni]);
$jumlahKeluar = $stokKeluar->fetchColumn() ?? 0;

// Retur barang bulan ini
$retur = $pdo->prepare("SELECT SUM(jumlah) FROM retur_barang WHERE tanggal_retur BETWEEN ? AND ?");
$retur->execute([$bulanIniAwal, $hariIni]);
$jumlahRetur = $retur->fetchColumn() ?? 0;

// Chart barang paling banyak keluar
$topBarangStmt = $pdo->query("
  SELECT b.nama_barang, SUM(bk.jumlah) as total_terjual
  FROM barang_keluar bk
  JOIN barang b ON bk.barang_id = b.barang_id
  GROUP BY bk.barang_id
  ORDER BY total_terjual DESC
  LIMIT 10
");

$topBarangData = $topBarangStmt->fetchAll();
$chartLabels = json_encode(array_column($topBarangData, 'nama_barang'));
$chartData = json_encode(array_column($topBarangData, 'total_terjual'));


// Stok rendah
$stokRendah = $pdo->query("SELECT COUNT(*) FROM barang WHERE stok_aktual <= stok_minimum")->fetchColumn();

// Notifikasi terbaru (5 terakhir)
$notifikasi = $pdo->prepare("SELECT * FROM notifikasi WHERE is_read = 0 ORDER BY created_at DESC LIMIT 5");
$notifikasi->execute();
$notifData = $notifikasi->fetchAll();
$jumlahNotif = count($notifData);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Dashboard - ShoeSizzle</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    .notif-badge {
      position: absolute;
      top: 4px;
      right: 8px;
      font-size: 12px;
    }
  </style>
</head>
<body>

<?php include '../../includes/navbar.php'; ?>

<div class="container mt-4">
  <div class="d-flex justify-content-between align-items-center">
    <h2>Dashboard Ringkasan</h2>
    <button class="btn btn-outline-secondary position-relative" data-bs-toggle="modal" data-bs-target="#notifModal">
      🔔
      <?php if ($jumlahNotif > 0): ?>
        <span class="badge bg-danger rounded-circle notif-badge"><?= $jumlahNotif ?></span>
      <?php endif; ?>
    </button>
  </div>

  <div class="row g-4">
    <div class="col-md-3">
      <div class="card text-bg-primary shadow-sm">
        <div class="card-body">
          <h6>Total Barang</h6>
          <h3><?= $totalBarang ?></h3>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card text-bg-success shadow-sm">
        <div class="card-body">
          <h6>Stok Masuk</h6>
          <h3><?= $jumlahMasuk ?></h3>
          <div class="progress mt-2" style="height: 6px;">
            <div class="progress-bar bg-light" role="progressbar" style="width: <?= min(100, $jumlahMasuk) ?>%;"></div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card text-bg-danger shadow-sm">
        <div class="card-body">
          <h6>Stok Keluar</h6>
          <h3><?= $jumlahKeluar ?></h3>
          <div class="progress mt-2" style="height: 6px;">
            <div class="progress-bar bg-light" role="progressbar" style="width: <?= min(100, $jumlahKeluar) ?>%;"></div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card text-bg-warning shadow-sm">
        <div class="card-body">
          <h6>Retur Barang</h6>
          <h3><?= $jumlahRetur ?></h3>
          <div class="progress mt-2" style="height: 6px;">
            <div class="progress-bar bg-dark" role="progressbar" style="width: <?= min(100, $jumlahRetur) ?>%;"></div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="mt-4">
    <div class="alert alert-warning">
      🔔 <strong>Notifikasi:</strong> Ada <?= $stokRendah ?> barang dengan stok rendah!
    </div>
  </div>

  <div class="mt-5">
    <h5 class="mb-3">Grafik Stok Masuk vs Keluar Bulan Ini</h5>
    <canvas id="stokChart" height="100"></canvas>
  </div>

  <div class="mt-5">
  <h5 class="mb-3">Top 10 Barang Paling Banyak Terjual</h5>
  <canvas id="barangTerjualChart" height="100"></canvas>
  </div>

  </div>
</div>

<!-- Modal Notifikasi -->
<div class="modal fade" id="notifModal" tabindex="-1" aria-labelledby="notifModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Notifikasi Terbaru</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
      </div>
      <div class="modal-body">
        <?php if ($jumlahNotif == 0): ?>
          <p class="text-muted text-center">Tidak ada notifikasi baru</p>
        <?php else: ?>
          <ul class="list-group">
            <?php foreach ($notifData as $n): ?>
              <li class="list-group-item">
                <strong><?= htmlspecialchars($n['judul']) ?></strong><br>
                <small><?= htmlspecialchars($n['pesan']) ?></small><br>
                <small class="text-muted"><?= date('d/m/Y H:i', strtotime($n['created_at'])) ?></small>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>


<script>
  const ctx = document.getElementById('stokChart').getContext('2d');
  new Chart(ctx, {
    type: 'bar',
    data: {
      labels: ['Stok Masuk', 'Stok Keluar', 'Retur'],
      datasets: [{
        label: 'Jumlah (unit)',
        data: [<?= $jumlahMasuk ?>, <?= $jumlahKeluar ?>, <?= $jumlahRetur ?>],
        backgroundColor: ['#198754', '#dc3545', '#ffc107']
      }]
    },
    options: {
      responsive: true,
      scales: {
        y: { beginAtZero: true }
      }
    }
  });

  const ctx2 = document.getElementById('barangTerjualChart').getContext('2d');
new Chart(ctx2, {
  type: 'line',
  data: {
    labels: <?= $chartLabels ?>,
    datasets: [{
      label: 'Jumlah Terjual',
      data: <?= $chartData ?>,
      backgroundColor: '#0d6efd'
    }]
  },
  options: {
    responsive: true,
    scales: {
      y: { beginAtZero: true }
    }
  }
});

</script>

<?php include '../../includes/footer.php'; ?>
