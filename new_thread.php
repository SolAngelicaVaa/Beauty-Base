<?php
// Start session bare én gang
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require 'config.php';

// Sjekk om brukeren er logget inn
$is_logged_in = isset($_SESSION['userId']);

// Definer rolle trygt
$user_role = $_SESSION['role'] ?? 'user';

// STOPP hvis ikke logget inn
if (!$is_logged_in) {
    header("Location: login.php?error=not_logged_in");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Hent og valider data
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $userId = (int) $_SESSION['userId'];

    // Tomme felt
    if ($title === '' || $content === '') {
        header("Location: new_thread.php?error=empty_fields");
        exit;
    }

    // Kall stored procedure CreateNewThread(title, content, userId)
    try {
        $sql = "CALL CreateNewThread(?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $title, $content, $userId);

        if ($stmt->execute()) {
            header("Location: forum.php?success=thread_created");
            exit;
        } else {
            header("Location: new_thread.php?error=db_error");
            exit;
        }
    } 
    catch (Exception $e) {
        error_log("DB Error: " . $e->getMessage());
        header("Location: new_thread.php?error=exception");
        exit;
    }
}

// Resten av HTML-koden følger under...
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create New Thread | BeautyBase</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <style>
        /* Fargevariabler for konsistens */
        :root {
            --beauty-color: #e3b8d2; 
            --beauty-color-hover: #d49bbd;
            --footer-color: #989094ff;
            --navbar-bg: rgba(255, 255, 255, 0.7); /* Konsekvent Navbar Bakgrunn */
        }
        body {
            background-color: #f9f4fc; /* Konsekvent Bakgrunn */
            font-family: 'Times New Roman', serif;
            padding-top: 70px; /* **Viktig for fixed-top navbar** */
        }
        /* KONISTENT NAVBAR STIL */
        .navbar {
            background: var(--navbar-bg) !important;
            backdrop-filter: blur(6px);
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .navbar-brand, .nav-link {
            color: #212529 !important; /* Svart tekst foran hvit/gjennomsiktig bakgrunn */
        }
        
        /* Unik styling for new_thread.php innhold */
        .btn-beauty {
            background-color: var(--beauty-color);
            color: white;
            border: none;
            border-radius: 50px; 
            padding: 10px 25px; 
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-beauty:hover {
            background-color: var(--beauty-color-hover);
            color: white; 
        }
        .btn-beauty-outline { 
            background-color: transparent;
            color: var(--beauty-color);
            border: 1px solid var(--beauty-color);
            border-radius: 50px;
            padding: 8px 20px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        .btn-beauty-outline:hover {
            background-color: var(--beauty-color);
            color: white;
            box-shadow: 0 4px 10px rgba(0,0,0,0.15);
        }
        .create-thread-card {
            background: white;
            border-radius: 12px;
            padding: 25px; 
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
            border: none;
            margin-bottom: 20px;
        }
        .form-control {
            border-radius: 10px;
            padding: 12px;
            border: 1px solid #ddd;
        }
        .form-control:focus {
            border-color: var(--beauty-color);
            box-shadow: 0 0 0 0.25rem rgba(227, 184, 210, 0.25);
        }
        ::placeholder {
            color: #aaa;
            opacity: 1; 
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg fixed-top">
  <div class="container">
    <a class="navbar-brand fw-bold fs-3" href="forside.php">BEAUTYBASE</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
      <ul class="navbar-nav">

        <li class="nav-item"><a class="nav-link" href="forside.php">Home</a></li>
        <li class="nav-item"><a class="nav-link" href="trends.php">Trends</a></li>
        <li class="nav-item"><a class="nav-link" href="product.php">Products</a></li>
        <li class="nav-item"><a class="nav-link" href="forum.php">Forum</a></li>
        <li class="nav-item"><a class="nav-link" href="profile.php">Profile</a></li>

        <?php if ($is_logged_in): ?>
            
            <!-- ADMIN KNAPP VISES KUN FOR ADMIN -->
            <?php if ($user_role === 'admin'): ?>
                <li class="nav-item">
                    <a class="nav-link fw-bold" href="admin/admin_dashboard.php">Admin Panel</a>

                </li>
            <?php endif; ?>

            <li class="nav-item"><a class="nav-link" href="logout.php">Log out</a></li>

        <?php else: ?>
            <li class="nav-item"><a class="nav-link" href="login.php">Log in</a></li>
        <?php endif; ?>

      </ul>
    </div>
  </div>
</nav>

<div class="container mt-5" style="max-width: 800px;">
    
    <a href="forum.php" class="btn btn-beauty-outline mb-4">← Back to forum</a>

    <h2 class="fw-bold text-center mb-3">✨ Create a new thread ✨</h2>
    <p class="text-muted text-center mb-5">Share your thoughts and kick off a new discussion!</p>
    
    <div class="create-thread-card">
        <form action="save_thread.php" method="POST">
            <div class="mb-3">
                <label for="title" class="form-label visually-hidden">Title</label> 
                <input type="text" name="title" id="title" class="form-control" placeholder="A catchy title for your discussion" required>
            </div>
            <div class="mb-4">
                <label for="content" class="form-label visually-hidden">Content</label> 
                <textarea name="content" id="content" class="form-control" rows="8" placeholder="Share your thoughts and questions here..." required></textarea>
            </div>
            <div class="d-grid">
                <button type="submit" class="btn btn-beauty">Publish Thread</button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>