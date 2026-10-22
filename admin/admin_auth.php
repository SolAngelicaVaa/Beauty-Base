<?php
session_start();

// Must be logged in
if (!isset($_SESSION['userId'])) {
    header("Location: ../login.php");
    exit();
}

// Must be admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../forside.php");
    exit();
}
?>
