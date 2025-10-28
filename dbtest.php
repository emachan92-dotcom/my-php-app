<?php
$servername = "10.0.2.4";       // SQL-VMのプライベートIP
$username   = "webapp";      // 先ほど作成したMySQLユーザ
$password   = "Nakaema202510"; // そのユーザのパスワード
$dbname     = "myappdb";        // もし作成したDBがあれば指定、なければ省略可

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}
echo "Connected successfully!";
$conn->close();
?>

