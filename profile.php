<?php
session_start();
require 'config.php';

// -------------- 1. SJEKK LOGIN FØRST ----------------
// Siden dette er profilsiden, sjekker vi dette med en gang.
if (!isset($_SESSION['userId'])) {
    header("Location: login.php");
    exit;
}

// Nå vet vi at brukeren er logget inn
$userId = (int)$_SESSION['userId'];
$is_logged_in = true; // Definerer denne så menyen din virker lenger ned

// -------------- 2. HENT ROLLE ----------------
$user_role = "guest"; // Default

$stmt = $conn->prepare("SELECT role FROM USERS WHERE userId = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $user_role = $row['role'];
}
$stmt->close();

// -------------- 3. HENT BRUKERDATA ----------------
$default_avatar = 'profile_images/default_avatar.png';
$user_data = [
    'userName' => 'User',
    'email' => 'No email',
    'bio' => 'No bio set.',
    'profile_picture' => $default_avatar
];

$user_stmt = $conn->prepare("SELECT userName, email, bio, profile_picture FROM USERS WHERE userId = ?");
$user_stmt->bind_param("i", $userId);
$user_stmt->execute();
$user_result = $user_stmt->get_result();

if ($row = $user_result->fetch_assoc()) {
    $user_data = array_merge($user_data, $row);
}
$user_stmt->close();

// -------------- 4. RIKTIG BILDESTI ----------------
$profile_img_path = $user_data['profile_picture'];

if (
    empty($profile_img_path) ||
    !file_exists($profile_img_path) ||
    !is_file($profile_img_path)
) {
    $profile_img_path = $default_avatar;
}

