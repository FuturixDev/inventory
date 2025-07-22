<?php
include 'db.php';
include 'nav.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $conn->prepare("SELECT * FROM expenses WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $expense = $stmt->get_result()->fetch_assoc();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $category = $_POST['category'];
    $amount = $_POST['amount'];
    $note = $_POST['note'];
    $date = $_POST['date'];

    $stmt = $conn->prepare("UPDATE expenses SET category = ?, amount = ?, note = ?, date = ? WHERE id = ?");
    $stmt->bind_param("sdssi", $category, $amount, $note, $date, $id);
    $stmt->execute();

    header("Location: expenses.php");
    exit;
}
?>

<form method="POST" class="container py-4">
  <h2 class="mb-4">✏️ 編輯支出</h2>
  <input type="hidden" name="id" value="<?= $expense['id'] ?>">
  <div class="mb-3"><label>項目</label><input name="category" class="form-control" value="<?= $expense['category'] ?>"></div>
  <div class="mb-3"><label>金額</label><input name="amount" type="number" step="0.01" class="form-control" value="<?= $expense['amount'] ?>"></div>
  <div class="mb-3"><label>備註</label><input name="note" class="form-control" value="<?= $expense['note'] ?>"></div>
  <div class="mb-3"><label>日期</label><input name="date" type="date" class="form-control" value="<?= $expense['date'] ?>"></div>
  <button class="btn btn-success">更新</button>
</form>
