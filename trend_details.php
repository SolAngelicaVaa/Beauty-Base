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
// NOTE: This array includes product recommendations.
$trends = [
    [
        'id' => 'bold_blush',
        'title' => 'Bold blush',
        'short_desc' => 'A bold blush color is one of the hottest trends this year. The heavier the blush, the better.',
        'image_url' => 'Bilder/bold.blush.jpg',
        'full_content' => 'Bold blush is about daring to use color. Apply high on the cheekbones and sweep the color lightly up towards the temples for a lifting effect. Pink, peach, and even red are popular shades. This creates a youthful and fresh glow. Experiment with cream or liquid blush for a more natural finish. Remember: Build the color up gradually to avoid a patchy result. For an extra clean look, keep the rest of your makeup minimalist.',
        'products' => [ 
            ['name' => 'Make Me Blush Bold Blurring Blush', 'brand' => 'YSL', 'link' => 'https://www.kicks.no/yves-saint-laurent-make-me-blush-bold-blurring-blush-42?utm_source=google&utm_medium=cpc&utm_campaign=DD%20%7C%20NO%20%7C%20S%20%7C%20Shopping%20%7C%20Brand&gad_source=1&gad_campaignid=19553747901&gbraid=0AAAAADuwE3oZVz7CPzVCMKpg-vtUTMB4S&gclid=CjwKCAiAoNbIBhB5EiwAZFbYGN_jIz2hVOZzt636P2tDIOcbu0rUM9Hn7YQGFkXWwFo-_36lhvjRYhoCmeAQAvD_BwE'],
            ['name' => 'Cheek Thrills Multi-Finish Face Trio', 'brand' => 'Morphe', 'link' => 'https://www.kicks.no/morphe-cheek-thrills-multi-finish-face-trio-wine-dine'],
            ['name' => 'CHEEKS OUT FREESTYLE CREAM BLUSH', 'brand' => 'Fenty Beauty', 'link' => 'https://fredrikoglouisa.no/produkt/cheeks-out-freestyle-cream-blush-3g/?attribute_pa_farge=raisin-standardz&gad_source=1&gad_campaignid=20914469726&gbraid=0AAAAADEe18KkDlM8K0_6YLRw8ZB3fYMNM&gclid=CjwKCAiAoNbIBhB5EiwAZFbYGFwqkNelOkUgy3cnLZ4ClyQlNZY9RyL2-oD_xH5PQpYt9Z_0YwTNsBoC0JwQAvD_BwE']
        ]
    ],
    [
        'id' => '90s_vibez',
        'title' => "90's vibez",
        'short_desc' => "Who said the 90's were over? From thin brows to metallic eyeshadow, the 90's got it all",
        'image_url' => 'Bilder/nittitall.webp',
        'full_content' => 'The nineties are back in full force! Think dark lip liners, shimmering (metallic) eyeshadow in blue and silver, and the iconic thin brow shape. While the thin brows might be difficult for everyone to recreate, the dark, matte lipsticks and the grunge-inspired eyeshadow are easy to implement. You can also include "frosty" eyeshadows for an authentic look. Feel free to combine with a matte complexion and light contour.',
        'products' => [ 
            ['name' => 'Mini Sugar Eye Shadow Palette', 'brand' => 'Anastasia Beverly Hills', 'link' => 'https://www.vita.no/makeup/oyne/oyenskygge/oyenskyggepalett/anastasia-beverly-hills-mini-sugar-eye-shadow-palette?v=240919011&gclsrc=aw.ds&gad_source=1&gad_campaignid=21066557411&gbraid=0AAAAADjQubWq-Laj-oAn5D2rXIwn9cG0p&gclid=CjwKCAiAoNbIBhB5EiwAZFbYGCbMO70NWfLXwEvgKh6kcSceC3tbfrbuptKnHKnFByI5AwPicYuizxoC7PcQAvD_BwE'],
            ['name' => 'Rouge Allure Velvet Les Perles, color "Sophistiquee"', 'brand' => 'Chanel', 'link' => 'https://www.chanel.com/no/sminke/p/151449/rouge-allure-velvet-les-perles-limited-edition-luminous-matt-lip-colour/?utm_campaign=fb_mu_sea_ao_multiplecategories&utm_source=Google&utm_medium=cpc&utm_content=shopping&gad_source=1&gad_campaignid=22592414638&gclid=CjwKCAiAoNbIBhB5EiwAZFbYGCgLSA39mv9MfSxyO89FmVoY8a3cegjmUZjhWbawg9nF536tErJ-MRoCexUQAvD_BwE'],
        ]
    ],
    [
        'id' => 'monochromatic_makeup',
        'title' => 'Monochromatic Makeup',
        'short_desc' => 'Harmonizing your eyes, cheeks, and lips to create a chic, cohesive look.',
        'image_url' => 'Bilder/mono.webp',
        'full_content' => 'Monochromatic makeup is about using the same color palette on your eyes, cheeks, and lips. This creates an incredibly cohesive and sophisticated expression. For example, use a peach-colored blush that also functions as eyeshadow and a lipstick in the same shade. It is the ultimate "effortless chic" look. The secret lies in using different textures of the same color – matte on the lips, cream on the cheeks, and light shimmer on the eyes.',
        'products' => [ 
            ['name' => 'DIORSHOW Mono Couleur', 'brand' => 'Dior', 'link' => 'https://fredrikoglouisa.no/produkt/diorshow-mono-couleur-2-g/?attribute_pa_farge=240-denim&gad_source=1&gad_campaignid=20914469726&gbraid=0AAAAADEe18KkDlM8K0_6YLRw8ZB3fYMNM&gclid=CjwKCAiAoNbIBhB5EiwAZFbYGDC8wR4rsImKdE03I4U74EVciqBjwuB52YJUL3YC17ykjulNLy8_oxoCNbUQAvD_BwE'],
            ['name' => 'Eye Tint, color"Mahogany"', 'brand' => 'Armani Beauty', 'link' => 'https://www.zalando.no/armani-beauty-eye-tint-oeyenskygge-mahogany-a4u34e018-b23.html?ssku=A4U34E018-B2303P9000&lang=no&otid=default&wmc=SEM470_NB_GO._8547063993_710858624_36657267065.&opc=2211&mpp=google|v1||pla-294505072980||1010882||g|c||163890259613||pla|A4U34E018-B2303P9000|294505072980|1|&gclsrc=aw.ds&gad_source=1&gad_campaignid=710858624&gbraid=0AAAAADM13oM6hQSu6Gqgol_WuXc65JSNY&gclid=CjwKCAiAoNbIBhB5EiwAZFbYGPe88tqAd4gJLVIsmnFNrLdpjxrnpY86rptI_oPU-Y3QOQMP7QRWMBoC-foQAvD_BwE'],
            ['name' => 'AFTERGLOW LIP BALM, color"wicked ways"', 'brand' => 'NARS', 'link' => 'https://www.zalando.no/nars-afterglow-lip-balm-leppebalsam-wicked-ways-n2q34e00p-i11.html?ssku=N2Q34E00P-I110003000&lang=no&otid=default&wmc=SEM470_NB_GO._8547063993_710858624_36657267065.&opc=2211&mpp=google|v1||pla-294505072980||1010882||g|c||163890259613||pla|N2Q34E00P-I110003000|294505072980|1|&gclsrc=aw.ds&gad_source=1&gad_campaignid=710858624&gbraid=0AAAAADM13oM6hQSu6Gqgol_WuXc65JSNY&gclid=CjwKCAiAoNbIBhB5EiwAZFbYGIpYyYHFdh7l9kfx99klk5Uvsa2eSAP1ET_6aiZI462aewl40xGBlhoCg-YQAvD_BwE'],
        ]
    ],
    [
        'id' => 'soft_brows',
        'title' => 'Soft brows',
        'short_desc' => 'A move away from heavily defined type brows towards a softer look.',
        'image_url' => 'Bilder/soft.brows.jpg',
        'full_content' => 'Forget the sharp, Instagram brows. The trend is moving towards softer, feathered brows. Use brow powder or a light pencil, and finish with a tinted brow gel that brushes the hairs upward. The goal is to create a natural, bushy, yet well-groomed shape without harsh edges. This provides a much more youthful and fresh expression that perfectly suits the "clean aesthetic" you are aiming for.',
        'products' => [
            ['name' => 'Brow Freeze', 'brand' => 'Anastasia Beverly Hills', 'link' => 'https://www.vita.no/anastasia-beverly-hills/brow-freeze?v=364091&gclsrc=aw.ds&gad_source=1&gad_campaignid=21066557411&gbraid=0AAAAADjQubWq-Laj-oAn5D2rXIwn9cG0p&gclid=CjwKCAiAoNbIBhB5EiwAZFbYGKKZ1hel57LxAsXO8f4upB28Q9KOnA-pXKZYQiqupc5cqo8uhPApwRoCEWQQAvD_BwE'],
            ['name' => '24-Hour Brow Setter - Transparent eyebrow geø', 'brand' => 'Benefit', 'link' => 'https://www.kicks.no/benefit-24-hour-brow-setter-eyebrow-gel-clear'],
        ]
    ],
    [
        'id' => 'blurred_ombre_lips',
        'title' => 'Blurred & Ombré lips',
        'short_desc' => 'Softly diffused lip lines with creamy balms or glosses, or ombré lips are still trending.',
        'image_url' => 'Bilder/ombre.lips.jpg',
        'full_content' => 'This trend, inspired by Korean beauty (K-Beauty), is about softening the lip line. Either by diffusing the color at the edges ("blurred") or by using a lighter color on the outside and a darker/stronger color in the center ("ombré"). This gives a soft, romantic, and "bitten-lip" effect. Use a creamy lipstick or lip color and dab it in with your finger. Finish with a light lip gloss for a dewy finish.',
        'products' => [
            ['name' => 'MAC lip pencil, color"Cherry"', 'brand' => 'MAC', 'link' => 'https://www.kicks.no/mac-lip-pencil-whirl?utm_source=google&utm_medium=cpc&utm_campaign=DD%20%7C%20NO%20%7C%20S%20%7C%20Shopping%20%7C%20Brand&gad_source=1&gad_campaignid=19553747901&gbraid=0AAAAADuwE3oZVz7CPzVCMKpg-vtUTMB4S&gclid=CjwKCAiAoNbIBhB5EiwAZFbYGBTO390PIxIDlV4FV8ITeB_IVMtnlzvpT-WWHl79Vy1x5GI4I3t7ARoCp8kQAvD_BwE'],
            ['name' => 'Macximal sleek satin lipstick, color "Paramount"', 'brand' => 'MAC', 'link' => 'https://fredrikoglouisa.no/produkt/macximal-sleek-satin-lipstick-mini-15-g/?attribute_pa_farge=paramount&gad_source=1&gad_campaignid=18469881055&gbraid=0AAAAADEe18KiT1llc6_NXlVutxS3FkzQF&gclid=CjwKCAiAoNbIBhB5EiwAZFbYGNdxujkdv65_y53uijEKrlHuJC7ZGxrEHF2OISHesKb0Cz7ncMbMUBoCa_oQAvD_BwE'],
        ]
    ]
];

