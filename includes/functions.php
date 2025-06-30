<?php
function logAktivitas($pdo, $user_id, $aktivitas) {
  // Ambil role dari tabel users
  $stmtUser = $pdo->prepare("SELECT role FROM users WHERE user_id = ?");
  $stmtUser->execute([$user_id]);
  $user = $stmtUser->fetch();

  $role = $user ? $user['role'] : 'unknown';

  // Simpan log dengan role
  $stmt = $pdo->prepare("INSERT INTO log_aktivitas (user_id, role, aktivitas, waktu) VALUES (?, ?, ?, NOW())");
  $stmt->execute([$user_id, $role, $aktivitas]);
}
?>
