<?php
include '../db.php';

$shipped_ids = $_POST['shipped_ids'] ?? [];
$redirect = $_POST['redirect'] ?? 'sales.php';

// 解析 redirect 中的分頁參數
parse_str(parse_url($redirect, PHP_URL_QUERY), $params);
$page = isset($params['page']) ? max(1, intval($params['page'])) : 1;
$per_page = 10; // 與 sales.php 保持一致
$offset = ($page - 1) * $per_page;

// 搜尋與排序條件（與 sales.php 一致）
$search = trim($params['search'] ?? '');
$sort = $params['sort'] ?? 'created_at_desc';
$where = [];
$sql_params = [];
$types = '';

if (!empty($search)) {
    $where[] = "(p.model LIKE ? OR s.note LIKE ?)";
    $sql_params[] = '%' . $search . '%';
    $sql_params[] = '%' . $search . '%';
    $types .= 'ss';
}

switch ($sort) {
    case 'price_asc': $sort_sql = 's.price ASC'; break;
    case 'price_desc': $sort_sql = 's.price DESC'; break;
    case 'shipped_asc': $sort_sql = 's.shipped ASC'; break;
    case 'shipped_desc': $sort_sql = 's.shipped DESC'; break;
    default: $sort_sql = 's.created_at DESC'; break;
}

$where_clause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

// 取得當前頁面的記錄 ID
$data_sql = "
    SELECT s.id FROM sales s
    JOIN products p ON s.product_id = p.id
    $where_clause
    ORDER BY $sort_sql
    LIMIT $per_page OFFSET $offset
";

if ($sql_params) {
    $stmt = $conn->prepare($data_sql);
    $stmt->bind_param($types, ...$sql_params);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query($data_sql);
}

$visible_ids = [];
while ($row = $result->fetch_assoc()) {
    $visible_ids[] = intval($row['id']);
}

// 只更新當前頁面的記錄
if (!empty($visible_ids)) {
    $ids_str = implode(',', $visible_ids);
    $conn->query("UPDATE sales SET shipped = 0 WHERE id IN ($ids_str)");

    if (!empty($shipped_ids)) {
        $shipped_ids_str = implode(',', array_map('intval', $shipped_ids));
        $conn->query("UPDATE sales SET shipped = 1 WHERE id IN ($shipped_ids_str) AND id IN ($ids_str)");
    }
}

header("Location: $redirect");
exit;