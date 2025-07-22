<?php
include '../db.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // 檢查產品是否存在
    $check = $conn->prepare("SELECT id FROM products WHERE id = ?");
    $check->bind_param("i", $id);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows > 0) {

        // 確認是否有庫存
        $stock_sql = $conn->prepare("
          SELECT SUM(CASE WHEN change_type = 'in' THEN quantity ELSE -quantity END) AS stock
          FROM inventory_logs WHERE product_id = ?
        ");
        $stock_sql->bind_param("i", $id);
        $stock_sql->execute();
        $stock = $stock_sql->get_result()->fetch_assoc()['stock'] ?? 0;

        if ($stock > 0) {
            echo "<script>alert('⚠️ 該產品仍有庫存，無法刪除');window.location='products.php';</script>";
            exit;
        }

        // ✅ 先刪除所有進出貨紀錄
        $del_logs = $conn->prepare("DELETE FROM inventory_logs WHERE product_id = ?");
        if ($del_logs) {
            $del_logs->bind_param("i", $id);
            $del_logs->execute();
        } else {
            echo "SQL 錯誤：刪除 inventory_logs 時失敗：" . $conn->error;
            exit;
        }

        // ✅ 再刪除產品本身
        $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
    }

    header("Location: products.php");
    exit;
}
