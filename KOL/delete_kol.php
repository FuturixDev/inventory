<?php
include '../db.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // 刪除 KOL 記錄
    $stmt = $conn->prepare("DELETE FROM kols WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    // 重定向回 KOL 名單頁面
    header("Location: kols.php");
    exit;
}
?>
