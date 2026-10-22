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

$title = "Hottest trends";

// Definer trendene med all informasjon (som før)
$trends = [
    [
        'id' => 'bold_blush',
        'title' => 'Bold blush',
        'short_desc' => 'A bold blush color is one of the hottest trends this year. The heavier the blush, the better.',
        'image_url' => 'Bilder/bold.blush.jpg' 
    ],
    [
        'id' => '90s_vibez',
        'title' => "90's vibez",
        'short_desc' => "Who said the 90's were over? From thin brows to metallic eyeshadow, the 90's got it all",
        'image_url' => 'Bilder/nittitall.webp' 
    ],
    [
        'id' => 'monochromatic_makeup',
        'title' => 'Monochromatic Makeup',
        'short_desc' => 'Harmonizing your eyes, cheeks, and lips to create a chic, cohesive look.',
        'image_url' => 'Bilder/mono.webp' 
    ],
    [
        'id' => 'soft_brows',
        'title' => 'Soft brows',
        'short_desc' => 'A move away from heavily defined type brows towards a softer look.',
        'image_url' => 'Bilder/soft.brows.jpg' 
    ],
    [
        'id' => 'blurred_ombre_lips',
        'title' => 'Blurred & Ombré lips',
        'short_desc' => 'Softly diffused lip lines with creamy balms or glosses, or ombré lips are still trending.',
        'image_url' => 'Bilder/ombre.lips.jpg' 
    ]
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">

    <style>
        body {
             background-color: #f9f4fc;
            font-family: 'Times New Roman', serif;
        }
        .navbar {
            background: rgba(255, 255, 255, 0.7); 
            backdrop-filter: blur(6px);
        }
        /* Definer den lilla aksentfargen for å bruke den på knapper og elementer */
        :root {
            --beauty-color: #e3b8d2;
            --beauty-color-hover: #d49bbd;
        }

        .card-custom {
            height: 100%;
            transition: transform 0.3s, box-shadow 0.3s;
            border-radius: 5px;
            overflow: hidden;
            text-decoration: none;
            color: inherit;
        }
        .card-custom:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
        }
        .card-img-top {
            height: 250px; /* Justert høyde for kortene */
            object-fit: cover; 
            width: 100%;
        }
        /* Matcher .btn-beauty stilen fra forside.php */
        .btn-dark { /* Gjenbruker .btn-dark i PHP, men styler den som .btn-beauty */
            background-color: var(--beauty-color); 
            color: #fff;
            border: none;
            border-radius: 50px;
            padding: 8px 20px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        .btn-dark:hover {
            background-color: var(--beauty-color-hover);
            color: white;
            box-shadow: 0 4px 10px rgba(0,0,0,0.15);
        }
        /* Matcher footer-fargen */
        footer {
            background-color:   #e6cdf6ff !important; 
            color: white !important;
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
<div class="container py-5" style="margin-top: 50px;">
    <h1 class="mb-5 text-center display-4 fw-bold">Hottest Trends</h1>
    
    <div class="row g-4 row-cols-1 row-cols-md-2 row-cols-lg-3">
        <?php foreach ($trends as $trend): ?>
        <div class="col d-flex">
            <a href="trend_details.php?trend=<?php echo $trend['id']; ?>" class="card card-custom w-100 shadow-sm border-0">
                <img src="<?php echo str_replace('images/', 'Bilder/', $trend['image_url']); ?>" class="card-img-top" alt="Image for <?php echo $trend['title']; ?>" onerror="this.onerror=null; this.src='https://via.placeholder.com/600x400.png?text=Bilde+kommer';"> 
                <div class="card-body d-flex flex-column">
                    <h5 class="card-title fw-bold text-dark"><?php echo $trend['title']; ?></h5>
                    <p class="card-text text-muted"><?php echo $trend['short_desc']; ?></p>
                    <div class="mt-auto pt-3"> 
                        <span class="btn btn-dark w-100">Read more</span>
                    </div>
                </div>
            </a>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<footer class="mt-5 py-3 text-center" style="background-color: #f9f4fc; color: white;">
    <div class="container">
        <p class="mb-0">© 2025 BeautyBase | Where Beauty Connects </p>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>