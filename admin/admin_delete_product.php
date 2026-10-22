<?php
require_once("admin_auth.php");
require_once("../config.php");

if (!isset($_GET['id'])) {
    die("Missing product ID");
}

$productId = intval($_GET['id']);

$conn->query("DELETE FROM PRODUCTS WHERE productId = $productId");

header("Location: admin_products.php");
exit();
?>
