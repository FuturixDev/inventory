<?php

date_default_timezone_set('Asia/Taipei'); // 🕒 確保寫入時間是台灣時區

include '../db.php';
include '../nav.php';


// 取得 KOL 與產品選單資料
$kols = $conn->query("SELECT id, name FROM kols");
$products = $conn->query("SELECT id, model FROM products");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $kol_id = $_POST['kol_id'];
  $product_id = $_POST['product_id'];
  $quantity = $_POST['quantity'];
  $user_note = trim($_POST['note'] ?? '');

  // 取得 KOL 名稱
  $kol_name_stmt = $conn->prepare("SELECT name FROM kols WHERE id = ?");
  $kol_name_stmt->bind_param("i", $kol_id);
  $kol_name_stmt->execute();
  $kol_name = $kol_name_stmt->get_result()->fetch_assoc()['name'] ?? '未知KOL';

  // 備註統一格式
  $note = "公關贈送給 {$kol_name}";
  if ($user_note !== '') {
    $note .= "（{$user_note}）";
  }

  // 平均成本
  $stmt = $conn->prepare("SELECT SUM(quantity * unit_cost) / SUM(quantity) AS avg_cost FROM inventory_logs WHERE product_id = ? AND change_type = 'in'");
  $stmt->bind_param("i", $product_id);
  $stmt->execute();
  $avg_cost = $stmt->get_result()->fetch_assoc()['avg_cost'] ?? 0;

  $total_cost = round($avg_cost * $quantity, 2);
  $current_time = date('Y-m-d H:i:s');

  // 1. 寫入 inventory_logs（扣除庫存）
  $change_type = 'out';
  $log_stmt = $conn->prepare("INSERT INTO inventory_logs (product_id, change_type, quantity, unit_cost, note, created_at) VALUES (?, ?, ?, ?, ?, ?)");
  $log_stmt->bind_param("isidss", $product_id, $change_type, $quantity, $avg_cost, $note, $current_time);
  $log_stmt->execute();

  // 2. 寫入 kol_transactions
  $type = 'gift';
  $gift_stmt = $conn->prepare("INSERT INTO kol_transactions (kol_id, type, product_id, quantity, amount, note, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)");
  $gift_stmt->bind_param("ssiidss", $kol_id, $type, $product_id, $quantity, $total_cost, $note, $current_time);
  $gift_stmt->execute();

  // 3. 寫入 sales（售價為 0）
  $channel = 'KOL';
  $price = 0.00;
  $sale_stmt = $conn->prepare("INSERT INTO sales (product_id, quantity, price, channel, sale_date, note, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)");
  $sale_stmt->bind_param("iidssss", $product_id, $quantity, $price, $channel, $current_time, $note, $current_time);
  $sale_stmt->execute();

  header("Location: /inventory-system/Product/inventory_history.php");
  exit;
}
?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>贈送公關機</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">
  <h2 class="mb-4">🎁 登記 KOL 公關贈送</h2>

  <form method="POST" class="card p-4 shadow-sm bg-white">
    <div class="mb-3">
      <label class="form-label">KOL 名稱</label>
      <select name="kol_id" class="form-select" required>
        <option value="">請選擇</option>
        <?php while ($k = $kols->fetch_assoc()): ?>
          <option value="<?= $k['id'] ?>"><?= htmlspecialchars($k['name']) ?></option>
        <?php endwhile; ?>
      </select>
    </div>

    <div class="mb-3">
      <label class="form-label">產品型號</label>
      <select name="product_id" class="form-select" required>
        <option value="">請選擇</option>
        <?php while ($p = $products->fetch_assoc()): ?>
          <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['model']) ?></option>
        <?php endwhile; ?>
      </select>
    </div>

    <div class="mb-3">
      <label class="form-label">數量</label>
      <input type="number" name="quantity" class="form-control" required>
    </div>

    <div class="mb-3">
      <label class="form-label">備註</label>
      <input type="text" name="note" class="form-control">
    </div>

    <div class="mb-3 d-flex gap-2">
      <button type="submit" class="btn btn-success">✅ 送出登記</button>
      <a href="/inventory-system/Product/inventory_history.php" class="btn btn-secondary">← 回歷史紀錄</a>
    </div>
  </form>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
