<?php
// threads.php - show a single thread with replies + inline edit
session_start();
require 'config.php'; // forventer $conn (mysqli)

// --- Auth / runtime variables ---
$is_logged_in = isset($_SESSION['userId']);
$user_role = $_SESSION['role'] ?? 'guest';
$current_user_id = isset($_SESSION['userId']) ? (int)$_SESSION['userId'] : null;

date_default_timezone_set('Europe/Oslo');

$thread_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$default_avatar = 'profile_images/default_avatar.png';

if ($thread_id <= 0) {
    header("Location: forum.php");
    exit;
}

// ----- 1) Fetch thread metadata and posts (main + replies) -----
// Fetch thread (basic check exists)
$stmt = $conn->prepare("SELECT threadId, title, created, userId FROM THREADS WHERE threadId = ? LIMIT 1");
$stmt->bind_param("i", $thread_id);
$stmt->execute();
$res = $stmt->get_result();
if (!$res || $res->num_rows === 0) {
    echo "The thread does not exist.";
    exit;
}
$threadRow = $res->fetch_assoc();
$stmt->close();

// Fetch all posts in the thread (main post first, ordered ascending)
$stmt = $conn->prepare("
    SELECT P.postId, P.content, P.created, P.userId, U.userName, U.profile_picture
    FROM POSTS P
    JOIN USERS U ON P.userId = U.userId
    WHERE P.threadId = ?
    ORDER BY P.created ASC
");
$stmt->bind_param("i", $thread_id);
$stmt->execute();
$res = $stmt->get_result();
$all_posts = $res->fetch_all(MYSQLI_ASSOC);
$stmt->close();

if (!$all_posts || count($all_posts) === 0) {
    echo "No posts found for this thread.";
    exit;
}

// Separate main post and replies
$main_post = $all_posts[0];
$replies = array_slice($all_posts, 1);

// Helper: safe image path
function safe_profile_image($row, $default_avatar) {
    if (!empty($row['profile_picture']) && file_exists($row['profile_picture'])) {
        return $row['profile_picture'];
    }
    return $default_avatar;
}
$main_post_img = safe_profile_image($main_post, $default_avatar);

?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($threadRow['title']) ?> | BeautyBase Forum</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        :root{
            --beauty-color: #e3b8d2;
            --beauty-color-hover: #d49bbd;
            --navbar-bg: rgba(255,255,255,0.7);
        }
        body { background-color: #f9f4fc; font-family: 'Times New Roman', serif; padding-top:70px; }
        .navbar { background: var(--navbar-bg) !important; backdrop-filter: blur(6px); box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .thread-container { max-width:900px; margin:50px auto; }
        .post-card { background:white; border-radius:12px; padding:25px; box-shadow:0 4px 8px rgba(0,0,0,0.05); margin-bottom:20px; }
        .reply-card { background:#fff; border:1px solid #f0e0f0; border-radius:10px; padding:15px; margin-bottom:15px; box-shadow:0 2px 4px rgba(0,0,0,0.03); }
        .btn-beauty { background-color:var(--beauty-color); color:white; border:none; border-radius:50px; padding:10px 25px; font-weight:600; }
        .btn-beauty:hover { background-color:var(--beauty-color-hover); color:white; }
        .post-profile-img { width:40px; height:40px; object-fit:cover; border-radius:50%; border:1px solid var(--beauty-color); }
        .reply-profile-img { width:30px; height:30px; object-fit:cover; border-radius:50%; border:1px solid #eee; }
        .btn-delete { font-size:0.85rem; color:#dc3545; text-decoration:none; }
        .btn-edit { font-size:0.85rem; text-decoration:none; color:#0d6efd; margin-right:8px; }
        .edit-area { margin-top:10px; }
        .small-muted { color:#6c757d; }
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

<div class="container thread-container">
    <a href="forum.php" class="btn btn-beauty mb-4">← Back to Forum</a>

    <!-- MAIN POST -->
    <div class="post-card">
        <h1 class="fw-bold mb-3"><?= htmlspecialchars($threadRow['title']) ?></h1>

        <div class="d-flex align-items-center mb-3">
            <img src="<?= htmlspecialchars($main_post_img) ?>" alt="<?= htmlspecialchars($main_post['userName'] ?? '') ?>'s Profile" class="post-profile-img me-3">
            <span class="fw-semibold"><?= htmlspecialchars($main_post['userName'] ?? '') ?></span>
        </div>

        <p class="lead"><?= nl2br(htmlspecialchars($main_post['content'])) ?></p>

        <small class="text-muted d-block border-top pt-2 mt-3">
            Posted: <span class="fw-semibold"><?= date("d.m.Y H:i", strtotime($main_post['created'])) ?></span>
        </small>
    </div>

    <!-- REPLIES -->
    <?php if (!empty($replies)): ?>
        <h3 class="mt-5 mb-3 fw-bold">Replies (<?= count($replies) ?>)</h3>

        <?php foreach ($replies as $post): 
            // Ensure types
            $postId = (int)$post['postId'];
            $post_user_id = (int)$post['userId'];
            $post_content = $post['content'];
            $post_created = $post['created'];
            $post_username = $post['userName'];
            $reply_img_path = !empty($post['profile_picture']) && file_exists($post['profile_picture']) ? $post['profile_picture'] : $default_avatar;
            $can_edit = ($current_user_id !== null && $current_user_id === $post_user_id) || $user_role === 'admin';
        ?>
            <div class="reply-card" id="reply-card-<?= $postId ?>">
                <!-- Display area -->
                <div id="reply-display-<?= $postId ?>">
                    <p><?= nl2br(htmlspecialchars($post_content)) ?></p>

                    <div class="d-flex justify-content-between align-items-center border-top pt-2 mt-2">
                        <div class="d-flex align-items-center">
                            <img src="<?= htmlspecialchars($reply_img_path) ?>" alt="<?= htmlspecialchars($post_username) ?>'s Profile" class="reply-profile-img me-2">
                            <small class="text-muted">
                                <span class="fw-semibold"><?= htmlspecialchars($post_username) ?></span> • Posted: <span class="fw-semibold"><?= date("d.m.Y H:i", strtotime($post_created)) ?></span>
                            </small>
                        </div>

                        <div>
                            <?php if ($can_edit): ?>
                                <a href="#" class="btn-edit me-2 edit-btn" data-post-id="<?= $postId ?>"><i class="bi bi-pencil-square"></i> Edit</a>
                                <a href="delete_post.php?id=<?= $postId ?>&thread=<?= $thread_id ?>" class="btn-delete" onclick="return confirm('Are you sure you want to delete this post?');"><i class="bi bi-trash-fill"></i> Delete</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Edit area (hidden by default) -->
                <?php if ($can_edit): ?>
                <div class="edit-area d-none" id="reply-edit-<?= $postId ?>">
                    <textarea class="form-control mb-2" id="reply-textarea-<?= $postId ?>" rows="4"><?= htmlspecialchars($post_content) ?></textarea>
                    <div class="d-flex gap-2">
                        <button class="btn btn-beauty save-edit" data-post-id="<?= $postId ?>">Save</button>
                        <button class="btn btn-secondary cancel-edit" data-post-id="<?= $postId ?>">Cancel</button>
                    </div>
                    <div class="mt-2 text-danger small d-none" id="reply-error-<?= $postId ?>"></div>
                </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>

    <?php else: ?>
        <p class="text-center text-muted mt-5">No replies yet. Be the first!</p>
    <?php endif; ?>

    <!-- REPLY FORM -->
    <?php if ($current_user_id): ?>
        <div class="post-card mt-5">
            <h3 class="mb-3 fw-bold">Write a Reply</h3>
            <form action="save_post.php" method="POST">
                <input type="hidden" name="threadId" value="<?= $thread_id ?>">
                <div class="mb-3">
                    <label for="content" class="form-label visually-hidden">Your Reply</label>
                    <textarea name="content" id="content" class="form-control" rows="5" placeholder="Write your reply here..." required></textarea>
                </div>
                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <button type="submit" class="btn btn-beauty">Publish Reply</button>
                </div>
            </form>
        </div>
    <?php else: ?>
        <div class="text-center mt-5 p-4 bg-light rounded">
            <p class="mb-0">You must be logged in to reply. <a href="login.php" class="fw-bold">Log in here</a>.</p>
        </div>
    <?php endif; ?>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener("DOMContentLoaded", () => {
    // Edit buttons
    document.querySelectorAll(".edit-btn").forEach(btn => {
        btn.addEventListener("click", function(e) {
            e.preventDefault();
            const id = this.dataset.postId;
            const display = document.getElementById("reply-display-" + id);
            const editArea = document.getElementById("reply-edit-" + id);
            if (display && editArea) {
                display.classList.add("d-none");
                editArea.classList.remove("d-none");
            }
        });
    });

    // Cancel buttons
    document.querySelectorAll(".cancel-edit").forEach(btn => {
        btn.addEventListener("click", function(e) {
            e.preventDefault();
            const id = this.dataset.postId;
            const display = document.getElementById("reply-display-" + id);
            const editArea = document.getElementById("reply-edit-" + id);
            const err = document.getElementById("reply-error-" + id);
            if (err) { err.classList.add("d-none"); err.innerText = ""; }
            if (editArea && display) {
                editArea.classList.add("d-none");
                display.classList.remove("d-none");
            }
        });
    });

    // Save buttons (AJAX)
    document.querySelectorAll(".save-edit").forEach(btn => {
        btn.addEventListener("click", function(e) {
            e.preventDefault();
            const id = this.dataset.postId;
            const textarea = document.getElementById("reply-textarea-" + id);
            const content = textarea ? textarea.value.trim() : "";
            const errEl = document.getElementById("reply-error-" + id);

            if (!content) {
                if (errEl) { errEl.innerText = "Content cannot be empty."; errEl.classList.remove("d-none"); }
                return;
            }

            // Send to save_edit.php (must accept POST: id, content) and return JSON {success: true} or {success:false, error: "..."}
            fetch("save_edit.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: "id=" + encodeURIComponent(id) + "&content=" + encodeURIComponent(content)
            })
            .then(r => r.json())
            .then(data => {
                if (data && data.success) {
                    // Update UI
                    const display = document.getElementById("reply-display-" + id);
                    const editArea = document.getElementById("reply-edit-" + id);
                    if (display) {
                        // replace inner paragraph text safely (no HTML)
                        const p = display.querySelector("p");
                        if (p) p.innerText = content;
                    }
                    if (editArea) editArea.classList.add("d-none");
                    if (display) display.classList.remove("d-none");
                    if (errEl) { errEl.classList.add("d-none"); errEl.innerText = ""; }
                } else {
                    const message = (data && data.error) ? data.error : "An error occurred while saving.";
                    if (errEl) { errEl.innerText = message; errEl.classList.remove("d-none"); }
                }
            })
            .catch(() => {
                if (errEl) { errEl.innerText = "Network error."; errEl.classList.remove("d-none"); }
            });
        });
    });
});
</script>
</body>
</html>

