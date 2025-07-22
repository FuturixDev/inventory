<?php
include '../db.php';
include '../nav.php';

$sql = "SELECT kt.*, k.name FROM kol_transactions kt
        JOIN kols k ON kt.kol_id = k.id
        ORDER BY kt.date DESC";
$result = $conn->query($sql);
?>

<h2>📜 KOL 合作紀錄</h2>
<a href="add_kol_transaction.php">➕ 新增合作紀錄</a>
<hr>

<table border="1" cellpadding="6">
  <tr><th>KOL</th><th>類型</th><th>描述</th><th>金額</th><th>日期</th></tr>
  <?php while($row = $result->fetch_assoc()): ?>
  <tr>
    <td><?= $row['name'] ?></td>
    <td><?= strtoupper($row['type']) ?></td>
    <td><?= $row['description'] ?></td>
    <td><?= $row['amount'] ?></td>
    <td><?= $row['date'] ?></td>
  </tr>
  <?php endwhile; ?>
</table>
