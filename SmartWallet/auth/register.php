<?php
require '../includes/db.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST["name"]);
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);

    if (empty($name) || strlen($name) < 3) {
        $_SESSION['error'] = "Name must be at least 3 characters long.";
        header("Location: ../views/register.html");
        exit();
    }


    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Invalid email format.";
        header("Location: ../views/register.html");
        exit();
    }


    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->rowCount() > 0) {
        $_SESSION['error'] = "Email is already registered.";
        header("Location: ../views/register.html");
        exit();
    }

    if (strlen($password) < 8) {
        $_SESSION['error'] = "Password must be at least 8 characters.";
        header("Location: ../views/register.html");
        exit();
    }


    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);


    $stmt = $pdo->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
    if ($stmt->execute([$name, $email, $hashedPassword])) {
        $_SESSION['success'] = "Registration successful! You can now log in.";
        header("Location: ../views/login.php");
        exit();
    } else {
        $_SESSION['error'] = "Registration failed.";
        header("Location: ../views/register.html");
        exit();
    }
}
