<?php
include '../db.php';
include '../nav.php';

$id = $_GET['id'] ?? $_POST['id'] ?? null;

if (!$id || !is_numeric($id)) {
    die("無效的銷售 ID");
}

// 讀取產品清單
$products = $conn->query("SELECT id, model FROM products");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = intval($_POST['product_id']);
    $price = floatval($_POST['price']);
    $quantity = intval($_POST['quantity']);
    $channel = $_POST['channel'];
    $note = trim($_POST['note'] ?? '');
    $sale_date = $_POST['sale_date'];

    // 取得原始資料（找舊 note、product_id、created_at）
    $old_stmt = $conn->prepare("SELECT * FROM sales WHERE id = ?");
    $old_stmt->bind_param("i", $id);
    $old_stmt->execute();
    $old_sale = $old_stmt->get_result()->fetch_assoc();

    $old_product_id = $old_sale['product_id'];
    $old_quantity = $old_sale['quantity'];
    $old_note = $old_sale['note'];
    $old_channel = $old_sale['channel'];
    $old_sale_date = $old_sale['sale_date'];

    // 建構舊備註內容（和寫入 inventory_logs 時相同）
    $old_note_full = "銷售出貨";
    if (!empty($old_channel)) $old_note_full .= "($old_channel)";
    if (!empty($old_note)) $old_note_full .= "，備註：$old_note";

    // 查平均進貨成本
    $cost_stmt = $conn->prepare("SELECT SUM(quantity * unit_cost) / SUM(quantity) AS avg_cost FROM inventory_logs WHERE product_id = ? AND change_type = 'in'");
    $cost_stmt->bind_param("i", $product_id);
    $cost_stmt->execute();
    $avg_cost = $cost_stmt->get_result()->fetch_assoc()['avg_cost'] ?? 0;

    // 🔄 更新 sales 表
    $update_stmt = $conn->prepare("UPDATE sales SET product_id = ?, price = ?, quantity = ?, channel = ?, note = ?, sale_date = ? WHERE id = ?");
    $update_stmt->bind_param("dissssi", $product_id, $price, $quantity, $channel, $note, $sale_date, $id);
    $update_stmt->execute();

    // 🔄 更新對應的 inventory_logs（找舊的 note/created_at 資料）
    $new_note = "銷售出貨";
    if (!empty($channel)) $new_note .= "($channel)";
    if (!empty($note)) $new_note .= "，備註：$note";

    $log_update = $conn->prepare("
        UPDATE inventory_logs 
        SET product_id = ?, quantity = ?, unit_cost = ?, note = ?
        WHERE product_id = ? AND change_type = 'out' AND quantity = ? AND note = ? AND DATE(created_at) = ?
    ");
    $log_update->bind_param(
        "iidsiiss",
        $product_id, $quantity, $avg_cost, $new_note,
        $old_product_id, $old_quantity, $old_note_full, $old_sale_date
    );
    $log_update->execute();

    header("Location: sales.php");
    exit;
}

// 初始讀取銷售資料
$stmt = $conn->prepare("SELECT * FROM sales WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$sale = $stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
  <meta charset="UTF-8">
  <title>✏️ 編輯銷售紀錄</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-4">
  <h2 class="mb-4 text-center">✏️ 編輯銷售紀錄</h2>

  <form method="POST" class="card p-4 shadow-sm bg-white">
    <input type="hidden" name="id" value="<?= $id ?>">

    <div class="mb-3">
      <label class="form-label">產品型號</label>
      <select name="product_id" class="form-select" required>
        <?php while ($p = $products->fetch_assoc()): ?>
          <option value="<?= $p['id'] ?>" <?= ($p['id'] == $sale['product_id']) ? 'selected' : '' ?>>
            <?= htmlspecialchars($p['model']) ?>
          </option>
        <?php endwhile; ?>
      </select>
    </div>

    <div class="mb-3">
      <label class="form-label">售價</label>
      <input type="number" step="0.01" name="price" class="form-control" value="<?= $sale['price'] ?>" required>
    </div>

    <div class="mb-3">
      <label class="form-label">數量</label>
      <input type="number" name="quantity" class="form-control" value="<?= $sale['quantity'] ?>" required>
    </div>

    <div class="mb-3">
      <label class="form-label">通路</label>
      <select name="channel" class="form-select" required>
        <option value="夜市" <?= ($sale['channel'] == '夜市') ? 'selected' : '' ?>>夜市</option>
        <option value="自營" <?= ($sale['channel'] == '自營') ? 'selected' : '' ?>>自營</option>
        <option value="KOL" <?= ($sale['channel'] == 'KOL') ? 'selected' : '' ?>>KOL</option>
      </select>
    </div>

    <div class="mb-3">
      <label class="form-label">銷售日期</label>
      <input type="date" name="sale_date" class="form-control" value="<?= $sale['sale_date'] ?>" required>
    </div>

    <div class="mb-3">
      <label class="form-label">備註</label>
      <input type="text" name="note" class="form-control" value="<?= htmlspecialchars($sale['note']) ?>">
    </div>

    <div class="mb-3 d-flex gap-2">
      <button type="submit" class="btn btn-success">✅ 更新銷售紀錄</button>
      <a href="sales.php" class="btn btn-secondary">← 回銷售紀錄</a>
    </div>
  </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
