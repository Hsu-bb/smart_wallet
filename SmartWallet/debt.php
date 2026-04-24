<?php
require_once 'includes/session.php';
require_once 'includes/db.php';

if (!isLoggedIn()) {
    header("Location: views/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$overdue = $pdo->prepare("
    SELECT d.id, d.title 
    FROM debt d
    JOIN debt_payments p ON d.id = p.debt_id
    WHERE p.user_id = ? AND d.due_date < CURDATE() AND d.status != 'paid'
    GROUP BY d.id
");
$overdue->execute([$user_id]);

foreach ($overdue->fetchAll() as $row) {
    $check = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND message LIKE ?");
    $check->execute([$user_id, "%{$row['title']}%"]);
    if ($check->fetchColumn() == 0) {
        $insert = $pdo->prepare("INSERT INTO notifications (user_id, type, message) VALUES (?, 'system', ?)");
        $insert->execute([$user_id, "Debt overdue: {$row['title']}"]);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $action = $_POST['action'];

    if ($action === 'create') {
        $title = trim($_POST['title']);
        $amount = round((float)$_POST['amount'], 2);
        $due_date = $_POST['due_date'];
        $description = trim($_POST['description'] ?? null);
        $status = $_POST['status'] ?? 'pending';

        if (strlen($title) < 3 || strlen($title) > 100) {
            echo json_encode(['error' => 'Title must be between 3 and 100 characters.']);
            exit;
        }

        if ($amount <= 0) {
            echo json_encode(['error' => 'Amount must be greater than 0.']);
            exit;
        }

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $due_date) || strtotime($due_date) < strtotime(date('Y-m-d'))) {
            echo json_encode(['error' => 'Due date is invalid or in the past.']);
            exit;
        }

        if (strlen($description) > 255) {
            echo json_encode(['error' => 'Description must be under 255 characters.']);
            exit;
        }

        $stmt = $pdo->prepare("INSERT INTO debt (title, amount, description, due_date, status) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$title, $amount, $description, $due_date, $status]);

        $msg = "New debt created: '$title' due on $due_date.";
        $pdo->prepare("INSERT INTO notifications (user_id, type, message) VALUES (?, 'info', ?)")->execute([$user_id, $msg]);

        echo json_encode(['success' => true]);
        exit;
    }

    if ($action === 'update') {
        $id = intval($_POST['id'] ?? 0);
        $title = trim($_POST['title']);
        $amount = round((float)$_POST['amount'], 2);
        $due_date = $_POST['due_date'];
        $description = trim($_POST['description'] ?? null);
        $status = $_POST['status'] ?? 'pending';

        if ($id <= 0) {
            echo json_encode(['error' => 'Invalid ID']);
            exit;
        }

        if (strlen($title) < 3 || strlen($title) > 100) {
            echo json_encode(['error' => 'Title must be between 3 and 100 characters.']);
            exit;
        }

        if ($amount <= 0) {
            echo json_encode(['error' => 'Amount must be greater than 0.']);
            exit;
        }

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $due_date) || strtotime($due_date) < strtotime(date('Y-m-d'))) {
            echo json_encode(['error' => 'Due date is invalid or in the past.']);
            exit;
        }

        if (strlen($description) > 255) {
            echo json_encode(['error' => 'Description must be under 255 characters.']);
            exit;
        }

        $stmt = $pdo->prepare("UPDATE debt SET title = ?, amount = ?, description = ?, due_date = ?, status = ? WHERE id = ?");
        $stmt->execute([$title, $amount, $description, $due_date, $status, $id]);

        echo json_encode(['success' => true]);
        exit;
    }

    if ($action === 'delete') {
        $debt_id = $_POST['id'] ?? null;

        if (!$debt_id || !is_numeric($debt_id)) {
            echo json_encode(['error' => 'Invalid debt ID.']);
            exit;
        }

        try {
            $stmt = $pdo->prepare("DELETE FROM debt WHERE id = ?");
            $stmt->execute([$debt_id]);

            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            echo json_encode(['error' => 'Delete failed.']);
        }
        exit;
    }

    if ($action === 'pay') {
        try {
            $debt_id = $_POST['debt_id'];
            $amount = round((float)$_POST['amount'], 2);
            $payment_date = $_POST['payment_date'];
            $note = trim($_POST['note'] ?? null);

            $stmt = $pdo->prepare("
                SELECT d.title, d.amount 
                FROM debt d
                INNER JOIN debt_payments dp ON d.id = dp.debt_id
                WHERE d.id = ? AND dp.user_id = ?
            ");
            $stmt->execute([$debt_id, $user_id]);
            $debt = $stmt->fetch(PDO::FETCH_ASSOC);
            $title = $debt['title'] ?? 'Unknown';

            if ($amount <= 0) {
                echo json_encode(['error' => 'Payment amount must be greater than 0.']);
                exit;
            }

            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $payment_date) || strtotime($payment_date) > strtotime(date('Y-m-d'))) {
                echo json_encode(['error' => 'Payment date is invalid or in the future.']);
                exit;
            }

            if (strlen($note) > 255) {
                echo json_encode(['error' => 'Note must be under 255 characters.']);
                exit;
            }

            $pdo->beginTransaction();

            $stmt = $pdo->prepare("INSERT INTO debt_payments (debt_id, user_id, amount, payment_date, note) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$debt_id, $user_id, $amount, $payment_date, $note]);

            $stmt = $pdo->prepare("SELECT amount FROM debt WHERE id = ? AND id IN (SELECT debt_id FROM debt_payments WHERE user_id = ?)");
            $stmt->execute([$debt_id, $user_id]);
            $debt_total = $stmt->fetchColumn();

            $stmt = $pdo->prepare("SELECT SUM(amount) FROM debt_payments WHERE debt_id = ? AND user_id = ?");
            $stmt->execute([$debt_id, $user_id]);
            $total_paid = $stmt->fetchColumn();

            if ($debt_total && $total_paid >= $debt_total) {
                $stmt = $pdo->prepare("UPDATE debt SET status = 'paid' WHERE id = ? AND id IN (SELECT debt_id FROM debt_payments WHERE user_id = ?)");
                $stmt->execute([$debt_id, $user_id]);
            }

            $stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND message = ?");
            $notifMsg = "🎉 Your debt \"{$title}\" has been fully paid!";
            $stmt->execute([$user_id, $notifMsg]);

            if ($stmt->fetchColumn() == 0) {
                $stmt = $pdo->prepare("INSERT INTO notifications (user_id, type, message) VALUES (?, 'success', ?)");
                $stmt->execute([$user_id, $notifMsg]);
            }

            $pdo->commit();

            $msg = "Debt payment of $$amount recorded for $title.";
            $pdo->prepare("INSERT INTO notifications (user_id, type, message) VALUES (?, 'success', ?)")->execute([$user_id, $msg]);

            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            $pdo->rollBack();
            echo json_encode(['error' => 'Payment failed: ' . $e->getMessage()]);
        }
        exit;
    }


    if ($action === 'history') {
        $stmt = $pdo->prepare("SELECT amount, payment_date, note FROM debt_payments WHERE debt_id = ? AND user_id = ? ORDER BY payment_date DESC");
        $stmt->execute([$_POST['debt_id'], $user_id]);
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        exit;
    }
}

