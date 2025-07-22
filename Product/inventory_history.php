<?php
include '../db.php';
include '../nav.php';

// 假資料（在資料庫失敗時用）
$fake_logs = [
  ['model' => 'A001', 'change_type' => 'in', 'quantity' => 10, 'unit_cost' => 100, 'note' => '首批進貨', 'created_at' => '2025-07-01 10:00:00'],
  ['model' => 'A001', 'change_type' => 'out', 'quantity' => 3, 'unit_cost' => 100, 'note' => '夜市銷售', 'created_at' => '2025-07-03 15:20:00'],
];

$result = false;
$note_keyword = $_GET['note'] ?? '';
$type_filter = $_GET['type'] ?? 'all';

if ($conn) {
    $where = [];
    $params = [];
    $types = '';

    if (!empty($note_keyword)) {
        $where[] = "l.note LIKE ?";
        $params[] = '%' . $note_keyword . '%';
        $types .= 's';
    }

    if ($type_filter === 'in' || $type_filter === 'out') {
        $where[] = "l.change_type = ?";
        $params[] = $type_filter;
        $types .= 's';
    }

    $where_clause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

    $sql = "
        SELECT l.*, p.model
        FROM inventory_logs l
        JOIN products p ON l.product_id = p.id
        $where_clause
        ORDER BY l.created_at DESC
    ";

    if ($params) {
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $result = $stmt->get_result();
        }
    } else {
        $result = $conn->query($sql);
    }
}
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
  <meta charset="UTF-8">
  <title>庫存異動紀錄</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-4">
  <h2 class="mb-4">📜 庫存異動紀錄</h2>

  <!-- 🔍 篩選表單 -->
  <form method="GET" class="mb-4 d-flex flex-wrap gap-2 align-items-center">
    <input type="text" name="note" class="form-control" placeholder="輸入備註關鍵字…" value="<?= htmlspecialchars($note_keyword) ?>" style="max-width: 300px;">
    <select name="type" class="form-select" style="max-width: 180px;">
      <option value="all" <?= $type_filter === 'all' ? 'selected' : '' ?>>📦 顯示全部</option>
      <option value="in" <?= $type_filter === 'in' ? 'selected' : '' ?>>📥 進貨</option>
      <option value="out" <?= $type_filter === 'out' ? 'selected' : '' ?>>📤 出貨</option>
    </select>
    <button type="submit" class="btn btn-primary">🔍 篩選</button>
    <a href="inventory_history.php" class="btn btn-outline-secondary">重置</a>
  </form>

  <!-- 🔽 資料表格 -->
  <div class="table-responsive">
    <table class="table table-bordered table-hover align-middle text-center bg-white shadow-sm">
      <thead class="table-light">
        <tr>
          <th>產品</th>
          <th>類型</th>
          <th>數量</th>
          <th>單價</th>
          <th>總成本</th>
          <th>備註</th>
          <th>時間</th>
          <th>操作</th>
          <?php if ($type_filter === 'out'): ?>
            <th>✅ 寄出</th>
          <?php endif; ?>
        </tr>
      </thead>
      <tbody>
        <?php if ($result): ?>
          <?php while ($row = $result->fetch_assoc()):
            $is_in = $row['change_type'] === 'in';
            $unit_cost = $is_in ? $row['unit_cost'] : null;
            $total_cost = $is_in ? $unit_cost * $row['quantity'] : null;
            $type_label = $is_in
                ? '<span class="badge bg-success">進貨</span>'
                : '<span class="badge bg-danger">出貨</span>';
          ?>
          <tr>
            <td><?= htmlspecialchars($row['model']) ?></td>
            <td><?= $type_label ?></td>
            <td><?= $row['quantity'] ?></td>
            <td><?= $is_in ? '$' . number_format($unit_cost, 2) : '-' ?></td>
            <td><?= $is_in ? '$' . number_format($total_cost, 2) : '-' ?></td>
            <td><?= htmlspecialchars($row['note']) ?></td>
            <td><?= $row['created_at'] ?></td>
            <td>
              <a href="delete_inventory.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('確定要刪除這筆紀錄嗎？');">刪除</a>
            </td>
            <?php if ($type_filter === 'out'): ?>
              <td><input type="checkbox" class="form-check-input"></td>
            <?php endif; ?>
          </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <?php foreach ($fake_logs as $row): 
            $is_in = $row['change_type'] === 'in';
            $unit_cost = $is_in ? $row['unit_cost'] : null;
            $total_cost = $is_in ? $unit_cost * $row['quantity'] : null;
            $type_label = $is_in
                ? '<span class="badge bg-success">進貨</span>'
                : '<span class="badge bg-danger">出貨</span>';
          ?>
          <tr>
            <td><?= htmlspecialchars($row['model']) ?></td>
            <td><?= $type_label ?></td>
            <td><?= $row['quantity'] ?></td>
            <td><?= $is_in ? '$' . number_format($unit_cost, 2) : '-' ?></td>
            <td><?= $is_in ? '$' . number_format($total_cost, 2) : '-' ?></td>
            <td><?= htmlspecialchars($row['note']) ?></td>
            <td><?= $row['created_at'] ?></td>
            <td><span class="text-muted">（無操作）</span></td>
            <?php if ($type_filter === 'out'): ?>
              <td><input type="checkbox" class="form-check-input" disabled></td>
            <?php endif; ?>
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
