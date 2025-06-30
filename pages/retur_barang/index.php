<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user_id'])) {
  header('Location: ../../auth/login.php');
  exit;
}

// Ambil data barang untuk dropdown retur
$barang = $pdo->query("SELECT * FROM barang WHERE is_active = 1")->fetchAll();

// Ambil filter dari GET
$keyword = $_GET['keyword'] ?? '';
$tanggal = $_GET['tanggal'] ?? '';

// Siapkan query retur dengan filter
$query = "SELECT r.*, b.nama_barang FROM retur_barang r 
          JOIN barang b ON r.barang_id = b.barang_id 
          WHERE 1=1";

$params = [];

// Filter berdasarkan keyword
if (!empty($keyword)) {
  $query .= " AND (b.nama_barang LIKE ? OR r.alasan_retur LIKE ?)";
  $like = "%$keyword%";
  $params[] = $like;
  $params[] = $like;
}

// Filter berdasarkan tanggal
if (!empty($tanggal)) {
  $query .= " AND r.tanggal_retur = ?";
  $params[] = $tanggal;
}

$query .= " ORDER BY r.retur_id DESC";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$retur = $stmt->fetchAll();

// Proses tambah retur
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $barang_id = $_POST['barang_id'] ?? 0;
  $jumlah = $_POST['jumlah'] ?? 0;
  $alasan = $_POST['alasan'] ?? '';
  $tanggal = $_POST['tanggal_retur'] ?? date('Y-m-d');

  $pdo->beginTransaction();
  try {
    $stmt = $pdo->prepare("INSERT INTO retur_barang (barang_id, jumlah, alasan_retur, tanggal_retur) VALUES (?, ?, ?, ?)");
    $stmt->execute([$barang_id, $jumlah, $alasan, $tanggal]);

    $stmt = $pdo->prepare("UPDATE barang SET stok_aktual = stok_aktual - ? WHERE barang_id = ?");
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
  <title>Retur Barang - ShoeSizzle</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<?php include '../../includes/navbar.php'; ?>

<div class="container mt-5">
  <h3 class="mb-4">Retur Barang ke Distributor</h3>

  <!-- FORM FILTER PENCARIAN -->
  <form method="GET" class="row g-3 mb-4">
    <div class="col-md-4">
      <label for="keyword" class="form-label">Cari Nama Barang / Alasan</label>
      <input type="text" name="keyword" id="keyword" class="form-control" placeholder="Contoh: Sneaker / rusak" value="<?= htmlspecialchars($keyword) ?>">
    </div>
    <div class="col-md-3">
      <label for="tanggal" class="form-label">Tanggal Retur</label>
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

  <!-- FORM TAMBAH RETUR -->
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
      <label for="jumlah" class="form-label">Jumlah Retur</label>
      <input type="number" class="form-control" name="jumlah" required>
    </div>
    <div class="col-md-3">
      <label for="tanggal_retur" class="form-label">Tanggal</label>
      <input type="date" class="form-control" name="tanggal_retur" value="<?= date('Y-m-d') ?>">
    </div>
    <div class="col-md-6">
      <label for="alasan" class="form-label">Alasan Retur</label>
      <textarea name="alasan" class="form-control" rows="2" placeholder="Contoh: tidak laku, rusak, model lama..." required></textarea>
    </div>
    <div class="col-md-2 d-grid">
      <label class="form-label">&nbsp;</label>
      <button class="btn btn-warning" type="submit">Retur</button>
    </div>
  </form>

  <!-- TABEL RETUR -->
  <table class="table table-bordered">
    <thead class="table-dark">
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
