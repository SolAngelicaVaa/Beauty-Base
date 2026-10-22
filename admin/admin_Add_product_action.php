<?php
// admin_add_product_action.php
session_start();
require_once "../config.php";
require_once(__DIR__ . '/../admin_auth.php'); // Admin check

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || $_POST['action'] !== 'add') {
    header("Location: admin_dashboard.php?section=products&error=invalid_request");
    exit;
}

// --- 1. Sanitize and Validate Input ---
$name = trim($_POST['name']);
$brand = trim($_POST['brand']);
$category = trim($_POST['category']);
$description = trim($_POST['description']);

if (empty($name) || empty($brand) || empty($category) || empty($description)) {
    header("Location: admin_dashboard.php?section=products&error=missing_fields");
    exit;
}

// --- 2. Handle File Upload (Image) ---
$target_dir = "../product_images/"; // Create this folder in your root directory!
if (!file_exists($target_dir)) {
    mkdir($target_dir, 0777, true);
}

$file_name = uniqid('prod_', true) . basename($_FILES["imageUpload"]["name"]);
$target_file = $target_dir . $file_name;
$imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

// Basic checks
if (getimagesize($_FILES["imageUpload"]["tmp_name"]) === false) {
    header("Location: admin_dashboard.php?section=products&error=not_image");
    exit;
}
if ($_FILES["imageUpload"]["size"] > 5000000) { // Max 5MB
    header("Location: admin_dashboard.php?section=products&error=file_too_large");
    exit;
}
if (!in_array($imageFileType, ['jpg', 'png', 'jpeg', 'gif'])) {
    header("Location: admin_dashboard.php?section=products&error=invalid_format");
    exit;
}

if (!move_uploaded_file($_FILES["imageUpload"]["tmp_name"], $target_file)) {
    header("Location: admin_dashboard.php?section=products&error=upload_failed");
    exit;
}

// Path to store in the database (relative path from the public root)
$db_image_path = "product_images/" . $file_name; 

// --- 3. Insert into Database ---
$stmt = $conn->prepare("INSERT INTO PRODUCTS (name, brand, category, description, imageLink) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("sssss", $name, $brand, $category, $description, $db_image_path);

if ($stmt->execute()) {
    header("Location: admin_dashboard.php?section=products&status=added_success");
} else {
    // If DB insert fails, delete the uploaded file
    unlink($target_file); 
    header("Location: admin_dashboard.php?section=products&error=db_insert_failed");
}

$stmt->close();
exit;
?>