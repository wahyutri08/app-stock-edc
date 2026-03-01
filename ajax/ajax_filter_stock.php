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
error_reporting(E_ALL);
ini_set('display_errors', 1);

/* =============================
   AMBIL DATA FILTER
============================= */

$where = [];

$keyword     = mysqli_real_escape_string($db, $_POST['search'] ?? '');
$date_pickup = $_POST['date_pickup'] ?? '';
$date_used   = $_POST['date_used'] ?? '';
$user_id     = $_POST['user_id'] ?? 'all';
$id_product  = $_POST['id_product_name'] ?? 'all';
$id_member   = $_POST['id_member_bank'] ?? 'all';
$work_type   = $_POST['work_type'] ?? 'all';
$status_edc  = $_POST['status_edc'] ?? 'all';

/* =============================
   FILTER
============================= */

if ($date_pickup != '')
    $where[] = "stock.date_pickup = '$date_pickup'";

if ($date_used != '')
    $where[] = "detail_list_stock.date_used = '$date_used'";

if ($user_id != 'all')
    $where[] = "stock.user_id = '$user_id'";

if ($id_product != 'all')
    $where[] = "stock.id_product_name = '$id_product'";

if ($id_member != 'all')
    $where[] = "detail_list_stock.id_member_bank = '$id_member'";

if ($work_type != 'all')
    $where[] = "detail_list_stock.work_type = '$work_type'";

if ($status_edc != 'all')
    $where[] = "stock.status_edc = '$status_edc'";

/* =============================
   KEYWORD SEARCH
============================= */

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

/* =============================
   QUERY
============================= */

$query = "SELECT
          stock.*,
          users.name,
          product_type.name_product,
          color_type.name_color,
          member_bank.name_member,
          detail_list_stock.*
          FROM stock
          LEFT JOIN users 
          ON stock.user_id = users.id
          LEFT JOIN product_type 
          ON stock.id_product_name = product_type.id_product
          LEFT JOIN color_type 
          ON stock.id_edc_color = color_type.id_color
          LEFT JOIN detail_list_stock 
          ON stock.id_stock = detail_list_stock.stock_id
          LEFT JOIN member_bank 
          ON detail_list_stock.id_member_bank = member_bank.id_member
          $whereSQL
          ORDER BY stock.id_stock DESC";

/* =============================
   EKSEKUSI QUERY
============================= */

$result = mysqli_query($db, $query);

if (!$result) {
    echo json_encode([
        'status' => 'error',
        'message' => mysqli_error($db)
    ]);
    exit;
}

$data = mysqli_fetch_all($result, MYSQLI_ASSOC);

if (!$data) {
    echo json_encode(['status' => 'empty']);
    exit;
}

/* =============================
   BUILD HTML OUTPUT
============================= */

ob_start();
?>

<div class="container-fluid">
    <div class="row">
        <div class="col">
            <div class="card card-warning">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-chart-bar"></i> Result Search</h3>
                </div>
                <div class="card-body table-responsive">
                    <table id="example1" class="table table-bordered table-hover">
                        <thead>
                            <tr class="text-center">
                                <th>Name</th>
                                <th>Product Type</th>
                                <th>SN EDC</th>
                                <th>Simcard</th>
                                <th>Samcard1</th>
                                <th>Samcard2</th>
                                <th>Samcard2</th>
                                <th>TID</th>
                                <th>MID</th>
                                <th>Merchant</th>
                                <th>Member Bank</th>
                                <th>Work Type</th>
                                <th>Date Pickup</th>
                                <th>Date Used</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data as $row): ?>
                                <tr class="text-center">
                                    <td><?= htmlspecialchars((string)($row["name"] ?? '')) ?></td>
                                    <td><?= htmlspecialchars((string)($row["name_product"] ?? '')) ?> <?= htmlspecialchars((string)($row["name_color"] ?? '')) ?></td>
                                    <td><?= htmlspecialchars((string)($row["sn_edc"] ?? '')) ?></td>
                                    <td><?= htmlspecialchars((string)($row["sn_simcard"] ?? '')) ?></td>
                                    <td><?= htmlspecialchars((string)($row["sn_samcard1"] ?? '')) ?></td>
                                    <td><?= htmlspecialchars((string)($row["sn_samcard2"] ?? '')) ?></td>
                                    <td><?= htmlspecialchars((string)($row["sn_samcard3"] ?? '')) ?></td>
                                    <td><?= htmlspecialchars((string)($row["tid"] ?? '')) ?></td>
                                    <td><?= htmlspecialchars((string)($row["mid"] ?? '')) ?></td>
                                    <td>
                                        <?= htmlspecialchars((string)($row["merchant_name"] ?? '')) ?><br>
                                        <h6 style="font-size:smaller;"><?= htmlspecialchars((string)($row["addres_name"] ?? '')) ?></h6>
                                    </td>
                                    <td><?= htmlspecialchars((string)($row["name_member"] ?? '')) ?></td>
                                    <td><?= htmlspecialchars((string)($row["work_type"] ?? '')) ?></td>
                                    <td><?= htmlspecialchars((string)($row["date_pickup"] ?? '')) ?></td>
                                    <td><?= htmlspecialchars((string)($row["date_used"] ?? '')) ?></td>
                                    <td>
                                        <?php if (($row["status_edc"] ?? '') === 'Used'): ?>
                                            <span class="badge bg-success">
                                                <?= htmlspecialchars((string)$row["status_edc"]) ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-warning">
                                                <?= htmlspecialchars((string)$row["status_edc"]) ?>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$html = ob_get_clean();

echo json_encode([
    'status' => 'success',
    'html'   => $html
]);
