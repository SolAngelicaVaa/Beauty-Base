<?php
require_once("admin_auth.php");
require_once("../config.php");

if (!isset($_GET['id'])) {
    die("Missing post ID");
}

$postId = intval($_GET['id']);

$conn->query("DELETE FROM POSTS WHERE postId = $postId");

header("Location: admin_posts.php");
exit();
?>
