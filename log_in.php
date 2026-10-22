<?php
session_start();
require_once __DIR__ . '/config.php';
/* --- UTLOGGING ------------------------------------------------------
   Menyen i header.php lenker til log_in.php?logout=1 */
if (isset($_GET['logout'])) {
    $_SESSION = [];
    session_destroy();
    header("Location: log_in.php");
    exit();
}

/* Hvis du allerede er innlogget, gå rett til dashboardet */
if (!empty($_SESSION['logged_in'])) {
    header("Location: dashboard.php");
    exit();
}

$error = "";
$success_message = "";
$email = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {

        $error = "Please fill in all fields";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $error = "Invalid email format";

    } elseif (!isset($conn) || !($conn instanceof mysqli)) {

        /* config.php må sette opp $conn = new mysqli(...) */
        $error = "Database connection is not available";

    } else {

        /* Hver rolle: tabell, kolonner og hva som skal vises som brukernavn.
           Rekkefølgen er sjekkrekkefølgen: admin, manager, employee. */
        $roles = [
            'admin' => [
                'table' => 'ADMINISTRATOR',
                'id'    => 'AdminId',
                'email' => 'Epost',
                'pass'  => 'Passord',
                'name'  => 'Epost',
            ],
            'manager' => [
                'table' => 'COMPANYMANAGER',
                'id'    => 'BedriftsId',
                'email' => 'Epost',
                'pass'  => 'Passord',
                'name'  => 'Navn',
            ],
            'employee' => [
                'table' => 'EMPLOYEE',
                'id'    => 'AnsattId',
                'email' => 'AnsattEpost',
                'pass'  => 'AnsattPassord',
                'name'  => 'AnsattEpost',
            ],
        ];

        $role     = null;
        $userId   = null;
        $userName = null;

        try {
            foreach ($roles as $roleName => $c) {

                $sql = "SELECT * FROM {$c['table']} WHERE {$c['email']} = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows === 1) {
                    $row = $result->fetch_assoc();

                    if (password_verify($password, $row[$c['pass']])) {
                        $role     = $roleName;
                        $userId   = $row[$c['id']];
                        $userName = $row[$c['name']];
                    }
                }

                $stmt->close();

                if ($role !== null) {
                    break;
                }
            }
        } catch (mysqli_sql_exception $e) {
            error_log('Login error: ' . $e->getMessage());
            $error = "Something went wrong. Please try again later.";
        }

        if ($role !== null) {

            session_regenerate_id(true);

            $_SESSION['logged_in'] = true;
            $_SESSION['role']      = $role;
            $_SESSION['email']     = $email;
            $_SESSION['userId']    = $userId;
            $_SESSION['userName']  = $userName;

            /* Alle roller sendes til samme dashboard. header.php tilpasser
               menyen etter rolle. */
            header("Location: dashboard.php");
            exit();

        } elseif ($error === "") {

            $error = "Invalid email or password";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="no">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Log in - WorkSync</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
  <style>
      :root {
          --beauty-color: #3456d2;
          --beauty-color-hover: #2d37c2;
          --text-dark: #212529;
      }
      body {
          font-family: 'Times New Roman', serif;
          margin: 0;
          height: 100vh;
          overflow: hidden;
          position: relative;
          background-color: #1a4bc8;
      }
      .glass-card {
          background: rgba(255, 255, 255, 0.55);
          backdrop-filter: blur(10px);
          border-radius: 20px;
          border: 1px solid rgba(255, 255, 255, 0.3);
          box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.15);
          position: relative;
          z-index: 1;
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

      /* Dynamisk bakgrunn */
      .wave-background {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 0;
        overflow: hidden;
        background: linear-gradient(135deg, #1b47c1, #0f2c82);
      }
      .wave-line {
        position: absolute;
        width: 220%;
        height: 60px;
        opacity: 0.6;
        filter: blur(10px);
        animation: waveAnimation 8s ease-in-out infinite alternate;
      }
      .line-blue {
        background: linear-gradient(90deg, transparent, #4da6ff, transparent);
        top: 15%;
        animation-duration: 9s;
      }
      .line-green {
        background: linear-gradient(90deg, transparent, #2ecc71, transparent);
        top: 45%;
        animation-duration: 6.5s;
        animation-delay: -2s;
      }
      .line-yellow{
        background: linear-gradient(90deg, transparent, #f1c40f, transparent);
        top: 75%;
        animation-duration: 10s;
        animation-delay: -5s;
      }
      @keyframes waveAnimation {
        0% {
            transform: translateX(-35%) translateY(0) rotate(-5deg);
        }
        25% {
            transform: translateX(-15%) translateY(-50px) rotate(3deg);
        }
        50% {
            transform: translateX(10%) translateY(30px) rotate(5deg);
        }
        75% {
            transform: translateX(-20%) translateY(-35px) rotate(-3deg);
        }
        100% {
            transform: translateX(-35%) translateY(40px) rotate(-4deg);
        }
      }
  </style>
</head>
<body class="d-flex align-items-center justify-content-center vh-100">

<div class="wave-background">
    <div class="wave-line line-blue"></div>
    <div class="wave-line line-green"></div>
    <div class="wave-line line-yellow"></div>
</div>

<div class="card glass-card p-5" style="max-width: 420px; width: 100%;">
  <div class="text-center mb-4">
      <h2 class="fw-bold">WorkSync</h2>
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
      <a href="index.php"><i class="bi bi-arrow-left"></i> Back to Home</a>
      <span>New here? <a href="register.php" class="register-link">Register</a></span>
  </div>

  <div class="text-center mt-3">
      <a href="forgot_password.php" class="small text-muted">Forgot Password?</a>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>