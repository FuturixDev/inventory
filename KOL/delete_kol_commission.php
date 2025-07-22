<?php
include 'db.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $conn->prepare("DELETE FROM kol_profit_records WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
}

header("Location: kol_commissions.php");
exit;
?>
