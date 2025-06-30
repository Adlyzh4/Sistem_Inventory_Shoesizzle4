<?php
session_start();
require_once '../config/database.php';

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("UPDATE notifikasi SET is_read = 1 WHERE user_id = ?");
$stmt->execute([$user_id]);

http_response_code(200);
