<?php
require_once 'includes/session.php';
require_once 'includes/db.php';

if (!isLoggedIn()) {
    header("Location: views/login.php");
    exit;
}

$user_id = $_SESSION['user_id'] ?? 1;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_budget_id'])) {
    $budget_id = intval($_POST['delete_budget_id']);

    try {
        $pdo->beginTransaction();

        // First delete budget_category entries
        $stmt = $pdo->prepare("DELETE FROM budget_category WHERE budget_id = ?");
        $stmt->execute([$budget_id]);

        // Then delete the budget itself
        $stmt = $pdo->prepare("DELETE FROM budget WHERE id = ? AND user_id = ?");
        $stmt->execute([$budget_id, $user_id]);

        $pdo->commit();
        $_SESSION['budget_delete_success'] = "Budget deleted successfully.";
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['budget_error'] = "Error deleting budget: " . $e->getMessage();
    }

    header("Location: budget.php");
    exit;
}


// Fetch categories
$stmt = $pdo->prepare("
    SELECT id, name 
    FROM category
");
$stmt->execute();


$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

$errors = [];
$success = false;

$stmt = $pdo->prepare("SELECT end_date FROM budget WHERE user_id = ? ORDER BY end_date DESC LIMIT 1");
$stmt->execute([$user_id]);
$last_budget = $stmt->fetchColumn();

if ($last_budget) {
    $next_start = date('Y-m-d', strtotime("$last_budget +1 day"));
    $next_end = date('Y-m-d', strtotime("$next_start +1 month -1 day"));
} else {
    $next_start = date('Y-m-01');
    $next_end = date('Y-m-t');
}
$next_name = date('F Y', strtotime($next_start));

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name']) && !isset($_POST['delete_budget_id'])) {
    $name = trim($_POST['name'] ?? '');
    $amount = floatval($_POST['total_amount'] ?? 0);
    $start = $_POST['start_date'] ?? '';
    $end = $_POST['end_date'] ?? '';
    $allocations = $_POST['allocations'] ?? [];

    if ($name === '' || strlen($name) > 100) $errors[] = "Budget name is required (max 100 chars).";
    if ($amount <= 0) $errors[] = "Total amount must be greater than 0.";
    if (!$start || !$end || $start > $end) $errors[] = "Invalid start/end date.";

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM budget WHERE user_id = ? AND start_date = ?");
    $stmt->execute([$user_id, $start]);
    if ($stmt->fetchColumn() > 0) {
        $errors[] = "A budget already exists for this month.";
    }

    $allocated_total = 0;
    $has_allocation = false;
    foreach ($allocations as $cat_id => $alloc_amt) {
        $amt = floatval($alloc_amt);
        if ($amt > 0) {
            $has_allocation = true;
            $allocated_total += $amt;
        }
    }
    if (!$has_allocation) $errors[] = "At least one category must be allocated.";
    if ($allocated_total > $amount) $errors[] = "Allocated amount exceeds total budget.";

    if (empty($errors)) {
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare("INSERT INTO budget (user_id, name, total_amount, start_date, end_date) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$user_id, $name, $amount, $start, $end]);
            $budget_id = $pdo->lastInsertId();

            $stmt = $pdo->prepare("INSERT INTO budget_category (budget_id, category_id, allocated_amount) VALUES (?, ?, ?)");
            foreach ($allocations as $cat_id => $alloc_amt) {
                if (floatval($alloc_amt) > 0) {
                    $stmt->execute([$budget_id, $cat_id, floatval($alloc_amt)]);
                }
            }

            $pdo->commit();
            $_SESSION['budget_success'] = true;
            header("Location: budget.php");
            exit;
        } catch (Exception $e) {
            $pdo->rollBack();
            $errors[] = "Database error: " . $e->getMessage();
        }
    }
}

if (isset($_SESSION['budget_success'])) {
    $success = true;
    unset($_SESSION['budget_success']);
}

