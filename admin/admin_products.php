<?php
require_once("admin_auth.php");
require_once("../config.php");
require_once("admin_header.php");
require_once("admin_sidebar.php");

$result = mysqli_query($conn, "
    SELECT productId, name, brand, category, created
    FROM PRODUCTS
    ORDER BY productId DESC
");
?>

<div class="content">
    <h2>Products</h2>
    <table class="table table-striped mt-3">
        <thead>
            <tr>
                <th>ID</th><th>Name</th><th>Brand</th><th>Category</th><th>Created</th><th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($p = mysqli_fetch_assoc($result)): ?>
            <tr>
                <td><?= $p['productId'] ?></td>
                <td><?= htmlspecialchars($p['name']) ?></td>
                <td><?= htmlspecialchars($p['brand']) ?></td>
                <td><?= htmlspecialchars($p['category']) ?></td>
                <td><?= $p['created'] ?></td>
                <td>
                    <a class="btn btn-danger btn-sm"
                       href="admin_delete_product.php?id=<?= $p['productId'] ?>"
                       onclick="return confirm('Delete product?');">
                        Delete
                    </a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php require_once("admin_footer.php"); ?>
