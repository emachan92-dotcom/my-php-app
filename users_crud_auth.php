<?php
session_start();
if (!isset($_SESSION['name'])) {
    header("Location: login.php");
    exit();
}

// Azure SQL 接続情報
$serverName = "nakaema.database.windows.net"; // Azure SQL サーバ名
$connectionOptions = [
    "Database" => "Azure-db-east",
    "Uid" => "webapp@nakaema",
    "PWD" => "Nakaema202510",
    "Encrypt" => true,
    "TrustServerCertificate" => false

];

// 接続
$conn = sqlsrv_connect($serverName, $connectionOptions);
if ($conn === false) {
    die(print_r(sqlsrv_errors(), true));
}

// CSVエクスポート
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=users.csv');
    $output = fopen('php://output', 'w');
    fputcsv($output, ['ID','Name','Email']);

    $tsql = "SELECT * FROM users";
    if (isset($_GET['search']) && $_GET['search'] !== '') {
        $search = $_GET['search'];
        $tsql .= " WHERE name LIKE ? OR email LIKE ?";
        $params = ["%$search%", "%$search%"];
    } else {
        $params = [];
    }

    $stmt = sqlsrv_query($conn, $tsql, $params);
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        fputcsv($output, $row);
    }
    fclose($output);
    exit();
}

// CREATE
if (isset($_POST['action']) && $_POST['action'] === 'add') {
    $name  = $_POST['name'];
    $email = $_POST['email'];
    if ($name !== "" && $email !== "") {
        $tsql = "INSERT INTO users (name, email) VALUES (?, ?)";
        sqlsrv_query($conn, $tsql, [$name, $email]);
    }
}

// UPDATE
if (isset($_POST['action']) && $_POST['action'] === 'edit') {
    $id    = intval($_POST['id']);
    $name  = $_POST['name'];
    $email = $_POST['email'];
    $tsql = "UPDATE users SET name=?, email=? WHERE id=?";
    sqlsrv_query($conn, $tsql, [$name, $email, $id]);
}

// DELETE
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $tsql = "DELETE FROM users WHERE id=?";
    sqlsrv_query($conn, $tsql, [$id]);
}

// 編集用データ取得
$editUser = null;
if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $tsql = "SELECT * FROM users WHERE id=?";
    $stmt = sqlsrv_query($conn, $tsql, [$id]);
    $editUser = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
}

// 検索用
$search = '';
if (isset($_GET['search'])) {
    $search = $_GET['search'];
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>Users CRUD with Authentication</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">

<div class="container">
    <h2 class="mb-4"><?php echo $editUser ? "Edit User" : "Add New User"; ?></h2>
    <form method="post" class="row g-3 mb-4">
        <input type="hidden" name="action" value="<?php echo $editUser ? 'edit' : 'add'; ?>">
        <?php if ($editUser) { ?>
            <input type="hidden" name="id" value="<?php echo $editUser['id']; ?>">
        <?php } ?>
        <div class="col-md-4">
            <input type="text" name="name" class="form-control" placeholder="Name" value="<?php echo $editUser['name'] ?? ''; ?>" required>
        </div>
        <div class="col-md-4">
            <input type="email" name="email" class="form-control" placeholder="Email" value="<?php echo $editUser['email'] ?? ''; ?>" required>
        </div>
        <div class="col-md-4">
            <button type="submit" class="btn btn-primary"><?php echo $editUser ? 'Update User' : 'Add User'; ?></button>
            <?php if ($editUser) { ?>
                <a href="users_crud_auth.php" class="btn btn-secondary">Cancel</a>
            <?php } ?>
            <a href="logout.php" class="btn btn-danger">Logout</a>
        </div>
    </form>

    <form method="get" class="mb-3 row g-2 align-items-center">
        <div class="col-auto">
            <input type="text" name="search" class="form-control" placeholder="Search name or email" value="<?php echo htmlspecialchars($search); ?>">
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-info">Search</button>
            <a href="users_crud_auth.php" class="btn btn-secondary">Reset</a>
        </div>
        <div class="col-auto">
            <a href="users_crud_auth.php?export=csv<?php echo $search ? '&search='.urlencode($search) : ''; ?>" class="btn btn-success">Export CSV</a>
        </div>
    </form>

    <h2 class="mb-3">Users List</h2>
    <?php
    $tsql = "SELECT * FROM users";
    $params = [];
    if ($search) {
        $tsql .= " WHERE name LIKE ? OR email LIKE ?";
        $params = ["%$search%", "%$search%"];
    }
    $stmt = sqlsrv_query($conn, $tsql, $params);
    if ($stmt && sqlsrv_has_rows($stmt)) {
        echo '<table class="table table-bordered table-striped">';
        echo "<thead class='table-dark'><tr><th>ID</th><th>Name</th><th>Email</th><th>Actions</th></tr></thead><tbody>";
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            echo "<tr>
                    <td>{$row['id']}</td>
                    <td>{$row['name']}</td>
                    <td>{$row['email']}</td>
                    <td>
                        <a href='?edit={$row['id']}' class='btn btn-sm btn-warning'>Edit</a>
                        <a href='?delete={$row['id']}' class='btn btn-sm btn-danger' onclick='return confirm(\"Are you sure?\");'>Delete</a>
                    </td>
                  </tr>";
        }
        echo "</tbody></table>";
    } else {
        echo "<p>No users found.</p>";
    }

    sqlsrv_close($conn);
    ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

