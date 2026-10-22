<?php
session_start();
require 'config.php';

if (!isset($_SESSION['userId'])) {
    header("Location: login.php");
    exit;
}

$userId = (int)$_SESSION['userId'];
$target_dir = "profile_images/"; // Pass på at denne mappen eksisterer og har skriverettigheter!
$default_avatar = $target_dir . "default_avatar.png";

// --- Din kode for å behandle filopplasting vil være her ---
if (isset($_FILES["profilePicture"]) && $_FILES["profilePicture"]["error"] == 0) {
    
    // Antar at du har en sjekk for filtype og størrelse
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    $file_type = mime_content_type($_FILES['profilePicture']['tmp_name']);
    
    if (!in_array($file_type, $allowed_types)) {
        header("Location: profile.php?error=file_type");
        exit;
    }
    
    $file_extension = pathinfo($_FILES["profilePicture"]["name"], PATHINFO_EXTENSION);
    $target_file = $target_dir . $userId . "_profile." . $file_extension;
    
    if (move_uploaded_file($_FILES["profilePicture"]["tmp_name"], $target_file)) {
        
        // Oppdater databasen med den nye filstien
        $stmt = $conn->prepare("UPDATE USERS SET profile_picture = ? WHERE userId = ?");
        $stmt->bind_param("si", $target_file, $userId);
        
        if ($stmt->execute()) {
            // Suksess: Omdiriger med suksess-parameter i URL (NYTT)
            header("Location: profile.php?success=picture");
            exit;
        } else {
            // Feil i DB-oppdatering
            header("Location: profile.php?error=picture");
            exit;
        }
    } else {
        // Feil ved flytting av fil
        header("Location: profile.php?error=picture");
        exit;
    }
} else {
    // Ingen fil sendt eller opplastingsfeil
    header("Location: profile.php");
    exit;
}
?>