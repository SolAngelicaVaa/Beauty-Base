<?php
if (session_status() === PHP_SESSION_NONE) session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Panel</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body { background:#faf4fb; }
        .admin-wrapper { display:flex; }
        .sidebar {
            width:260px;
            min-height:100vh;
            background:#e3b8d2;
            padding:20px 0;
        }
        .content {
            flex-grow:1;
            padding:30px;
        }
    </style>
</head>

<body>
<div class="admin-wrapper">
