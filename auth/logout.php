<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

if (isset($_SESSION['user_id'])) {
  logAktivitas($pdo, $_SESSION['user_id'], "Logout dari sistem");
}

session_unset(); // hapus semua data session
session_destroy(); // hancurkan session

header('Location: login.php'); // arahkan ke halaman login
exit;
