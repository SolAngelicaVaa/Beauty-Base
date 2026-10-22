<?php
session_start();
header('Content-Type: application/json');
require 'config.php';

if (!isset($_SESSION['userId'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$userId = (int)$_SESSION['userId'];
$productId = (int)$_POST['productId'];
$action = $_POST['action'];

if ($action === 'add') {

    $stmt = $conn->prepare("INSERT IGNORE INTO FAVORITES (user_id, product_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $userId, $productId);
    $stmt->execute();
    $stmt->close();

    echo json_encode(['success' => true]);
    exit;

}

if ($action === 'remove') {

    $stmt = $conn->prepare("DELETE FROM FAVORITES WHERE user_id = ? AND product_id = ?");
    $stmt->bind_param("ii", $userId, $productId);
    $stmt->execute();
    $stmt->close();

    echo json_encode(['success' => true]);
    exit;

}

echo json_encode(['success' => false, 'message' => 'Invalid action']);
