<?php
require 'vendor/autoload.php';
include 'db.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// 撈資料
$res1 = $conn->query("SELECT SUM(price * quantity) AS income FROM sales");
$res2 = $conn->query("SELECT SUM(p.cost * s.quantity) AS cost FROM sales s JOIN products p ON s.product_id = p.id");
$res3 = $conn->query("SELECT SUM(amount) AS kol FROM kol_transactions WHERE type IN ('paid','commission')");
$res4 = $conn->query("SELECT SUM(amount) AS ops FROM expenses");

$income = $res1->fetch_assoc()['income'] ?? 0;
$cost   = $res2->fetch_assoc()['cost'] ?? 0;
$kol    = $res3->fetch_assoc()['kol'] ?? 0;
$ops    = $res4->fetch_assoc()['ops'] ?? 0;
$profit = $income - $cost - $kol - $ops;

// 建立試算表
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('損益表');
$sheet->setCellValue('A1', '項目');
$sheet->setCellValue('B1', '金額');

$rows = [
    ['銷售收入', $income],
    ['銷售成本', -$cost],
    ['KOL 支出', -$kol],
    ['營運支出', -$ops],
    ['淨利', $profit]
];

$rowNum = 2;
foreach ($rows as $row) {
    $sheet->setCellValue("A{$rowNum}", $row[0]);
    $sheet->setCellValue("B{$rowNum}", $row[1]);
    $rowNum++;
}

// 匯出下載
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="損益表.xlsx"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
