<?php
$host = "localhost";
$user = "root";
$pass = "";  // XAMPP 預設沒密碼
$dbname = "inventory_system";

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("連線失敗：" . $conn->connect_error);
}
?>
