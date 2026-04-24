<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Forgot Password | Smart Wallet</title>
  <link rel="stylesheet" href="../assets/css/login.css" />
  <link rel="stylesheet" href="../assets/css/forgot_password.css" />
  <link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
</head>

<body>
  <?php session_start(); ?>

  <?php if (isset($_SESSION['error']) || isset($_SESSION['success'])): ?>
    <div class="message-container">
      <?php if (isset($_SESSION['error'])): ?>
        <p class="error-message">
          <?php echo $_SESSION['error'];
          unset($_SESSION['error']); ?>
        </p>
      <?php endif; ?>
      <?php if (isset($_SESSION['success'])): ?>
        <p class="success-message">
          <?php echo $_SESSION['success'];
          unset($_SESSION['success']); ?>
        </p>
      <?php endif; ?>
    </div>
  <?php endif; ?>

  <div class="auth-container">
    <div class="auth-box">

      <!-- Icon -->
      <div class="fp-icon">
        <i class="fas fa-lock-open"></i>
      </div>

      <h2>Forgot Password?</h2>
      <p>Enter your registered email and we'll send you a reset link.</p>

      <form action="../auth/forgot_password.php" method="POST">
        <div class="input-group">
          <i class="fas fa-envelope"></i>
          <input
            type="email"
            name="email"
            placeholder="Your email address"
            required
            autocomplete="email" />
        </div>

        <button type="submit" class="btn-primary">
          <i class="fas fa-paper-plane"></i> Send Reset Link
        </button>
      </form>

      <p class="switch-form">
        Remember your password? <a href="login.php">Back to Login</a>
      </p>

    </div>
  </div>

  <script src="../assets/js/script.js" defer></script>
</body>

</html>