$stmt = $pdo->prepare("SELECT b.id, b.name, b.start_date, b.end_date, b.total_amount,
                              IFNULL(SUM(bc.allocated_amount), 0) AS allocated
                       FROM budget b
                       LEFT JOIN budget_category bc ON b.id = bc.budget_id
                       WHERE b.user_id = ?
                       GROUP BY b.id
                       ORDER BY b.start_date DESC");
$stmt->execute([$user_id]);
$budgets = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Budget Management</title>
    <link rel="stylesheet" href="assets/css/sidebar.css" />
    <link rel="stylesheet" href="assets/css/header.css" />
    <link rel="stylesheet" href="assets/css/budget.css" />
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
</head>

<body>
    <?php include 'includes/sidebar.php'; ?>
    <?php include 'includes/header.php'; ?>

    <main class="content">
        <div class="container">
            <h1>Create Budget</h1>

            <?php if (!empty($errors)): ?>
                <div class="error-box">
                    <ul><?php foreach ($errors as $err) echo "<li>$err</li>"; ?></ul>
                </div>
            <?php elseif ($success): ?>
                <div class="success-box">Budget created successfully!</div>
            <?php elseif (isset($_SESSION['budget_delete_success'])): ?>
                <div class="success-box"><?= $_SESSION['budget_delete_success']; ?></div>
                <?php unset($_SESSION['budget_delete_success']); ?>
            <?php elseif (isset($_SESSION['budget_error'])): ?>
                <div class="error-box"><?= $_SESSION['budget_error']; ?></div>
                <?php unset($_SESSION['budget_error']); ?>
            <?php endif; ?>

            <form method="POST" id="budgetForm">
                <label>Budget Name:
                    <select name="name" id="budget-name-select" required>
                        <option value="2025-01">January 2025</option>
                        <option value="2025-02">February 2025</option>
                        <option value="2025-03">March 2025</option>
                        <option value="2025-04">April 2025</option>
                        <option value="2025-05">May 2025</option>
                        <option value="2025-06">June 2025</option>
                        <option value="2025-07">July 2025</option>
                        <option value="2025-08">August 2025</option>
                        <option value="2025-09">September 2025</option>
                        <option value="2025-10">October 2025</option>
                        <option value="2025-11">November 2025</option>
                        <option value="2025-12">December 2025</option>
                    </select>
                </label>

                <label>Total Amount:
                    <input type="number" name="total_amount" step="0.01" min="1" required>
                </label>
                <label>Start Date:
                    <input type="date" name="start_date" required value="<?= $next_start ?>">
                </label>
                <label>End Date:
                    <input type="date" name="end_date" required value="<?= $next_end ?>">
                </label>
                <p id="allocationSummary">Allocated: 0 / 0</p>
                <h3>Category Allocations</h3>
                <div class="alloc-list">
                    <?php foreach ($categories as $cat): ?>
                        <div class="alloc-item">
                            <label for="alloc-<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></label>
                            <input type="number" name="allocations[<?= $cat['id'] ?>]" id="alloc-<?= $cat['id'] ?>" step="0.01" min="0" value="0">
                        </div>
                    <?php endforeach; ?>
                </div>

                <button type="submit">Save Budget</button>
            </form>

            <h2>Your Budgets</h2>
            <?php if ($budgets): ?>
                <?php foreach ($budgets as $b):
                    $percent = $b['total_amount'] > 0 ? round(($b['allocated'] / $b['total_amount']) * 100, 1) : 0;
                ?>
                    <div class="budget-card">
                        <div class="budget-row">
                            <strong><?= htmlspecialchars($b['name']) ?></strong>
                            <span><?= $b['start_date'] ?> → <?= $b['end_date'] ?></span>
                        </div>
                        <div class="budget-row">
                            <span>Total: $<?= number_format($b['total_amount'], 2) ?></span>
                            <span>Allocated: $<?= number_format($b['allocated'], 2) ?></span>
                        </div>
                        <div class="budget-progress-container">
                            <div class="budget-progress-bar" data-percent="<?= $percent ?>" style="width: <?= $percent ?>%; background: <?= $percent > 100 ? '#dc3545' : '#28a745' ?>;"></div>
                        </div>
                        <small><?= $percent ?>%</small>
                        <a href="budget_view.php?budget_id=<?= $b['id'] ?>">View</a>
                        <form method="POST" onsubmit="return confirm('Are you sure you want to delete this budget?');">
                            <input type="hidden" name="delete_budget_id" value="<?= $b['id'] ?>">
                            <button type="submit" class="delete-btn">Delete</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No budgets yet.</p>
            <?php endif; ?>
        </div>
    </main>
    <div id="toast" class="toast"></div>

    <button id="mobileToggle" class="hamburger">&#9776;</button>

    <script src="assets/js/budget.js"></script>
    <script src="assets/js/header.js"></script>
    <script src="assets/js/sidebar.js" defer></script>
</body>

</html>