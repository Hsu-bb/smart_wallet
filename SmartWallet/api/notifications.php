<?php
require_once '../includes/session.php';
require_once '../includes/db.php';

header('Content-Type: application/json');

if (!isLoggedIn()) exit(json_encode([]));

$user_id = $_SESSION['user_id'];
$limit = intval($_GET['limit'] ?? 5);

$stmt = $pdo->prepare("SELECT id, message, created_at, is_read FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT ?");
$stmt->bindValue(1, $user_id, PDO::PARAM_INT);
$stmt->bindValue(2, $limit, PDO::PARAM_INT);
$stmt->execute();

echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
