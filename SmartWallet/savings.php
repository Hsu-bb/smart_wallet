<?php
require_once 'includes/session.php';
require_once 'includes/db.php';

if (!isLoggedIn()) {
    header("Location: views/login.php");
    exit();
}

$user_id = $_SESSION["user_id"];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    $action = $_POST['action'];

    switch ($action) {
        case 'add':
        case 'update':
            $id = $_POST['id'] ?? null;
            $name = $_POST['goal_name'];
            $target = round((float)$_POST['target_amount'], 2);
            $current = round((float)$_POST['current_amount'], 2);
            $start = $_POST['start_date'];
            $end = $_POST['end_date'];
            $type = $_POST['type'];
            $status = $_POST['status'];

            if ($target < 0 || $current < 0) {
                echo json_encode(['error' => 'Amounts must be positive.']);
                exit;
            }

            if ($action === 'add') {
                $stmt = $pdo->prepare("INSERT INTO savings (user_id, goal_name, target_amount, current_amount, start_date, end_date, type, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$user_id, $name, $target, $current, $start, $end, $type, $status]);

                $msg = "🌟 New goal \"$name\" has been added.";
                $pdo->prepare("INSERT INTO notifications (user_id, type, message) VALUES (?, 'info', ?)")->execute([$user_id, $msg]);
            } else {
                $stmt = $pdo->prepare("UPDATE savings SET goal_name=?, target_amount=?, current_amount=?, start_date=?, end_date=?, type=?, status=? WHERE id=? AND user_id=?");
                $stmt->execute([$name, $target, $current, $start, $end, $type, $status, $id, $user_id]);
            }

            echo json_encode(['success' => true]);
            exit;

        case 'delete':
            $stmt = $pdo->prepare("DELETE FROM savings WHERE id=? AND user_id=?");
            $stmt->execute([$_POST['id'], $user_id]);

            $name = $stmt->fetchColumn();

            $stmt = $pdo->prepare("DELETE FROM savings WHERE id=? AND user_id=?");
            $stmt->execute([$_POST['id'], $user_id]);

            $msg = "🔚 Goal \"$name\" was deleted.";
            $pdo->prepare("INSERT INTO notifications (user_id, type, message) VALUES (?, 'warning', ?)")->execute([$user_id, $msg]);

            echo json_encode(['success' => true]);
            exit;

        case 'add_to_savings':
            $id = $_POST['id'];
            $amount = floatval($_POST['amount']);
            if ($amount <= 0) {
                echo json_encode(['error' => 'Invalid amount']);
                exit;
            }
            $stmt = $pdo->prepare("UPDATE savings SET current_amount = current_amount + ? WHERE id = ? AND user_id = ?");
            $stmt->execute([$amount, $id, $user_id]);

            $stmt = $pdo->prepare("SELECT goal_name FROM savings WHERE id = ? AND user_id = ?");
            $stmt->execute([$id, $user_id]);
            $name = $stmt->fetchColumn();

            $msg = "💰 $$amount added to \"$name\" savings.";
            $pdo->prepare("INSERT INTO notifications (user_id, type, message) VALUES (?, 'success', ?)")->execute([$user_id, $msg]);

            echo json_encode(['success' => true]);
            exit;
    }
}

$stmt = $pdo->prepare("SELECT * FROM savings WHERE user_id = ? ORDER BY updated_at DESC");
$stmt->execute([$user_id]);
$savings = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Savings Goals</title>
    <link rel="stylesheet" href="assets/css/savings.css" />
    <link rel="stylesheet" href="assets/css/header.css" />
    <link rel="stylesheet" href="assets/css/sidebar.css" />
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
</head>

