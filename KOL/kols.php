<?php
include '../db.php';
include '../nav.php';

// 取得排序參數
$sort = $_GET['sort'] ?? 'created_at_desc';

switch ($sort) {
  case 'percent_asc':         $order_by = 'commission_percent ASC'; break;
  case 'percent_desc':        $order_by = 'commission_percent DESC'; break;
  case 'start_after_asc':     $order_by = 'commission_start_after ASC'; break;
  case 'start_after_desc':    $order_by = 'commission_start_after DESC'; break;
  case 'note_asc':            $order_by = 'note ASC'; break;
  case 'note_desc':           $order_by = 'note DESC'; break;
  case 'created_asc':         $order_by = 'created_at ASC'; break;
  default:                    $order_by = 'created_at DESC'; break;
}

$result = $conn->query("SELECT * FROM kols ORDER BY $order_by");
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
  <meta charset="UTF-8">
  <title>👥 KOL 名單</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">
  <h2 class="mb-4 text-center">👥 KOL 名單</h2>

  <!-- 操作按鈕區 -->
  <div class="mb-3 d-flex flex-wrap gap-2 justify-content-center">
    <a href="add_kol.php" class="btn btn-success">➕ 新增 KOL</a>
    <a href="kol_transactions.php" class="btn btn-warning">📜 合作紀錄</a>
    <a href="add_kol_gift.php" class="btn btn-primary">🎁 贈送公關台</a>
    <a href="kol_commissions.php" class="btn btn-info">🤑 KOL 分潤</a>
  </div>

  <!-- 排序表單 -->
  <form method="GET" class="d-flex flex-wrap gap-2 justify-content-center mb-3">
    <select name="sort" class="form-select w-auto">
      <option value="created_at_desc" <?= $sort === 'created_at_desc' ? 'selected' : '' ?>>🕒 建立時間（新到舊）</option>
      <option value="created_asc" <?= $sort === 'created_asc' ? 'selected' : '' ?>>⏳ 建立時間（舊到新）</option>
      <option value="percent_desc" <?= $sort === 'percent_desc' ? 'selected' : '' ?>>💰 分潤高到低</option>
      <option value="percent_asc" <?= $sort === 'percent_asc' ? 'selected' : '' ?>>💸 分潤低到高</option>
      <option value="start_after_desc" <?= $sort === 'start_after_desc' ? 'selected' : '' ?>>🚚 分潤起始（多到少）</option>
      <option value="start_after_asc" <?= $sort === 'start_after_asc' ? 'selected' : '' ?>>📦 分潤起始（少到多）</option>
      <option value="note_asc" <?= $sort === 'note_asc' ? 'selected' : '' ?>>🔤 備註 A → Z</option>
      <option value="note_desc" <?= $sort === 'note_desc' ? 'selected' : '' ?>>🔡 備註 Z → A</option>
    </select>
    <button class="btn btn-primary">排序</button>
    <a href="kols.php" class="btn btn-outline-secondary">重置</a>
  </form>

  <!-- KOL 名單表格 -->
  <div class="table-responsive">
    <table class="table table-bordered table-hover align-middle text-center bg-white shadow-sm">
      <thead class="table-light">
        <tr>
          <th>姓名</th>
          <th>分潤 %</th>
          <th>第幾台後分潤</th>
          <th>備註</th>
          <th>建立時間</th>
          <th>操作</th>
        </tr>
      </thead>
      <tbody>
        <?php while($row = $result->fetch_assoc()): ?>
        <tr>
          <td><?= htmlspecialchars($row['name']) ?></td>
          <td><?= htmlspecialchars($row['commission_percent']) ?>%</td>
          <td><?= htmlspecialchars($row['commission_start_after']) ?> 台後</td>
          <td><?= htmlspecialchars($row['note']) ?></td>
          <td><?= htmlspecialchars($row['created_at']) ?></td>
          <td>
            <a href="edit_kol.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning">編輯</a>
            <a href="delete_kol.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('確定要刪除這個 KOL 嗎？');">刪除</a>
          </td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
