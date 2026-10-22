<?php 
require_once("admin_auth.php");
require_once("admin_header.php");
require_once("admin_sidebar.php");
?>

<div class="content">
    <h2>Welcome, <?= htmlspecialchars($_SESSION['userName']); ?>!</h2>

    <p>You are logged in as administrator.</p>

    <div class="alert alert-info mt-3">
        Choose an option from the left menu to manage the website.
    </div>
</div>

<?php require_once("admin_footer.php"); ?>
