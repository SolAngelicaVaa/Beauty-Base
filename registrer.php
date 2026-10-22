<?php
session_start();
require 'config.php'; 

$error = "";
$email = "";
$username = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['email'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $passwordConfirm = $_POST['passwordConfirm'] ?? '';

    //Validering
    if (empty($email) || empty($username) || empty($password)) {
        $error = "All fields are required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format";
    } elseif (strlen($password) < 8) {
        $error = "Password must be at least 8 characters";
    } elseif ($password !== $passwordConfirm) {
        $error = "Passwords do not match";
    } else {
        // Sjekk om e-post finnes
        $stmt = $conn->prepare("SELECT userId FROM USERS WHERE email = ? OR userName = ?");
        $stmt->bind_param("ss", $email, $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0){
          $error = "Email or username already exists";
        } else {
                // Registrer bruker
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("INSERT INTO USERS (userName, email, password) VALUES (?, ?, ?)");
                $stmt->bind_param("sss", $username, $email, $hash);
                
                if ($stmt->execute()) {
                    $userId = $conn->insert_id;
                    $_SESSION['userId'] = $userId;
                    $_SESSION['username'] = $username;
                    $_SESSION['role'] = 'guest'; // Setter standard rolle
                    $_SESSION['logged_in'] = true;
                    
                    header("Location: forside.php");
                    exit();
                } else {
                    $error = "Registration failed";
                }
            }
            $stmt->close();
        }
    }
?>

<!DOCTYPE html>
<html lang="no">
<head>
  <meta charset="UTF-8">
  <title>Register - BeautyBase</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
  
  <style>
       :root {
          --beauty-color: #e3b8d2; 
          --beauty-color-hover: #d49bbd;
          --text-dark: #212529;
      }

      body {
          background-image: url('Bilder/svartBrush.jpg'); /* Din mørke bakgrunn */
          background-size: cover;
          background-position: center;
          background-attachment: fixed;
          font-family: 'Times New Roman', serif;
          min-height: 100vh; /* Sikrer at bakgrunnen dekker hele høyden */
          padding: 20px 0; /* Litt luft oppe og nede på små skjermer */
      }

      /* Glassmorphism effekt */
      .glass-card {
          background: rgba(255, 255, 255, 0.90); /* Litt mindre gjennomsiktig enn login pga mørk bakgrunn */
          backdrop-filter: blur(10px);
          border-radius: 20px;
          border: 1px solid rgba(255, 255, 255, 0.3);
          box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.3); /* Mørkere skygge pga mørk bakgrunn */
      }

      h3 {
          font-weight: bold;
          letter-spacing: 1px;
          color: var(--text-dark);
      }

      /* Input felter */
      .form-control {
          border-radius: 50px;
          padding: 12px 20px;
          border: 1px solid #ddd;
          background: rgba(255, 255, 255, 0.9);
      }

      .form-control:focus {
          box-shadow: 0 0 0 4px rgba(227, 184, 210, 0.3);
          border-color: var(--beauty-color);
      }

      /* Hovedknapp */
      .btn-beauty {
          background-color: var(--beauty-color);
          color: white;
          border: none;
          border-radius: 50px;
          padding: 12px;
          font-weight: bold;
          text-transform: uppercase;
          letter-spacing: 1px;
          transition: all 0.3s ease;
      }

      .btn-beauty:hover {
          background-color: var(--beauty-color-hover);
          transform: translateY(-2px);
          color: white;
          box-shadow: 0 4px 10px rgba(227, 184, 210, 0.6);
      }

      /* Linker nederst */
      .bottom-links a {
          color: #666;
          text-decoration: none;
          font-size: 0.9rem;
          transition: color 0.3s;
      }
      
      .bottom-links a:hover {
          color: var(--beauty-color-hover);
          text-decoration: underline;
      }
      
      .login-link {
          font-weight: bold;
          color: var(--text-dark) !important;
      }
  </style>
</head>
<body class="d-flex align-items-center justify-content-center">

<div class="card glass-card p-5" style="max-width: 450px; width: 100%;">
  
  <div class="text-center mb-4">
      <h2 class="fw-bold">BEAUTYBASE</h2>
      <p class="text-muted small">Create your account to join the community.</p>
  </div>

  <?php if (!empty($error)) : ?>
    <div class='alert alert-danger text-center rounded-pill py-2 small'><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <form method="POST">
    <div class="mb-3">
      <label for="username" class="form-label fw-semibold ms-2 small text-muted">Username</label>
      <input type="text" class="form-control" id="username" name="username" placeholder="Choose a username" value="<?= htmlspecialchars($username) ?>" required>
    </div>
    
    <div class="mb-3">
      <label for="email" class="form-label fw-semibold ms-2 small text-muted">E-Mail</label>
      <input type="email" class="form-control" id="email" name="email" placeholder="name@example.com" value="<?= htmlspecialchars($email) ?>" required>
    </div>
    
    <div class="mb-3">
      <label for="password" class="form-label fw-semibold ms-2 small text-muted">Password</label>
      <input type="password" class="form-control" id="password" name="password" placeholder="Min. 8 characters" required>
    </div>
    
    <div class="mb-4">
      <label for="passwordConfirm" class="form-label fw-semibold ms-2 small text-muted">Confirm Password</label>
      <input type="password" class="form-control" id="passwordConfirm" name="passwordConfirm" placeholder="Repeat password" required>
    </div>
    
    <button type="submit" class="btn btn-beauty w-100 mb-4">Create Account</button>
  </form>

  <div class="d-flex justify-content-between align-items-center bottom-links mt-2 border-top pt-3">
      <a href="forside.php"><i class="bi bi-arrow-left"></i> Home</a>
      <span>Already a member? <a href="login.php" class="login-link">Log in</a></span>
  </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>