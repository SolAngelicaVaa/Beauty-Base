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
        body {
            font-family: 'Times New Roman', serif;
        }
        .navbar {
            background: rgba(255, 255, 255, 0.7); /* Gjennomsiktig */
            backdrop-filter: blur(6px); /* Glass-effekt */
        }
        .hero {
            margin-top: 80px; /* plass til navbar */
            text-align: center;
            padding: 40px 0;
        }
        
        .card-img-top {
        max-height: 350px;   /* bestemmer maks høyde */
        object-fit: cover;   /* gjør at de ser like ut uten å strekke */
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
       transform: translateY(-8px); /* løfter kortet litt opp */
       box-shadow: 0 8px 20px rgba(0,0,0,0.2); /* sterkere skygge */
       }

    </style>
</head>
<body>

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
            <?php if ($user_role === 'admin'): ?>
                <li class="nav-item">
                    <a class="nav-link text-danger fw-bold" href="admin_dashboard.php">
                        Admin Panel
                    </a>
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

<section class="hero container">
   <h1 class="display-4 fw-bold"></h1>
        <p class="lead"></p>
        <p></p>
</section>

<footer class="mt-5 py-3 text-center" style="background-color: #989094ff; color: white;">
    <div class="container">
        <p>© 2025 BeautyBase | Where Beauty Connects </p>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>