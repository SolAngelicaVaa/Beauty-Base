<?php
require_once("admin_auth.php");
require_once("../config.php");

if (!isset($_GET['id'])) {
    die("Missing trend ID");
}

$trendId = intval($_GET['id']);

$stmt = $conn->prepare("DELETE FROM TRENDS WHERE trendId = ?");
$stmt->bind_param("i", $trendId);

if ($stmt->execute()) {
    header("Location: admin_trends.php?deleted=1");
    exit();
} else {
    die("Could not delete trend.");
}
?>
