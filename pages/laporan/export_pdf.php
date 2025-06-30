<?php
require_once '../../config/database.php';
require_once '../../vendor/autoload.php';

use Dompdf\Dompdf;

$start = $_GET['start'] ?? date('Y-m-01');
$end = $_GET['end'] ?? date('Y-m-d');

// Ambil data stok masuk dan keluar
$stmt = $pdo->prepare("
    SELECT 'Masuk' as tipe, sm.tanggal_masuk as tanggal, b.nama_barang, sm.jumlah 
    FROM barang_masuk sm 
    JOIN barang b ON sm.barang_id = b.barang_id 
    WHERE sm.tanggal_masuk BETWEEN ? AND ?
    UNION ALL
    SELECT 'Keluar' as tipe, sk.tanggal_keluar, b.nama_barang, sk.jumlah 
    FROM barang_keluar sk 
    JOIN barang b ON sk.barang_id = b.barang_id 
    WHERE sk.tanggal_keluar BETWEEN ? AND ?
    ORDER BY tanggal ASC
");
$stmt->execute([$start, $end, $start, $end]);
$data = $stmt->fetchAll();

$html = '<h3>Laporan Stok Barang</h3>';
$html .= '<p>Periode: ' . $start . ' s/d ' . $end . '</p>';
$html .= '<table border="1" cellspacing="0" cellpadding="5" width="100%">
<thead>
<tr style="background-color: #f2f2f2;">
  <th>Tanggal</th>
  <th>Nama Barang</th>
  <th>Jumlah</th>
  <th>Tipe</th>
</tr>
</thead><tbody>';

foreach ($data as $row) {
    $html .= "<tr>
        <td>{$row['tanggal']}</td>
        <td>{$row['nama_barang']}</td>
        <td>{$row['jumlah']}</td>
        <td>{$row['tipe']}</td>
    </tr>";
}
$html .= '</tbody></table>';

$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream("laporan_stok_" . $start . "_sampai_" . $end . ".pdf", array("Attachment" => false));
exit;
?>
