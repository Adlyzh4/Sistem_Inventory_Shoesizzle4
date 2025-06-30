<?php
// pages/barang/tambah.php
session_start();
require_once '../../config/database.php';
require_once '../../includes/functions.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../auth/login.php');
    exit;
}

// Ambil data kategori & merek
$kategori = $pdo->query("SELECT * FROM kategori")->fetchAll();
$merek = $pdo->query("SELECT * FROM merek")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kode = $_POST['kode_barang'] ?? '';
    $nama = $_POST['nama_barang'] ?? '';
    $kategori_id = $_POST['kategori_id'] ?? '';
    $merek_id = $_POST['merek_id'] ?? '';
    $ukuran = $_POST['ukuran'] ?? '';
    $warna = $_POST['warna'] ?? '';
    $harga_beli = $_POST['harga_beli'] ?? 0;
    $harga_jual = $_POST['harga_jual'] ?? 0;
    $stok = $_POST['stok'] ?? 0;
    $stok_minimum = $_POST['stok_minimum'] ?? 0;

    // Validasi simple
    if ($kode && $nama && $kategori_id && $merek_id && $harga_beli >= 0 && $harga_jual >= 0 && $stok >= 0 && $stok_minimum >= 0) {
        $stmt = $pdo->prepare("INSERT INTO barang 
            (kode_barang, nama_barang, kategori_id, merek_id, ukuran, warna, harga_beli, harga_jual, stok_aktual, stok_minimum, is_active) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)");
        $stmt->execute([$kode, $nama, $kategori_id, $merek_id, $ukuran, $warna, $harga_beli, $harga_jual, $stok, $stok_minimum]);

        // Catat aktivitas admin
        logAktivitas($pdo, $_SESSION['user_id'], "Menambahkan barang: $nama");

        header('Location: index.php');
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Tambah Barang - ShoeSizzle</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<?php include '../../includes/navbar.php'; ?>

<div class="container mt-5">
  <h3 class="mb-4">Tambah Barang</h3>
  <form method="POST">
    <div class="mb-3">
      <label for="kode_barang" class="form-label">Kode Barang</label>
      <input type="text" class="form-control" id="kode_barang" name="kode_barang" required>
    </div>
    <div class="mb-3">
      <label for="nama_barang" class="form-label">Nama Barang</label>
      <input type="text" class="form-control" id="nama_barang" name="nama_barang" required>
    </div>
    <div class="mb-3">
      <label for="kategori_id" class="form-label">Kategori</label>
      <select class="form-control" id="kategori_id" name="kategori_id" required>
        <option value="">-- Pilih Kategori --</option>
        <?php foreach ($kategori as $k): ?>
          <option value="<?= $k['kategori_id'] ?>"><?= htmlspecialchars($k['nama_kategori']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="mb-3">
      <label for="merek_id" class="form-label">Merek</label>
      <select class="form-control" id="merek_id" name="merek_id" required>
        <option value="">-- Pilih Merek --</option>
        <?php foreach ($merek as $m): ?>
          <option value="<?= $m['merek_id'] ?>"><?= htmlspecialchars($m['nama_merek']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="mb-3">
      <label for="ukuran" class="form-label">Ukuran</label>
      <input type="text" class="form-control" id="ukuran" name="ukuran" required>
    </div>
    <div class="mb-3">
      <label for="warna" class="form-label">Warna</label>
      <input type="text" class="form-control" id="warna" name="warna" required>
    </div>
    <div class="mb-3">
      <label for="harga_beli" class="form-label">Harga Beli</label>
      <input type="number" class="form-control" id="harga_beli" name="harga_beli" required>
    </div>
    <div class="mb-3">
      <label for="harga_jual" class="form-label">Harga Jual</label>
      <input type="number" class="form-control" id="harga_jual" name="harga_jual" required>
    </div>
    <div class="mb-3">
      <label for="stok" class="form-label">Stok</label>
      <input type="number" class="form-control" id="stok" name="stok" required>
    </div>
    <div class="mb-3">
      <label for="stok_minimum" class="form-label">Stok_minimum</label>
      <input type="number" class="form-control" id="stok_minimum" name="stok_minimum" required>
    </div>
    <button type="submit" class="btn btn-primary">Simpan</button>
    <a href="index.php" class="btn btn-secondary">Batal</a>
  </form>
</div>


<?php include '../../includes/footer.php'; ?>
