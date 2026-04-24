<?php

/**
 * auth/reset_password.php
 *
 * Step 2: User submits new password via the reset form.
 *  - Validate token exists and is not expired
 *  - Validate new password
 *  - Hash and save new password
 *  - Clear the reset token from DB
 */

require '../includes/db.php';
require '../includes/session.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../views/login.php');
    exit();
}

$token           = trim($_POST['token']            ?? '');
$newPassword     = trim($_POST['new_password']     ?? '');
$confirmPassword = trim($_POST['confirm_password'] ?? '');

// ── Basic validation ─────────────────────────────────────────────────────────
if (empty($token)) {
    $_SESSION['error'] = 'Invalid or missing reset token.';
    header('Location: ../views/login.php');
    exit();
}

if (strlen($newPassword) < 8) {
    $_SESSION['error'] = 'Password must be at least 8 characters.';
    header("Location: ../views/reset_password.php?token=" . urlencode($token));
    exit();
}

if ($newPassword !== $confirmPassword) {
    $_SESSION['error'] = 'Passwords do not match.';
    header("Location: ../views/reset_password.php?token=" . urlencode($token));
    exit();
}

// ── Validate token in DB ─────────────────────────────────────────────────────
$stmt = $pdo->prepare(
    'SELECT id, name FROM users
     WHERE reset_token = ?
       AND reset_token_expiry > NOW()'
);
$stmt->execute([$token]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    $_SESSION['error'] = 'This reset link is invalid or has expired. Please request a new one.';
    header('Location: ../views/forgot_password.php');
    exit();
}

// ── Hash new password ─────────────────────────────────────────────────────────
$hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);

// ── Update DB: save new password and clear token ──────────────────────────────
$stmt = $pdo->prepare(
    'UPDATE users
     SET password = ?, reset_token = NULL, reset_token_expiry = NULL, updated_at = NOW()
     WHERE id = ?'
);
$stmt->execute([$hashedPassword, $user['id']]);

$_SESSION['success'] = 'Your password has been reset successfully. You can now log in.';
header('Location: ../views/login.php');
exit();
