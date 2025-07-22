<?php
include '../db.php';
include '../nav.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $note = $_POST['note'];
    $commission = floatval($_POST['commission_percent']);
    $start_after = intval($_POST['commission_start_after']);

    $stmt = $conn->prepare("INSERT INTO kols (name, note, commission_percent, commission_start_after) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssdi", $name, $note, $commission, $start_after);
    $stmt->execute();

    header("Location: kols.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
  <meta charset="UTF-8">
  <title>➕ 新增 KOL</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">
  <h2 class="text-center mb-4">➕ 新增 KOL</h2>
  <form method="POST" class="card p-4 shadow-sm bg-white">
    <div class="mb-3">
      <label class="form-label">姓名</label>
      <input type="text" name="name" class="form-control" required>
    </div>
    <div class="mb-3">
      <label class="form-label">分潤百分比（%）</label>
      <input type="number" step="0.01" name="commission_percent" class="form-control" required>
    </div>
    <div class="mb-3">
      <label class="form-label">第幾台後開始分潤</label>
      <input type="number" name="commission_start_after" class="form-control" required>
    </div>
    <div class="mb-3">
      <label class="form-label">備註</label>
      <input type="text" name="note" class="form-control">
    </div>
    <div class="mb-3 d-flex gap-2">
      <button type="submit" class="btn btn-success">✅ 新增 KOL</button>
      <a href="kols.php" class="btn btn-secondary">← 返回 KOL 名單</a>
    </div>
  </form>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
