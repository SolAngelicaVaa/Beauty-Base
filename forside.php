<?php
session_start();
require 'config.php'; 

// Sjekk om bruker er logget inn
$is_logged_in = isset($_SESSION['userId']);
$userId = $is_logged_in ? (int)$_SESSION['userId'] : 0;

// Hent rolle hvis logget inn
$user_role = "guest";

if ($is_logged_in) {
    $stmt = $conn->prepare("SELECT role FROM USERS WHERE userId = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $user_role = $row['role'];
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="no">
<head>       
    </div>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!--BootStrap-->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"> 
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">

    <!--Design-->
    <style>
      :root {
         --beauty-color: #e3b8d2; 
         --beauty-color-hover: #d49bbd;
         --footer-color: #989094ff;
         --navbar-bg: rgba(255, 255, 255, 0.7);
         }
        body {
          background-color: #f9f4fc;
            font-family: 'Times New Roman', serif;
        }
        .navbar {
            background: rgba(255, 255, 255, 0.7); 
            backdrop-filter: blur(6px);
        }
        .hero {
            margin-top: 80px;
            max-width: 800px;
            text-align: center;
            padding: 20px 0 10px 0;
        }
        .hero ul{
          text-align: left;
          display: inline-block;
        }
        
        .card-img-top {
          height: 350px;       
          object-fit: cover;   
          width: 100%;        
        }

        .card-custom {
            transition: transform 0.3s, box-shadow 0.3s;
        }
        .card-custom:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
        }
        .inspo-images .card {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

       .inspo-images .card:hover {
       transform: translateY(-8px); 
       box-shadow: 0 8px 20px rgba(0,0,0,0.2); 
       }
       .trend-img {
        max-height: 300px;   
        width: auto;         
        object-fit: cover;   
      }
     .btn-beauty {
      background-color: #e3b8d2; 
      color: #fff;
      border: none;
      border-radius: 50px;
      padding: 8px 20px;
      font-weight: 500;
      transition: all 0.3s ease;
      }

    .btn-beauty:hover {
      background-color: #d49bbd;
      color: white;
      box-shadow: 0 4px 10px rgba(0,0,0,0.15);
     }
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg fixed-top">
  <div class="container">
    <a class="navbar-brand fw-bold fs-3" href="forside.php">BEAUTYBASE</a>
    <div class="collapse navbar-collapse justify-content-end">
      <ul class="navbar-nav">

        <li class="nav-item"><a class="nav-link" href="forside.php">Home</a></li>
        <li class="nav-item"><a class="nav-link" href="trends.php">Trends</a></li>
        <li class="nav-item"><a class="nav-link" href="product.php">Products</a></li>
        <li class="nav-item"><a class="nav-link" href="forum.php">Forum</a></li>
        <li class="nav-item"><a class="nav-link" href="profile.php">Profile</a></li>

        <?php if ($is_logged_in): ?>
            
            <!-- ADMIN KNAPP VISES KUN FOR ADMIN -->
           <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
           <a class="nav-link fw-bold" href="admin/admin_dashboard.php">Admin Panel</a>
           <?php endif; ?>


            <li class="nav-item"><a class="nav-link" href="logout.php">Log out</a></li>

        <?php else: ?>
            <li class="nav-item"><a class="nav-link" href="login.php">Log in</a></li>
        <?php endif; ?>

      </ul>
    </div>
  </div>
</nav>

<!-- Hero section -->
<section class="hero container text-center">
   <h1 class="display-4 fw-bold">Your Beauty Community Awaits</h1>
   <p class="lead">Discover products, follow trends, and share experiences with beauty enthusiasts worldwide.</p>

   <p class="fw-bold mt-3">Ready to find your next favorite?<br> Dive in and let the community guide you.</p>

   <?php if (!$is_logged_in): ?>
      <h2>Are you ready to join our community?</h2>
      <div class="mt-3 mb-4">
        <a href="registrer.php" class="btn btn-beauty btn-lg me-3">Join now!</a>
      </div>
   <?php endif; ?>
</section>

<!-- Cards -->
<section class="inspo-images container my-2">
  <div class="row g-4 justify-content-center text-center">
    <div class="col-md-4">
      <a href="trends.php" class="text-decoration-none text-dark">
      <div class="card border-0 h-100 shadow-sm">
        <img src="Bilder/Look1.jpg" class="card-img-top img-fluid rounded shadow" alt="Trends">
        <div class="card-body">
          <h5 class="fw-bold">Trends</h5>
        </div>
      </div>
      </a>
    </div>

    <div class="col-md-4">
      <a href="product.php" class="text-decoration-none text-dark">
        <div class="card border-0 h-100 shadow-sm">
          <img src="Bilder/charlotte.jpg" class="card-img-top img-fluid rounded" alt="Products">
          <div class="card-body">
            <h5 class="fw-bold">Products</h5>
          </div>
        </div>
      </a>
    </div>

    <div class="col-md-4">
      <a href="forum.php" class="text-decoration-none text-dark">
        <div class="card border-0 h-100 shadow-sm">
          <img src="Bilder/Look6.jpg" class="card-img-top img-fluid rounded" alt="Popular forums">
          <div class="card-body">
            <h5 class="fw-bold">Popular forums</h5>
          </div>
        </div>
      </a>
    </div>
  </div>
</section>

<footer class="mt-5 py-3 text-center" style="background-color:  #e6cdf6ff; color: white;">
    <div class="container">
        <p>© 2025 BeautyBase | Where Beauty Connects </p>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
