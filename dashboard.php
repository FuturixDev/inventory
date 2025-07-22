<?php
include 'db.php';

// 產品總數
$res1 = $conn->query("SELECT COUNT(*) AS total FROM products");
$total_products = $res1->fetch_assoc()['total'] ?? 0;

// 總庫存（進 - 出）
$res2 = $conn->query("
  SELECT SUM(CASE WHEN change_type = 'in' THEN quantity ELSE -quantity END) AS total_stock
  FROM inventory_logs
");
$total_stock = $res2->fetch_assoc()['total_stock'] ?? 0;

// 總銷售收入
$res3 = $conn->query("SELECT SUM(price * quantity) AS income FROM sales");
$total_income = $res3->fetch_assoc()['income'] ?? 0;

// 利潤（計算每個產品的平均進貨成本 × 銷售數量）
$res4 = $conn->query("SELECT product_id, SUM(quantity) AS qty, SUM(price * quantity) AS revenue FROM sales GROUP BY product_id");
$total_profit = 0;

while ($row = $res4->fetch_assoc()) {
    $pid = $row['product_id'];
    $sold_qty = $row['qty'];
    $revenue = $row['revenue'];

    // 查平均成本
    $stmt = $conn->prepare("
        SELECT SUM(quantity * unit_cost) / SUM(quantity) AS avg_cost
        FROM inventory_logs
        WHERE product_id = ? AND change_type = 'in'
    ");
    $stmt->bind_param("i", $pid);
    $stmt->execute();
    $avg_result = $stmt->get_result()->fetch_assoc();
    $avg_cost = $avg_result['avg_cost'] ?? 0;

    // 利潤 = 銷售金額 - 成本
    $total_profit += $revenue - ($avg_cost * $sold_qty);
}
?>

<?php
include 'db.php';
?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<?php include 'nav.php'; ?>

<?php
// 總庫存（數量）
$res1 = $conn->query("
  SELECT SUM(CASE WHEN change_type = 'in' THEN quantity ELSE -quantity END) AS total_stock
  FROM inventory_logs
");
$total_stock = $res1->fetch_assoc()['total_stock'] ?? 0;

// 總成本（進貨金額總和）
$res2 = $conn->query("
  SELECT SUM(quantity * unit_cost) AS total_cost
  FROM inventory_logs
  WHERE change_type = 'in'
");
$total_cost = $res2->fetch_assoc()['total_cost'] ?? 0;

// 銷售收入
$res3 = $conn->query("SELECT SUM(price * quantity) AS income FROM sales");
$total_income = $res3->fetch_assoc()['income'] ?? 0;

// 總支出（KOL + 營運）
$res4 = $conn->query("
  SELECT
    IFNULL((SELECT SUM(amount) FROM kol_transactions WHERE type IN ('paid','commission')), 0) +
    IFNULL((SELECT SUM(amount) FROM expenses), 0) AS total_expense
");
$total_expense = $res4->fetch_assoc()['total_expense'] ?? 0;

// 淨利
$total_cost_of_goods_sold = 0;

// 查所有銷售紀錄（依產品彙總）
$res = $conn->query("SELECT product_id, SUM(quantity) AS qty FROM sales GROUP BY product_id");

while ($row = $res->fetch_assoc()) {
    $product_id = $row['product_id'];
    $qty = $row['qty'];

    // 查這個產品的平均進貨成本
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

$total_profit = $total_income - $total_cost_of_goods_sold - $total_expense;?>

<div class="container py-4">
  <h2 class="mb-4">📊 系統總覽 Dashboard</h2>

  <div class="row g-4">
    <div class="col-md-2">
      <div class="card shadow-sm text-center bg-light">
        <div class="card-body">
          <h6 class="card-title">📦 總庫存（數量）</h6>
          <p class="fs-4"><?= $total_stock ?></p>
        </div>
      </div>
    </div>

    <div class="col-md-2">
      <div class="card shadow-sm text-center bg-light">
        <div class="card-body">
          <h6 class="card-title">💸 總成本</h6>
          <p class="fs-4">$<?= number_format($total_cost, 0) ?></p>
        </div>
      </div>
    </div>

    <div class="col-md-2">
      <div class="card shadow-sm text-center bg-light">
        <div class="card-body">
          <h6 class="card-title">💰 總銷售</h6>
          <p class="fs-4">$<?= number_format($total_income, 0) ?></p>
        </div>
      </div>
    </div>

    <div class="col-md-2">
      <div class="card shadow-sm text-center bg-light">
        <div class="card-body">
          <h6 class="card-title">📤 總支出</h6>
          <p class="fs-4">$<?= number_format($total_expense, 0) ?></p>
        </div>
      </div>
    </div>

    <div class="col-md-4">
      <div class="card shadow-sm text-center bg-light">
        <div class="card-body">
          <h5 class="card-title">🟢 淨利</h5>
          <p class="fs-3 fw-bold">$<?= number_format($total_profit, 0) ?></p>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
