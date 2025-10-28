<?php session_start(); ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>ログイン</title>
</head>
<body>
<h2>ログインフォーム</h2>
<form method="post" action="login.php">
    Email: <input type="email" name="email" required><br>
    名前: <input type="text" name="name" required><br>
    <button type="submit">ログイン</button>
</form>
<p><a href="register.php">新規登録はこちら</a></p>
</body>
</html>

