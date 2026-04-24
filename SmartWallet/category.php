<?php
require_once 'includes/session.php';
require_once 'includes/db.php';

if (!isLoggedIn()) {
    header("Location: views/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$all_categories = $pdo->prepare("SELECT * FROM category ORDER BY name");
$all_categories->execute();

?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>My Categories</title>
    <link rel="stylesheet" href="assets/css/sidebar.css" />
    <link rel="stylesheet" href="assets/css/header.css" />
    <link rel="stylesheet" href="assets/css/category.css" />
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

</head>

<body>
    <?php include 'includes/sidebar.php'; ?>
    <?php include 'includes/header.php'; ?>
    <main class="content">
        <div class="container">
            <div class="header">
                <h1>System Categories</h1>
            </div>

            <ul>
                <?php foreach ($all_categories as $all_categories): ?>
                    <li><strong><?= htmlspecialchars($all_categories['name']) ?></strong> — <?= htmlspecialchars($all_categories['description']) ?> <span class="badge">System</span></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </main>

    <div id="toast" class="toast" style="display: none;"></div>

    <button id="mobileToggle" class="hamburger">&#9776;</button>

    <?php if (isset($_SESSION['msg'])): ?>
        <div id="session-message" data-message="<?= htmlspecialchars($_SESSION['msg'], ENT_QUOTES) ?>"></div>
        <?php unset($_SESSION['msg']); ?>
    <?php endif; ?>

    <script src="assets/js/category.js"></script>
    <script src="assets/js/header.js"></script>
    <script src="assets/js/sidebar.js" defer></script>
</body>

</html>