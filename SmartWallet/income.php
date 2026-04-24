<?php
require_once 'includes/session.php';
require_once 'includes/db.php';

if (!isLoggedIn()) {
    header("Location: views/login.php");
    exit;
}

$user_id = $_SESSION["user_id"];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    header("Content-Type: application/json");
    $action = $_POST["action"];
    $income_id = $_POST["income_id"] ?? null;
    $amount = $_POST["amount"] ?? null;
    $description = $_POST["description"] ?? null;
    $date = $_POST["date"] ?? null;
    $status = $_POST["status"] ?? "pending";

    if ($action === "add" || $action === "edit") {
        if (!is_numeric($amount) || empty($description) || empty($date)) {
            echo json_encode(["error" => "All fields are required!"]);
            exit;
        }

        if ($action === "add") {
            $pdo->prepare("INSERT INTO income (user_id, amount, description, date, status)
                           VALUES (?, ?, ?, ?, ?)")
                ->execute([$user_id, $amount, $description, $date, $status]);
            echo json_encode(["success" => "Income added"]);
        } else {
            $pdo->prepare("UPDATE income SET amount = ?, description = ?, date = ?, status = ?
                           WHERE id = ? AND user_id = ?")
                ->execute([$amount, $description, $date, $status, $income_id, $user_id]);
            echo json_encode(["success" => "Income updated"]);
        }
        exit;
    }

    if ($action === "delete") {
        $pdo->prepare("DELETE FROM income WHERE id = ? AND user_id = ?")
            ->execute([$income_id, $user_id]);
        echo json_encode(["success" => "Income deleted"]);
        exit;
    }
}

$statusFilter = $_GET['status'] ?? 'all';
$sortBy = $_GET['sort_by'] ?? 'date';

$whereClause = "WHERE i.user_id = ?";
$params = [$user_id];

if (in_array($statusFilter, ['confirmed', 'pending'])) {
    $whereClause .= " AND i.status = ?";
    $params[] = $statusFilter;
}

$orderClause = match ($sortBy) {
    'amount' => "ORDER BY i.amount DESC",
    default => "ORDER BY i.date DESC"
};

$query = "SELECT i.id, i.amount, i.description, i.date, i.status
          FROM income i 
          $whereClause $orderClause";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$incomes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Income</title>
    <link rel="stylesheet" href="assets/css/sidebar.css" />
    <link rel="stylesheet" href="assets/css/header.css" />
    <link rel="stylesheet" href="assets/css/income.css" />
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
</head>

<body>
    <?php include 'includes/sidebar.php'; ?>
    <?php include 'includes/header.php'; ?>


    <main class="content">
        <div class="header">
            <h1>Income</h1>
            <button id="addIncomeBtn">+ Add</button>
        </div>

        <div class="filter-bar">
            <select id="statusFilter">
                <option value="all">All Status</option>
                <option value="confirmed">Confirmed</option>
                <option value="pending">Pending</option>
            </select>

            <select id="sortOrder">
                <option value="date_desc">Date (Newest → Oldest)</option>
                <option value="date_asc">Date (Oldest → Newest)</option>
                <option value="amount_desc">Amount (High → Low)</option>
                <option value="amount_asc">Amount (Low → High)</option>
            </select>
        </div>

        <div class="income-list" id="incomeList">
            <?php foreach ($incomes as $income): ?>
                <div class="income-card" data-id="<?= $income["id"] ?>">
                    <div class="income-info">
                        <strong>$<?= number_format($income["amount"], 2) ?></strong>
                        <div class="meta">
                            <span class="badge-date"><?= $income["date"] ?></span>
                            <span class="badge-status <?= strtolower($income["status"]) ?>"><?= $income["status"] ?></span>
                        </div>
                        <p><?= htmlspecialchars($income["description"]) ?></p>
                    </div>

                    <div class="income-actions">
                        <button class="kebab-toggle">⋮</button>
                        <div class="kebab-menu">
                            <button class="edit" data-id="<?= $income["id"] ?>">✏️ Edit</button>
                            <button class="delete" data-id="<?= $income["id"] ?>">🗑️ Delete</button>
                        </div>
                    </div>

                </div>
            <?php endforeach; ?>
        </div>


        <div class="modal" id="incomeModal">
            <div class="modal-content">
                <h2 id="modalTitle">New Income</h2>
                <form id="incomeForm">
                    <input type="hidden" name="action" value="add" />
                    <input type="hidden" name="income_id" />
                    <label>Amount <input type="number" name="amount" required /></label>
                    <label>Description <input type="text" name="description" required /></label>
                    <label>Date <input type="date" name="date" required /></label>
                    <label>Status
                        <select name="status">
                            <option value="pending">Pending</option>
                            <option value="confirmed">Confirmed</option>
                        </select>
                    </label>
                    <div class="modal-actions">
                        <button type="submit">Save</button>
                        <button type="button" id="cancelIncomeModal">Cancel</button>
                    </div>
                </form>
            </div>
        </div>


        <div id="deleteIncomeModal" class="modal">
            <div class="modal-content">
                <h2>Delete Income</h2>
                <p>Are you sure you want to delete this income record?</p>
                <div class="modal-actions">
                    <button type="button" id="confirmDeleteIncomeBtn" class="btn red">Delete</button>
                    <button type="button" id="cancelDeleteIncomeBtn" class="btn cancel">Cancel</button>
                </div>
            </div>
        </div>
    </main>
    <div id="toast" class="toast"></div>

    <button id="mobileToggle" class="hamburger">&#9776;</button>

    <script src="assets/js/income.js"></script>
    <script src="assets/js/header.js"></script>
    <script src="assets/js/sidebar.js" defer></script>
</body>

</html>