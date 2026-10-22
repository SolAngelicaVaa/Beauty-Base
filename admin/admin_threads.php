<?php
require_once("admin_auth.php");
require_once("../config.php");
require_once("admin_header.php");
require_once("admin_sidebar.php");

$result = mysqli_query($conn, "
    SELECT 
        T.threadId,
        T.title,
        T.created,
        U.userName,
        (SELECT COUNT(postId) FROM POSTS P WHERE P.threadId = T.threadId) AS postCount
    FROM THREADS T
    JOIN USERS U ON U.userId = T.userId
    ORDER BY T.created DESC
");
?>

<div class="content">
    <h2>Threads</h2>

    <table class="table table-striped mt-3">
        <thead>
            <tr>
                <th>ID</th><th>Title</th><th>Author</th><th>Posts</th><th>Date</th><th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php while($t = mysqli_fetch_assoc($result)): ?>
            <tr>
                <td><?= $t['threadId']; ?></td>
                <td><?= htmlspecialchars($t['title']); ?></td>
                <td><?= htmlspecialchars($t['userName']); ?></td>
                <td><?= $t['postCount']; ?></td>
                <td><?= $t['created']; ?></td>
                <td>
                    <a href="admin_delete_thread.php?id=<?= $t['threadId']; ?>"
                       class="btn btn-danger btn-sm">Delete</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php require_once("admin_footer.php"); ?>
