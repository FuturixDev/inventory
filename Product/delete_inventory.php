<?php
include '../db.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // 查出該筆 inventory_logs 資料
    $stmt = $conn->prepare("SELECT * FROM inventory_logs WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $inventory = $stmt->get_result()->fetch_assoc();

    if ($inventory) {
        $product_id = $inventory['product_id'];
        $quantity = $inventory['quantity'];
        $unit_cost = $inventory['unit_cost'];
        $note = $inventory['note'];
        $created_at = $inventory['created_at'];
        $amount = round($unit_cost * $quantity, 2);

        // 1️⃣ 刪除 inventory_logs 本身
        $del1 = $conn->prepare("DELETE FROM inventory_logs WHERE id = ?");
        $del1->bind_param("i", $id);
        $del1->execute();

        // 2️⃣ 刪除 kol_transactions（僅限公關贈送）
        $del2 = $conn->prepare("DELETE FROM kol_transactions WHERE product_id = ? AND quantity = ? AND amount = ? AND note = ? AND type = 'gift'");
        $del2->bind_param("iids", $product_id, $quantity, $amount, $note);
        $del2->execute();

        // 3️⃣ 刪除 sales（精準比對：price=0 且 created_at 相同）
        $del3 = $conn->prepare("DELETE FROM sales WHERE product_id = ? AND quantity = ? AND price = 0 AND note = ? AND created_at = ?");
        $del3->bind_param("iiss", $product_id, $quantity, $note, $created_at);
        $del3->execute();
    }

    header("Location: inventory_history.php");
    exit;
}
?>
