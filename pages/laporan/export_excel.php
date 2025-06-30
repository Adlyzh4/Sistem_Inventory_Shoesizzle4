<?php
require_once '../../config/database.php';
require '../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$start = $_GET['start'] ?? date('Y-m-01');
$end = $_GET['end'] ?? date('Y-m-d');

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Laporan Stok');

$sheet->setCellValue('A1', 'Laporan Stok Masuk, Keluar, dan Retur');
$sheet->setCellValue('A2', 'Periode: ' . $start . ' s/d ' . $end);
$sheet->mergeCells('A1:E1');
$sheet->mergeCells('A2:E2');

$row = 4;
$sheet->setCellValue("A$row", 'Tipe');
$sheet->setCellValue("B$row", 'Nama Barang');
$sheet->setCellValue("C$row", 'Jumlah');
$sheet->setCellValue("D$row", 'Tanggal');
$sheet->setCellValue("E$row", 'Keterangan');

$row++;

// Ambil data stok masuk
$stmtMasuk = $pdo->prepare("SELECT sm.*, b.nama_barang FROM barang_masuk sm JOIN barang b ON sm.barang_id = b.barang_id WHERE sm.tanggal_masuk BETWEEN ? AND ?");
$stmtMasuk->execute([$start, $end]);
foreach ($stmtMasuk as $data) {
    $sheet->setCellValue("A$row", 'Masuk');
    $sheet->setCellValue("B$row", $data['nama_barang']);
    $sheet->setCellValue("C$row", $data['jumlah']);
    $sheet->setCellValue("D$row", $data['tanggal_masuk']);
    $sheet->setCellValue("E$row", '-');
    $row++;
}

// Ambil data stok keluar
$stmtKeluar = $pdo->prepare("SELECT sk.*, b.nama_barang FROM barang_keluar sk JOIN barang b ON sk.barang_id = b.barang_id WHERE sk.tanggal_keluar BETWEEN ? AND ?");
$stmtKeluar->execute([$start, $end]);
foreach ($stmtKeluar as $data) {
    $sheet->setCellValue("A$row", 'Keluar');
    $sheet->setCellValue("B$row", $data['nama_barang']);
    $sheet->setCellValue("C$row", $data['jumlah']);
    $sheet->setCellValue("D$row", $data['tanggal_keluar']);
    $sheet->setCellValue("E$row", '-');
    $row++;
}

// Ambil data retur
$stmtRetur = $pdo->prepare("SELECT r.*, b.nama_barang FROM retur_barang r JOIN barang b ON r.barang_id = b.barang_id WHERE r.tanggal_retur BETWEEN ? AND ?");
$stmtRetur->execute([$start, $end]);
foreach ($stmtRetur as $data) {
    $sheet->setCellValue("A$row", 'Retur');
    $sheet->setCellValue("B$row", $data['nama_barang']);
    $sheet->setCellValue("C$row", $data['jumlah']);
    $sheet->setCellValue("D$row", $data['tanggal_retur']);
    $sheet->setCellValue("E$row", $data['alasan_retur']);
    $row++;
}

$filename = 'laporan_shoesizzle_' . date('YmdHis') . '.xlsx';
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $filename . '"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
