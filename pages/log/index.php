<?php
require_once '../../config/database.php';
require_once '../../includes/functions.php';
// require_once '../../includes/header.php';
// require_once '../../includes/navbar.php';

$stmt = $pdo->query("SELECT la.*, u.username, u.role FROM log_aktivitas la 
                     JOIN users u ON la.user_id = u.user_id 
                     ORDER BY la.waktu DESC");
$logs = $stmt->fetchAll();
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
    
<?php include '../../includes/navbar.php'; ?>

<div class="container mt-5">
  <h3>Log Aktivitas Admin</h3>
  <table class="table table-bordered mt-3">
    <thead class="table-dark">
      <tr>
        <th>#</th>
        <th>Username</th>
        <th>Role</th>
        <th>Aktivitas</th>
        <th>Waktu</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($logs as $log): ?>
        <tr>
          <td><?= $log['log_id'] ?></td>
          <td><?= htmlspecialchars($log['username']) ?></td>
          <td><?= htmlspecialchars($log['role']) ?></td>
          <td><?= htmlspecialchars($log['aktivitas']) ?></td>
          <td><?= $log['waktu'] ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<?php include '../../includes/footer.php'; ?>
