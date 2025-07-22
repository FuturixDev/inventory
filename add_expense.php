<?php
include 'db.php';
include 'nav.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $cat = $_POST['category'];
    $amt = $_POST['amount'];
    $note = $_POST['note'];
    $date = $_POST['date'];

    $stmt = $conn->prepare("INSERT INTO expenses (category, amount, note, date) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sdss", $cat, $amt, $note, $date);
    $stmt->execute();

    header("Location: expenses.php");
    exit;
}
?>

<h2>➕ 登錄營運支出</h2>
<form method="POST">
    項目：<input type="text" name="category" required><br>
    金額：<input type="number" step="0.01" name="amount" required><br>
    備註：<input type="text" name="note"><br>
    日期：<input type="date" name="date" required><br>
    <button type="submit">送出</button>
</form>
