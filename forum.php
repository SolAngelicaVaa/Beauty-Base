<?php
session_start();
require 'config.php'; 

// Sjekk om bruker er logget inn
$is_logged_in = isset($_SESSION['userId']);
$userId = $is_logged_in ? (int)$_SESSION['userId'] : 0;

// Hent rolle hvis logget inn
$user_role = "guest";
if ($is_logged_in) {
    $stmtRole = $conn->prepare("SELECT role FROM USERS WHERE userId = ?");
    $stmtRole->bind_param("i", $userId);
    $stmtRole->execute();
    $resRole = $stmtRole->get_result();
    if ($rowRole = $resRole->fetch_assoc()) {
        $user_role = $rowRole['role'] ?? 'guest';
    }
    $stmtRole->close();
}

// Tidssone
date_default_timezone_set('Europe/Oslo');

// Paging
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;
$default_avatar = 'profile_images/default_avatar.png';

// 1) Hent totalt antall tråder
$total_threads = 0;
if ($res = $conn->query("SELECT COUNT(*) as c FROM THREADS")) {
    $row = $res->fetch_assoc();
    $total_threads = (int)$row['c'];
    $res->free();
}
$total_pages = max(1, (int)ceil($total_threads / $limit));

// 2) Hent tråder for denne siden
$sql = "
    SELECT 
        T.threadId, T.title, T.userId, U.userName, U.profile_picture AS profilePicture, T.created,
        COUNT(P.postId) AS postCount
    FROM THREADS T
    JOIN USERS U ON T.userId = U.userId
    LEFT JOIN POSTS P ON T.threadId = P.threadId
    GROUP BY T.threadId
    ORDER BY T.created DESC
    LIMIT ? OFFSET ?
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $limit, $offset);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Forum | BeautyBase</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
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
            padding-top: 70px;
        }
        .navbar {
            background: var(--navbar-bg) !important;
            backdrop-filter: blur(6px);
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .navbar-brand, .nav-link { color: #212529 !important; }
        .forum-container { max-width: 900px; margin: 50px auto; background: white; border-radius: 15px; padding: 25px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        .btn-beauty { background-color: var(--beauty-color); color: white; border: none; border-radius: 50px; padding: 10px 25px; font-weight: 600; }
        .btn-beauty:hover { background-color: var(--beauty-color-hover); color: white; }
        .thread-card { transition: all 0.2s ease; }
        .thread-card:hover { background-color: #f6ebfa; }
        .pagination .page-item.active .page-link { background-color: var(--beauty-color); border-color: var(--beauty-color); color: white; }
        .profile-list-img { width: 45px; height: 45px; object-fit: cover; border-radius: 50%; border: 1px solid #eee; }
        .delete-thread-btn { padding: 5px 10px; font-size: 0.8rem; border-radius: 50px; }
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
            <?php if ($user_role === 'admin'): ?>
                <li class="nav-item"><a class="nav-link fw-bold" href="admin/admin_dashboard.php">Admin Panel</a></li>
            <?php endif; ?>
            <li class="nav-item"><a class="nav-link" href="logout.php">Log out</a></li>
        <?php else: ?>
            <li class="nav-item"><a class="nav-link" href="login.php">Log in</a></li>
        <?php endif; ?>

      </ul>
    </div>
  </div>
</nav>

<div class="forum-container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold">💬 Community Forum</h2>
        <?php if ($is_logged_in): ?>
            <a href="new_thread.php" class="btn btn-beauty px-4">+ New Thread</a>
        <?php else: ?>
            <a href="login.php" class="btn btn-beauty px-4">Log in to post</a>
        <?php endif; ?>
    </div>

    <?php if ($result && $result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
            <?php 
                $reply_count = max(0, (int)$row['postCount'] - 1);
                $img_path = !empty($row['profilePicture']) && file_exists($row['profilePicture']) ? $row['profilePicture'] : $default_avatar;
            ?>
            <a href="threads.php?id=<?= (int)$row['threadId'] ?>" class="text-decoration-none text-dark">
                <div class="card mb-3 shadow-sm thread-card"> 
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <img src="<?= htmlspecialchars($img_path) ?>" alt="<?= htmlspecialchars($row['userName']) ?>'s Profile" class="profile-list-img me-3">
                            <div>
                                <h5 class="card-title fw-bold mb-1"><?= htmlspecialchars($row['title']) ?></h5>
                                <p class="text-muted small mb-0">By <span class="fw-semibold"><?= htmlspecialchars($row['userName']) ?></span> • <?= date("d.m.Y H:i", strtotime($row['created'])) ?></p>
                            </div>
                        </div>

                        <div class="text-end d-flex flex-column align-items-end">
                            <p class="mb-2 small">💬 <?= $reply_count ?> replies</p>
                            <?php if ($is_logged_in && $userId == $row['userId']): ?>
                                <a href="delete_thread.php?id=<?= (int)$row['threadId'] ?>" class="btn btn-outline-danger btn-sm delete-thread-btn" onclick="event.preventDefault(); if(confirm('Are you sure? This will delete the thread and all posts.')){ window.location.href='delete_thread.php?id=<?= (int)$row['threadId'] ?>'; }"> <i class="bi bi-trash-fill"></i> Delete</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </a>
        <?php endwhile; ?>
    <?php else: ?>
        <p class="text-center text-muted p-4">No threads yet. Be the first to start a discussion!</p>
    <?php endif; ?>

    <?php if ($total_pages > 1): ?>
    <nav aria-label="Thread navigation" class="mt-5">
        <ul class="pagination justify-content-center">
            <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                <a class="page-link" href="?page=<?= $page - 1 ?>">Previous</a>
            </li>
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item <?= ($page == $i) ? 'active' : '' ?>">
                    <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                </li>
            <?php endfor; ?>
            <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : '' ?>">
                <a class="page-link" href="?page=<?= $page + 1 ?>">Next</a>
            </li>
        </ul>
    </nav>
    <?php endif; ?>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