<body>
    <?php include 'includes/sidebar.php'; ?>
    <?php include 'includes/header.php'; ?>
    <main class="content">
        <div class="container">
            <div class="header">
                <h1>Savings Goals</h1>
                <button id="addGoalBtn" class="btn green">+ New Goal</button>
            </div>

            <div id="savingsList" class="cards">
                <?php foreach ($savings as $goal):
                    $progress = ($goal['target_amount'] > 0)
                        ? min(100, round(($goal['current_amount'] / $goal['target_amount']) * 100))
                        : 0;
                ?>
                    <div class="card" data-id="<?= $goal['id'] ?>">
                        <div class="card-header">
                            <h2><?= htmlspecialchars($goal['goal_name']) ?></h2>
                            <div class="kebab-menu-wrapper">
                                <button class="kebab-btn">&#x22EE;</button>
                                <div class="kebab-dropdown">
                                    <button class="edit">✏️ Edit</button>
                                    <button class="delete">🗑️ Delete</button>
                                    <button class="history">📜 History</button>
                                </div>
                            </div>
                        </div>
                        <span class="type-label"><?= htmlspecialchars($goal['type']) ?></span>
                        <span class="status-label <?= $goal['status'] ?>"><?= htmlspecialchars($goal['status']) ?></span>
                        <p style="margin-top: 10px;">Target: $<?= number_format($goal['target_amount'], 2) ?></p>
                        <p>Saved: $<?= number_format($goal['current_amount'], 2) ?></p>
                        <p>Start: <?= htmlspecialchars($goal['start_date']) ?></p>
                        <p>End: <?= htmlspecialchars($goal['end_date']) ?></p>
                        <div class="progress-bar">
                            <div class="progress" style="width: <?= $progress ?>%"></div>
                        </div>
                        <button class="btn green save-money">💵 Add to Savings</button>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </main>

    <div id="saveModal" class="modal">
        <div class="modal-content">
            <h2>Add to Savings</h2>
            <form id="saveForm">
                <label>
                    Amount:
                    <input type="number" id="saveAmount" placeholder="Enter amount" step="0.01" required />
                </label>
                <label>
                    Date:
                    <input type="date" id="historyDate" required />
                </label>
                <label>
                    Notes:
                    <input type="text" id="historyNotes" value="Auto Generated" disabled />
                </label>

                <div class="modal-actions">
                    <button type="submit" class="btn green">Save</button>
                    <button type="button" id="closeSaveModal" class="btn cancel">Cancel</button>
                </div>
            </form>
        </div>
    </div>


    <div id="goalModal" class="modal">
        <div class="modal-content">
            <h2 id="modalTitle">New Goal</h2>
            <form id="goalForm" class="goal-form">
                <input type="hidden" name="action" value="add">
                <input type="hidden" name="id">

                <div class="form-row">
                    <label>Goal Name:
                        <input type="text" name="goal_name" required>
                    </label>
                    <label>Target Amount:
                        <input type="number" name="target_amount" step="0.01" min="0" required>
                    </label>
                </div>

                <div class="form-row">
                    <label>Current Saved:
                        <input type="number" name="current_amount" step="0.01" min="0" required>
                    </label>
                    <label>Start Date:
                        <input type="date" name="start_date" required>
                    </label>
                </div>

                <div class="form-row">
                    <label>End Date:
                        <input type="date" name="end_date" required>
                    </label>
                    <label>Type:
                        <select name="type">
                            <option value="short-term">Short-Term</option>
                            <option value="long-term">Long-Term</option>
                        </select>
                    </label>
                </div>

                <div class="form-row">
                    <label>Status:
                        <select name="status">
                            <option value="in-progress">In Progress</option>
                            <option value="completed">Completed</option>
                        </select>
                    </label>
                </div>

                <div class="modal-actions">
                    <button type="submit" class="btn green">Save</button>
                    <button type="button" id="cancelGoalModal" class="btn cancel">Cancel</button>
                </div>

            </form>
        </div>
    </div>

    <div id="toast" class="toast">Saved successfully!</div>

    <div id="historyModal" class="modal">
        <div class="modal-content">
            <h2>Savings History</h2>

            <div id="historyList">
                <ul>
                    <li>No history yet.</li>
                </ul>
            </div>

            <div class="modal-actions">
                <button type="button" id="closeHistory" class="btn cancel">Close</button>
            </div>
        </div>
    </div>

    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <h2>Delete Goal</h2>
            <p>Are you sure you want to delete this savings goal?</p>
            <div class="modal-actions">
                <button type="button" id="confirmDeleteBtn" class="btn red">Delete</button>
                <button type="button" id="cancelDeleteModal" class="btn cancel">Cancel</button>
            </div>
        </div>
    </div>

    <button id="mobileToggle" class="hamburger">&#9776;</button>

    <script src="assets/js/savings.js"></script>
    <script src="assets/js/header.js"></script>
    <script src="assets/js/sidebar.js" defer></script>
</body>

</html>