$stmt = $pdo->prepare("
    SELECT d.*, COALESCE(SUM(p.amount), 0) AS paid_amount 
    FROM debt d 
    LEFT JOIN debt_payments p ON d.id = p.debt_id AND p.user_id = ? 
    GROUP BY d.id 
    ORDER BY d.due_date ASC
");
$stmt->execute([$user_id]);
$debts = $stmt->fetchAll(PDO::FETCH_ASSOC);

$tomorrow = date('Y-m-d', strtotime('+1 day'));
$stmt = $pdo->prepare("
    SELECT d.id, d.title 
    FROM debt d 
    WHERE d.due_date = ? 
      AND d.status = 'pending'
      AND NOT EXISTS (
        SELECT 1 FROM notifications n 
        WHERE n.user_id = ? 
          AND n.message LIKE CONCAT('%', d.title, '%') 
          AND n.type = 'reminder'
      )
");
$stmt->execute([$tomorrow, $user_id]);
$dueSoonDebts = $stmt->fetchAll();

foreach ($dueSoonDebts as $debt) {
    $msg = "🔔 Reminder: Your debt \"{$debt['title']}\" is due tomorrow.";
    $insert = $pdo->prepare("INSERT INTO notifications (user_id, type, message) VALUES (?, 'reminder', ?)");
    $insert->execute([$user_id, $msg]);
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Debt Tracker</title>
    <link rel="stylesheet" href="assets/css/sidebar.css" />
    <link rel="stylesheet" href="assets/css/header.css" />
    <link rel="stylesheet" href="assets/css/debt.css" />
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
</head>

<body>
    <?php include 'includes/sidebar.php'; ?>
    <?php include 'includes/header.php'; ?>
    <main class="content">
        <div class="container">
            <div class="header">
                <h1>Debt Tracker</h1>
                <button id="addDebtBtn" class="btn green">+ New Debt</button>
            </div>

            <?php if (empty($debts)): ?>
                <div class="empty-state">
                    <p>You have no debts yet.</p>
                </div>
            <?php endif; ?>

            <div class="cards">
                <?php foreach ($debts as $debt): ?>
                    <?php
                    $remaining = max(0, $debt['amount'] - $debt['paid_amount']);
                    $progress = ($debt['amount'] > 0) ? min(100, round($debt['paid_amount'] / $debt['amount'] * 100)) : 0;
                    ?>
                    <div class="card" data-id="<?= $debt['id'] ?>">
                        <div class="card-header">
                            <h2><?= htmlspecialchars($debt['title']) ?></h2>
                            <div class="kebab-menu-wrapper">
                                <button class="kebab-btn">&#x22EE;</button>
                                <div class="kebab-dropdown">
                                    <button class="edit">Edit</button>
                                    <button class="delete">Delete</button>
                                    <button class="history" data-id="<?= $debt['id'] ?>">History</button>
                                </div>
                            </div>

                        </div>
                        <span class="type-label"><?= htmlspecialchars($debt['status']) ?></span>
                        <p>Total: $<?= number_format($debt['amount'], 2) ?></p>
                        <p>Paid: $<?= number_format($debt['paid_amount'], 2) ?></p>
                        <p>Due: <?= htmlspecialchars($debt['due_date']) ?></p>
                        <p>Remaining: $<?= number_format($remaining, 2) ?></p>
                        <div class="progress-bar">
                            <div class="progress" style="width: <?= $progress ?>%"></div>
                        </div>
                        <button class="btn green pay" data-id="<?= $debt['id'] ?>">💸 Pay Debt</button>
                    </div>
                <?php endforeach; ?>
            </div>

            <div id="payModal" class="modal">
                <div class="modal-content">
                    <h2>Make Payment</h2>
                    <form id="payForm">
                        <div id="formError"></div>
                        <input type="hidden" name="action" value="pay">
                        <input type="hidden" name="debt_id">
                        <label>Amount:
                            <input type="number" name="amount" step="0.01" required>
                        </label>
                        <label>Payment Date:
                            <input type="date" name="payment_date" required>
                        </label>
                        <label>Note:
                            <input type="text" name="note" placeholder="Optional note">
                        </label>
                        <div class="modal-actions">
                            <button type="submit" class="btn green">Save</button>
                            <button type="button" id="cancelPay" class="btn cancel">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>

            <div id="debtModal" class="modal">
                <div class="modal-content">
                    <h2 id="modalTitle">New/Edit Debt</h2>
                    <form id="debtForm">
                        <input type="hidden" name="action" value="create" />
                        <input type="hidden" name="id" />

                        <label>Title: <input type="text" name="title" required /></label>
                        <label>Amount: <input type="number" name="amount" step="0.01" required /></label>
                        <label>Description: <input type="text" name="description" /></label>
                        <label>Due Date: <input type="date" name="due_date" required /></label>
                        <label>Status:
                            <select name="status">
                                <option value="pending">Pending</option>
                                <option value="paid">Paid</option>
                            </select>
                        </label>

                        <div class="modal-actions">
                            <button type="submit" class="btn green">Save</button>
                            <button type="button" id="cancelDebt" class="btn cancel">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>

            <div id="deleteModal" class="modal">
                <div class="modal-content">
                    <h2>Confirm Deletion</h2>
                    <p id="deleteInfo">Are you sure you want to delete this debt?</p>
                    <div class="modal-actions">
                        <button id="confirmDeleteBtn" class="btn red">Delete</button>
                        <button id="cancelDeleteBtn" class="btn cancel">Cancel</button>
                    </div>
                </div>
            </div>

            <div id="historyModal" class="modal">
                <div class="modal-content">
                    <h2>Payment History</h2>
                    <ul id="historyList"></ul>
                    <div class="modal-actions">
                        <button type="button" id="closeHistory" class="btn cancel">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <div id="toast" class="toast"></div>

    <button id="mobileToggle" class="hamburger">&#9776;</button>

    <script src="assets/js/debt.js"></script>
    <script src="assets/js/header.js"></script>
    <script src="assets/js/sidebar.js" defer></script>
</body>

</html>