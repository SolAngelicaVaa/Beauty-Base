<?php
require_once("admin_auth.php");
require_once("../config.php");
require_once("admin_header.php");
require_once("admin_sidebar.php");

$result = mysqli_query($conn, "
    SELECT P.postId, P.content, P.created, U.userName, T.title
    FROM POSTS P
    JOIN USERS U ON U.userId = P.userId
    JOIN THREADS T ON T.threadId = P.threadId
    ORDER BY P.created DESC
");
?>

<div class="content">
    <h2>Posts</h2>

    <table class="table mt-3">
        <thead>
            <tr>
                <th>ID</th><th>Thread</th><th>User</th><th>Content</th><th>Date</th><th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php while($p = mysqli_fetch_assoc($result)): ?>
            <tr>
                <td><?= $p['postId']; ?></td>
                <td><?= htmlspecialchars($p['title']); ?></td>
                <td><?= htmlspecialchars($p['userName']); ?></td>
                <td><?= htmlspecialchars($p['content']); ?></td>
                <td><?= $p['created']; ?></td>
                <td>
                    <a href="admin_delete_post.php?id=<?= $p['postId']; ?>" class="btn btn-danger btn-sm">
                        Delete
                    </a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php require_once("admin_footer.php"); ?>
