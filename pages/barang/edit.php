<?php
// pages/barang/edit.php
session_start();
require_once '../../config/database.php';
require_once '../../includes/functions.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../auth/login.php');
    exit;
}

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: index.php');
    exit;
}

// Ambil data barang
$stmt = $pdo->prepare("SELECT * FROM barang WHERE barang_id = ? AND is_active = 1");
$stmt->execute([$id]);
$barang = $stmt->fetch();
if (!$barang) {
    header('Location: index.php');
    exit;
}

$kategori = $pdo->query("SELECT * FROM kategori ")->fetchAll();
$merek = $pdo->query("SELECT * FROM merek ")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kode = $_POST['kode_barang'] ?? '';
    $nama = $_POST['nama_barang'] ?? '';
    $kategori_id = $_POST['kategori_id'] ?? '';
    $merek_id = $_POST['merek_id'] ?? '';
    $ukuran = $_POST['ukuran'] ?? '';
    $warna = $_POST['warna'] ?? '';
    $harga_beli = $_POST['harga_beli'] ?? '';
    $harga_jual = $_POST['harga_jual'] ?? '';
    $stok_minimum = $_POST['stok_minimum'] ?? '';
    $stok_aktual = $_POST['stok_aktual'] ?? '';

    $stmt = $pdo->prepare("UPDATE barang SET kode_barang=?, nama_barang=?, kategori_id=?, merek_id=?, ukuran=?, warna=?, harga_beli=?, harga_jual=?, stok_minimum=?, stok_aktual=? WHERE barang_id = ?");
    $stmt->execute([$kode, $nama, $kategori_id, $merek_id, $ukuran, $warna, $harga_beli, $harga_jual, $stok_minimum, $stok_aktual, $id]);


    // Catat aktivitas admin
        logAktivitas($pdo, $_SESSION['user_id'], "Mengedit barang: $nama");

    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Barang - ShoeSizzle</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<?php include '../../includes/navbar.php'; ?>

<div class="container mt-5">
  <h3 class="mb-4">Edit Barang</h3>
  <form method="POST">
    <div class="mb-3">
      <label for="kode_barang" class="form-label">Kode Barang</label>
      <input type="text" class="form-control" id="kode_barang" name="kode_barang" value="<?= htmlspecialchars($barang['kode_barang']) ?>" required>
    </div>
    <div class="mb-3">
      <label for="nama_barang" class="form-label">Nama Barang</label>
      <input type="text" class="form-control" id="nama_barang" name="nama_barang" value="<?= htmlspecialchars($barang['nama_barang']) ?>" required>
    </div>
    <div class="mb-3">
      <label for="kategori_id" class="form-label">Kategori</label>
      <select class="form-control" id="kategori_id" name="kategori_id" required>
        <option value="">-- Pilih Kategori --</option>
        <?php foreach ($kategori as $k): ?>
          <option value="<?= $k['kategori_id'] ?>" <?= $barang['kategori_id'] == $k['kategori_id'] ? 'selected' : '' ?>><?= htmlspecialchars($k['nama_kategori']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="mb-3">
      <label for="merek_id" class="form-label">Merek</label>
      <select class="form-control" id="merek_id" name="merek_id" required>
        <option value="">-- Pilih Merek --</option>
        <?php foreach ($merek as $m): ?>
          <option value="<?= $m['merek_id'] ?>" <?= $barang['merek_id'] == $m['merek_id'] ? 'selected' : '' ?>><?= htmlspecialchars($m['nama_merek']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="mb-3">
      <label for="ukuran" class="form-label">Ukuran</label>
      <input type="text" class="form-control" id="ukuran" name="ukuran" value="<?= $barang['ukuran'] ?>" required>
    </div>
    <div class="mb-3">
      <label for="warna" class="form-label">Warna</label>
      <input type="text" class="form-control" id="warna" name="warna" value="<?= $barang['warna'] ?>" required>
    </div>
    <div class="mb-3">
      <label for="harga_beli" class="form-label">Harga Beli</label>
      <input type="number" class="form-control" id="harga_beli" name="harga_beli" value="<?= $barang['harga_beli'] ?>" required>
    </div>
    <div class="mb-3">
      <label for="harga_jual" class="form-label">Harga Jual</label>
      <input type="number" class="form-control" id="harga_jual" name="harga_jual" value="<?= $barang['harga_jual'] ?>" required>
    </div>
    <div class="mb-3">
      <label for="stok_minimum" class="form-label">Stok Minimum</label>
      <input type="number" class="form-control" id="stok_minimum" name="stok_minimum" value="<?= $barang['stok_minimum'] ?>" required>
    </div>
    <div class="mb-3">
      <label for="stok_aktual" class="form-label">Stok</label>
      <input type="number" class="form-control" id="stok_aktual" name="stok_aktual" value="<?= $barang['stok_aktual'] ?>" required>
    </div>
    <button type="submit" class="btn btn-primary">Update</button>
    <a href="index.php" class="btn btn-secondary">Batal</a>
  </form>
</div>

<?php include '../../includes/footer.php'; ?>
