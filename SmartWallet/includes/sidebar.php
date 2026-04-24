<?php $current = basename($_SERVER['PHP_SELF']); ?>
<div class="sidebar" id="sidebar">
    <button class="toggle" id="sidebarToggle" aria-label="Toggle Sidebar">
        <span class="material-icons-outlined">menu</span>
    </button>

    <nav class="sidebar-nav">
        <a href="dashboard.php" class="<?= $current == 'dashboard.php' ? 'active' : '' ?>" data-tooltip="Dashboard">
            <i class="material-icons-outlined">dashboard</i>
            <span class="label">Dashboard</span>
        </a>

        <a href="income.php" class="<?= $current == 'income.php' ? 'active' : '' ?>" data-tooltip="Income">
            <i class="material-icons-outlined">savings</i>
            <span class="label">Income</span>
        </a>

        <a href="expense.php" class="<?= $current == 'expense.php' ? 'active' : '' ?>" data-tooltip="Expense">
            <i class="material-icons-outlined">credit_card</i>
            <span class="label">Expense</span>
        </a>

        <a href="budget.php" class="<?= $current == 'budget.php' ? 'active' : '' ?>" data-tooltip="Budget">
            <i class="material-icons-outlined">pie_chart</i>
            <span class="label">Budget</span>
        </a>

        <a href="category.php" class="<?= $current == 'category.php' ? 'active' : '' ?>" data-tooltip="Category">
            <i class="material-icons-outlined">folder</i>
            <span class="label">Category</span>
        </a>

        <a href="savings.php" class="<?= $current == 'savings.php' ? 'active' : '' ?>" data-tooltip="Savings">
            <i class="material-icons-outlined">account_balance</i>
            <span class="label">Savings</span>
        </a>

        <a href="debt.php" class="<?= $current == 'debt.php' ? 'active' : '' ?>" data-tooltip="Debt">
            <i class="material-icons-outlined">monetization_on</i>
            <span class="label">Debt</span>
        </a>

        <a href="report.php" class="<?= $current == 'report.php' ? 'active' : '' ?>" data-tooltip="Report">
            <i class="material-icons-outlined">analytics</i>
            <span class="label">Report</span>
        </a>

        <a href="settings.php" class="<?= $current == 'settings.php' ? 'active' : '' ?>" data-tooltip="Settings">
            <i class="material-icons-outlined">settings</i>
            <span class="label">Settings</span>
        </a>
    </nav>

    <a href="auth/logout.php" class="logout-btn" data-tooltip="Logout">
        <i class="material-icons-outlined">logout</i>
        <span class="label">Logout</span>
    </a>
</div>