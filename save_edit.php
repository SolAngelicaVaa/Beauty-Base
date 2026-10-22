<?php
// save_edit.php
session_start();
require 'config.php';
header('Content-Type: application/json');

if (!isset($_SESSION['userId'])) {
    echo json_encode(['success'=>false,'error'=>'Not authenticated']);
    exit;
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$content = trim($_POST['content'] ?? '');
$userId = (int)$_SESSION['userId'];
$role = $_SESSION['role'] ?? 'user';

if ($id <= 0 || $content === '') {
    echo json_encode(['success'=>false,'error'=>'Invalid input']);
    exit;
}

// Check ownership or admin
$stmt = $conn->prepare("SELECT userId FROM POSTS WHERE postId = ? LIMIT 1");
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();
if (!$res || $res->num_rows === 0) {
    echo json_encode(['success'=>false,'error'=>'Post not found']);
    exit;
}
$row = $res->fetch_assoc();
$stmt->close();

$postOwner = (int)$row['userId'];
if ($postOwner !== $userId && $role !== 'admin') {
    echo json_encode(['success'=>false,'error'=>'Permission denied']);
    exit;
}

// Update
$stmt = $conn->prepare("UPDATE POSTS SET content = ?, created = created WHERE postId = ?"); // keep created unchanged
$stmt->bind_param("si", $content, $id);
$ok = $stmt->execute();
$stmt->close();

if ($ok) {
    echo json_encode(['success'=>true]);
} else {
    echo json_encode(['success'=>false,'error'=>'DB error']);
}
