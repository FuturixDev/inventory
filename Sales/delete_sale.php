<?php
include '../db.php';

if (isset($_GET['id'])) {
    $sale_id = intval($_GET['id']);

    // 取得銷售資料
    $stmt = $conn->prepare("SELECT * FROM sales WHERE id = ?");
    $stmt->bind_param("i", $sale_id);
    $stmt->execute();
    $sale = $stmt->get_result()->fetch_assoc();

    if ($sale) {
        $product_id = $sale['product_id'];
        $quantity = $sale['quantity'];
        $created_at = $sale['created_at'];
        $note = $sale['note'];
        $price = floatval($sale['price']);
        $channel = $sale['channel'];

        // ✅ 修正 note 組法
        if ($price == 0 && stripos($note, '公關贈送') !== false) {
            $note_inventory = $note; // 公關贈送原樣 note
        } else {
            $note_inventory = "銷售出貨";
            if (!empty($channel)) $note_inventory .= "($channel)";
            if (!empty($note)) $note_inventory .= "，備註：$note";
        }

        // ✅ 精準刪除 inventory_logs 出貨紀錄
        $stmt2 = $conn->prepare("
            DELETE FROM inventory_logs 
            WHERE product_id = ? AND change_type = 'out' AND quantity = ? AND note = ? AND created_at = ?
        ");
        $stmt2->bind_param("iiss", $product_id, $quantity, $note_inventory, $created_at);
        $stmt2->execute();

        // ✅ 如果是公關贈送，再刪 kol_transactions
        if ($price == 0 && stripos($note, '公關贈送') !== false) {
            $stmt4 = $conn->prepare("
                DELETE FROM kol_transactions 
                WHERE product_id = ? AND quantity = ? AND note = ? AND type = 'gift'
            ");
            $stmt4->bind_param("iis", $product_id, $quantity, $note);
            $stmt4->execute();
        }

        // ✅ 刪除 sales 紀錄
        $stmt3 = $conn->prepare("DELETE FROM sales WHERE id = ?");
        $stmt3->bind_param("i", $sale_id);
        $stmt3->execute();
    }

    header("Location: sales.php");
    exit;
}
?>
