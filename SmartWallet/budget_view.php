<?php
require_once 'includes/session.php';
require_once 'includes/db.php';

if (!isLoggedIn()) {
    header("Location: views/login.php");
    exit;
}

$budget_id = $_GET['budget_id'] ?? null;
if (!$budget_id || !is_numeric($budget_id)) {
    die("Invalid budget ID.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['budget_id']) && $_POST['budget_id'] == $budget_id) {
    $allocations = $_POST['allocations'] ?? [];
    $total_allocated = array_sum(array_map('floatval', $allocations));

    $stmt = $pdo->prepare("SELECT total_amount FROM budget WHERE id = ?");
    $stmt->execute([$budget_id]);
    $budget_total = $stmt->fetchColumn();

    if ($total_allocated > $budget_total) {
        $_SESSION['update_error'] = "Allocation exceeds total budget!";
        header("Location: budget_view.php?budget_id=$budget_id");
        exit;
    }

    try {
        $pdo->beginTransaction();
        $stmt = $pdo->prepare("UPDATE budget_category SET allocated_amount = ? WHERE budget_id = ? AND category_id = ?");
        foreach ($allocations as $cat_id => $amount) {
            $stmt->execute([floatval($amount), $budget_id, $cat_id]);
        }
        $pdo->commit();
        $_SESSION['update_success'] = true;
        header("Location: budget_view.php?budget_id=$budget_id");
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['update_error'] = 'Error: ' . $e->getMessage();
        header("Location: budget_view.php?budget_id=$budget_id");
        exit;
    }
}

$update_success = false;
$update_error = '';
if (isset($_SESSION['update_success'])) {
    $update_success = true;
    unset($_SESSION['update_success']);
}
if (isset($_SESSION['update_error'])) {
    $update_error = $_SESSION['update_error'];
    unset($_SESSION['update_error']);
}

$stmt = $pdo->prepare("SELECT * FROM budget WHERE id = ?");
$stmt->execute([$budget_id]);
$budget = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$budget) {
    die("Budget not found.");
}

$stmt = $pdo->prepare("SELECT c.id, c.name, bc.allocated_amount FROM budget_category bc JOIN category c ON bc.category_id = c.id WHERE bc.budget_id = ?");
$stmt->execute([$budget_id]);
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Budget Detail - <?= htmlspecialchars($budget['name']) ?></title>
    <link rel="stylesheet" href="assets/css/sidebar.css" />
    <link rel="stylesheet" href="assets/css/budget_view.css" />
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
</head>

<body>
    <?php include 'includes/sidebar.php'; ?>

    <main class="content">
        <div class="container">
            <h1>Budget: <?= htmlspecialchars($budget['name']) ?></h1>
            <p><strong>Start:</strong> <?= $budget['start_date'] ?> &nbsp;&nbsp; <strong>End:</strong> <?= $budget['end_date'] ?></p>
            <p><strong>Total Amount:</strong> $<?= number_format($budget['total_amount'], 2) ?></p>

            <h3>Category Allocations</h3>
            <div class="alloc-list" id="allocationTable">
                <?php foreach ($categories as $cat): ?>
                    <div class="alloc-item" data-cat-id="<?= $cat['id'] ?>">
                        <span class="alloc-label"><?= htmlspecialchars($cat['name']) ?></span>
                        <span class="amount-cell">$<?= number_format($cat['allocated_amount'], 2) ?></span>
                    </div>
                <?php endforeach; ?>
            </div>

            <button onclick="openModal()">Edit Allocations</button>
            <p><a href="budget.php">← Back to Budgets</a></p>
        </div>

        <?php if ($update_success): ?>
            <div class="success-box" id="updateMsg">Allocations updated successfully!</div>
        <?php elseif ($update_error): ?>
            <div class="error-box"><?= htmlspecialchars($update_error) ?></div>
        <?php endif; ?>

        <div class="modal" id="editModal">
            <div class="modal-content">
                <span class="close-btn" onclick="closeModal()">&times;</span>
                <h3>Edit Allocations</h3>
                <form method="POST" action="budget_view.php?budget_id=<?= $budget_id ?>">
                    <input type="hidden" name="budget_id" value="<?= $budget_id ?>">
                    <div class="alloc-list">
                        <?php foreach ($categories as $cat): ?>
                            <div class="alloc-item">
                                <label for="alloc-<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></label>
                                <input type="number" name="allocations[<?= $cat['id'] ?>]" id="alloc-<?= $cat['id'] ?>" step="0.01" min="0" value="<?= htmlspecialchars($cat['allocated_amount']) ?>">
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div id="editSummary" style="margin-top:10px; font-weight:bold;"></div>

                    <button type="submit" id="editSubmit" data-total="<?= $budget['total_amount'] ?>">Save Changes</button>
                </form>
            </div>
        </div>
    </main>
    <div id="toast" class="toast"></div>

    <button id="mobileToggle" class="hamburger">&#9776;</button>
    <script src="assets/js/budget_view.js"></script>
    <script src="assets/js/sidebar.js" defer></script>
</body>

</html>