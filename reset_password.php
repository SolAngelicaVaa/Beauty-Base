<?php
session_start();
require 'config.php'; // forventer $conn (mysqli)

$message = "";
$message_class = "";
$valid_token = false;
$userId = null;
$token = isset($_GET['token']) ? $_GET['token'] : '';

// Hvis token mangler, vis feilmelding
if (empty($token)) {
    $message = "<div class='alert alert-danger text-center rounded-pill py-2 small'>Missing password reset token.</div>";
    $message_class = "alert-danger";
} else {
    // Sjekk token mot USERS-tabellen og om det er ikke-utløpt
    $stmt = $conn->prepare("SELECT userId, resetExpires FROM USERS WHERE resetToken = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows === 1) {
        $row = $result->fetch_assoc();
        $expires = $row['resetExpires'];
        $userId = $row['userId'];

        if ($expires && strtotime($expires) > time()) {
            $valid_token = true;
        } else {
            $message = "<div class='alert alert-danger text-center rounded-pill py-2 small'>The password reset link is invalid or has expired.</div>";
            $message_class = "alert-danger";
        }
    } else {
        $message = "<div class='alert alert-danger text-center rounded-pill py-2 small'>The password reset link is invalid or has expired.</div>";
        $message_class = "alert-danger";
    }
    if ($stmt) $stmt->close();
}

// Håndter skjemaet når bruker sender nytt passord
if ($valid_token && $_SERVER["REQUEST_METHOD"] === "POST") {
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';

    if (empty($password) || empty($confirm_password)) {
        $message = "<div class='alert alert-danger text-center rounded-pill py-2 small'>Please fill in all fields.</div>";
        $message_class = "alert-danger";
    } elseif ($password !== $confirm_password) {
        $message = "<div class='alert alert-danger text-center rounded-pill py-2 small'>Passwords do not match.</div>";
        $message_class = "alert-danger";
    } elseif (strlen($password) < 8) {
        $message = "<div class='alert alert-danger text-center rounded-pill py-2 small'>Password must be at least 8 characters long.</div>";
        $message_class = "alert-danger";
    } else {
        // Hash og oppdater passord
        $hashed = password_hash($password, PASSWORD_DEFAULT);

        $update = $conn->prepare("UPDATE USERS SET password = ?, resetToken = NULL, resetExpires = NULL WHERE userId = ?");
        $update->bind_param("si", $hashed, $userId);
        $update->execute();
        $update->close();

        // Sett suksessmelding i session og redirect til login
        $_SESSION['success_message'] = "Your password has been successfully reset. Please log in with your new password.";
        header("Location: login.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="no">
<head>
  <meta charset="UTF-8">
  <title>Reset Password - BeautyBase</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
  <style>
     :root { --beauty-color: #e3b8d2; --beauty-color-hover: #d49bbd; --text-dark: #212529; }
     body {
         background-image: url('Bilder/powderBrush.jpg');
         background-size: cover; background-position: center; background-attachment: fixed;
         font-family: 'Times New Roman', serif;
     }
     .glass-card {
         background: rgba(255, 255, 255, 0.85); backdrop-filter: blur(10px); border-radius: 20px;
         border: 1px solid rgba(255, 255, 255, 0.3); box-shadow: 0 8px 32px 0 rgba(31,38,135,0.15);
     }
     .form-control { border-radius: 50px; padding: 12px 20px; border: 1px solid #ddd; background: rgba(255,255,255,0.9); }
     .form-control:focus { box-shadow: 0 0 0 4px rgba(227,184,210,0.3); border-color: var(--beauty-color); }
     .btn-beauty { background-color: var(--beauty-color); color: white; border: none; border-radius: 50px; padding: 12px; font-weight: bold; text-transform: uppercase; letter-spacing:1px; transition: all .3s ease; }
     .btn-beauty:hover { background-color: var(--beauty-color-hover); transform: translateY(-2px); color: white; box-shadow: 0 4px 10px rgba(227,184,210,0.6); }
     .bottom-links a { color: #666; text-decoration: none; font-size: 0.9rem; transition: color 0.3s; }
     .bottom-links a:hover { color: var(--beauty-color-hover); text-decoration: underline; }
     h3 { font-weight: bold; letter-spacing: 1px; color: var(--text-dark); }
  </style>
</head>
<body class="d-flex align-items-center justify-content-center vh-100">

<div class="card glass-card p-5" style="max-width:420px; width:100%;">
  <div class="text-center mb-4">
    <h3 class="fw-bold">New Password</h3>
    <p class="text-muted small">Please enter and confirm your new password.</p>
  </div>

  <?= $message ?>

  <?php if ($valid_token): ?>
    <form method="POST" novalidate>
      <div class="mb-3">
        <label class="form-label fw-semibold ms-2 small text-muted">New Password</label>
        <input type="password" class="form-control" name="password" placeholder="••••••••" required>
      </div>
      <div class="mb-4">
        <label class="form-label fw-semibold ms-2 small text-muted">Confirm New Password</label>
        <input type="password" class="form-control" name="confirm_password" placeholder="••••••••" required>
      </div>
      <button type="submit" class="btn btn-beauty w-100 mb-4">Change Password</button>
    </form>
  <?php else: ?>
    <div class="text-center">
      <a href="forgot_password.php" class="btn btn-outline-secondary">Request a new password reset</a>
    </div>
  <?php endif; ?>

  <div class="d-flex justify-content-center bottom-links mt-2 border-top pt-3">
    <a href="login.php"><i class="bi bi-arrow-left"></i> Back to Login</a>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
