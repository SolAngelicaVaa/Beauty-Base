<?php 
session_start();
require 'config.php'; 

//  Sjekk om brukeren er logget inn
if (!isset($_SESSION['userId'])) {
    header("Location: login.php");
    exit("You have to be logged in to delete a thread.");
}

// Sjekk at tråd-id er sendt med i URL
if (!isset($_GET['id'])) {
    exit("No thread specified.");
}

$threadId = (int)$_GET['id'];
$userId = $_SESSION['userId']; 

// Finn eieren av tråden
$stmt = $conn->prepare("SELECT userId FROM THREADS WHERE threadId = ?");
$stmt->bind_param("i", $threadId);
$stmt->execute();
$stmt->bind_result($ownerId);
$stmt->fetch();
$stmt->close();

// Sjekk om tråden finnes
if (!$ownerId) {
    exit("Thread not found.");
}

// Sjekk at brukeren eier tråden
if ($ownerId !== $userId) {
    exit("You are not allowed to delete this thread.");
}

// Slett alle innlegg (unngå foreign key feil)
$stmt = $conn->prepare("DELETE FROM POSTS WHERE threadId = ?");
$stmt->bind_param("i", $threadId);
$stmt->execute();
$stmt->close();

// Slett selve tråden
$stmt = $conn->prepare("DELETE FROM THREADS WHERE threadId = ?");
$stmt->bind_param("i", $threadId);
$stmt->execute();
$stmt->close();

header("Location: forum.php");
exit;
?>
