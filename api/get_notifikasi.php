<?php
session_start();
require_once '../config/database.php';

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT * FROM notifikasi WHERE user_id = ? ORDER BY created_at DESC LIMIT 20");
$stmt->execute([$user_id]);
$notifikasi = $stmt->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: application/json');
echo json_encode($notifikasi);
