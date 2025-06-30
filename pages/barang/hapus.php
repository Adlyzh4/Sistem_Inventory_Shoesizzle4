<?php
// pages/barang/hapus.php
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

// Ambil nama barang dulu sebelum dihapus
$stmtNama = $pdo->prepare("SELECT nama_barang FROM barang WHERE barang_id = ?");
$stmtNama->execute([$id]);
$barang = $stmtNama->fetch();

$nama = $barang ? $barang['nama_barang'] : 'Tidak Diketahui';

// Hapus data
$stmt = $pdo->prepare("DELETE FROM barang WHERE barang_id = ?");
$stmt->execute([$id]);

// Catat aktivitas
logAktivitas($pdo, $_SESSION['user_id'], "Menghapus barang: $nama");

header('Location: index.php');
exit;
?>
