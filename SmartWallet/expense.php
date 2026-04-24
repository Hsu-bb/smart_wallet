<?php
require_once 'includes/session.php';
require_once 'includes/db.php';

if (!isLoggedIn()) {
    header("Location: views/login.php");
    exit;
}

$user_id = $_SESSION['user_id'] ?? 1;

$form_date = $_SESSION['form_data']['date'] ?? date('Y-m-d');
$errors = $_SESSION['form_errors'] ?? [];
$success = false;

unset($_SESSION['form_errors'], $_SESSION['form_data']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_expense'])) {
    $id = intval($_POST['expense_id']);
    $amount = floatval($_POST['amount']);
    $description = trim($_POST['description']);
    $expense_date = $_POST['expense_date'];

    $stmt = $pdo->prepare("UPDATE expense SET amount = ?, description = ?, expense_date = ?, updated_at = NOW() WHERE id = ? AND user_id = ?");
    $stmt->execute([$amount, $description, $expense_date, $id, $user_id]);

    $_SESSION['expense_message'] = "Expense updated successfully.";
    header("Location: expense.php#expense-history");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['delete_id'])) {
    $expense_id = intval($_GET['delete_id']);
    $stmt = $pdo->prepare("DELETE FROM expense WHERE id = ? AND user_id = ?");
    $stmt->execute([$expense_id, $user_id]);
    $_SESSION['expense_message'] = "Expense deleted successfully.";
    header("Location: expense.php#expense-history");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['date'])) {
    $date = $_POST['date'] ?? '';
    $category_id = $_POST['category_id'] ?? '';
    $amount = $_POST['amount'] ?? '';
    $note = trim($_POST['note'] ?? '');
    $errors = [];

    if (!$date) {
        $errors[] = "Date is required.";
    } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        $errors[] = "Invalid date format.";
    } elseif (strtotime($date) > strtotime(date('Y-m-d'))) {
        $errors[] = "Date cannot be in the future.";
    }

    if (!$category_id || !is_numeric($category_id)) {
        $errors[] = "Valid category is required.";
    }

    if (!is_numeric($amount) || floatval($amount) <= 0) {
        $errors[] = "Amount must be a positive number.";
    }

    if (strlen($note) > 255) {
        $errors[] = "Note must be 255 characters or less.";
    }

    if (!empty($errors)) {
        $_SESSION['form_errors'] = $errors;
        $_SESSION['form_data'] = ['date' => $date];
        header("Location: expense.php");
        exit;
    }

    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare("INSERT INTO expense (user_id, category_id, amount, expense_date, description) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            $user_id,
            $category_id,
            floatval($amount),
            $date,
            $note
        ]);
        $pdo->commit();
        $_SESSION['expense_success'] = true;
        header("Location: expense.php");
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['form_errors'] = ["Error saving expense: " . $e->getMessage()];
        $_SESSION['form_data'] = ['date' => $date];
        header("Location: expense.php");
        exit;
    }
}

if (isset($_SESSION['expense_success'])) {
    $success = true;
    unset($_SESSION['expense_success']);
}

$stmt = $pdo->prepare("
    SELECT id, name 
    FROM category
    ORDER BY name
");
$stmt->execute();

$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("SELECT e.*, c.name AS category_name
    FROM expense e
    LEFT JOIN category c ON e.category_id = c.id
    WHERE e.user_id = ?
    ORDER BY e.expense_date DESC, e.created_at DESC");
$stmt->execute([$user_id]);
$expenses = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Add Expense</title>
    <link rel="stylesheet" href="assets/css/sidebar.css" />
    <link rel="stylesheet" href="assets/css/header.css" />
    <link rel="stylesheet" href="assets/css/expense.css" />
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
</head>

<body>
    <?php include 'includes/sidebar.php'; ?>
    <?php include 'includes/header.php'; ?>

    <main class="content">
        <div class="container">
            <h1>Expenses</h1>

            <button onclick="openModal()">Add Expense</button>

            <?php if (!empty($errors)): ?>
                <div class="error-box">
                    <ul><?php foreach ($errors as $err) echo "<li>$err</li>"; ?></ul>
                </div>
            <?php elseif ($success): ?>
                <div class="success-box">Expense recorded successfully.</div>
            <?php endif; ?>

            <div class="modal" id="expenseModal">
                <div class="modal-content">
                    <span class="close-btn" onclick="closeModal()">&times;</span>
                    <h2>Add Expense</h2>
                    <form method="POST">
                        <label>Date:
                            <input type="date" name="date" required value="<?= htmlspecialchars($form_date) ?>">
                        </label>

                        <label>Category:
                            <select name="category_id" required>
                                <option value="">-- Choose category --</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </label>

                        <label>Amount:
                            <input type="number" name="amount" step="0.01" min="0.01" required>
                        </label>

                        <label>Note:
                            <input type="text" name="note" maxlength="255">
                        </label>

                        <button type="submit">Save Expense</button>
                    </form>
                </div>
            </div>

            <?php if (isset($_SESSION['expense_message'])): ?>
                <div class="success-box" id="expenseMsg"> <?= $_SESSION['expense_message'] ?> </div>
                <script>
                    setTimeout(() => {
                        const box = document.getElementById('expenseMsg');
                        if (box) box.style.display = 'none';
                    }, 4000);
                </script>
                <?php unset($_SESSION['expense_message']); ?>
            <?php endif; ?>

            <div id="editModal" class="modal">
                <div class="modal-content">
                    <span class="close-btn">&times;</span>
                    <h2>Edit Expense</h2>
                    <form id="editExpenseForm" method="POST">
                        <input type="hidden" name="expense_id" value="">
                        <label>Amount:</label>
                        <input type="number" name="amount" step="0.01" required>

                        <label>Description:</label>
                        <input type="text" name="description">

                        <label>Date:</label>
                        <input type="date" name="expense_date" required>

                        <button type="submit" name="edit_expense">Save Changes</button>
                    </form>
                </div>
            </div>

            <h2 id="expense-history"></h2>
            <?php if (count($expenses) === 0): ?>
                <p>No expenses found.</p>
            <?php else: ?>
                <div class="expense-list">
                    <?php foreach ($expenses as $e): ?>
                        <div class="expense-row"
                            data-id="<?= $e['id'] ?>"
                            data-amount="<?= $e['amount'] ?>"
                            data-description="<?= htmlspecialchars($e['description']) ?>"
                            data-date="<?= $e['expense_date'] ?>">

                            <div>
                                <strong><?= htmlspecialchars($e['category_name']) ?></strong> - $<?= number_format($e['amount'], 2) ?>
                            </div>
                            <div class="small">Date: <?= $e['expense_date'] ?></div>
                            <?php if ($e['description']): ?><div class="small muted">Note: <?= htmlspecialchars($e['description']) ?></div><?php endif; ?>

                            <div class="kebab-container">
                                <button class="kebab-toggle">⋮</button>
                                <div class="kebab-menu">
                                    <button class="edit-expense" data-id="<?= $e['id'] ?>">Edit</button>
                                    <button class="delete-expense" data-id="<?= $e['id'] ?>">Delete</button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>
    <div id="toast" class="toast"></div>

    <button id="mobileToggle" class="hamburger">&#9776;</button>

    <script src="assets/js/expense.js"></script>
    <script src="assets/js/header.js"></script>
    <script src="assets/js/sidebar.js" defer></script>

</body>

</html>