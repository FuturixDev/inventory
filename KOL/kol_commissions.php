<?php
include '../db.php';
include '../nav.php';

$results = [];

if ($conn) {
    // 計算並更新每位 KOL 分潤
    $kol_query = $conn->query("SELECT id, name, commission_percent, commission_start_after FROM kols");
    if ($kol_query) {
        $kols = $kol_query->fetch_all(MYSQLI_ASSOC);

        foreach ($kols as $kol) {
            $kol_id = $kol['id'];
            $kol_name = $kol['name'];
            $percent = floatval($kol['commission_percent']);
            $start_after = intval($kol['commission_start_after']);

            $stmt = $conn->prepare("SELECT * FROM sales WHERE note LIKE ? AND note NOT LIKE '%公關贈送%' ORDER BY created_at ASC");
            $like_note = "%$kol_name%";
            $stmt->bind_param("s", $like_note);
            $stmt->execute();
            $sales_result = $stmt->get_result();

            $total_qty = 0;
            $total_amount = 0;
            $commissionable_qty = 0;
            $commission_amount = 0;

            while ($row = $sales_result->fetch_assoc()) {
                $qty = intval($row['quantity']);
                $price = floatval($row['price']);
                $total_qty += $qty;
                $total_amount += $qty * $price;

                for ($i = 1; $i <= $qty; $i++) {
                    if (($total_qty - $qty + $i) > $start_after) {
                        $commissionable_qty++;
                        $commission_amount += $price * $percent / 100;
                    }
                }
            }

            if ($total_qty > 0) {
                $check = $conn->prepare("SELECT id FROM kol_profit_records WHERE kol_id = ?");
                $check->bind_param("i", $kol_id);
                $check->execute();
                $check_result = $check->get_result();

                if ($check_result->num_rows > 0) {
                    $update = $conn->prepare("
                        UPDATE kol_profit_records
                        SET total_sales_quantity = ?, total_sales_amount = ?, commission_percent = ?, total_commission = ?, created_at = NOW()
                        WHERE kol_id = ?
                    ");
                    $update->bind_param("idddi", $total_qty, $total_amount, $percent, $commission_amount, $kol_id);
                    $update->execute();
                } else {
                    $insert = $conn->prepare("
                        INSERT INTO kol_profit_records (kol_id, total_sales_quantity, total_sales_amount, commission_percent, total_commission)
                        VALUES (?, ?, ?, ?, ?)
                    ");
                    $insert->bind_param("iidd", $kol_id, $total_qty, $total_amount, $percent, $commission_amount);
                    $insert->execute();
                }
            }
        }
    }

    // 顯示所有 kol_profit_records
    $res = $conn->query("
        SELECT r.*, k.name 
        FROM kol_profit_records r
        JOIN kols k ON r.kol_id = k.id
        ORDER BY r.total_commission DESC
    ");
    if ($res) {
        $results = $res->fetch_all(MYSQLI_ASSOC);
    }
}
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>KOL 分潤統計</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">
    <h2 class="text-center mb-4">📊 KOL 分潤統計結果</h2>

    <div class="table-responsive">
        <table class="table table-bordered table-hover text-center bg-white">
            <thead class="table-light">
                <tr>
                    <th>姓名</th>
                    <th>銷售數量</th>
                    <th>銷售總額</th>
                    <th>分潤 %</th>
                    <th>實際分潤金額</th>
                    <th>建立時間</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($results): ?>
                    <?php foreach ($results as $r): ?>
                    <tr>
                        <td><?= htmlspecialchars($r['name']) ?></td>
                        <td><?= $r['total_sales_quantity'] ?></td>
                        <td>$<?= number_format($r['total_sales_amount'], 2) ?></td>
                        <td><?= $r['commission_percent'] ?>%</td>
                        <td>$<?= number_format($r['total_commission'], 2) ?></td>
                        <td><?= $r['created_at'] ?></td>
                        <td>
                            <a href="edit_kol_commission.php?id=<?= $r['id'] ?>" class="btn btn-sm btn-warning">編輯</a>
                            <a href="delete_kol_commission.php?id=<?= $r['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('確定要刪除這筆分潤紀錄嗎？');">刪除</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="7" class="text-muted">⚠ 尚無分潤紀錄或無法連接資料庫</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <a href="kols.php" class="btn btn-secondary mt-3">← 回 KOL 名單</a>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
