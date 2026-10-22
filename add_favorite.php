<?php
session_start();
require 'config.php';

// Sikkerhetssjekk: Brukeren må være logget inn
if (!isset($_SESSION['userId'])) {
    // Omdiriger til login hvis brukeren ikke er logget inn
    header("Location: login.php");
    exit; 
}

$userId = (int)$_SESSION['userId'];
// Sjekk om product_id finnes og er et tall
$productId = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;

// Omdirigeringsmål: Send alltid brukeren tilbake til produktsiden (product.php) etter handling
$redirect_url = "product.php"; 

if ($productId > 0) {
    // Bruk INSERT IGNORE for å unngå duplikater hvis favoritten allerede finnes
    $stmt = $conn->prepare("INSERT IGNORE INTO FAVORITES (product_id, user_id) VALUES (?, ?)");
    
    // Feilsøking av forberedelse av spørringen
    if ($stmt === false) {
        // Hvis prepare feiler, omdiriger med en feilmelding
        header("Location: " . $redirect_url . "?error=db_prepare_failed");
        exit; 
    }
    
    $stmt->bind_param("ii", $productId, $userId);
    
    if ($stmt->execute()) {
        // SUKSESS: Favoritt lagt til (eller ignorert hvis den allerede fantes).
        // Omdirigerer tilbake til produktsiden uten tekst-output
        header("Location: " . $redirect_url . "?success=favorite_added");
    } else {
        // Databasefeil ved utførelse
        header("Location: " . $redirect_url . "?error=db_execution_failed");
    }
    
    $stmt->close();
    exit; // VELDIG VIKTIG: Sikrer at ingen tekst sendes etter header
} else {
    // Feil: Manglende produkt-ID
    header("Location: " . $redirect_url . "?error=no_product_id");
    exit;
}
?>