<?php
include '../db.php';
include '../nav.php';

date_default_timezone_set('Asia/Taipei'); // 設定時區

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $product_id = $_POST['product_id'];
    $quantity = $_POST['quantity'];
    $price = $_POST['price'];
    $channel = $_POST['channel'] ?? '未知通路';
    $date = $_POST['sale_date'];
    $note = $_POST['note'];
  
    // ✅ 新增
    $current_datetime = date('Y-m-d H:i:s');
  
    // 1. 寫入銷售資料
    $stmt = $conn->prepare("INSERT INTO sales (product_id, quantity, price, channel, sale_date, note, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iidssss", $product_id, $quantity, $price, $channel, $date, $note, $current_datetime);
    $stmt->execute();
  
    // 2. 查平均成本
    $stmt2 = $conn->prepare("
        SELECT SUM(quantity * unit_cost) / SUM(quantity) AS avg_cost
        FROM inventory_logs
        WHERE product_id = ? AND change_type = 'in'
    ");
    $stmt2->bind_param("i", $product_id);
    $stmt2->execute();
    $avg_result = $stmt2->get_result()->fetch_assoc();
    $avg_cost = $avg_result['avg_cost'] ?? 0;
  
    // 3. 建立庫存備註
    $note_inventory = "銷售出貨";
    if (!empty($channel)) $note_inventory .= "($channel)";
    if (!empty($note)) $note_inventory .= "，備註：$note";
  
    // 4. 寫入庫存異動紀錄
    $stmt3 = $conn->prepare("INSERT INTO inventory_logs (product_id, change_type, quantity, unit_cost, note, created_at) VALUES (?, 'out', ?, ?, ?, ?)");
    $stmt3->bind_param("iidss", $product_id, $quantity, $avg_cost, $note_inventory, $current_datetime);
    $stmt3->execute();
  
    header("Location: sales.php");
    exit;
  }
  

// 讀取產品清單
$products = $conn->query("SELECT id, model FROM products");
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>➕ 登錄銷售</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-4">
    <h2 class="mb-4 text-center">➕ 登錄銷售</h2>

    <form method="POST" class="card p-4 shadow-sm bg-white">
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
            <label class="form-label">通路</label>
            <select name="channel" class="form-select" required>
                <option value="夜市">夜市</option>
                <option value="自營">自營</option>
                <option value="KOL">KOL</option>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">售價</label>
            <input type="number" step="0.01" name="price" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">數量</label>
            <input type="number" name="quantity" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">銷售日期</label>
            <input type="date" name="sale_date" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">備註</label>
            <input type="text" name="note" class="form-control">
        </div>

        <div class="mb-3 d-flex gap-2">
            <button type="submit" class="btn btn-success">✅ 送出登錄</button>
            <a href="sales.php" class="btn btn-secondary">← 回銷售紀錄</a>
        </div>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
