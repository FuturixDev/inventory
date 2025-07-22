<?php
include 'db.php';
include 'nav.php';

// 若無資料庫，使用假資料以避免報錯
if (!$conn) {
  $income = 20000;
  $cost = 8000;
  $kol_gift = 1500;
  $kol_commission = 2000;
  $ops = 3000;
  $profit = $income - $cost - $kol_gift - $kol_commission - $ops;
} else {
  // 🔹 銷售收入（排除公關贈送 price = 0）
  $res1 = $conn->query("SELECT SUM(price * quantity) AS income FROM sales WHERE price > 0");
  $income = $res1->fetch_assoc()['income'] ?? 0;

  // 🔹 銷售成本
  $res2 = $conn->query("
    SELECT s.product_id, SUM(s.quantity) AS total_qty
    FROM sales s
    WHERE price > 0
    GROUP BY s.product_id
  ");

  $cost = 0;
  while ($row = $res2->fetch_assoc()) {
      $pid = $row['product_id'];
      $qty = $row['total_qty'];

      $avg_sql = $conn->prepare("
        SELECT SUM(quantity * unit_cost) / SUM(quantity) AS avg_cost
        FROM inventory_logs
        WHERE product_id = ? AND change_type = 'in'
      ");
      $avg_sql->bind_param("i", $pid);
      $avg_sql->execute();
      $avg_result = $avg_sql->get_result()->fetch_assoc();
      $avg_cost = $avg_result['avg_cost'] ?? 0;

      $cost += $qty * $avg_cost;
  }

  // 🔹 KOL 公關贈送成本
  $kol_gift_cost = 0;
  $gift_result = $conn->query("
    SELECT product_id, SUM(quantity) AS total_qty 
    FROM kol_transactions 
    WHERE type = 'gift' 
    GROUP BY product_id
  ");

  while ($row = $gift_result->fetch_assoc()) {
      $pid = $row['product_id'];
      $qty = $row['total_qty'];

      $stmt = $conn->prepare("
        SELECT SUM(quantity * unit_cost) / SUM(quantity) AS avg_cost
        FROM inventory_logs
        WHERE product_id = ? AND change_type = 'in'
      ");
      $stmt->bind_param("i", $pid);
      $stmt->execute();
      $avg_cost = $stmt->get_result()->fetch_assoc()['avg_cost'] ?? 0;

      $kol_gift_cost += $avg_cost * $qty;
  }
  $kol_gift = $kol_gift_cost;

  // 🔹 KOL 分潤
  $get_kol_commission = $conn->query("SELECT SUM(total_commission) AS total FROM kol_profit_records");
  $kol_commission = $get_kol_commission->fetch_assoc()['total'] ?? 0;

  // 🔹 營運支出
  $res4 = $conn->query("SELECT SUM(amount) AS ops FROM expenses");
  $ops = $res4->fetch_assoc()['ops'] ?? 0;

  // 🔹 淨利
  $profit = $income - $cost - $kol_gift - $kol_commission - $ops;
}
?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>綜合損益表（P&L）</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-4">
  <h2 class="text-center mb-4">📄 綜合損益表（P&L）</h2>

  <a href="export_pnl_pdf.php" target="_blank" class="btn btn-primary mb-3">📄 匯出 PDF 報表</a>

  <div class="row mb-4">
    <div class="col-md-6 col-lg-3">
      <div class="card shadow-sm border-success text-center">
        <div class="card-body">
          <h5 class="card-title">📦 銷售收入</h5>
          <p class="fs-3 text-success">$<?= number_format($income, 2) ?></p>
        </div>
      </div>
    </div>

    <div class="col-md-6 col-lg-3">
      <div class="card shadow-sm border-danger text-center">
        <div class="card-body">
          <h5 class="card-title">📉 銷售成本</h5>
          <p class="fs-3 text-danger">($<?= number_format($cost, 2) ?>)</p>
        </div>
      </div>
    </div>
  </div>

  <div class="row mb-4">
    <div class="col-md-6 col-lg-3">
      <div class="card shadow-sm border-warning text-center">
        <div class="card-body">
          <h5 class="card-title">🎁 KOL 公關贈送</h5>
          <p class="fs-3 text-warning">($<?= number_format($kol_gift, 2) ?>)</p>
        </div>
      </div>
    </div>

    <div class="col-md-6 col-lg-3">
      <div class="card shadow-sm border-warning text-center">
        <div class="card-body">
          <h5 class="card-title">💼 KOL 分潤</h5>
          <p class="fs-3 text-warning">($<?= number_format($kol_commission, 2) ?>)</p>
        </div>
      </div>
    </div>
  </div>

  <div class="row mb-4">
    <div class="col-md-6 col-lg-3">
      <div class="card shadow-sm border-danger text-center">
        <div class="card-body">
          <h5 class="card-title">💸 營運支出</h5>
          <p class="fs-3 text-danger">($<?= number_format($ops, 2) ?>)</p>
        </div>
      </div>
    </div>
  </div>

  <div class="row mb-4">
    <div class="col-md-6 col-lg-3">
      <div class="card shadow-sm border-success text-center">
        <div class="card-body">
          <h5 class="card-title">🟢 淨利</h5>
          <p class="fs-3 text-success">$<?= number_format($profit, 2) ?></p>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
