<?php
// 一定要是檔案第一行！前面不能有任何空格或換行！
require 'vendor/autoload.php';
include 'db.php';

// 設定時區（可選）
date_default_timezone_set('Asia/Taipei');

// 撈資料
$res1 = $conn->query("SELECT SUM(price * quantity) AS income FROM sales WHERE price > 0");
$res2 = $conn->query("
    SELECT s.product_id, SUM(s.quantity) AS total_qty
    FROM sales s
    WHERE price > 0
    GROUP BY s.product_id
");

$cost = 0;
while ($row = $res2->fetch_assoc()) {
    $pid = $row['product_id'];
    $qty = $row['total_qty'];

    $stmt = $conn->prepare("
      SELECT SUM(quantity * unit_cost) / SUM(quantity) AS avg_cost
      FROM inventory_logs
      WHERE product_id = ? AND change_type = 'in'
    ");
    $stmt->bind_param("i", $pid);
    $stmt->execute();
    $avg_cost = $stmt->get_result()->fetch_assoc()['avg_cost'] ?? 0;

    $cost += $avg_cost * $qty;
}

// KOL 支出（公關）
$gift_res = $conn->query("SELECT SUM(quantity * unit_cost) AS kol_gift FROM inventory_logs WHERE note LIKE '公關贈送%'");
$kol_gift = $gift_res->fetch_assoc()['kol_gift'] ?? 0;

// KOL 分潤
$commission_res = $conn->query("SELECT SUM(total_commission) AS kol_commission FROM kol_profit_records");
$kol_commission = $commission_res->fetch_assoc()['kol_commission'] ?? 0;

// 營運支出
$res4 = $conn->query("SELECT SUM(amount) AS ops FROM expenses");
$ops = $res4->fetch_assoc()['ops'] ?? 0;

$income = $res1->fetch_assoc()['income'] ?? 0;
$profit = $income - $cost - $kol_gift - $kol_commission - $ops;

// 建立 PDF
$pdf = new \TCPDF();
$pdf->SetCreator('System');
$pdf->SetAuthor('你');
$pdf->SetTitle('損益表 PDF');
$pdf->SetMargins(15, 20, 15);
$pdf->AddPage();

// ✅ 中文支援：使用 cid0cs 字型
$pdf->SetFont('cid0cs', '', 12);

// HTML 表格內容
$html = '<h1 style="text-align:center;">損益表</h1>';
$html .= '<table border="1" cellpadding="6">';
$html .= '<tr><th>項目</th><th>金額</th></tr>';
$html .= '<tr><td>📦 銷售收入</td><td>$' . number_format($income, 2) . '</td></tr>';
$html .= '<tr><td>📉 銷售成本</td><td>($' . number_format($cost, 2) . ')</td></tr>';
$html .= '<tr><td>🎁 KOL 公關贈送</td><td>($' . number_format($kol_gift, 2) . ')</td></tr>';
$html .= '<tr><td>💼 KOL 分潤</td><td>($' . number_format($kol_commission, 2) . ')</td></tr>';
$html .= '<tr><td>💸 營運支出</td><td>($' . number_format($ops, 2) . ')</td></tr>';
$html .= '<tr><th>🟢 淨利</th><th>$' . number_format($profit, 2) . '</th></tr>';
$html .= '</table>';

// 輸出
$pdf->writeHTML($html, true, false, true, false, '');
$pdf->Output('損益表.pdf', 'I');
exit;
