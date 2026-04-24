<?php
require '../includes/db.php';
require '../includes/session.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);
    $rememberMe = isset($_POST["remember_me"]);


    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Invalid email format.";
        header("Location: ../views/login.php");
        exit();
    }

    $stmt = $pdo->prepare("SELECT id, password FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user) {
        $_SESSION['error'] = "No account found with this email.";
        header("Location: ../views/login.php");
        exit();
    }


    if (password_verify($password, $user["password"])) {
        $_SESSION["user_id"] = $user["id"];


        $stmt = $pdo->prepare("UPDATE users SET updated_at = NOW() WHERE id = ?");
        $stmt->execute([$user["id"]]);

        if ($rememberMe) {
            setcookie("user_id", $user["id"], time() + (86400 * 30), "/");
        }

        $_SESSION['success'] = "Login successful!";
        header("Location: ../dashboard.php");
        exit();
    } else {
        $_SESSION['error'] = "Invalid password.";
        header("Location: ../views/login.php");
        exit();
    }
}
