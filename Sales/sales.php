<?php
include '../db.php';
include '../nav.php';

if (!$conn) {
    // 模擬空資料，避免程式錯誤
    $result = new class {
        function fetch_assoc() { return false; }
    };
    $total_quantity = $shipped_quantity = $unshipped_quantity = 0;
    $gift_quantity = $unshipped_gift_quantity = 0;
    $total_pages = 1;
} else {
    // 原本的資料庫邏輯...
    // --- 分頁處理 ---
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $per_page = 10;
    $offset = ($page - 1) * $per_page;

    // --- 搜尋與排序處理 ---
    $search = trim($_GET['search'] ?? '');
    $sort = $_GET['sort'] ?? 'created_at_desc';
    $where = [];
    $params = [];
    $types = '';

    if (!empty($search)) {
        $where[] = "(p.model LIKE ? OR s.note LIKE ?)";
        $params[] = '%' . $search . '%';
        $params[] = '%' . $search . '%';
        $types .= 'ss';
    }

    switch ($sort) {
        case 'price_asc':    $sort_sql = 's.price ASC'; break;
        case 'price_desc':   $sort_sql = 's.price DESC'; break;
        case 'shipped_asc':  $sort_sql = 's.shipped ASC'; break;
        case 'shipped_desc': $sort_sql = 's.shipped DESC'; break;
        default:             $sort_sql = 's.created_at DESC'; break;
    }

    $where_clause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

    function getValue($sql, $types = '', $params = []) {
        global $conn;
        if ($params) {
            $stmt = $conn->prepare($sql);
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            return $stmt->get_result()->fetch_assoc();
        } else {
            return $conn->query($sql)->fetch_assoc();
        }
    }

    $total_rows = getValue("SELECT COUNT(*) AS total FROM sales s JOIN products p ON s.product_id = p.id $where_clause", $types, $params)['total'];
    $total_pages = ceil($total_rows / $per_page);

    // 各類數量
    $shipped_quantity = getValue("SELECT SUM(s.quantity) AS shipped_quantity FROM sales s JOIN products p ON s.product_id = p.id $where_clause" . ($where_clause ? " AND" : " WHERE") . " s.shipped = 1", $types . 'i', [...$params, 1])['shipped_quantity'] ?? 0;
    $unshipped_quantity = getValue("SELECT SUM(s.quantity) AS unshipped_quantity FROM sales s JOIN products p ON s.product_id = p.id $where_clause" . ($where_clause ? " AND" : " WHERE") . " s.shipped = 0", $types . 'i', [...$params, 0])['unshipped_quantity'] ?? 0;
    $total_quantity = getValue("SELECT SUM(quantity) AS total_qty FROM sales s JOIN products p ON s.product_id = p.id $where_clause", $types, $params)['total_qty'] ?? 0;
    $gift_quantity = getValue("SELECT SUM(quantity) AS gift_qty FROM sales s JOIN products p ON s.product_id = p.id $where_clause" . ($where_clause ? " AND" : " WHERE") . " s.note LIKE '%\u516c\u95d6\u8d08\u9001\u7d66%'", $types, $params)['gift_qty'] ?? 0;
    $unshipped_gift_quantity = getValue("SELECT SUM(quantity) AS unshipped_gift_qty FROM sales s JOIN products p ON s.product_id = p.id $where_clause" . ($where_clause ? " AND" : " WHERE") . " s.note LIKE '%\u516c\u95d6\u8d08\u9001\u7d66%' AND s.shipped = 0", $types, $params)['unshipped_gift_qty'] ?? 0;

    $data_sql = "SELECT s.*, p.model FROM sales s JOIN products p ON s.product_id = p.id $where_clause ORDER BY $sort_sql LIMIT $per_page OFFSET $offset";
    if ($params) {
        $stmt = $conn->prepare($data_sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
    } else {
        $result = $conn->query($data_sql);
    }
}

?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>💰 銷售紀錄</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-4">
        <!-- 標題 -->
        <h2 class="mb-4 text-center">💰 銷售紀錄</h2>

        <!-- 搜尋與功能列 -->
        <div class="row align-items-center g-3 mb-4">
            <form method="GET" class="col d-flex flex-wrap gap-2">
                <input type="text" name="search" class="form-control" placeholder="搜尋型號或備註…" value="<?= htmlspecialchars($search) ?>">
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
            <div class="col-auto">
                <a href="add_sale.php" class="btn btn-success">➕ 登錄銷售</a>
            </div>
        </div>
          <!-- 整體統計 -->
          <div class="mb-4 text-center">
            <span class="badge bg-primary me-2">📦 總銷售數量: <?= $total_quantity ?></span>
            <span class="badge bg-success me-2">📬 已寄出: <?= $shipped_quantity ?></span>
            <span class="badge bg-warning text-dark me-2">📦 未寄出: <?= $unshipped_quantity ?></span>
            <span class="badge bg-info text-dark me-2">🎁 公關台: <?= $gift_quantity ?></span>
            <span class="badge bg-danger text-white">🎁 未寄出公關台: <?= $unshipped_gift_quantity ?></span>
          </div>


        <!-- 銷售表格表單 -->
        <form method="POST" action="update_shipped.php">
            <input type="hidden" name="redirect" value="sales.php?search=<?= urlencode($search) ?>&sort=<?= $sort ?>&page=<?= $page ?>">

            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle text-center bg-white shadow-sm">
                    <thead class="table-light">
                        <tr>
                            <th>產品</th>
                            <th>通路</th>
                            <th>售價</th>
                            <th>平均成本</th>
                            <th>數量</th>
                            <th>利潤</th>
                            <th>時間</th>
                            <th>備註</th>
                            <th>✅ 寄出</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): 
                            $product_id = $row['product_id'];
                            $stmt = $conn->prepare("SELECT SUM(quantity * unit_cost) / SUM(quantity) AS avg_cost FROM inventory_logs WHERE product_id = ? AND change_type = 'in'");
                            $stmt->bind_param("i", $product_id);
                            $stmt->execute();
                            $avg_cost = $stmt->get_result()->fetch_assoc()['avg_cost'] ?? 0;
                            $profit = $row['price'] - $avg_cost;
                        ?>
                            <tr>
                                <td><?= htmlspecialchars($row['model']) ?></td>
                                <td><?= htmlspecialchars($row['channel']) ?></td>
                                <td>$<?= number_format($row['price'], 2) ?></td>
                                <td>$<?= number_format($avg_cost, 2) ?></td>
                                <td><?= $row['quantity'] ?></td>
                                <td>$<?= number_format($profit, 2) ?></td>
                                <td><?= $row['created_at'] ?></td>
                                <td><?= htmlspecialchars($row['note']) ?></td>
                                <td>
                                    <input type="checkbox" class="form-check-input" name="shipped_ids[]" value="<?= $row['id'] ?>" <?= $row['shipped'] ? 'checked' : '' ?>>
                                </td>
                                <td>
                                    <a href="delete_sale.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('確定要刪除這筆銷售紀錄？');">刪除</a>
                                    <a href="edit_sale.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning">編輯</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <div class="text-end mt-3">
                <button type="submit" class="btn btn-primary">💾 更新寄出紀錄</button>
            </div>
        </form>

        <!-- 分頁導航 -->
        <nav class="mt-4">
            <ul class="pagination justify-content-center">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                        <a class="page-link" href="?search=<?= urlencode($search) ?>&sort=<?= $sort ?>&page=<?= $i ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>