<?php
// Hanya boleh via AJAX
if (
    !isset($_SERVER['HTTP_X_REQUESTED_WITH']) ||
    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest'
) {
    http_response_code(403);
    exit;
}

// Hanya boleh method POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit;
}
session_start();
require_once("../auth_check.php");
if (!isset($_SESSION["login"]) || $_SESSION["login"] !== true) {
    header("Location: ../login");
    exit;
}

header('Content-Type: application/json');

$where = [];

$keyword     = mysqli_real_escape_string($db, $_POST['search'] ?? '');
$date_pickup = $_POST['date_pickup'] ?? '';
$date_used   = $_POST['date_used'] ?? '';
$user_id     = $_POST['user_id'] ?? 'all';
$id_product_name = $_POST['id_product_name'] ?? 'all';
$id_member_bank  = $_POST['id_member_bank'] ?? 'all';
$work_type   = $_POST['work_type'] ?? 'all';
$status_edc  = $_POST['status_edc'] ?? 'all';

/* FILTER */

if ($date_pickup != '')
    $where[] = "stock.date_pickup = '$date_pickup'";

if ($date_used != '')
    $where[] = " detail_list_stock.date_used = '$date_used'";

if ($user_id != 'all')
    $where[] = "stock.user_id = '$user_id'";

if ($id_product_name != 'all')
    $where[] = "stock.id_product_name = '$id_product_name'";

if ($id_member_bank != 'all')
    $where[] = " detail_list_stock.id_member_bank = '$id_member_bank'";

if ($work_type != 'all')
    $where[] = " detail_list_stock.work_type = '$work_type'";

if ($status_edc != 'all')
    $where[] = "stock.status_edc = '$status_edc'";

/* KEYWORD SEARCH */

if ($keyword != '') {
    $where[] = "(
        detail_list_stock.tid LIKE '%$keyword%' OR
        detail_list_stock.mid LIKE '%$keyword%' OR
        detail_list_stock.merchant_name LIKE '%$keyword%' OR
        stock.sn_edc LIKE '%$keyword%' OR
        stock.sn_simcard LIKE '%$keyword%' OR
        stock.sn_samcard1 LIKE '%$keyword%' OR
        stock.sn_samcard2 LIKE '%$keyword%' OR
        stock.sn_samcard3 LIKE '%$keyword%'
    )";
}

$whereSQL = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

/* QUERY */

$query = "
SELECT 
    stock.*,
    users.name,
    product_type.name_product,
    member_bank.name_member,
    detail_list_stock.*
FROM stock
LEFT JOIN users ON stock.user_id = users.id
LEFT JOIN product_type ON stock.id_product_name = product_type.id_product
LEFT JOIN detail_list_stock ON stock.id_stock = detail_list_stock.stock_id
LEFT JOIN member_bank ON detail_list_stock.id_member_bank = member_bank.id_member
$whereSQL
ORDER BY stock.id_stock DESC
";

$data = query($query);

if (!$data) {
    echo json_encode(['status' => 'empty']);
    exit;
}

/* BUILD TABLE */

$html = '
<div class="card card-primary">
<div class="card-header">
<h3 class="card-title">Result</h3>
</div>
<div class="card-body table-responsive">
<table class="table table-bordered table-hover">
<thead>
<tr>
<th>Name</th>
<th>Product</th>
<th>SN EDC</th>
<th>TID</th>
<th>MID</th>
<th>Merchant</th>
<th>Member</th>
<th>Status</th>
</tr>
</thead>
<tbody>
';

foreach ($data as $row) {

    $html .= '
<tr>
<td>' . $row["name"] . '</td>
<td>' . $row["name_product"] . '</td>
<td>' . $row["sn_edc"] . '</td>
<td>' . $row["tid"] . '</td>
<td>' . $row["mid"] . '</td>
<td>' . $row["merchant_name"] . '<br><small>' . $row["addres_name"] . '</small></td>
<td>' . $row["name_member"] . '</td>
<td><span class="badge bg-success">' . $row["status_edc"] . '</span></td>
</tr>
';
}

$html .= '
</tbody>
</table>
</div>
</div>
';

echo json_encode([
    'status' => 'success',
    'html'   => $html
]);
