<?php
session_start();
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: forum.php");
    exit;
}

$postId = (int) $_POST['postId'];
$content = trim($_POST['content']);
$userId = $_SESSION['userId'] ?? null;
$userRole = $_SESSION['role'] ?? 'guest';

// Hent original post for rolle-sjekk
$sql = "SELECT * FROM POSTS WHERE postId = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $postId);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    die("Post not found.");
}

$post = $res->fetch_assoc();

if ($post['userId'] !== $userId && $userRole !== 'admin') {
    die("You are not allowed to edit this post.");
}

$sql_update = "UPDATE POSTS SET content = ? WHERE postId = ?";
$stmt2 = $conn->prepare($sql_update);
$stmt2->bind_param("si", $content, $postId);
$stmt2->execute();

header("Location: threads.php?id=" . $post['threadId'] . "&updated=1");
exit;
