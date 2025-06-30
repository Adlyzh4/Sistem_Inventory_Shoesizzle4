<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user_id'])) {
  header('Location: ../../auth/login.php');
  exit;
}

// Ambil filter dari GET
$keyword = $_GET['keyword'] ?? '';
$tanggal = $_GET['tanggal'] ?? '';

// Siapkan query stok masuk dengan filter
$query = "SELECT s.*, b.nama_barang FROM barang_masuk s 
          JOIN barang b ON s.barang_id = b.barang_id 
          WHERE 1=1";
$params = [];

// Filter berdasarkan nama barang
if (!empty($keyword)) {
  $query .= " AND b.nama_barang LIKE ?";
  $params[] = '%' . $keyword . '%';
}

// Filter berdasarkan tanggal masuk
if (!empty($tanggal)) {
  $query .= " AND s.tanggal_masuk = ?";
  $params[] = $tanggal;
}

$query .= " ORDER BY s.masuk_id DESC";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$stokMasuk = $stmt->fetchAll();

// Ambil data barang aktif
$barang = $pdo->query("SELECT * FROM barang WHERE is_active = 1")->fetchAll();

// Proses tambah stok masuk
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $barang_id = $_POST['barang_id'] ?? 0;
  $transaksi = $_POST['nomor_transaksi'] ?? 0;
  $jumlah = $_POST['jumlah'] ?? 0;
  $tanggal = $_POST['tanggal_masuk'] ?? date('Y-m-d');

  $pdo->beginTransaction();
  try {
    $stmt = $pdo->prepare("INSERT INTO barang_masuk (barang_id, nomor_transaksi, jumlah, tanggal_masuk) VALUES (?, ?, ?, ?)");
    $stmt->execute([$barang_id, $transaksi, $jumlah, $tanggal]);

    $stmt = $pdo->prepare("UPDATE barang SET stok_aktual = stok_aktual + ? WHERE barang_id = ?");
    $stmt->execute([$jumlah, $barang_id]);

    $pdo->commit();
    header('Location: index.php');
    exit;
  } catch (Exception $e) {
    $pdo->rollBack();
    echo "Terjadi kesalahan: " . $e->getMessage();
  }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Stok Masuk - ShoeSizzle</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<?php include '../../includes/navbar.php'; ?>

<div class="container mt-5">
  <h3 class="mb-4">Stok Masuk</h3>

  <!-- Form Filter -->
  <form method="GET" class="row g-3 mb-4">
    <div class="col-md-4">
      <label for="keyword" class="form-label">Cari Nama Barang</label>
      <input type="text" name="keyword" id="keyword" class="form-control" placeholder="Contoh: Sneakers" value="<?= htmlspecialchars($keyword) ?>">
    </div>
    <div class="col-md-3">
      <label for="tanggal" class="form-label">Tanggal Masuk</label>
      <input type="date" name="tanggal" id="tanggal" class="form-control" value="<?= htmlspecialchars($tanggal) ?>">
    </div>
    <div class="col-md-2 d-grid align-items-end">
      <label class="form-label">&nbsp;</label>
      <button class="btn btn-primary" type="submit">Filter</button>
    </div>
    <div class="col-md-2 d-grid align-items-end">
      <label class="form-label">&nbsp;</label>
      <a href="index.php" class="btn btn-secondary">Reset</a>
    </div>
  </form>

  <!-- Form Tambah Stok Masuk -->
  <form method="POST" class="row g-3 mb-4">
    <div class="col-md-4">
      <label for="barang_id" class="form-label">Pilih Barang</label>
      <select name="barang_id" id="barang_id" class="form-select" required>
        <option value="">-- Pilih Barang --</option>
        <?php foreach ($barang as $b): ?>
          <option value="<?= $b['barang_id'] ?>"><?= htmlspecialchars($b['nama_barang']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-3">
      <label for="nomor_transaksi" class="form-label">Nomor Transaksi</label>
      <input type="text" class="form-control" name="nomor_transaksi" required>
    </div>
    <div class="col-md-3">
      <label for="jumlah" class="form-label">Jumlah Masuk</label>
      <input type="number" class="form-control" name="jumlah" required>
    </div>
    <div class="col-md-3">
      <label for="tanggal_masuk" class="form-label">Tanggal</label>
      <input type="date" class="form-control" name="tanggal_masuk" value="<?= date('Y-m-d') ?>">
    </div>
    <div class="col-md-2 d-grid">
      <label class="form-label">&nbsp;</label>
      <button class="btn btn-success" type="submit">Tambah</button>
    </div>
  </form>

  <!-- Tabel Data -->
  <table class="table table-bordered">
    <thead class="table-dark">
      <tr>
        <th>#</th>
        <th>Nama Barang</th>
        <th>Nomor Transaksi</th>
        <th>Jumlah Masuk</th>
        <th>Tanggal</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($stokMasuk as $row): ?>
        <tr>
          <td><?= $row['masuk_id'] ?></td>
          <td><?= htmlspecialchars($row['nama_barang']) ?></td>
          <td><?= $row['nomor_transaksi'] ?></td>
          <td><?= $row['jumlah'] ?></td>
          <td><?= $row['tanggal_masuk'] ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<?php include '../../includes/footer.php'; ?>
