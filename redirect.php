<?php
session_start();
//Sjekker om brukeren er logget inn, hvis ikke sendes brukeren tilbake til login siden

if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}
?>