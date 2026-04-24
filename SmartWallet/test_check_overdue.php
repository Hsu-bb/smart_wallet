<?php
require_once 'includes/session.php';
require_once 'includes/db.php';

if (!isLoggedIn()) {
    die("Not logged in.");
}

$user_id = $_SESSION['user_id'];
$today = date('Y-m-d');

$stmt = $pdo->prepare("SELECT id, title FROM debt WHERE user_id = ? AND status = 'pending' AND due_date < ?");
$stmt->execute([$user_id, $today]);
$overdueDebts = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($overdueDebts as $debt) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND message LIKE ?");
    $stmt->execute([$user_id, "%{$debt['title']}%"]);
    $alreadyExists = $stmt->fetchColumn();

    if (!$alreadyExists) {
        $message = "⚠️ Your debt \"{$debt['title']}\" is overdue!";
        $stmt = $pdo->prepare("INSERT INTO notifications (user_id, type, message) VALUES (?, 'warning', ?)");
        $stmt->execute([$user_id, $message]);
    }
}

echo "Done checking overdue debts.";
