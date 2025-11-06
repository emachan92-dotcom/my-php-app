<?php
session_start();

// Azure SQL 接続情報
$serverName = "nakaema.database.windows.net"; // Azure SQL サーバー名
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
if (isset($_POST['username'], $_POST['password'])) {
    $user = $_POST['username'];
    $pass = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // 既存ユーザ確認
    $tsqlCheck = "SELECT * FROM accounts WHERE username = ?";
    $paramsCheck = [$user];
    $stmtCheck = sqlsrv_query($conn, $tsqlCheck, $paramsCheck);

    if ($stmtCheck === false) {
        die(print_r(sqlsrv_errors(), true));
    }

    if (sqlsrv_has_rows($stmtCheck)) {
        $error = "name already exists";
    } else {
        // INSERT
        $tsqlInsert = "INSERT INTO accounts (username, password_hash) VALUES (?, ?)";
        $paramsInsert = [$user, $pass];
        $stmtInsert = sqlsrv_query($conn, $tsqlInsert, $paramsInsert);

        if ($stmtInsert === false) {
            die(print_r(sqlsrv_errors(), true));
        }

        // 自動ログイン
        $_SESSION['username'] = $user;
        header("Location: users_crud_auth.php");
        exit();
    }
}

sqlsrv_close($conn);
?>

<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>Register</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">

<div class="container">
    <h2>Register</h2>
    <?php if ($error) echo "<div class='alert alert-danger'>$error</div>"; ?>
    <form method="post" class="mb-3">
        <div class="mb-3">
            <input type="text" name="username" class="form-control" placeholder="name" required>
        </div>
        <div class="mb-3">
            <input type="password" name="password" class="form-control" placeholder="Password" required>
        </div>
        <button type="submit" class="btn btn-primary">Register & Login</button>
        <a href="login.php" class="btn btn-secondary">Go to Login</a>
    </form>
</div>

</body>
</html>

