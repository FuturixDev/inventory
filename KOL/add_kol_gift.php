<?php
date_default_timezone_set('Asia/Taipei'); // 🕒 使用台灣時區

include '../db.php';
include '../nav.php';

// 初始化選單資料
$kols = $products = [];

if ($conn) {
    // 取得 KOL 清單
    $kol_res = $conn->query("SELECT id, name FROM kols");
    if ($kol_res) {
        $kols = $kol_res->fetch_all(MYSQLI_ASSOC);
    }

    // 取得產品清單
    $prod_res = $conn->query("SELECT id, model FROM products");
    if ($prod_res) {
        $products = $prod_res->fetch_all(MYSQLI_ASSOC);
    }
}

// ⬇️ POST 提交處理
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $conn) {
    $kol_id = intval($_POST['kol_id']);
    $product_id = intval($_POST['product_id']);
    $quantity = intval($_POST['quantity']);
    $user_note = trim($_POST['note'] ?? '');

    // 查詢 KOL 名稱
    $kol_name = '未知KOL';
    $stmt = $conn->prepare("SELECT name FROM kols WHERE id = ?");
    $stmt->bind_param("i", $kol_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $kol_name = $row['name'];
    }

    // 備註格式統一
    $note = "公關贈送給 {$kol_name}";
    if ($user_note !== '') {
        $note .= "（{$user_note}）";
    }

    // 計算平均成本
    $stmt = $conn->prepare("SELECT SUM(quantity * unit_cost) / SUM(quantity) AS avg_cost FROM inventory_logs WHERE product_id = ? AND change_type = 'in'");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $avg_cost = floatval($stmt->get_result()->fetch_assoc()['avg_cost'] ?? 0);

    $total_cost = round($avg_cost * $quantity, 2);
    $now = date('Y-m-d H:i:s');

    // 1️⃣ 寫入 inventory_logs（扣庫存）
    $change_type = 'out';
    $stmt = $conn->prepare("INSERT INTO inventory_logs (product_id, change_type, quantity, unit_cost, note, created_at) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isidss", $product_id, $change_type, $quantity, $avg_cost, $note, $now);
    $stmt->execute();

    // 2️⃣ 寫入 kol_transactions
    $type = 'gift';
    $stmt = $conn->prepare("INSERT INTO kol_transactions (kol_id, type, product_id, quantity, amount, note, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issidss", $kol_id, $type, $product_id, $quantity, $total_cost, $note, $now);
    $stmt->execute();

    // 3️⃣ 寫入 sales（售價為 0）
    $channel = 'KOL';
    $price = 0.00;
    $stmt = $conn->prepare("INSERT INTO sales (product_id, quantity, price, channel, sale_date, note, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iidssss", $product_id, $quantity, $price, $channel, $now, $note, $now);
    $stmt->execute();

    // ⏩ 導回歷史紀錄頁
    header("Location: /inventory-system/Product/inventory_history.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
  <meta charset="UTF-8">
  <title>贈送公關機</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-4">
  <h2 class="mb-4">🎁 登記 KOL 公關贈送</h2>

  <?php if (!$conn): ?>
    <div class="alert alert-danger">❌ 資料庫未連接，請稍後再試。</div>
  <?php else: ?>
    <form method="POST" class="card p-4 shadow-sm bg-white">
      <div class="mb-3">
        <label class="form-label">KOL 名稱</label>
        <select name="kol_id" class="form-select" required>
          <option value="">請選擇</option>
          <?php foreach ($kols as $k): ?>
            <option value="<?= $k['id'] ?>"><?= htmlspecialchars($k['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="mb-3">
        <label class="form-label">產品型號</label>
        <select name="product_id" class="form-select" required>
          <option value="">請選擇</option>
          <?php foreach ($products as $p): ?>
            <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['model']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="mb-3">
        <label class="form-label">數量</label>
        <input type="number" name="quantity" class="form-control" required min="1">
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
  <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
