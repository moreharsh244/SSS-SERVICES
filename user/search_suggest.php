<?php
if (session_status() === PHP_SESSION_NONE) {
    session_name('SSS_USER_SESS');
    session_start();
}

header('Content-Type: application/json; charset=UTF-8');

if (!isset($_SESSION['is_login'])) {
    http_response_code(401);
    echo json_encode([]);
    exit;
}

include('../admin/conn.php');

$query = isset($_GET['q']) ? trim((string)$_GET['q']) : '';
if ($query === '') {
    echo json_encode([]);
    exit;
}

$query = substr($query, 0, 80);
$queryEscaped = mysqli_real_escape_string($con, strtolower($query));
$parts = preg_split('/\s+/', $query, -1, PREG_SPLIT_NO_EMPTY);

$conditions = [
    "LOWER(pname) LIKE '%{$queryEscaped}%'",
    "LOWER(pcompany) LIKE '%{$queryEscaped}%'",
    "LOWER(pcat) LIKE '%{$queryEscaped}%'",
    "LOWER(pdisc) LIKE '%{$queryEscaped}%'"
];

if ($queryEscaped === 'cpu') {
    $conditions[] = "LOWER(pcat) = 'processor'";
    $conditions[] = "LOWER(pcat) = 'cooler'";
}
if ($queryEscaped === 'processor') {
    $conditions[] = "LOWER(pcat) = 'processor'";
}
if ($queryEscaped === 'gpu') {
    $conditions[] = "LOWER(pcat) = 'gpu'";
}
if ($queryEscaped === 'cooler' || $queryEscaped === 'cooling fan') {
    $conditions[] = "LOWER(pcat) = 'cooler'";
}

foreach ($parts as $part) {
    $part = strtolower(trim($part));
    if (strlen($part) < 2) {
        continue;
    }

    $partEscaped = mysqli_real_escape_string($con, $part);
    $conditions[] = "LOWER(pname) LIKE '%{$partEscaped}%'";
    $conditions[] = "LOWER(pcompany) LIKE '%{$partEscaped}%'";
    $conditions[] = "LOWER(pcat) LIKE '%{$partEscaped}%'";
    $conditions[] = "LOWER(pdisc) LIKE '%{$partEscaped}%'";
}

$sql = "SELECT pid, pname, pcompany, pprice, pimg
        FROM products
        WHERE " . implode(' OR ', array_unique($conditions)) . "
        ORDER BY
            CASE WHEN LOWER(pname) LIKE '{$queryEscaped}%' THEN 0 ELSE 1 END,
            CASE WHEN LOWER(pcat) LIKE '{$queryEscaped}%' THEN 0 ELSE 1 END,
            pid DESC
        LIMIT 6";

$result = mysqli_query($con, $sql);
$items = [];

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $items[] = [
            'pid' => (int) ($row['pid'] ?? 0),
            'pname' => $row['pname'] ?? '',
            'pcompany' => $row['pcompany'] ?? '',
            'pprice' => (float) ($row['pprice'] ?? 0),
            'pimg' => $row['pimg'] ?? ''
        ];
    }
}

echo json_encode($items, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
?>