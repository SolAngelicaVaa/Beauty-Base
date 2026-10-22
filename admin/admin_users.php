<?php
require_once("admin_auth.php");
require_once("../config.php");
require_once("admin_header.php");
require_once("admin_sidebar.php");

// Fetch users
$result = mysqli_query($conn, "SELECT * FROM USERS ORDER BY userId DESC");
?>

<div class="content">
    <h2>Users</h2>

    <table class="table table-striped mt-3">
        <thead>
            <tr>
                <th>ID</th><th>Username</th><th>Email</th><th>Role</th><th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php while($u = mysqli_fetch_assoc($result)): ?>
            <tr>
                <td><?= $u['userId']; ?></td>
                <td><?= htmlspecialchars($u['userName']); ?></td>
                <td><?= htmlspecialchars($u['email']); ?></td>
                <td><?= htmlspecialchars($u['role']); ?></td>
                <td>
                    <a href="admin_delete_user.php?id=<?= $u['userId']; ?>" class="btn btn-danger btn-sm">Delete</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php require_once("admin_footer.php"); ?>
