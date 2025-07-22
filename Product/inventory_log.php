<?php
include '../db.php';
include '../nav.php';

$products = false;

if ($conn && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $product_id = $_POST['product_id'];
    $type = $_POST['change_type'];
    $qty = $_POST['quantity'];
    $note = $_POST['note'] ?? '';
    $unit_cost = ($_POST['unit_cost'] !== '' && $type == 'in') ? $_POST['unit_cost'] : null;

    $stmt = $conn->prepare("INSERT INTO inventory_logs (product_id, change_type, quantity, unit_cost, note) VALUES (?, ?, ?, ?, ?)");
    if (!$stmt) {
        die("❌ SQL Prepare 錯誤：" . $conn->error);
    }

    $stmt->bind_param("issds", $product_id, $type, $qty, $unit_cost, $note);
    $stmt->execute();

    header("Location: inventory_history.php");
    exit;
}

// 取得產品清單（下拉用）
if ($conn) {
    $products = $conn->query("SELECT id, model, name FROM products");
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>登錄進貨</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">
  <h2 class="mb-4">📥 登錄進貨</h2>

  <form method="POST" class="card shadow-sm p-4 bg-white">
    <div class="mb-3">
      <label class="form-label">產品</label>
      <select name="product_id" class="form-select" required>
        <?php while($p = $products->fetch_assoc()): ?>
          <option value="<?= $p['id'] ?>"><?= $p['model'] ?> - <?= $p['name'] ?></option>
        <?php endwhile; ?>
      </select>
    </div>

    <div class="mb-3">
      <label class="form-label">類型</label>
      <select name="change_type" class="form-select" required>
        <option value="in">進貨</option>
        <option value="out" disabled>出貨（由銷售自動產生）</option>
      </select>
    </div>

    <div class="mb-3">
      <label class="form-label">數量</label>
      <input type="number" name="quantity" class="form-control" required>
    </div>

    <div class="mb-3">
      <label class="form-label">單價</label>
      <input type="number" step="0.01" name="unit_cost" class="form-control">
    </div>

    <div class="mb-3">
      <label class="form-label">備註</label>
      <input type="text" name="note" class="form-control">
    </div>

    <div class="mb-3 d-flex gap-2">
        <button type="submit" class="btn btn-primary">✅ 送出登錄</button>
        <a href="inventory_history.php" class="btn btn-secondary">← 回歷史紀錄</a>
    </div>

  </form>
</div>
</body>
</html>
