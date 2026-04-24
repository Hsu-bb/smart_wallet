<?php
require_once '../includes/db.php';

$user_id = 1;

$today = date('Y-m-d');
$three_days_later = date('Y-m-d', strtotime('+3 days'));


$stmt = $pdo->prepare("SELECT id, title, due_date FROM debt WHERE user_id = ? AND status = 'pending' AND due_date = ?");
$stmt->execute([$user_id, $three_days_later]);
$debts = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($debts as $debt) {
    $message = "Your debt '{$debt['title']}' is due on {$debt['due_date']}";


    $check = $pdo->prepare("SELECT 1 FROM notifications WHERE user_id = ? AND message = ? LIMIT 1");
    $check->execute([$user_id, $message]);
    $exists = $check->fetchColumn();

    if (!$exists) {
        $insert = $pdo->prepare("INSERT INTO notifications (user_id, type, message, created_at, is_read, is_sent) VALUES (?, 'system', ?, NOW(), 0, 0)");
        $insert->execute([$user_id, $message]);
    }
}


if (!empty($debts)) {
    echo "Notifications created: " . count($debts);
} else {
    echo "No upcoming debts due in 3 days.";
}
