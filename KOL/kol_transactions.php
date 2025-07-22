<?php
include '../db.php';
include '../nav.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $kol_id = $_POST['kol_id'];
    $type = $_POST['type'];
    $desc = $_POST['description'];
    $amount = $_POST['amount'];
    $date = $_POST['date'];

    $stmt = $conn->prepare("INSERT INTO kol_transactions (kol_id, type, description, amount, date) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issds", $kol_id, $type, $desc, $amount, $date);
    $stmt->execute();

    header("Location: kol_transactions.php");
    exit;
}

$kols = $conn->query("SELECT id, name FROM kols");
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>➕ 新增合作紀錄</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">


<div class="container py-4">
  <h2 class="text-center mb-4">➕ 新增合作紀錄</h2>

  <!-- 表單區域 -->
  <form method="POST" class="card p-4 shadow-sm bg-white">
    <div class="mb-3">
      <label class="form-label">KOL 名稱</label>
      <select name="kol_id" class="form-select" required>
        <option value="">請選擇 KOL</option>
        <?php while ($k = $kols->fetch_assoc()): ?>
        <option value="<?= $k['id'] ?>"><?= htmlspecialchars($k['name']) ?></option>
        <?php endwhile; ?>
      </select>
    </div>

    <div class="mb-3">
      <label class="form-label">類型</label>
      <select name="type" class="form-select" required>
        <option value="gift">贈送</option>
        <option value="paid">付費</option>
        <option value="commission">抽成</option>
      </select>
    </div>

    <div class="mb-3">
      <label class="form-label">說明</label>
      <input type="text" name="description" class="form-control" required>
    </div>

    <div class="mb-3">
      <label class="form-label">金額</label>
      <input type="number" step="0.01" name="amount" class="form-control" required>
    </div>

    <div class="mb-3">
      <label class="form-label">日期</label>
      <input type="date" name="date" class="form-control" required>
    </div>

    <!-- 提交按鈕 -->
    <div class="mb-3 d-flex gap-2">
      <button type="submit" class="btn btn-success">✅ 送出</button>
      <a href="kol_transactions.php" class="btn btn-secondary">← 返回紀錄</a>
    </div>
  </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
 