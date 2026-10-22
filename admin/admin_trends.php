<?php
require_once("admin_auth.php");
require_once("../config.php");
require_once("admin_header.php");
require_once("admin_sidebar.php");

$result = mysqli_query($conn, "
    SELECT trendId, title, created
    FROM TRENDS
    ORDER BY trendId DESC
");
?>

<div class="content">
    <h2>Trends</h2>
    <table class="table table-striped mt-3">
        <thead>
            <tr><th>ID</th><th>Title</th><th>Created</th><th>Actions</th></tr>
        </thead>
        <tbody>
            <?php while ($t = mysqli_fetch_assoc($result)): ?>
            <tr>
                <td><?= $t['trendId'] ?></td>
                <td><?= htmlspecialchars($t['title']) ?></td>
                <td><?= $t['created'] ?></td>
                <td>
                    <a class="btn btn-danger btn-sm"
                       href="admin_delete_trend.php?id=<?= $t['trendId'] ?>"
                       onclick="return confirm('Delete trend?');">
                       Delete
                    </a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php require_once("admin_footer.php"); ?>
