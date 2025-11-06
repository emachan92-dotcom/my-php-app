<?php
$serverName = "nakaema.database.windows.net";
$connectionOptions = array(
    "Database" => "Azure-db-east",
    "Uid" => "webapp",
    "PWD" => "Nakaema202510",
    "Encrypt" => true,
    "TrustServerCertificate" => false
);

$conn = sqlsrv_connect($serverName, $connectionOptions);

if ($conn) {
    echo "✅ SQL接続成功！";
    sqlsrv_close($conn);
} else {
    echo "❌ SQL接続失敗<br>";
    die(print_r(sqlsrv_errors(), true));
}
?>
