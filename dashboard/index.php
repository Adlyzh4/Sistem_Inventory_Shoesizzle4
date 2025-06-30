<?php
// dashboard/index.php
require_once '../config/database.php';

// if (!isset($_SESSION['user_id'])) {
//     header('Location: ../auth/login.php');
//     exit;
// }

// Query ringkasan
$total_barang = $pdo->query("SELECT COUNT(*) FROM barang WHERE is_active = 1")->fetchColumn();
$total_masuk = $pdo->query("SELECT SUM(jumlah) FROM detail_barang_masuk")->fetchColumn();
$total_keluar = $pdo->query("SELECT SUM(jumlah) FROM detail_barang_keluar")->fetchColumn();
$total_retur = $pdo->query("SELECT SUM(jumlah_retur) FROM detail_retur_barang")->fetchColumn();

?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard Admin - ShoeSizzle</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container-fluid">
    <a class="navbar-brand" href="#">ShoeSizzle</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav me-auto">
        <li class="nav-item"><a class="nav-link" href="../pages/dashboard/index.php">Dashboard</a></li>
        <li class="nav-item"><a class="nav-link" href="../pages/barang/index.php">Barang</a></li>
        <li class="nav-item"><a class="nav-link" href="../pages/stok_masuk/index.php">Stok Masuk</a></li>
        <li class="nav-item"><a class="nav-link" href="../pages/stok_keluar/index.php">Stok Keluar</a></li>
        <li class="nav-item"><a class="nav-link" href="../pages/retur/index.php">Retur</a></li>
        <li class="nav-item"><a class="nav-link" href="../pages/laporan/index.php">Laporan</a></li>
      </ul>
      <ul class="navbar-nav">
        <li class="nav-item">
          <a class="nav-link text-danger" href="../auth/logout.php" onclick="return confirm('Yakin mau logout?')">Logout</a>
        </li>
      </ul>
    </div>
  </div>
</nav>

  <div class="container mt-5">
    <h3 class="mb-4">Dashboard Ringkasan</h3>
    <div class="row">
      <div class="col-md-3">
        <div class="card border-primary mb-3">
          <div class="card-body">
            <h5 class="card-title">Total Barang</h5>
            <p class="card-text display-6"><?= $total_barang ?: 0 ?></p>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card border-success mb-3">
          <div class="card-body">
            <h5 class="card-title">Stok Masuk</h5>
            <p class="card-text display-6"><?= $total_masuk ?: 0 ?></p>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card border-danger mb-3">
          <div class="card-body">
            <h5 class="card-title">Stok Keluar</h5>
            <p class="card-text display-6"><?= $total_keluar ?: 0 ?></p>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card border-warning mb-3">
          <div class="card-body">
            <h5 class="card-title">Total Retur</h5>
            <p class="card-text display-6"><?= $total_retur ?: 0 ?></p>
          </div>
        </div>
      </div>
    </div>
  </div>

<?php include '../includes/footer.php'; ?>
