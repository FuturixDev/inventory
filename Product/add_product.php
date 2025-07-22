<?php
include '../db.php';
include '../nav.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $model = $_POST['model'];
    $name = $_POST['name'];

    $stmt = $conn->prepare("INSERT INTO products (model, name) VALUES (?, ?)");
    $stmt->bind_param("ss", $model, $name);
    $stmt->execute();

    header("Location: products.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>新增產品</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">


<div class="container py-4">
  <h2 class="mb-4">➕ 新增產品</h2>

  <form method="POST" class="card p-4 shadow-sm bg-white">
    <div class="mb-3">
      <label class="form-label">型號 <span class="text-danger">*</span></label>
      <input type="text" name="model" class="form-control" required>
    </div>

    <div class="mb-3">
      <label class="form-label">名稱</label>
      <input type="text" name="name" class="form-control">
    </div>

    <div class="mb-3 d-flex gap-2">
      <button type="submit" class="btn btn-success">✅ 新增</button>
      <a href="products.php" class="btn btn-secondary">← 回產品列表</a>
    </div>
  </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
