<?php
session_start();
require 'config.php'; // forventer $conn (mysqli)

$message = "";
$message_class = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';

    if (empty($email)) {
        $message = "Please enter your email address.";
        $message_class = "alert-danger";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Invalid email format.";
        $message_class = "alert-danger";
    } else {
        $generic_success_message = "If your email address is in our system, a password reset link has been sent.";
        $message_class = "alert-success";
        $message = $generic_success_message;

        $stmt = $conn->prepare("SELECT userId, userName FROM USERS WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows === 1) {
            $user = $result->fetch_assoc();
            $userId = $user['userId'];

            $token = bin2hex(random_bytes(32));
            $expires = date("Y-m-d H:i:s", time() + 3600);

            $update = $conn->prepare("UPDATE USERS SET resetToken = ?, resetExpires = ? WHERE userId = ?");
            $update->bind_param("ssi", $token, $expires, $userId);
            $update->execute();
            $update->close();

            $resetLink = "https://web01.usn.no/~223573/Prosjekt%20BeautyBase/forgot_password.php" . $token;

            // --- Send e-post med mail() ---
            $to = $email;
            $subject = "BeautyBase Password Reset";
            $body = "Hello " . $user['userName'] . ",\n\n";
            $body .= "You requested a password reset. Click the link below to reset your password:\n\n";
            $body .= $resetLink . "\n\n";
            $body .= "This link will expire in 60 minutes.\n\n";
            $body .= "If you did not request this, please ignore this email.\n\n";
            $headers = "From: support@BeautyBase.com\r\n";
            $headers .= "Reply-To: support@BeautyBase.com\r\n";
            $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

            @mail($to, $subject, $body, $headers); // @ for å undertrykke warnings
        }

        if ($stmt) $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="no">
<head>
  <meta charset="UTF-8">
  <title>Forgot Password - BeautyBase</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
     :root {
         --beauty-color: #e3b8d2;
         --beauty-color-hover: #d49bbd;
         --text-dark: #212529;
     }
     body {
         background-image: url('Bilder/powderBrush.jpg');
         background-size: cover;
         background-position: center;
         background-attachment: fixed;
         font-family: 'Times New Roman', serif;
     }
     .glass-card {
         background: rgba(255, 255, 255, 0.85);
         backdrop-filter: blur(10px);
         border-radius: 20px;
         border: 1px solid rgba(255, 255, 255, 0.3);
         box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.15);
     }
     .form-control { border-radius: 50px; padding: 12px 20px; }
     .btn-beauty {
         background-color: var(--beauty-color);
         color: white; border: none; border-radius: 50px; padding: 12px;
         font-weight: bold; text-transform: uppercase; letter-spacing: 1px;
         transition: all 0.3s ease;
     }
     .btn-beauty:hover {
         background-color: var(--beauty-color-hover);
         transform: translateY(-2px);
         color: white; box-shadow: 0 4px 10px rgba(227,184,210,0.6);
     }
     .back-link { color: #666; text-decoration: none; font-size: 0.9rem; transition: color 0.3s; }
     .back-link:hover { color: var(--beauty-color-hover); text-decoration: underline; }
     h3 { font-weight: bold; letter-spacing: 1px; color: var(--text-dark); }
  </style>
</head>
<body class="d-flex align-items-center justify-content-center vh-100">

<div class="card glass-card p-5 text-center" style="max-width: 420px; width: 100%;">
  <h3 class="fw-bold mb-3">Forgot Password</h3>
  <p class="text-muted small mb-4">Enter your email address to receive a password reset link.</p>

  <?php if (!empty($message)) : ?>
    <div class="alert <?= htmlspecialchars($message_class) ?> text-center rounded-pill py-2 small mb-3"><?= htmlspecialchars($message) ?></div>
  <?php endif; ?>

  <form method="POST" novalidate>
    <div class="mb-4">
      <label class="form-label fw-semibold ms-2 small text-muted">Email Address</label>
      <input type="email" class="form-control" name="email" placeholder="name@example.com" required>
    </div>

    <button type="submit" class="btn btn-beauty w-100 mb-4">Send Reset Link</button>
  </form>

  <div class="mt-3 border-top pt-3">
      <a href="login.php" class="back-link">&leftarrow; Back to Login</a>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
