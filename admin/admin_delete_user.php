<?php
require_once("admin_auth.php");
require_once("../config.php");

if (!isset($_GET['id'])) {
    die("Missing user ID");
}

$userId = intval($_GET['id']);

// Delete posts by user
$conn->query("DELETE FROM POSTS WHERE userId = $userId");

// Delete threads by user (posts inside threads already deleted above)
$conn->query("DELETE FROM THREADS WHERE userId = $userId");

// Finally delete user
$conn->query("DELETE FROM USERS WHERE userId = $userId");

header("Location: admin_users.php");
exit();
?>
