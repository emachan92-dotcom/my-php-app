<?php
$servername = "10.0.2.4";
$username   = "webapp";
$password   = "Nakaema202510";
$dbname     = "myappdb";

// 接続
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// CREATE
if (isset($_POST['action']) && $_POST['action'] === 'add') {
    $name  = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    if ($name !== "" && $email !== "") {
        $conn->query("INSERT INTO users (name, email) VALUES ('$name', '$email')");
    }
}

// UPDATE
if (isset($_POST['action']) && $_POST['action'] === 'edit') {
    $id    = intval($_POST['id']);
    $name  = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    $conn->query("UPDATE users SET name='$name', email='$email' WHERE id=$id");
}

// DELETE
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM users WHERE id=$id");
}

// 編集用データ取得
$editUser = null;
if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $res = $conn->query("SELECT * FROM users WHERE id=$id");
    $editUser = $res->fetch_assoc();
}
?>

<h2><?php echo $editUser ? "Edit User" : "Add New User"; ?></h2>
<form method="post">
    <input type="hidden" name="action" value="<?php echo $editUser ? 'edit' : 'add'; ?>">
    <?php if ($editUser) { ?>
        <input type="hidden" name="id" value="<?php echo $editUser['id']; ?>">
    <?php } ?>
    Name: <input type="text" name="name" value="<?php echo $editUser['name'] ?? ''; ?>" required><br>
    Email: <input type="email" name="email" value="<?php echo $editUser['email'] ?? ''; ?>" required><br>
    <input type="submit" value="<?php echo $editUser ? 'Update User' : 'Add User'; ?>">
</form>

<h2>Users List</h2>
<?php
$result = $conn->query("SELECT * FROM users");
if ($result->num_rows > 0) {
    echo "<table border='1' cellpadding='5'><tr><th>ID</th><th>Name</th><th>Email</th><th>Actions</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>
                <td>{$row['id']}</td>
                <td>{$row['name']}</td>
                <td>{$row['email']}</td>
                <td>
                    <a href='?edit={$row['id']}'>Edit</a> |
                    <a href='?delete={$row['id']}' onclick='return confirm(\"Are you sure?\");'>Delete</a>
                </td>
              </tr>";
    }
    echo "</table>";
} else {
    echo "<p>No users found.</p>";
}

$conn->close();
?>

