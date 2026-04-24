<?php
require_once 'includes/session.php';
require_once 'includes/db.php';

if (!isLoggedIn()) {
    header("Location: views/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];


$totalIncome = $pdo->query("SELECT SUM(amount) FROM income WHERE user_id = $user_id AND MONTH(date) = MONTH(CURDATE())")->fetchColumn() ?: 0;
$totalExpense = $pdo->query("SELECT SUM(amount) FROM expense WHERE user_id = $user_id AND MONTH(expense_date) = MONTH(CURDATE())")->fetchColumn() ?: 0;
$totalSavings = $pdo->query("SELECT SUM(current_amount) FROM savings WHERE user_id = $user_id")->fetchColumn() ?: 0;
$totalDebt = $pdo->query("
    SELECT 
        IFNULL(SUM(d.amount), 0) - IFNULL(SUM(dp.amount), 0) AS remaining_debt
    FROM debt d
    LEFT JOIN debt_payments dp ON dp.debt_id = d.id AND dp.user_id = $user_id
    WHERE d.id IN (
        SELECT DISTINCT debt_id FROM debt_payments WHERE user_id = $user_id
    )
")->fetchColumn() ?: 0;



$stmt = $pdo->prepare("SELECT message, created_at FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
$stmt->execute([$user_id]);
$recentActivities = $stmt->fetchAll(PDO::FETCH_ASSOC);


$stmt = $pdo->prepare("
    SELECT 'income' AS type, date AS date, amount FROM income WHERE user_id = ? AND MONTH(date) = MONTH(CURDATE())
    UNION ALL
    SELECT 'expense' AS type, expense_date AS date, amount FROM expense WHERE user_id = ? AND MONTH(expense_date) = MONTH(CURDATE())
");
$stmt->execute([$user_id, $user_id]);
$chartData = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="assets/css/sidebar.css" />
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>
    <?php include 'includes/sidebar.php'; ?>
    <main class="content">
        <div class="container">
            <h1>Dashboard</h1>

            <div class="summary-cards">
                <div class="card">Income: $<?= number_format($totalIncome, 2) ?></div>
                <div class="card">Expense: $<?= number_format($totalExpense, 2) ?></div>
                <div class="card">Savings: $<?= number_format($totalSavings, 2) ?></div>
                <div class="card">Debt: $<?= number_format($totalDebt, 2) ?></div>
            </div>

            <h2>Recent Activities</h2>
            <ul>
                <?php foreach ($recentActivities as $act): ?>
                    <li><?= htmlspecialchars($act['message']) ?> <small><?= date('Y-m-d H:i', strtotime($act['created_at'])) ?></small></li>
                <?php endforeach; ?>
            </ul>

            <h2>Income vs Expense (This Month)</h2>
            <canvas id="lineChart" height="1000"></canvas>
        </div>
    </main>

    <div id="toast" class="toast"></div>

    <button id="mobileToggle" class="hamburger">&#9776;</button>

    <script>
        const chartData = <?= json_encode($chartData) ?>;
    </script>
    <script src="assets/js/dashboard.js"></script>
    <script src="assets/js/sidebar.js" defer></script>
</body>

</html>