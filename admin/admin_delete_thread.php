<?php
require_once("admin_auth.php");
require_once("../config.php");

if (!isset($_GET['id'])) {
    die("Missing thread ID");
}

$threadId = intval($_GET['id']);

// Delete posts in this thread
$conn->query("DELETE FROM POSTS WHERE threadId = $threadId");

// Delete thread
$conn->query("DELETE FROM THREADS WHERE threadId = $threadId");

header("Location: admin_threads.php");
exit();
?>
