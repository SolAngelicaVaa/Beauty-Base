<?php
session_start();
require 'config.php';

// Sjekker om brukeren er logget inn
if (!isset($_SESSION['userId'])) {
    header("Location: login.php");
    exit;
}

// 1. Henter post ID fra URL-parameteren 'id' (som satt i threads.php)
$post_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
// Henter thread ID for å omdirigere brukeren tilbake
$thread_id = isset($_GET['thread']) ? (int)$_GET['thread'] : 0;

// Sjekker at vi faktisk har en post ID
if ($post_id === 0) {
    // Dette er meldingen du får: "no post selected"
    echo "No post selected.";
    exit;
}

// 2. Sjekker at brukeren eier posten før sletting (Sikkerhetsjekk)
$check_ownership_sql = "SELECT userId FROM POSTS WHERE postId = ?";
$stmt_check = $conn->prepare($check_ownership_sql);
$stmt_check->bind_param("i", $post_id);
$stmt_check->execute();
$result_check = $stmt_check->get_result();

if ($result_check->num_rows === 0) {
    echo "Post not found.";
    exit;
}

$post_owner = $result_check->fetch_assoc();
$stmt_check->close();

if ($post_owner['userId'] != $_SESSION['userId']) {
    // Hvis brukeren prøver å slette en annens post
    echo "Error: You do not have permission to delete this post.";
    exit;
}

// 3. Utfører sletting
$delete_sql = "DELETE FROM POSTS WHERE postId = ?";
$stmt_delete = $conn->prepare($delete_sql);
$stmt_delete->bind_param("i", $post_id);

if ($stmt_delete->execute()) {
    // Omdirigerer tilbake til tråden etter vellykket sletting
    if ($thread_id > 0) {
        header("Location: threads.php?id=" . $thread_id);
    } else {
        // Hvis threadId mangler, send til forumoversikten
        header("Location: forum.php");
    }
    exit;
} else {
    echo "Error deleting post: " . $conn->error;
}

$stmt_delete->close();
$conn->close();
?>