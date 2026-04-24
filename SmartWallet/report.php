<?php
require_once 'includes/session.php';
require_once 'includes/db.php';

if (!isLoggedIn()) {
    header("Location: views/login.php");
    exit;
}

$user_id = $_SESSION['user_id'] ?? 1;


$year = isset($_GET['year']) ? $_GET['year'] : date('Y');
$month = isset($_GET['month']) ? $_GET['month'] : date('m');


$sql = "
    SELECT c.name AS category, SUM(e.amount) AS total
    FROM expense e
    JOIN category c ON e.category_id = c.id
    WHERE e.user_id = :user_id
    AND YEAR(e.expense_date) = :year
    AND MONTH(e.expense_date) = :month
    GROUP BY c.name
";
$stmt = $pdo->prepare($sql);

$stmt->execute([
    'user_id' => $user_id,
    'year' => $year,
    'month' => $month
]);
$expenseData = $stmt->fetchAll(PDO::FETCH_ASSOC);


$categories = [];
$totals = [];

foreach ($expenseData as $row) {
    $categories[] = $row['category'];
    $totals[] = $row['total'];
}

$yearsQuery = "SELECT DISTINCT YEAR(expense_date) AS year FROM expense WHERE user_id = :user_id ORDER BY year DESC";
$yearsStmt = $pdo->prepare($yearsQuery);
$yearsStmt->execute(['user_id' => $user_id]);
$years = $yearsStmt->fetchAll(PDO::FETCH_ASSOC);

$monthsQuery = "
    SELECT DISTINCT MONTH(expense_date) AS month
    FROM expense
    WHERE user_id = :user_id AND YEAR(expense_date) = :year
    ORDER BY month
";
$monthsStmt = $pdo->prepare($monthsQuery);
$monthsStmt->execute(['user_id' => $user_id, 'year' => $year]);
$availableMonths = $monthsStmt->fetchAll(PDO::FETCH_COLUMN);


$monthNames = [
    '01' => 'January',
    '02' => 'February',
    '03' => 'March',
    '04' => 'April',
    '05' => 'May',
    '06' => 'June',
    '07' => 'July',
    '08' => 'August',
    '09' => 'September',
    '10' => 'October',
    '11' => 'November',
    '12' => 'December'
];



$stmt = $pdo->prepare("
    SELECT id, start_date, end_date FROM budget 
    WHERE user_id = ? 
    AND start_date <= CURDATE() AND end_date >= CURDATE()
    LIMIT 1
");
$stmt->execute([$user_id]);
$budget = $stmt->fetch(PDO::FETCH_ASSOC);

$allocationData = [];

if ($budget) {
    $currentBudgetId = $budget['id'];
    $startDate = $budget['start_date'];
    $endDate = $budget['end_date'];

    $stmt = $pdo->prepare("
        SELECT 
            c.name AS category,
            COALESCE(bc.allocated_amount, 0) AS allocated_amount,
            COALESCE(SUM(e.amount), 0) AS spent_amount
        FROM (
            SELECT DISTINCT category_id 
            FROM budget_category 
            WHERE budget_id = ?
            UNION
            SELECT DISTINCT category_id 
            FROM expense 
            WHERE user_id = ? AND expense_date BETWEEN ? AND ?
        ) AS cat_ids
        JOIN category c ON c.id = cat_ids.category_id
        LEFT JOIN budget_category bc 
            ON bc.category_id = c.id AND bc.budget_id = ?
        LEFT JOIN expense e 
            ON e.category_id = c.id AND e.user_id = ? AND e.expense_date BETWEEN ? AND ?
        GROUP BY c.id, c.name, bc.allocated_amount
        ORDER BY c.name
    ");
    $stmt->execute([
        $currentBudgetId,
        $user_id,
        $startDate,
        $endDate,
        $currentBudgetId,
        $user_id,
        $startDate,
        $endDate
    ]);

    $allocationData = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    echo "No active budget found.";
}


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Report</title>
    <link rel="stylesheet" href="assets/css/sidebar.css" />
    <link rel="stylesheet" href="assets/css/header.css" />
    <link rel="stylesheet" href="assets/css/report.css" />
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
</head>

<body>
    <?php include 'includes/sidebar.php'; ?>
    <?php include 'includes/header.php'; ?>

    <h2>Monthly Expense Breakdown</h2>

    <form method="get" action="report.php">
        <label for="year">Year:</label>
        <select name="year" id="year">
            <?php foreach ($years as $yearOption): ?>
                <option value="<?= $yearOption['year']; ?>" <?= $yearOption['year'] == $year ? 'selected' : ''; ?>>
                    <?= $yearOption['year']; ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label for="month">Month:</label>
        <select name="month" id="month">
            <?php foreach ($availableMonths as $monthValue):
                $monthKey = str_pad($monthValue, 2, '0', STR_PAD_LEFT); ?>
                <option value="<?= $monthKey; ?>" <?= $monthKey == $month ? 'selected' : ''; ?>>
                    <?= $monthNames[$monthKey]; ?>
                </option>
            <?php endforeach; ?>
        </select>

        <button type="submit">Filter</button>
    </form>

    <canvas id="expensePieChart"></canvas>

    <h2>Budget Allocation vs Actual Spending</h2>
    <canvas id="budgetChart" height="200"></canvas>


    <script>
        const labels = <?= json_encode($categories); ?>;
        const data = <?= json_encode($totals); ?>;

        const ctx = document.getElementById('expensePieChart').getContext('2d');
        new Chart(ctx, {
            type: 'pie',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Expenses by Category',
                    data: data,
                    backgroundColor: [
                        '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40'
                    ],
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    </script>

    <script>
        const budgetData = <?= json_encode($allocationData); ?>;
    </script>


    <button id="mobileToggle" class="hamburger">&#9776;</button>

    <script src="assets/js/header.js"></script>
    <script src="assets/js/report.js"></script>
    <script src="assets/js/sidebar.js" defer></script>
</body>

</html>