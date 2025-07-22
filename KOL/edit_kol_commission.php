<?php
include 'db.php';
include 'nav.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $conn->prepare("SELECT * FROM kol_profit_records WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $record = $stmt->get_result()->fetch_assoc();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $quantity = $_POST['total_sales_quantity'];
    $amount = $_POST['total_sales_amount'];
    $percent = $_POST['commission_percent'];
    $commission = $_POST['total_commission'];

    $update = $conn->prepare("UPDATE kol_profit_records SET total_sales_quantity = ?, total_sales_amount = ?, commission_percent = ?, total_commission = ? WHERE id = ?");
    $update->bind_param("idddi", $quantity, $amount, $percent, $commission, $id);
    $update->execute();

    header("Location: kol_commissions.php");
    exit;
}
?>

<form method="POST" class="container py-4">
  <h2 class="mb-4">✏️ 編輯 KOL 分潤</h2>
  <input type="hidden" name="id" value="<?= $record['id'] ?>">
  <div class="mb-3"><label>銷售數量</label><input name="total_sales_quantity" class="form-control" value="<?= $record['total_sales_quantity'] ?>"></div>
  <div class="mb-3"><label>銷售總額</label><input name="total_sales_amount" class="form-control" value="<?= $record['total_sales_amount'] ?>"></div>
  <div class="mb-3"><label>分潤 %</label><input name="commission_percent" class="form-control" value="<?= $record['commission_percent'] ?>"></div>
  <div class="mb-3"><label>實際分潤金額</label><input name="total_commission" class="form-control" value="<?= $record['total_commission'] ?>"></div>
  <button class="btn btn-success">更新</button>
</form>
