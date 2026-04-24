<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Login | Smart Wallet</title>
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
      <h2>Welcome Back!</h2>
      <p>Log in to manage your finances smarter</p>
      <form action="../auth/login.php" method="POST">
        <div class="input-group">
          <i class="fas fa-envelope"></i>
          <input type="email" name="email" placeholder="Email" required />
        </div>
        <div class="input-group">
          <i class="fas fa-lock"></i>
          <input
            type="password"
            name="password"
            placeholder="Password"
            required />
        </div>

        <!-- ✅ NEW: Forgot Password link -->
        <div style="text-align: right; margin-top: -10px; margin-bottom: 18px;">
          <a href="forgot_password.php" style="font-size: 13px; color: #5a67d8; text-decoration: none;">
            Forgot password?
          </a>
        </div>

        <button type="submit" class="btn-primary">Login</button>
        <p class="switch-form">
          New here? <a href="register.html">Create an account</a>
        </p>
      </form>
    </div>
  </div>

  <script src="../assets/js/script.js" defer></script>
</body>

</html>