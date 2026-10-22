<?php
require_once("admin_auth.php");
require_once("../config.php");
require_once("admin_header.php");
require_once("admin_sidebar.php");

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $title = trim($_POST["title"]);
    $description = trim($_POST["description"]);
    $link = trim($_POST["link"]);

    if (empty($title)) {
        $message = "Title cannot be empty.";
    } else {
        $stmt = $conn->prepare("INSERT INTO TRENDS (title, description, link, created) VALUES (?, ?, ?, CURDATE())");
        $stmt->bind_param("sss", $title, $description, $link);

        if ($stmt->execute()) {
            $message = "Trend added!";
        } else {
            $message = "Database error.";
        }
    }
}
?>

<div class="content">
    <h2>Add Trend</h2>

    <?php if ($message): ?>
        <div class="alert alert-info"><?= $message ?></div>
    <?php endif; ?>

    <form method="POST">
        <label>Title *</label>
        <input type="text" name="title" class="form-control" required>

        <label>Description</label>
        <textarea name="description" class="form-control"></textarea>

        <label>Link</label>
        <input type="text" name="link" class="form-control">

        <button class="btn btn-success mt-3">Add Trend</button>
    </form>
</div>

<?php require_once("admin_footer.php"); ?>
