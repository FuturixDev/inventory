<?php
include '../db.php';

$id = $_POST['id'] ?? null;
$shipped = $_POST['shipped'] ?? 0;

if ($id !== null) {
    $stmt = $conn->prepare("UPDATE inventory_logs SET shipped = ? WHERE id = ?");
    $stmt->bind_param("ii", $shipped, $id);
    $stmt->execute();
}
