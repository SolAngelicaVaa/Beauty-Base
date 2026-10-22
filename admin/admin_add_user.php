<?php
require_once("admin_auth.php");
require_once("../config.php");
require_once("admin_header.php");
require_once("admin_sidebar.php");

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = trim($_POST["username"]);
    $email = trim($_POST["email"]);
    $role = trim($_POST["role"]);
    $password = password_hash($_POST["password"], PASSWORD_DEFAULT);

    if (empty($username) || empty($email)) {
        $message = "Username and email are required.";
    } else {
        $stmt = $conn->prepare("INSERT INTO USERS (userName, email, password, role, created_at) VALUES (?, ?, ?, ?, NOW())");
        $stmt->bind_param("ssss", $username, $email, $password, $role);

        if ($stmt->execute()) {
            $message = "User created!";
        } else {
            $message = "Database error.";
        }
    }
}
?>

<div class="content">
    <h2>Add User</h2>

    <?php if ($message): ?>
        <div class="alert alert-info"><?= $message ?></div>
    <?php endif; ?>

    <form method="POST">
        <label>Username *</label>
        <input type="text" name="username" class="form-control" required>

        <label>Email *</label>
        <input type="email" name="email" class="form-control" required>

        <label>Password *</label>
        <input type="password" name="password" class="form-control" required>

        <label>Role</label>
        <select name="role" class="form-control">
            <option value="user">User</option>
            <option value="admin">Administrator</option>
        </select>

        <button class="btn btn-success mt-3">Add User</button>
    </form>
</div>

<?php require_once("admin_footer.php"); ?>
