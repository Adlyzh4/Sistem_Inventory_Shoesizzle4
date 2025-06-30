<?php
// pages/laporan/index.php
session_start();
require_once '../../config/database.php';
require_once '../../vendor/autoload.php'; // buat PHPSpreadsheet nanti

if (!isset($_SESSION['user_id'])) {
  header('Location: ../../auth/login.php');
  exit;
}

$start = $_GET['start'] ?? date('Y-m-01');
$end = $_GET['end'] ?? date('Y-m-d');

// Ambil laporan stok masuk
$stmtMasuk = $pdo->prepare("SELECT sm.*, b.nama_barang FROM barang_masuk sm 
                            JOIN barang b ON sm.barang_id = b.barang_id 
                            WHERE sm.tanggal_masuk BETWEEN ? AND ?");
$stmtMasuk->execute([$start, $end]);
$stokMasuk = $stmtMasuk->fetchAll();

// Ambil laporan stok keluar
$stmtKeluar = $pdo->prepare("SELECT sk.*, b.nama_barang FROM barang_keluar sk 
                              JOIN barang b ON sk.barang_id = b.barang_id 
                              WHERE sk.tanggal_keluar BETWEEN ? AND ?");
$stmtKeluar->execute([$start, $end]);
$stokKeluar = $stmtKeluar->fetchAll();

// Ambil laporan retur
$stmtRetur = $pdo->prepare("SELECT r.*, b.nama_barang FROM retur_barang r 
                            JOIN barang b ON r.barang_id = b.barang_id 
                            WHERE r.tanggal_retur BETWEEN ? AND ?");
$stmtRetur->execute([$start, $end]);
$retur = $stmtRetur->fetchAll();

// Notifikasi stok rendah
$stmtNotif = $pdo->prepare("SELECT * FROM barang WHERE stok_aktual < 5 ORDER BY stok_aktual ASC");
$stmtNotif->execute();
$stokRendah = $stmtNotif->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Laporan Stok - ShoeSizzle</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    
    <?php include '../../includes/navbar.php'; ?>

<div class="container mt-5">
  <h3 class="mb-4">Laporan Stok</h3>
  <form method="GET" class="row g-3 mb-4">
    <div class="col-md-4">
      <label class="form-label">Tanggal Mulai</label>
      <input type="date" name="start" class="form-control" value="<?= $start ?>">
    </div>
    <div class="col-md-4">
      <label class="form-label">Tanggal Akhir</label>
      <input type="date" name="end" class="form-control" value="<?= $end ?>">
    </div>
    <div class="col-md-1 d-grid">
      <label class="form-label">&nbsp;</label>
      <button class="btn btn-primary">Filter</button>
    </div>
    <div class="col-md-1 d-grid">
      <label class="form-label">&nbsp;</label>
      <a href="export_excel.php?start=<?= $start ?>&end=<?= $end ?>" class="btn btn-success">Export Excel</a>
    </div>
    <div class="col-md-1 d-grid">
      <label class="form-label">&nbsp;</label>
      <a href="export_pdf.php?start=<?= $start ?>&end=<?= $end ?>" class="btn btn-danger">Export PDF</a>
    </div>
  </form>

  <?php if (count($stokRendah) > 0): ?>
    <div class="alert alert-warning">
      <strong>⚠️ Notifikasi:</strong> Ada barang dengan stok rendah!
      <ul>
        <?php foreach ($stokRendah as $item): ?>
          <li><?= htmlspecialchars($item['nama_barang']) ?> (Stok: <?= $item['stok'] ?>)</li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <h5 class="mt-4">Stok Masuk</h5>
  <table class="table table-bordered">
    <thead class="table-success">
      <tr>
        <th>#</th>
        <th>Nama Barang</th>
        <th>Jumlah</th>
        <th>Tanggal</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($stokMasuk as $row): ?>
        <tr>
          <td><?= $row['masuk_id'] ?></td>
          <td><?= htmlspecialchars($row['nama_barang']) ?></td>
          <td><?= $row['jumlah'] ?></td>
          <td><?= $row['tanggal_masuk'] ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <h5 class="mt-4">Stok Keluar</h5>
  <table class="table table-bordered">
    <thead class="table-danger">
      <tr>
        <th>#</th>
        <th>Nama Barang</th>
        <th>Jumlah</th>
        <th>Tanggal</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($stokKeluar as $row): ?>
        <tr>
          <td><?= $row['keluar_id'] ?></td>
          <td><?= htmlspecialchars($row['nama_barang']) ?></td>
          <td><?= $row['jumlah'] ?></td>
          <td><?= $row['tanggal_keluar'] ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <h5 class="mt-4">Retur Barang</h5>
  <table class="table table-bordered">
    <thead class="table-warning">
      <tr>
        <th>#</th>
        <th>Nama Barang</th>
        <th>Jumlah</th>
        <th>Alasan</th>
        <th>Tanggal</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($retur as $row): ?>
        <tr>
          <td><?= $row['retur_id'] ?></td>
          <td><?= htmlspecialchars($row['nama_barang']) ?></td>
          <td><?= $row['jumlah'] ?></td>
          <td><?= htmlspecialchars($row['alasan_retur']) ?></td>
          <td><?= $row['tanggal_retur'] ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<?php include '../../includes/footer.php'; ?>