$trend_id = $_GET['trend'] ?? null;
$current_trend = null;

foreach ($trends as $t) {
    if ($t['id'] === $trend_id) {
        $current_trend = $t;
        break;
    }
}

if (!$current_trend) {
    header('Location: trends.php');
    exit;
}

$title = $current_trend['title'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
         background-color: #f9f4fc;
            font-family: 'Times New Roman', serif;
        }
        .navbar {
            background: rgba(255, 255, 255, 0.7); 
            backdrop-filter: blur(6px);
        }
        :root {
            --beauty-color: #e3b8d2;
            --beauty-color-hover: #d49bbd;
        }
        .trend-image {
            max-height: 500px;
            object-fit: cover;
            border-radius: 5px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        /* Matcher .btn-beauty stilen for å gå tilbake */
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
        /* Footer */
        footer {
            background-color: #e6cdf6ff  !important; 
            color: white !important;
        }
        /* Ny stil for produktkortene */
        .product-card {
            background-color: #ffffff;
            border: 1px solid #eee;
            border-radius: 5px;
            transition: box-shadow 0.3s;
        }
        .product-card:hover {
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        }
        .product-link {
             color: var(--beauty-color);
             font-weight: 600;
             text-decoration: none;
             transition: color 0.2s;
        }
        .product-link:hover {
            color: var(--beauty-color-hover);
            text-decoration: underline;
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

<div class="container py-5" style="margin-top: 85px;">
    <div class="row">
        <div class="col-12">
            <h1 class="display-4 fw-bold mb-4 text-center"><?php echo $current_trend['title']; ?></h1>
        </div>
    </div>
    
    <div class="row justify-content-center">
        <div class="col-lg-8 mb-5">
            <img src="<?php echo str_replace('images/', 'Bilder/', $current_trend['image_url']); ?>" class="img-fluid trend-image w-100" alt="Image for <?php echo $current_trend['title']; ?>" onerror="this.onerror=null; this.src='https://via.placeholder.com/1200x500.png?text=Bilde+kommer';">
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <p class="h4 text-muted mb-4"><?php echo $current_trend['short_desc']; ?></p>
            <hr>
            <div class="mt-4 fs-5" style="line-height: 1.8;">
                <p><?php echo nl2br($current_trend['full_content']); ?></p> 
            </div>
            
            <?php if (!empty($current_trend['products'])): ?>
            <h3 class="fw-bold mt-5 mb-4 text-center">💖 Recommended Products 💖</h3>
            
            <div class="row g-3">
                <?php foreach ($current_trend['products'] as $product): ?>
                <div class="col-md-6">
                    <div class="p-3 product-card">
                        <h5 class="fw-bold fs-6 mb-1"><?php echo $product['name']; ?></h5>
                        <p class="text-muted mb-2 small">by <?php echo $product['brand']; ?></p>
                        <a href="<?php echo $product['link']; ?>" class="product-link">View Product →</a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <hr class="mt-5">
            <?php endif; ?>
            
            <div class="mt-5 text-center">
                <a href="trends.php" class="btn btn-beauty-outline btn-lg">← Back to trends</a>
            </div>
        </div>
    </div>
</div>

<footer class="mt-5 py-3 text-center" style="background-color: #989094ff; color: white;">
    <div class="container">
        <p class="mb-0">© 2025 BeautyBase | Where Beauty Connects </p>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>