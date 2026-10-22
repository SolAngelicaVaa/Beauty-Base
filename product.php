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

// Henter brukerens favoritter i én spørring (hvis logget inn)
$favorite_ids = [];
if ($is_logged_in) {
    $fav_stmt = $conn->prepare("SELECT product_id FROM FAVORITES WHERE user_id = ?");
    $fav_stmt->bind_param("i", $userId);
    $fav_stmt->execute();
    $fav_result = $fav_stmt->get_result();
    
    while ($row = $fav_result->fetch_assoc()) {
        $favorite_ids[$row['product_id']] = true;
    }
    $fav_stmt->close();
}

// Map for de statiske produktene i "Popular products right now"
// VIKTIG: Kontroller at IDene stemmer med de FAKTISKE IDene i din PRODUCTS-tabell!
$popular_products_map = [
    1 => ['name' => 'Urban Decay: All Nighter Setting Spray', 'image' => 'Bilder/urban_decay.webp', 'rating' => '4.8'],
    2 => ['name' => 'Haus Labs By Lady Gaga: Liquid Foundation', 'image' => 'Bilder/Haus-labs.webp', 'rating' => '4'],
    3 => ['name' => 'MILK Makeup: Hydro Grip Primer', 'image' => 'Bilder/hydroprimer.webp', 'rating' => '4'],
    4 => ['name' => 'DIOR: DiorShow 24h Buildable Volume Mascara', 'image' => 'Bilder/diro.webp', 'rating' => '4.1'],
];

// Map for de statiske produktene i "Our recommendations"
// Jeg antar IDene 5, 6, 7. Juster disse IDene hvis de er annerledes i din database!
$recommendations_map = [
    5 => ['name' => 'Clinique: Even Better Makeup', 'image' => 'Bilder/clinique.jpeg', 'rating' => '4.5'],
    6 => ['name' => 'Estée Lauder: Double Wear', 'image' => 'Bilder/estee.jpeg', 'rating' => '4.7'],
    7 => ['name' => 'Tarte: Shape Tape Concealer', 'image' => 'Bilder/tarte.avif', 'rating' => '4.6'],
];
?>

