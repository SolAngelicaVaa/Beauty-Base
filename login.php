<?php
session_start();
require 'config.php';

$error = "";
$email = "";

// Success from reset
$success_message = "";
if (isset($_SESSION['success_message'])) {
    $success_message = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = "Please fill in all fields";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format";
    } else {

        $stmt = $conn->prepare("SELECT userId, userName, password, role FROM USERS WHERE Email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows === 1) {
            $user = $result->fetch_assoc();

            if (password_verify($password, $user['password'])) {

                session_regenerate_id(true);
                $_SESSION['userId'] = $user['userId'];
                $_SESSION['userName'] = $user['userName'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['logged_in'] = true;

                // ADMIN → ADMIN PANEL
                if ($user['role'] === 'admin') {
                    header("Location: admin/admin_dashboard.php");
                    exit();
                }

                // NORMAL USER
                header("Location: forside.php");
                exit();
            }
        }

        $error = "Invalid email or password";
        $stmt->close();
    }
}
?>



<!DOCTYPE html>
<html lang="no">
<head>
  <meta charset="UTF-8">
  <title>Log in - BeautyBase</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
  <style>
      :root {
          --beauty-color: #e3b8d2;
          --beauty-color-hover: #d49bbd;
          --text-dark: #212529;
      }
      body {
          background-image: url('Bilder/powderBrush.jpg');
          background-size: cover; background-position: center; background-attachment: fixed;
          font-family: 'Times New Roman', serif;
      }
      .glass-card {
          background: rgba(255, 255, 255, 0.85);
          backdrop-filter: blur(10px);
          border-radius: 20px;
          border: 1px solid rgba(255, 255, 255, 0.3);
          box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.15);
      }
      h3 { font-weight: bold; letter-spacing: 1px; color: var(--text-dark); }
      .form-control { border-radius: 50px; padding: 12px 20px; border: 1px solid #ddd; background: rgba(255,255,255,0.9); }
      .form-control:focus { box-shadow: 0 0 0 4px rgba(227,184,210,0.3); border-color: var(--beauty-color); }
      .btn-beauty {
          background-color: var(--beauty-color); color: white; border: none; border-radius: 50px; padding: 12px;
          font-weight: bold; text-transform: uppercase; letter-spacing:1px; transition: all .3s ease;
      }
      .btn-beauty:hover { background-color: var(--beauty-color-hover); transform: translateY(-2px); color: white; box-shadow: 0 4px 10px rgba(227,184,210,0.6); }
      .bottom-links a { color: #666; text-decoration: none; font-size: 0.9rem; transition: color 0.3s; }
      .bottom-links a:hover { color: var(--beauty-color-hover); text-decoration: underline; }
  </style>
</head>
<body class="d-flex align-items-center justify-content-center vh-100">

<div class="card glass-card p-5" style="max-width: 420px; width: 100%;">
  <div class="text-center mb-4">
      <h2 class="fw-bold">BEAUTYBASE</h2>
      <p class="text-muted small">Welcome back, please login to your account.</p>
  </div>

  <?php if (!empty($success_message)) : ?>
    <div class='alert alert-success text-center rounded-pill py-2 small mb-3'><?= htmlspecialchars($success_message) ?></div>
  <?php endif; ?>

  <?php if (!empty($error)) : ?>
    <div class='alert alert-danger text-center rounded-pill py-2 small mb-3'><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <form method="POST" novalidate>
    <div class="mb-3">
      <label class="form-label fw-semibold ms-2 small text-muted">Email Address</label>
      <input type="email" class="form-control" name="email" placeholder="name@example.com" value="<?= htmlspecialchars($email) ?>" required>
    </div>
    <div class="mb-4">
      <label class="form-label fw-semibold ms-2 small text-muted">Password</label>
      <input type="password" class="form-control" name="password" placeholder="••••••••" required>
    </div>

    <button type="submit" class="btn btn-beauty w-100 mb-3">Log In</button>
  </form>

  <div class="d-flex justify-content-between align-items-center bottom-links mt-2 border-top pt-3">
      <a href="forside.php"><i class="bi bi-arrow-left"></i> Back to Home</a>
      <span>New here? <a href="registrer.php" class="register-link">Register</a></span>
  </div>

  <div class="text-center mt-3">
      <a href="forgot_password.php" class="small text-muted">Forgot Password?</a>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
