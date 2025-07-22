<?php
include 'db.php';
include 'nav.php';

$res = $conn->query("SELECT * FROM expenses ORDER BY date DESC");
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>💸 營運支出</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-4">
  <h2 class="text-center mb-4">💸 營運支出</h2>

  <!-- 操作按鈕區 -->
  <div class="mb-3 d-flex justify-content-center">
    <a href="add_expense.php" class="btn btn-success">➕ 登錄支出</a>
  </div>

  <!-- 支出表格 -->
  <div class="table-responsive">
    <table class="table table-bordered table-hover align-middle text-center bg-white shadow-sm">
      <thead class="table-light">
        <tr>
          <th>項目</th>
          <th>金額</th>
          <th>備註</th>
          <th>日期</th>
          <th>操作</th>
        </tr>
      </thead>
      <tbody>
        <?php while($row = $res->fetch_assoc()): ?>
        <tr>
          <td><?= htmlspecialchars($row['category']) ?></td>
          <td>$<?= number_format($row['amount'], 2) ?></td>
          <td><?= htmlspecialchars($row['note']) ?></td>
          <td><?= $row['date'] ?></td>
          <td>
            <a href="edit_expense.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning">編輯</a>
            <a href="delete_expense.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('確定要刪除這筆支出嗎？');">刪除</a>
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
