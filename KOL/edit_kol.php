<?php
include '../db.php';
include '../nav.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    $stmt = $conn->prepare("SELECT * FROM kols WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $kol = $stmt->get_result()->fetch_assoc();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $note = $_POST['note'];
    $commission = floatval($_POST['commission_percent']);
    $start_after = intval($_POST['commission_start_after']);

    $update_stmt = $conn->prepare("UPDATE kols SET name = ?, note = ?, commission_percent = ?, commission_start_after = ? WHERE id = ?");
    $update_stmt->bind_param("ssdii", $name, $note, $commission, $start_after, $id);
    $update_stmt->execute();

    header("Location: kols.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
  <meta charset="UTF-8">
  <title>編輯 KOL</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">
  <h2 class="mb-4 text-center">✏️ 編輯 KOL</h2>
  <form method="POST" class="card p-4 shadow-sm bg-white">
    <div class="mb-3">
      <label class="form-label">姓名</label>
      <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($kol['name']) ?>" required>
    </div>
    <div class="mb-3">
      <label class="form-label">分潤百分比（%）</label>
      <input type="number" step="0.01" name="commission_percent" class="form-control" value="<?= htmlspecialchars($kol['commission_percent']) ?>" required>
    </div>
    <div class="mb-3">
      <label class="form-label">第幾台後開始分潤</label>
      <input type="number" name="commission_start_after" class="form-control" value="<?= htmlspecialchars($kol['commission_start_after']) ?>" required>
    </div>
    <div class="mb-3">
      <label class="form-label">備註</label>
      <input type="text" name="note" class="form-control" value="<?= htmlspecialchars($kol['note']) ?>">
    </div>
    <button type="submit" class="btn btn-success">更新 KOL</button>
    <a href="kols.php" class="btn btn-secondary ms-2">← 回 KOL 名單</a>
  </form>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