// -------------- 5. HENT FAVORITTER ----------------
$favorites = [];
$fav_stmt = $conn->prepare("
    SELECT F.product_id, P.name, P.imageLink
    FROM FAVORITES F
    JOIN PRODUCTS P ON F.product_id = P.productId
    WHERE F.user_id = ?
");
$fav_stmt->bind_param("i", $userId);
$fav_stmt->execute();
$fav_result = $fav_stmt->get_result();

while ($fav = $fav_result->fetch_assoc()) {
    $favorites[] = $fav;
}
$fav_stmt->close();

// -------------- 7. HENT BRUKERENS FORUMINNLEGG ----------------
$user_posts = [];
$post_stmt = $conn->prepare("
    SELECT 
        P.postId, 
        T.threadId, 
        T.title, 
        P.created,
        P.content 
    FROM POSTS P
    JOIN THREADS T ON P.threadId = T.threadId
    WHERE P.userId = ? 
    ORDER BY P.created DESC
");
$post_stmt->bind_param("i", $userId);
$post_stmt->execute();
$post_result = $post_stmt->get_result();

while ($p = $post_result->fetch_assoc()) {
    $user_posts[] = $p;
}
$post_stmt->close();

// -------------- 6. HÅNDTER URL-MELDINGER (NYTT) ----------------
$success_message = null;
$error_message = null;

if (isset($_GET['success'])) {
    if ($_GET['success'] === 'bio') {
        $success_message = "Bio updated successfully!";
    } elseif ($_GET['success'] === 'picture') {
        $success_message = "Profile picture updated successfully!";
    }
} elseif (isset($_GET['error'])) {
    if ($_GET['error'] === 'bio') {
        $error_message = "Failed to update bio.";
    } elseif ($_GET['error'] === 'picture') {
        $error_message = "Failed to upload profile picture.";
    } elseif ($_GET['error'] === 'file_type') {
        $error_message = "Invalid file type. Only JPEG, PNG, or GIF allowed.";
    }
}
?>

<!DOCTYPE html>
<html lang="no">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile | BeautyBase</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">

    <style>
        :root {
            --beauty-color: #e3b8d2;
            --beauty-color-hover: #d49bbd;
            --footer-bg: #e6cdf6ff;
            --body-bg: #f9f4fc;
        }
        body {
            background-color: var(--body-bg);
            font-family: 'Times New Roman', serif;
            padding-top: 60px;
        }
        .navbar {
            background: rgba(255,255,255,0.9);
            backdrop-filter: blur(6px);
            box-shadow: 0 0 5px rgba(0,0,0,0.1);
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1030;
        }
        .sidebar {
            background-color: #ffffff;
            border-radius: 10px;
            padding: 20px;
        }
        .btn-beauty {
            background-color: var(--beauty-color);
            color: white;
            border: none;
        }
        .btn-beauty:hover {
            background-color: var(--beauty-color-hover);
            color: white;
        }
        .profile-picture-placeholder {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            border: 2px solid var(--beauty-color);
            object-fit: cover;
        }
        .profile-picture-container {
            background-color: #fff;
            border: 1px solid #dee2e6;
            border-radius: 0.25rem;
            padding: 20px;
        }
        .card-img-top-fav {
            height: 180px;
            object-fit: cover;
        }
    </style>
</head>
<body>

<!-- NAVBAR (Uendret) -->
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
        <li class="nav-item"><a class="nav-link active fw-bold" href="profile.php">Profile</a></li>

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

<div class="container my-5">
    <div class="row">

        <!-- SIDEBAR (Uendret) -->
        <div class="col-md-3">
            <div class="sidebar shadow-sm">
                <h5 class="mb-3" style="color:var(--beauty-color);">Account</h5>
                <a class="nav-link active text-dark mb-2" href="profile.php"><i class="bi bi-person-fill"></i> Profile</a>
                <a class="nav-link text-dark" href="#favorites"><i class="bi bi-heart-fill"></i> Favorites</a>
               <a class="nav-link text-dark" href="#my_posts">
    <i class="bi bi-chat-text-fill me-2"></i> My Posts
</a>
                <h5 class="mt-4 mb-3" style="color:var(--beauty-color);">Quick actions</h5>
                <a href="new_thread.php" class="btn btn-sm btn-outline-secondary w-100 mb-2">+ New thread</a>
                <a href="product.php" class="btn btn-sm btn-outline-secondary w-100">Browse products</a>
            </div>
        </div>

        <!-- MAIN CONTENT -->
        <div class="col-md-9">

            <!-- SUKSESS/FEILMELDINGER (Uendret) -->
            <?php if ($success_message): ?>
                <div class="alert alert-success mt-3" role="alert"><?= htmlspecialchars($success_message); ?></div>
            <?php endif; ?>

            <?php if ($error_message): ?>
                <div class="alert alert-danger mt-3" role="alert"><?= htmlspecialchars($error_message); ?></div>
            <?php endif; ?>

            <!-- PROFIL-SEKSJON -->
            <div class="card p-4 profile-picture-container mb-4">

                <!-- ENDRING: FLYTTET BILDET OG KNAPPEN INN I SAMME FLEX-CONTAINER -->
                <div class="d-flex flex-column flex-sm-row align-items-center mb-4">
                    <img src="<?= htmlspecialchars($profile_img_path); ?>"
                             class="profile-picture-placeholder me-4 mb-3 mb-sm-0"
                             alt="Profilbilde">

                    <!-- Brukerinformasjon -->
                    <div class="text-center text-sm-start me-auto">
                        <h4 class="fw-bold"><?= htmlspecialchars($user_data['userName']); ?></h4>
                        <p class="text-muted mb-0"><?= htmlspecialchars($user_data['email']); ?></p>
                        <p class="text-secondary small mt-1">Role: <?= ucfirst(htmlspecialchars($user_role)); ?></p>
                    </div>

                    <!-- NY PLASSERING AV BILDEOPPLASTINGS-KNAPPEN -->
                    <button class="btn btn-sm btn-outline-secondary mt-3 mt-sm-0" 
                            type="button" 
                            data-bs-toggle="collapse" 
                            data-bs-target="#picture-edit-form" 
                            aria-expanded="false" 
                            aria-controls="picture-edit-form">
                        <i class="bi bi-camera"></i> Change Picture
                    </button>
                </div>
                <!-- SLUTT ENDRING -->

                <!-- Edit Mode for Profile Picture (Skjult form) -->
                <!-- Lagt til border-top og litt padding for å skille fra headeren når skjemaet er åpent -->
                <div class="collapse border-top pt-3 mb-4" id="picture-edit-form">
                    <h6 class="fw-bold mb-3">Upload New Profile Picture</h6>
                    <form action="profile_edit.php" method="POST" enctype="multipart/form-data">
                        <label for="profilePicture" class="form-label">Select image (JPEG/PNG/GIF, max 5MB)</label>
                        <input type="file" class="form-control" id="profilePicture" name="profilePicture" accept="image/jpeg, image/png, image/gif" required>
                        <input type="hidden" name="action" value="update_picture">

                        <button class="btn btn-beauty mt-2">Upload Picture</button>
                    </form>
                </div>

                <!-- BIO - View/Edit Toggle (Fjernet border-top fra h6 for bedre flyt) -->
                <h6 class="fw-bold mt-4 pt-3">Bio</h6>

                <?php $current_bio = $user_data['bio'] ?? 'No bio set.'; ?>
                
                <!-- View Mode for Bio -->
                <div id="bio-view" class="card card-body bg-light mb-3 p-3">
                    <p class="mb-0 text-break" style="min-height: 20px;">
                        <?= nl2br(htmlspecialchars($current_bio)) ?>
                    </p>
                    <!-- Viser skjemaet ved klikk (knappen flyttet til høyre) -->
                    <div class="d-flex justify-content-end">
                        <button class="btn btn-sm btn-outline-secondary mt-2 w-sm-25" type="button" data-bs-toggle="collapse" data-bs-target="#bio-edit-form" aria-expanded="false" aria-controls="bio-edit-form">
                            Edit Bio
                        </button>
                    </div>
                </div>

                <!-- Edit Mode for Bio (Skjult form) -->
                <div class="collapse" id="bio-edit-form">
                    <form action="profile_edit.php" method="POST" class="mb-4">
                        <label for="bio" class="form-label">Update Bio Content</label>
                        <textarea id="bio" name="bio" class="form-control mb-2" rows="4" required><?= htmlspecialchars($current_bio) ?></textarea>
                        <input type="hidden" name="action" value="update_bio">
                        
                        <button type="submit" class="btn btn-beauty">
                            Update Bio
                        </button>
                    </form>
                </div>
                <!-- SLUTT ENDRING: BIO -->

            </div>


            <!-- FAVORITTER (Uendret) -->
            <div id="favorites" class="card p-4 shadow-sm">
                <h5 class="mb-3">❤️ My Favorites (<?= count($favorites); ?>)</h5>

                <?php if (!empty($favorites)): ?>
                    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-4">
                        <?php foreach ($favorites as $fav): ?>
                            <div class="col">
                                <div class="card h-100 text-center shadow-sm">

                                    <img src="<?= htmlspecialchars($fav['imageLink']); ?>"
                                             class="card-img-top-fav"
                                             alt="<?= htmlspecialchars($fav['name']); ?>">

                                    <div class="card-body">
                                        <h6 class="text-truncate"><?= htmlspecialchars($fav['name']); ?></h6>

                                        <form action="favorite_action.php" method="POST" class="d-inline">
                                            <input type="hidden" name="productId" value="<?= $fav['product_id']; ?>">
                                            <input type="hidden" name="action" value="remove">
                                            <button class="btn btn-danger btn-sm mt-2">
                                                <i class="bi bi-x-circle-fill"></i> Remove
                                            </button>
                                        </form>

                                        <a href="product.php?id=<?= $fav['product_id']; ?>"
                                           class="btn btn-outline-secondary btn-sm mt-2 d-block">View Product</a>
                                    </div>

                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-muted mt-3">You have no favorites yet. Add some from the products page!</p>
                <?php endif; ?>
            </div>
 <!-- USER POSTS SECTION -->
<div id="my_posts" class="card p-4 shadow-sm mt-4">
    <h5 class="mb-3">📝 My Forum Posts (<?= count($user_posts); ?>)</h5>

    <?php if (!empty($user_posts)): ?>
        <div class="list-group">

            <?php foreach ($user_posts as $post): ?>
                <a href="threads.php?id=<?= $post['threadId']; ?>" 
                   class="list-group-item list-group-item-action d-block text-decoration-none text-dark">

                    <strong><?= htmlspecialchars($post['title']); ?></strong><br>

                    <small class="text-secondary fst-italic">
                        "<?= htmlspecialchars(substr($post['content'], 0, 50)); ?>..."
                    </small>

                    <small class="text-muted d-block">
                        Posted: <?= date("d.m.Y H:i", strtotime($post['created'])); ?>
                    </small>

                </a>
            <?php endforeach; ?>

        </div>

    <?php else: ?>
        <p class="text-muted">You haven't created any forum posts yet.</p>
    <?php endif; ?>
</div>




        </div>
    </div>
</div>



<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>