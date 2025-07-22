<?php
include '../db.php';
include '../nav.php';

date_default_timezone_set('Asia/Taipei');

// 模擬產品清單（若沒連接資料庫）
$fallback_products = [
    ['id' => 1, 'model' => 'TEST123'],
    ['id' => 2, 'model' => 'SAMPLE456'],
];

// 如果有資料庫連線就讀資料，否則使用預設
if ($conn) {
    $products = $conn->query("SELECT id, model FROM products");
} else {
    $products = null;
}

// 如果是 POST 且有連線才執行新增
if ($_SERVER["REQUEST_METHOD"] == "POST" && $conn) {
    $product_id = $_POST['product_id'];
    $quantity = $_POST['quantity'];
    $price = $_POST['price'];
    $channel = $_POST['channel'] ?? '未知通路';
    $date = $_POST['sale_date'];
    $note = $_POST['note'];
    $current_datetime = date('Y-m-d H:i:s');

    $stmt = $conn->prepare("INSERT INTO sales (...) VALUES (...)"); // 省略其餘程式碼
    // ...
    header("Location: sales.php");
    exit;
}
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
                    <?php if ($products): ?>
                        <?php while ($p = $products->fetch_assoc()): ?>
                            <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['model']) ?></option>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <?php foreach ($fallback_products as $p): ?>
                            <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['model']) ?></option>
                        <?php endforeach; ?>
                    <?php endif; ?>
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
