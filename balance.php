<?php
include 'db.php';
include 'nav.php';

// 1. 現金資產（收入 - 所有支出）
$res1 = $conn->query("
  SELECT
    (SELECT IFNULL(SUM(price * quantity), 0) FROM sales) -
    (SELECT IFNULL(SUM(p.cost * s.quantity), 0) FROM sales s JOIN products p ON s.product_id = p.id) -
    (SELECT IFNULL(SUM(amount), 0) FROM kol_transactions) -
    (SELECT IFNULL(SUM(amount), 0) FROM expenses)
  AS cash
");
$cash = $res1->fetch_assoc()['cash'];

// 2. 商品庫存價值
$res2 = $conn->query("SELECT SUM(cost * quantity) AS stock_value FROM products");
$stock = $res2->fetch_assoc()['stock_value'] ?? 0;

// 3. 應付（簡化為預估 KOL 未結支出）
$payables = 0; // 可根據業務情境補上

// 4. 資產 / 負債 / 淨值
$total_assets = $cash + $stock;
$total_liabilities = $payables;
$net_worth = $total_assets - $total_liabilities;
?>

<h2>📄 資產負債表（B/S）</h2>
<table border="1" cellpadding="6">
  <tr><th>項目</th><th>金額</th></tr>
  <tr><td>現金（估算）</td><td>$<?= number_format($cash, 2) ?></td></tr>
  <tr><td>庫存價值</td><td>$<?= number_format($stock, 2) ?></td></tr>
  <tr><td><strong>資產總計</strong></td><td>$<?= number_format($total_assets, 2) ?></td></tr>
  <tr><td>應付款（估）</td><td>$<?= number_format($payables, 2) ?></td></tr>
  <tr><td><strong>負債總計</strong></td><td>$<?= number_format($total_liabilities, 2) ?></td></tr>
  <tr><th>淨值</th><th>$<?= number_format($net_worth, 2) ?></th></tr>
</table>
