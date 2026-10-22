<?php
session_start();
require 'config.php';

// Sjekk at brukeren er logget inn
if (!isset($_SESSION['userId'])) {
    header("Location: login.php");
    exit;
}

$userId = (int)$_SESSION['userId'];

// Sjekker hvilken handling som skal utføres basert på skjult felt i skjemaet
$action = $_POST['action'] ?? '';


// ##################################################################
// # A. HÅNDTERING AV BIO-OPPDATERING
// ##################################################################
if ($action === 'update_bio' && isset($_POST['bio'])) {
    
    $new_bio = trim($_POST['bio']);
    
    // Sikkerhetssjekk: Maks lengde på bio (sett til 500 tegn)
    if (strlen($new_bio) > 500) { 
        header("Location: profile.php?error=bio&message=Bio is too long (max 500 characters)");
        exit;
    }

    try {
        // Bruk Prepared Statement for å oppdatere databasen
        $stmt = $conn->prepare("UPDATE USERS SET bio = ? WHERE userId = ?");
        $stmt->bind_param("si", $new_bio, $userId);

        if ($stmt->execute()) {
            // Suksess: Omdiriger tilbake til profilen med suksessmelding
            header("Location: profile.php?success=bio");
            exit;
        } else {
            // Feil i DB-oppdatering
            header("Location: profile.php?error=bio");
            exit;
        }
    } catch (Exception $e) {
        // Generell feilhåndtering
        header("Location: profile.php?error=bio");
        exit;
    }
}


// ##################################################################
// # B. HÅNDTERING AV BILDEOPPDATERING
// ##################################################################
if ($action === 'update_picture' && isset($_FILES["profilePicture"]) && $_FILES["profilePicture"]["error"] == 0) {
    
    $target_dir = "profile_images/";
    
    // Sjekk at mappen eksisterer. Hvis ikke, forsøk å opprette den.
    if (!is_dir($target_dir)) {
        if (!mkdir($target_dir, 0755, true)) {
             header("Location: profile.php?error=picture&message=Target directory could not be created.");
             exit;
        }
    }
    
    // Filtype-sjekk
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    
    // Dette krever at `fileinfo` PHP-utvidelsen er aktivert på serveren din
    if (function_exists('mime_content_type')) {
        $file_type = mime_content_type($_FILES['profilePicture']['tmp_name']);
    } else {
        // Fallback for systemer uten mime_content_type (mindre sikkert)
        $file_type = $_FILES['profilePicture']['type'];
    }

    if (!in_array($file_type, $allowed_types)) {
        header("Location: profile.php?error=file_type");
        exit;
    }
    
    $file_extension = pathinfo($_FILES["profilePicture"]["name"], PATHINFO_EXTENSION);
    // Bruk bruker-ID for å sikre et unikt filnavn og unngå kollisjoner
    $target_file = $target_dir . $userId . "_profile." . $file_extension; 
    
    if (move_uploaded_file($_FILES["profilePicture"]["tmp_name"], $target_file)) {
        
        // Oppdater databasen med den nye filstien
        $stmt = $conn->prepare("UPDATE USERS SET profile_picture = ? WHERE userId = ?");
        $stmt->bind_param("si", $target_file, $userId);
        
        if ($stmt->execute()) {
            header("Location: profile.php?success=picture");
            exit;
        } else {
            header("Location: profile.php?error=picture");
            exit;
        }
    } else {
        header("Location: profile.php?error=picture");
        exit;
    }
}


// Omdiriger til profilsiden hvis ingen gyldig handling ble utført
header("Location: profile.php");
exit;