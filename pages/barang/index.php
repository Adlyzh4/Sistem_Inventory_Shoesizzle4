<?php
// pages/barang/index.php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../auth/login.php');
    exit;
}

// Ambil data kategori & merek untuk filter
$kategori = $pdo->query("SELECT * FROM kategori")->fetchAll();
$merek = $pdo->query("SELECT * FROM merek")->fetchAll();

// Ambil filter dari form
$filter_kategori = $_GET['kategori_id'] ?? '';
$filter_merek = $_GET['merek_id'] ?? '';
$keyword = $_GET['keyword'] ?? '';

// Query barang dinamis
$query = "SELECT b.*, k.nama_kategori, m.nama_merek FROM barang b
          LEFT JOIN kategori k ON b.kategori_id = k.kategori_id
          LEFT JOIN merek m ON b.merek_id = m.merek_id
          WHERE b.is_active = 1";

$params = [];

// Tambahkan filter pencarian
if (!empty($keyword)) {
    $query .= " AND (
        b.barang_id LIKE ? OR 
        b.kode_barang LIKE ? OR 
        b.nama_barang LIKE ?
    )";
    $keywordLike = "%$keyword%";
    array_push($params, $keywordLike, $keywordLike, $keywordLike);
}

// Filter kategori
if (!empty($filter_kategori)) {
    $query .= " AND b.kategori_id = ?";
    $params[] = $filter_kategori;
}

// Filter merek
if (!empty($filter_merek)) {
    $query .= " AND b.merek_id = ?";
    $params[] = $filter_merek;
}

$query .= " ORDER BY b.barang_id DESC";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$barang = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manajemen Barang - ShoeSizzle</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include '../../includes/navbar.php'; ?>

<div class="container mt-5">
  <h3 class="mb-4">Manajemen Barang</h3>

  <form method="GET" class="row g-3 mb-4">
    <div class="col-md-3">
      <label for="keyword" class="form-label">Cari Barang (ID / Kode / Nama)</label>
      <input type="text" name="keyword" id="keyword" class="form-control" placeholder="Contoh: 1 / KD123 / Sneaker" value="<?= htmlspecialchars($keyword) ?>">
    </div>
    <div class="col-md-3">
      <label for="kategori_id" class="form-label">Filter Kategori</label>
      <select name="kategori_id" id="kategori_id" class="form-select">
        <option value="">-- Semua Kategori --</option>
        <?php foreach ($kategori as $k): ?>
          <option value="<?= $k['kategori_id'] ?>" <?= $filter_kategori == $k['kategori_id'] ? 'selected' : '' ?>>
            <?= htmlspecialchars($k['nama_kategori']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-3">
      <label for="merek_id" class="form-label">Filter Merek</label>
      <select name="merek_id" id="merek_id" class="form-select">
        <option value="">-- Semua Merek --</option>
        <?php foreach ($merek as $m): ?>
          <option value="<?= $m['merek_id'] ?>" <?= $filter_merek == $m['merek_id'] ? 'selected' : '' ?>>
            <?= htmlspecialchars($m['nama_merek']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-3 d-flex align-items-end">
      <button type="submit" class="btn btn-primary me-2">Terapkan</button>
      <a href="index.php" class="btn btn-secondary">Reset</a>
    </div>
  </form>

  <a href="tambah.php" class="btn btn-success mb-3">+ Tambah Barang</a>

  <table class="table table-bordered table-striped">
    <thead class="table-dark">
      <tr>
        <th>ID</th>
        <th>Kode Barang</th>
        <th>Nama</th>
        <th>Kategori</th>
        <th>Merek</th>
        <th>Ukuran</th>
        <th>Warna</th>
        <th>Harga Beli</th>
        <th>Harga Jual</th>
        <th>Stok</th>
        <th>Stok Minimum</th>
        <th>Aksi</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($barang as $row): ?>
        <tr>
          <td><?= $row['barang_id'] ?></td>
          <td><?= $row['kode_barang'] ?></td>
          <td><?= htmlspecialchars($row['nama_barang']) ?></td>
          <td><?= htmlspecialchars($row['nama_kategori']) ?></td>
          <td><?= htmlspecialchars($row['nama_merek']) ?></td>
          <td><?= htmlspecialchars($row['ukuran']) ?></td>
          <td><?= htmlspecialchars($row['warna']) ?></td>
          <td>Rp<?= number_format($row['harga_beli'], 0, ',', '.') ?></td>
          <td>Rp<?= number_format($row['harga_jual'], 0, ',', '.') ?></td>
          <td><?= $row['stok_aktual'] ?></td>
          <td><?= $row['stok_minimum'] ?></td>
          <td>
            <a href="edit.php?id=<?= $row['barang_id'] ?>" class="btn btn-sm btn-primary">Edit</a>
            <a href="hapus.php?id=<?= $row['barang_id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin hapus?')">Hapus</a>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<?php include '../../includes/footer.php'; ?>
