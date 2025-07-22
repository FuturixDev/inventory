<?php
include '../db.php';
include '../nav.php';
$result = false;

if ($conn) {
    $result = $conn->query("SELECT * FROM products");
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>產品列表</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-4">
  <h2 class="mb-4">📦 產品列表</h2>

  <div class="mb-3 d-flex flex-wrap gap-2">
    <a href="../dashboard.php" class="btn btn-secondary">← 回系統總覽</a>
    <a href="add_product.php" class="btn btn-success">➕ 新增產品</a>
    <a href="inventory_log.php" class="btn btn-primary">📥 進出貨登錄</a>
    <a href="inventory_history.php" class="btn btn-warning">📜 庫存紀錄</a>
  </div>

  <div class="table-responsive">
    <table class="table table-bordered table-hover align-middle text-center bg-white shadow-sm">
      <thead class="table-light">
        <tr>
          <th>ID</th>
          <th>型號</th>
          <th>名稱</th>
          <th>平均成本</th>
          <th>庫存</th>
          <th>總成本</th>
          <th>建立時間</th>
          <th>操作</th>
        </tr>
      </thead>
      <tbody>
      <?php if ($result && $result->num_rows > 0): ?>
        <?php while($row = $result->fetch_assoc()): 
          $product_id = $row['id'];
          $total_in_qty = 0;
          $total_in_cost = 0;
          $stock = 0;
          $avg_cost = 0;

          if ($conn) {
              $stmt = $conn->prepare("
                SELECT 
                  SUM(CASE WHEN change_type = 'in' THEN quantity ELSE 0 END) AS total_in_qty,
                  SUM(CASE WHEN change_type = 'in' THEN quantity * unit_cost ELSE 0 END) AS total_in_cost,
                  SUM(CASE WHEN change_type = 'in' THEN quantity ELSE 0 END) -
                  SUM(CASE WHEN change_type = 'out' THEN quantity ELSE 0 END) AS stock
                FROM inventory_logs
                WHERE product_id = ?
              ");
              $stmt->bind_param("i", $product_id);
              $stmt->execute();
              $result2 = $stmt->get_result()->fetch_assoc();

              $total_in_qty = $result2['total_in_qty'] ?? 0;
              $total_in_cost = $result2['total_in_cost'] ?? 0;
              $stock = $result2['stock'] ?? 0;
              $avg_cost = ($total_in_qty > 0) ? $total_in_cost / $total_in_qty : 0;
          }
        ?>
          <tr>
            <td><?= $row['id'] ?></td>
            <td><?= htmlspecialchars($row['model']) ?></td>
            <td><?= htmlspecialchars($row['name']) ?></td>
            <td>$<?= number_format($avg_cost, 2) ?></td>
            <td><?= $stock ?></td>
            <td>$<?= number_format($total_in_cost, 2) ?></td>
            <td><?= $row['created_at'] ?></td>
            <td>
              <a href="delete_product.php?id=<?= $row['id'] ?>"
                class="btn btn-sm btn-danger"
                onclick="return confirm('確定要刪除這個產品嗎？');">
                刪除
              </a>
            </td>
          </tr>
        <?php endwhile; ?>
      <?php else: ?>
        <tr>
          <td colspan="8" class="text-muted">⚠️ 尚未連接資料庫或查無資料。</td>
        </tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
</body>
</html>
