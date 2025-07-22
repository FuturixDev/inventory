<?php
include 'db.php';   // 正確引入 $conn
$total_stock = 0;
$total_cost = 0;
$total_income = 0;
$total_expense = 0;
$total_profit = 0;

if ($conn) {
    // 總庫存
    $res1 = $conn->query("
      SELECT SUM(CASE WHEN change_type = 'in' THEN quantity ELSE -quantity END) AS total_stock
      FROM inventory_logs
    ");
    $total_stock = $res1->fetch_assoc()['total_stock'] ?? 0;

    // 總成本
    $res2 = $conn->query("
      SELECT SUM(quantity * unit_cost) AS total_cost
      FROM inventory_logs
      WHERE change_type = 'in'
    ");
    $total_cost = $res2->fetch_assoc()['total_cost'] ?? 0;

    // 銷售收入
    $res3 = $conn->query("SELECT SUM(price * quantity) AS income FROM sales");
    $total_income = $res3->fetch_assoc()['income'] ?? 0;

    // 總支出
    $res4 = $conn->query("
      SELECT
        IFNULL((SELECT SUM(amount) FROM kol_transactions WHERE type IN ('paid','commission')), 0) +
        IFNULL((SELECT SUM(amount) FROM expenses), 0) AS total_expense
    ");
    $total_expense = $res4->fetch_assoc()['total_expense'] ?? 0;

    // 利潤計算
    $total_cost_of_goods_sold = 0;
    $res = $conn->query("SELECT product_id, SUM(quantity) AS qty FROM sales GROUP BY product_id");

    while ($row = $res->fetch_assoc()) {
        $product_id = $row['product_id'];
        $qty = $row['qty'];

        $stmt = $conn->prepare("
            SELECT SUM(quantity * unit_cost) / SUM(quantity) AS avg_cost
            FROM inventory_logs
            WHERE product_id = ? AND change_type = 'in'
        ");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $avg_cost = $stmt->get_result()->fetch_assoc()['avg_cost'] ?? 0;

        $total_cost_of_goods_sold += $avg_cost * $qty;
    }

    $total_profit = $total_income - $total_cost_of_goods_sold - $total_expense;
}
?>
