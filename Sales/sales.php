<?php
include '../db.php';
include '../nav.php';

// ✅ 預設變數，避免 Warning
$search = $_GET['search'] ?? '';
$sort = $_GET['sort'] ?? 'created_at_desc';
$res = false;

// SQL 排序條件
switch ($sort) {
    case 'price_asc':     $order_by = 'price ASC'; break;
    case 'price_desc':    $order_by = 'price DESC'; break;
    case 'shipped_asc':   $order_by = 'shipped ASC'; break;
    case 'shipped_desc':  $order_by = 'shipped DESC'; break;
    default:              $order_by = 'created_at DESC';
}

if ($conn) {
    $query = "SELECT * FROM sales WHERE 1";
    $params = [];
    $types = '';

    if (!empty($search)) {
        $query .= " AND (model LIKE ? OR note LIKE ?)";
        $search_param = "%{$search}%";
        $params[] = $search_param;
        $params[] = $search_param;
        $types .= 'ss';
    }

    $query .= " ORDER BY $order_by";

    if (!empty($params)) {
        $stmt = $conn->prepare($query);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $res = $stmt->get_result();
    } else {
        $res = $conn->query($query);
    }
}

// 🌐 假資料 fallback
$fake_sales = [
    ['model' => 'FX-1000', 'quantity' => 2, 'price' => 199.99, 'note' => '展示用機種', 'shipped' => 0, 'created_at' => '2025-07-21 14:20:00'],
    ['model' => 'ZB-300', 'quantity' => 1, 'price' => 299.5, 'note' => '公關贈送給 Youtuber', 'shipped' => 1, 'created_at' => '2025-07-20 10:00:00'],
];
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>銷售紀錄</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-4">
    <h2 class="mb-4">🧾 銷售紀錄</h2>

    <form method="GET" class="d-flex flex-wrap gap-2 mb-4">
        <input type="text" name="search" class="form-control" placeholder="搜尋型號或備註…" value="<?= htmlspecialchars($search ?? '') ?>" style="max-width: 300px;">
        <select name="sort" class="form-select w-auto">
            <option value="created_at_desc" <?= $sort === 'created_at_desc' ? 'selected' : '' ?>>🕒 最新優先</option>
            <option value="price_asc" <?= $sort === 'price_asc' ? 'selected' : '' ?>>⬆️ 價格低到高</option>
            <option value="price_desc" <?= $sort === 'price_desc' ? 'selected' : '' ?>>⬇️ 價格高到低</option>
            <option value="shipped_asc" <?= $sort === 'shipped_asc' ? 'selected' : '' ?>>📦 未寄出優先</option>
            <option value="shipped_desc" <?= $sort === 'shipped_desc' ? 'selected' : '' ?>>📬 已寄出優先</option>
        </select>
        <button class="btn btn-primary">搜尋</button>
        <a href="sales.php" class="btn btn-outline-secondary">重置</a>
    </form>

    <div class="table-responsive">
        <table class="table table-bordered table-hover text-center bg-white">
            <thead class="table-light">
                <tr>
                    <th>型號</th>
                    <th>數量</th>
                    <th>單價</th>
                    <th>備註</th>
                    <th>寄送狀態</th>
                    <th>建立時間</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($res): ?>
                    <?php while ($row = $res->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['model']) ?></td>
                            <td><?= $row['quantity'] ?></td>
                            <td>$<?= number_format($row['price'], 2) ?></td>
                            <td><?= htmlspecialchars($row['note']) ?></td>
                            <td><?= $row['shipped'] ? '✅ 已寄出' : '❌ 未寄出' ?></td>
                            <td><?= $row['created_at'] ?></td>
                            <td>
                                <a href="edit_sale.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning">編輯</a>
                                <a href="delete_sale.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('確定要刪除？');">刪除</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <?php foreach ($fake_sales as $row): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['model']) ?></td>
                            <td><?= $row['quantity'] ?></td>
                            <td>$<?= number_format($row['price'], 2) ?></td>
                            <td><?= htmlspecialchars($row['note']) ?></td>
                            <td><?= $row['shipped'] ? '✅ 已寄出' : '❌ 未寄出' ?></td>
                            <td><?= $row['created_at'] ?></td>
                            <td><span class="text-muted">（僅預覽）</span></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
