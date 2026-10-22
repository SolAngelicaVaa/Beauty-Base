<?php
require_once("admin_auth.php");
require_once("../config.php");
require_once("admin_header.php");
require_once("admin_sidebar.php");

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name = trim($_POST['name']);
    $brand = trim($_POST['brand']);
    $category = trim($_POST['category']);
    $description = trim($_POST['description']);
    $imageLink = trim($_POST['imageLink']);

    if (empty($name) || empty($brand) || empty($category)) {
        $message = "Please fill out all required fields.";
    } else {
        $stmt = $conn->prepare("INSERT INTO PRODUCTS (name, brand, category, description, imageLink, created) VALUES (?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param("sssss", $name, $brand, $category, $description, $imageLink);

        if ($stmt->execute()) {
            $message = "Product added successfully!";
        } else {
            $message = "Database error while adding product.";
        }
    }
}
?>

<div class="content">
    <h2>Add Product</h2>

    <?php if ($message): ?>
        <div class="alert alert-info"><?= $message ?></div>
    <?php endif; ?>

    <form method="POST">
        <label>Name *</label>
        <input type="text" name="name" class="form-control" required>

        <label>Brand *</label>
        <input type="text" name="brand" class="form-control" required>

        <label>Category *</label>
        <input type="text" name="category" class="form-control" required>

        <label>Description</label>
        <textarea name="description" class="form-control"></textarea>

        <label>Image Link</label>
        <input type="text" name="imageLink" class="form-control">

        <button class="btn btn-success mt-3">Add Product</button>
    </form>
</div>

<?php require_once("admin_footer.php"); ?>
