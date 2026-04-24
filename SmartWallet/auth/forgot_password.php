<?php
/**
 * auth/forgot_password.php
 *
 * Step 1: User submits their email.
 *  - Validate email exists in DB
 *  - Generate a secure token
 *  - Store token + expiry (1 hour) in DB
 *  - Send reset email (via PHP mail() — swap for PHPMailer/SMTP in production)
 */

require '../includes/db.php';
require '../includes/session.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../views/forgot_password.php');
    exit();
}

$email = trim($_POST['email'] ?? '');

// ── Validate ────────────────────────────────────────────────────────────────
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['error'] = 'Please enter a valid email address.';
    header('Location: ../views/forgot_password.php');
    exit();
}

// ── Check user exists ────────────────────────────────────────────────────────
$stmt = $pdo->prepare('SELECT id, name FROM users WHERE email = ?');
$stmt->execute([$email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Always show success message (security: don't reveal if email exists)
if (!$user) {
    $_SESSION['success'] = 'If this email is registered, a reset link has been sent.';
    header('Location: ../views/forgot_password.php');
    exit();
}

// ── Generate token ───────────────────────────────────────────────────────────
$token  = bin2hex(random_bytes(32));          // 64-char hex string
$expiry = date('Y-m-d H:i:s', time() + 3600); // 1 hour from now

// ── Save token to DB ─────────────────────────────────────────────────────────
$stmt = $pdo->prepare(
    'UPDATE users SET reset_token = ?, reset_token_expiry = ? WHERE id = ?'
);
$stmt->execute([$token, $expiry, $user['id']]);

// ── Build reset URL ───────────────────────────────────────────────────────────
$protocol  = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host      = $_SERVER['HTTP_HOST'];
$resetLink = "$protocol://$host/SmartWallet/views/reset_password.php?token=$token";

// ── Send email ────────────────────────────────────────────────────────────────
$to      = $email;
$subject = 'Smart Wallet — Password Reset Request';
$name    = htmlspecialchars($user['name']);

$message = "
Hello $name,

We received a request to reset your Smart Wallet password.

Click the link below to reset your password (valid for 1 hour):

$resetLink

If you did not request this, you can safely ignore this email.
Your password will NOT change unless you click the link above.

— Smart Wallet Team
";

$headers = "From: no-reply@smartwallet.com\r\n" .
           "Reply-To: no-reply@smartwallet.com\r\n" .
           "X-Mailer: PHP/" . phpversion();

/*
 * PRODUCTION NOTE:
 * Replace mail() below with PHPMailer or SendGrid for reliable delivery.
 *
 * PHPMailer example:
 *   require 'vendor/autoload.php';
 *   $mail = new PHPMailer\PHPMailer\PHPMailer();
 *   $mail->isSMTP();
 *   $mail->Host       = 'smtp.gmail.com';
 *   $mail->SMTPAuth   = true;
 *   $mail->Username   = 'your@gmail.com';
 *   $mail->Password   = 'your-app-password';
 *   $mail->SMTPSecure = 'tls';
 *   $mail->Port       = 587;
 *   $mail->setFrom('no-reply@smartwallet.com', 'Smart Wallet');
 *   $mail->addAddress($email, $user['name']);
 *   $mail->Subject = $subject;
 *   $mail->Body    = $message;
 *   $mail->send();
 */
mail($to, $subject, $message, $headers);

$_SESSION['success'] = 'If this email is registered, a reset link has been sent. Please check your inbox.';
header('Location: ../views/forgot_password.php');
exit();
