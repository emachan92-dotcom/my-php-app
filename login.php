<?php
session_start();

// Azure SQL 接続情報
$serverName = "nakaema-db.database.windows.net"; // Azure SQL サーバー名
$connectionOptions = [
    "Database" => "Azure-db-east",           // データベース名
    "Uid"      => "webapp",                  // SQL 認証ユーザ
    "PWD"      => "Nakaema202510",           // パスワード
    "Encrypt"  => true,                       // 暗号化
    "TrustServerCertificate" => false

];

// 接続
$conn = sqlsrv_connect($serverName, $connectionOptions);
if ($conn === false) {
    die(print_r(sqlsrv_errors(), true));
}

$error = '';
if (isset($_POST['name'], $_POST['password'])) {
    $user = $_POST['name'];
    $pass = $_POST['password'];

    // パラメータ化クエリで SQL インジェクション防止
    $tsql = "SELECT * FROM accounts WHERE name = ?";
    $params = [$user];
    $stmt = sqlsrv_query($conn, $tsql, $params);

    if ($stmt === false) {
        die(print_r(sqlsrv_errors(), true));
    }

    if (sqlsrv_has_rows($stmt)) {
        $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
        if (password_verify($pass, $row['password_hash'])) {
            $_SESSION['name'] = $user;
            header("Location: users_crud_auth.php");
            exit();
        } else {
            $error = "Invalid password";
        }
    } else {
        $error = "User not found";
    }
}

sqlsrv_close($conn);
?>

<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>Login</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">

<div class="container">
    <h2>Login</h2>
    <?php if ($error) echo "<div class='alert alert-danger'>$error</div>"; ?>
    <form method="post" class="mb-3">
        <div class="mb-3">
            <input type="text" name="name" class="form-control" placeholder="name" required>
        </div>
        <div class="mb-3">
            <input type="password" name="password" class="form-control" placeholder="Password" required>
        </div>
        <button type="submit" class="btn btn-primary">Login</button>
        <a href="register.php" class="btn btn-secondary">Register</a>
    </form>
</div>

</body>
</html>