<!DOCTYPE html>
<html lang="no">
<head>      
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products | BeautyBase</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"> 
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">

    <style>
        /* Definerer fargevariabler (Dine Originale Farger) */
        :root {
            --beauty-color: #e3b8d2; /* Lys Rosa/Lilla Aksent */
            --beauty-color-hover: #d49bbd;
            --footer-bg: #e6cdf6ff; /* Lilla for Footer */
            --body-bg: #f9f4fc; /* Veldig lys lilla bakgrunn */
        }
    
        body {
            background-color: var(--body-bg);
            font-family: 'Times New Roman', serif; 
        }
        .navbar {
            background: rgba(255, 255, 255, 0.7); 
            backdrop-filter: blur(6px); 
        }
        .hero {
            margin-top: 80px; 
            text-align: center;
            padding: 40px 0;
        }
        
        .card-img-top {
            max-height: 350px;      
            object-fit: cover;      
        }

        .card-custom {
            transition: transform 0.3s, box-shadow 0.3s;
        }
        .card-custom:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
        }
        
        /* Knappestil: Pink/Lilla for Add */
        .btn-beauty {
            background-color: var(--beauty-color); 
            color: #fff;
            border: none;
            border-radius: 30px; 
            padding: 8px 20px;
            font-weight: 500;
            transition: all 0.3s ease;
            text-decoration: none;
            width: 100%; 
        }

        .btn-beauty:hover {
            background-color: var(--beauty-color-hover);
            color: white;
            box-shadow: 0 4px 10px rgba(0,0,0,0.15);
        }

        /* Knappestil: Rød for Remove (når favoritt) */
        .btn-danger {
            background-color: #dc3545; 
            border: none;
            color: #fff;
            border-radius: 30px;
            width: 100%;
            padding: 8px 20px;
        }
        
        /* Paginering Styling */
        .page-item.active .page-link {
            background-color: var(--beauty-color);
            border-color: var(--beauty-color);
            color: white;
        }
        .page-link {
            color: var(--beauty-color-hover); 
        }
        .page-link:hover {
            color: var(--beauty-color);
            background-color: #f8f9fa; 
        }
        
        /* Footer */
        footer {
            background-color:   var(--footer-bg);
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

<section class="hero container">
    <h1 class="display-4 fw-bold">Your Beauty Guide, Powered by Community</h1>
    <p class="lead">Discover the best and most popular makeup products.</p>
    <p>For the early mornings and the late nights. For the days you need a boost and the moments you're ready to take on the world. Our mission is to find the best products for you – highly-rated, community-tested favorites designed to enhance your light, not overshadow it. We only recommend the top-reviewed, tried-and-true staples made for a life in motion. So go ahead, find your next go-to product. Your perfect match awaits.</p>
</section>

<section class="container my-5">
    <h2 class="fw-bold text-center mb-4">Popular products right now </h2>
    <div class="row g-4">
    <?php
        // Viser de 4 første produktene
        foreach ($popular_products_map as $productId => $product): 
            $is_favorite = isset($favorite_ids[$productId]);
            $btn_class = $is_favorite ? 'btn-danger' : 'btn-beauty';
            $icon_class = $is_favorite ? 'bi-heart-fill' : 'bi-heart';
            $btn_text = $is_favorite ? 'Remove favorite' : 'Add to favorites';
        ?>
            <div class="col-md-3 col-sm-6">
                <div class="card card-custom border-0 shadow-sm h-100">
                    <img src="<?= htmlspecialchars($product['image']) ?>" class="card-img-top" alt="Product">
                    <div class="card-body text-center">
                        <h6><?= htmlspecialchars($product['name']) ?></h6>
                        <p>⭐ <?= htmlspecialchars($product['rating']) ?></p>
                        
                        <div class="mt-2">
                        <?php if ($is_logged_in): ?>
                            <button type="button" 
                                    class="favorite-toggle btn <?= $btn_class ?> w-100" 
                                    data-product-id="<?= $productId ?>"
                                    data-is-favorite="<?= $is_favorite ? 'true' : 'false' ?>">
                                <i class="bi <?= $icon_class ?>"></i>
                                <?= $btn_text ?>
                            </button>
                        <?php else: ?>
                            <a href="login.php" class="btn btn-beauty mt-2 w-100"><i class="bi bi-heart"></i> Add to favorites</a>
                        <?php endif; ?>
                        </div>
                        
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    
    <nav aria-label="Product page navigation 1" class="mt-5">
        <ul class="pagination justify-content-center">
            <li class="page-item disabled"><a class="page-link" href="#">Previous</a></li>
            <li class="page-item active" aria-current="page"><a class="page-link" href="#">1</a></li>
            <li class="page-item"><a class="page-link" href="product.php?page=2">2</a></li>
            <li class="page-item"><a class="page-link" href="product.php?page=3">3</a></li>
            <li class="page-item"><a class="page-link" href="product.php?page=2">Next</a></li>
        </ul>
    </nav>

</section>

<section class="container my-5">
    <h2 class="fw-bold text-center mb-4">Our recommendations</h2>
    <div class="row g-4 justify-content-center">
    <?php
        // Viser anbefalingene med samme stil og funksjonalitet
        foreach ($recommendations_map as $productId => $product): 
            $is_favorite = isset($favorite_ids[$productId]);
            $btn_class = $is_favorite ? 'btn-danger' : 'btn-beauty';
            $icon_class = $is_favorite ? 'bi-heart-fill' : 'bi-heart';
            $btn_text = $is_favorite ? 'Remove favorite' : 'Add to favorites';
        ?>
            <div class="col-md-4 col-sm-6">
                <div class="card card-custom border-0 shadow-sm h-100">
                    <img src="<?= htmlspecialchars($product['image']) ?>" class="card-img-top" alt="Product">
                    <div class="card-body text-center">
                        <h6><?= htmlspecialchars($product['name']) ?></h6>
                        <p>⭐ <?= htmlspecialchars($product['rating']) ?></p>
                        
                        <div class="mt-2">
                        <?php if ($is_logged_in): ?>
                            <button type="button" 
                                    class="favorite-toggle btn <?= $btn_class ?> w-100" 
                                    data-product-id="<?= $productId ?>"
                                    data-is-favorite="<?= $is_favorite ? 'true' : 'false' ?>">
                                <i class="bi <?= $icon_class ?>"></i>
                                <?= $btn_text ?>
                            </button>
                        <?php else: ?>
                            <a href="login.php" class="btn btn-beauty mt-2 w-100"><i class="bi bi-heart"></i> Add to favorites</a>
                        <?php endif; ?>
                        </div>
                        
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    
    <nav aria-label="Product page navigation 2" class="mt-5">
        <ul class="pagination justify-content-center">
            <li class="page-item disabled"><a class="page-link" href="#">Previous</a></li>
            <li class="page-item active" aria-current="page"><a class="page-link" href="#">1</a></li>
            <li class="page-item"><a class="page-link" href="#">2</a></li>
            <li class="page-item"><a class="page-link" href="#">Next</a></li>
        </ul>
    </nav>
</section>

<footer class="mt-5 py-3 text-center">
    <div class="container">
        <p>© 2025 BeautyBase | Where Beauty Connects </p>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Velger alle knapper i begge seksjoner
    const favoriteButtons = document.querySelectorAll('.favorite-toggle');

    favoriteButtons.forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.getAttribute('data-product-id');
            // Henter status fra data-attributtet
            let isFavorite = this.getAttribute('data-is-favorite') === 'true';
            
            const action = isFavorite ? 'remove' : 'add';

            this.disabled = true; // Deaktiver knappen under forespørsel

            fetch('favorite_action.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `productId=${productId}&action=${action}`
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('HTTP error! Status: ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    // Inverterer status og oppdaterer DOM
                    isFavorite = !isFavorite; 
                    this.setAttribute('data-is-favorite', isFavorite.toString());
                    updateButtonState(this, isFavorite);
                    
                    // Oppdaterer alle andre knapper med samme produkt-ID (viktig for synkronisering)
                    document.querySelectorAll(`.favorite-toggle[data-product-id="${productId}"]`).forEach(otherButton => {
                        if (otherButton !== this) {
                            otherButton.setAttribute('data-is-favorite', isFavorite.toString());
                            updateButtonState(otherButton, isFavorite);
                        }
                    });
                    
                } else {
                    if (data.message === 'Not logged in') {
                        alert('You must be logged in to add favorites.');
                        window.location.href = 'login.php'; 
                    } else {
                        alert('Error: ' + data.message); 
                    }
                }
            })
            .catch(error => {
                console.error('Fetch error:', error);
                alert('An error occurred during the request. Check the console for details: ' + error.message);
            })
            .finally(() => {
                this.disabled = false;
            });
        });
    });

    // Funksjon for å oppdatere knappens utseende og tekst
    function updateButtonState(buttonElement, isFavorite) {
        const heartIcon = isFavorite 
            ? '<i class="bi bi-heart-fill"></i> Remove favorite' 
            : '<i class="bi bi-heart"></i> Add to favorites';
        
        buttonElement.innerHTML = heartIcon; 

        if (isFavorite) {
            buttonElement.classList.add('btn-danger'); 
            buttonElement.classList.remove('btn-beauty');
        } else {
            buttonElement.classList.add('btn-beauty');
            buttonElement.classList.remove('btn-danger');
        }
    }
});
</script>

</body>
</html>