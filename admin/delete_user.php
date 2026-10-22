<?php 
//require_once "admin_auth.php"; 
require_once(__DIR__ . '/admin_auth.php');
require_once "../config.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Users</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

<div class="container mt-5">
    <h2>Users</h2>
    <a href="index.php" class="btn btn-secondary mb-3">Back to Admin</a>

    <?php
    $result = $db->query("SELECT userId, userName, email, role FROM USERS");
    ?>

    <table class="table table-bordered table-striped">
        <thead>
        <tr>
            <th>ID</th><th>Username</th><th>Email</th><th>Role</th><th>Actions</th>
        </tr>
        </thead>
        <tbody>
        <?php while($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= $row['userId'] ?></td>
                <td><?= htmlspecialchars($row['userName']) ?></td>
                <td><?= htmlspecialchars($row['email']) ?></td>
                <td><?= $row['role'] ?></td>
                <td>
                    <?php if ($row['role'] !== "admin"): ?>
                        <a href="delete_user.php?id=<?= $row['id'] ?>" 
                           class="btn btn-danger btn-sm"
                           onclick="return confirm('Delete this user?');">
                           Delete
                        </a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>

</div>

</body>
</html>
