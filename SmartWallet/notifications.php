<?php
require_once 'includes/session.php';
require_once 'includes/db.php';

if (!isLoggedIn()) {
    header("Location: views/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$perPage = 10;
$page = max(1, intval($_GET['page'] ?? 1));
$offset = ($page - 1) * $perPage;

$filter_type = $_GET['type'] ?? '';
$filter_status = $_GET['status'] ?? 'unread';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'mark_read' && isset($_POST['id'])) {
        $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?");
        $stmt->execute([$_POST['id'], $user_id]);
    } elseif ($action === 'mark_all') {
        $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?");
        $stmt->execute([$user_id]);
    }
    exit(json_encode(['success' => true]));
}

$where = "WHERE user_id = ?";
$params = [$user_id];

if ($filter_status === 'unread') {
    $where .= " AND is_read = 0";
} elseif ($filter_status === 'read') {
    $where .= " AND is_read = 1";
}

if ($filter_type) {
    $where .= " AND type = ?";
    $params[] = $filter_type;
}

$stmt = $pdo->prepare("SELECT * FROM notifications $where ORDER BY created_at DESC");
$stmt->execute($params);
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
    ob_clean();
    renderNotifications($notifications, $filter_type, $filter_status);
    exit;
}


function renderNotifications($notifications, $filter_type, $filter_status)
{
?>
    <div id="notiList" class="notifications-list">
        <?php if (empty($notifications)): ?>
            <p>No notifications found.</p>
        <?php else: ?>
            <ul style="list-style: none; padding: 0;">
                <?php foreach ($notifications as $n): ?>
                    <li class="<?= $n['is_read'] ? '' : 'unread' ?>" style="margin-bottom: 1rem;">
                        <strong><?= htmlspecialchars($n['message']) ?></strong>
                        <span class="badge <?= $n['type'] ?>">
                            <?= strtoupper($n['type']) ?>
                        </span>
                        <br>
                        <small class="relative-time" data-time="<?= $n['created_at'] ?>"></small><br>
                        <?php if (!$n['is_read']): ?>
                            <form method="POST" style="margin-top: 5px;">
                                <input type="hidden" name="action" value="mark_read">
                                <input type="hidden" name="id" value="<?= $n['id'] ?>">
                                <button type="submit" class="btn small">Mark as Read</button>
                            </form>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
<?php
}

if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
    renderNotifications($notifications, $filter_type, $filter_status);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications</title>
    <link rel="stylesheet" href="assets/css/sidebar.css" />
    <link rel="stylesheet" href="assets/css/notifications.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
</head>

<body>
    <?php include 'includes/sidebar.php'; ?>
    <main class="content">
        <div class="container">
            <h1>Notifications</h1>

            <form method="GET" style="margin-bottom: 20px; display: flex; gap: 10px;">
                <select name="status">
                    <option value="unread" <?= $filter_status === 'unread' ? 'selected' : '' ?>>Unread Only</option>
                    <option value="read" <?= $filter_status === 'read' ? 'selected' : '' ?>>Read Only</option>
                    <option value="all" <?= $filter_status === 'all' ? 'selected' : '' ?>>All</option>
                </select>
                <button type="submit">Filter</button>
            </form>

            <form method="POST" style="margin-bottom: 15px;">
                <input type="hidden" name="action" value="mark_all">
                <button class="btn">Mark All as Read</button>
            </form>

            <div id="notiList">
                <?php renderNotifications($notifications, $filter_type, $filter_status); ?>
            </div>
        </div>
    </main>

    <button id="mobileToggle" class="hamburger">&#9776;</button>
    <script src="assets/js/sidebar.js" defer></script>
</body>

</html>