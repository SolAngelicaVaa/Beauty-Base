<?php
session_start();
require 'config.php';

if (!isset($_GET['id'])) {
    header("Location: forum.php");
    exit;
}

$postId = (int) $_GET['id'];
$userId = $_SESSION['userId'] ?? null;
$userRole = $_SESSION['role'] ?? 'guest';

// Hent posten
$sql = "SELECT * FROM POSTS WHERE postId = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $postId);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    echo "Post not found.";
    exit;
}

$post = $res->fetch_assoc();

if ($post['userId'] !== $userId && $userRole !== 'admin') {
    echo "You are not allowed to edit this post.";
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Post</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">

<h2>Edit your post</h2>

<form action="update_post.php" method="POST">
    <input type="hidden" name="postId" value="<?= $post['postId'] ?>">

    <textarea class="form-control mb-3" name="content" rows="6" required><?= htmlspecialchars($post['content']) ?></textarea>

    <button type="submit" class="btn btn-primary">Save changes</button>
    <a href="threads.php?id=<?= $post['threadId'] ?>" class="btn btn-secondary">Cancel</a>
</form>

</body>
</html>
