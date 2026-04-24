<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Reset Password | Smart Wallet</title>
    <link rel="stylesheet" href="../assets/css/login.css" />
    <link rel="stylesheet" href="../assets/css/forgot_password.css" />
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"
    />
  </head>
  <body>
    <?php
    session_start();
    require '../includes/db.php';

    // ── Validate token on page load ──────────────────────────────────────────
    $token = trim($_GET['token'] ?? '');

    if (empty($token)) {
        $_SESSION['error'] = 'No reset token provided.';
        header('Location: forgot_password.php');
        exit();
    }

    $stmt = $pdo->prepare(
        'SELECT id FROM users
         WHERE reset_token = ?
           AND reset_token_expiry > NOW()'
    );
    $stmt->execute([$token]);
    $user = $stmt->fetch();

    if (!$user) {
        $_SESSION['error'] = 'This reset link is invalid or has expired. Please request a new one.';
        header('Location: forgot_password.php');
        exit();
    }
    ?>

    <?php if (isset($_SESSION['error']) || isset($_SESSION['success'])): ?>
    <div class="message-container">
      <?php if (isset($_SESSION['error'])): ?>
      <p class="error-message">
        <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
      </p>
      <?php endif; ?>
      <?php if (isset($_SESSION['success'])): ?>
      <p class="success-message">
        <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
      </p>
      <?php endif; ?>
    </div>
    <?php endif; ?>

    <div class="auth-container">
      <div class="auth-box">

        <div class="fp-icon fp-icon--green">
          <i class="fas fa-key"></i>
        </div>

        <h2>Set New Password</h2>
        <p>Choose a strong password (at least 8 characters).</p>

        <form action="../auth/reset_password.php" method="POST" id="resetForm">
          <!-- Hidden token passed through form -->
          <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">

          <div class="input-group">
            <i class="fas fa-lock"></i>
            <input
              type="password"
              name="new_password"
              id="new_password"
              placeholder="New password"
              required
              minlength="8"
              autocomplete="new-password"
            />
            <button type="button" class="toggle-pw" data-target="new_password" title="Show/Hide password">
              <i class="fas fa-eye"></i>
            </button>
          </div>

          <div class="input-group">
            <i class="fas fa-lock"></i>
            <input
              type="password"
              name="confirm_password"
              id="confirm_password"
              placeholder="Confirm new password"
              required
              minlength="8"
              autocomplete="new-password"
            />
            <button type="button" class="toggle-pw" data-target="confirm_password" title="Show/Hide password">
              <i class="fas fa-eye"></i>
            </button>
          </div>

          <!-- Strength indicator -->
          <div class="pw-strength" id="pwStrength">
            <div class="pw-strength-bar" id="pwStrengthBar"></div>
          </div>
          <p class="pw-strength-label" id="pwStrengthLabel"></p>

          <button type="submit" class="btn-primary" id="submitBtn">
            <i class="fas fa-check-circle"></i> Reset Password
          </button>
        </form>

        <p class="switch-form">
          <a href="forgot_password.php">← Request a new link</a>
        </p>

      </div>
    </div>

    <script>
      // ── Password visibility toggles ────────────────────────────────────────
      document.querySelectorAll('.toggle-pw').forEach(btn => {
        btn.addEventListener('click', () => {
          const input = document.getElementById(btn.dataset.target);
          const icon  = btn.querySelector('i');
          if (input.type === 'password') {
            input.type = 'text';
            icon.classList.replace('fa-eye', 'fa-eye-slash');
          } else {
            input.type = 'password';
            icon.classList.replace('fa-eye-slash', 'fa-eye');
          }
        });
      });

      // ── Password strength meter ────────────────────────────────────────────
      const pwInput  = document.getElementById('new_password');
      const bar      = document.getElementById('pwStrengthBar');
      const label    = document.getElementById('pwStrengthLabel');

      pwInput.addEventListener('input', () => {
        const val = pwInput.value;
        let score = 0;
        if (val.length >= 8)              score++;
        if (/[A-Z]/.test(val))            score++;
        if (/[0-9]/.test(val))            score++;
        if (/[^A-Za-z0-9]/.test(val))     score++;

        const levels = [
          { pct: '25%',  color: '#e53e3e', text: 'Weak' },
          { pct: '50%',  color: '#dd6b20', text: 'Fair' },
          { pct: '75%',  color: '#d69e2e', text: 'Good' },
          { pct: '100%', color: '#38a169', text: 'Strong' },
        ];

        const lvl = levels[Math.max(0, score - 1)] || levels[0];
        bar.style.width      = val.length ? lvl.pct : '0';
        bar.style.background = lvl.color;
        label.textContent    = val.length ? lvl.text : '';
        label.style.color    = lvl.color;
      });

      // ── Client-side match check before submit ──────────────────────────────
      document.getElementById('resetForm').addEventListener('submit', e => {
        const pw1 = document.getElementById('new_password').value;
        const pw2 = document.getElementById('confirm_password').value;
        if (pw1 !== pw2) {
          e.preventDefault();
          alert('Passwords do not match. Please try again.');
        }
      });
    </script>

    <script src="../assets/js/script.js" defer></script>
  </body>
</html>
