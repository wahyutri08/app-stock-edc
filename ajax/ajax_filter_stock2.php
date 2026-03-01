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
    $where[] = "s.date_pickup = '$date_pickup'";

if ($date_used != '')
    $where[] = "d.date_used = '$date_used'";

if ($user_id != 'all')
    $where[] = "s.user_id = '$user_id'";

if ($id_product != 'all')
    $where[] = "s.id_product_name = '$id_product'";

if ($id_member != 'all')
    $where[] = "d.id_member_bank = '$id_member'";

if ($work_type != 'all')
    $where[] = "d.work_type = '$work_type'";

if ($status_edc != 'all')
    $where[] = "s.status_edc = '$status_edc'";

/* =============================
   KEYWORD SEARCH
============================= */

if ($keyword != '') {
    $where[] = "(
        d.tid LIKE '%$keyword%' OR
        d.mid LIKE '%$keyword%' OR
        d.merchant_name LIKE '%$keyword%' OR
        s.sn_edc LIKE '%$keyword%' OR
        s.sn_simcard LIKE '%$keyword%' OR
        s.sn_samcard1 LIKE '%$keyword%' OR
        s.sn_samcard2 LIKE '%$keyword%' OR
        s.sn_samcard3 LIKE '%$keyword%'
    )";
}

$whereSQL = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

/* =============================
   QUERY
============================= */

$query = "
SELECT 
    s.*,
    u.name,
    p.name_product,
    mb.name_member,
    d.tid,
    d.mid,
    d.merchant_name,
    d.addres_name
FROM stock s
LEFT JOIN users u ON s.user_id = u.id
LEFT JOIN product_type p ON s.id_product_name = p.id_product
LEFT JOIN detail_list_stock d ON s.id_stock = d.stock_id
LEFT JOIN member_bank mb ON d.id_member_bank = mb.id_member
$whereSQL
ORDER BY s.id_stock DESC
";

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
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Result</h3>
                </div>
                <div class="card-body table-responsive">
                    <table id="example1" class="table table-bordered table-hover">
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
                            <?php foreach ($data as $row): ?>
                                <tr>
                                    <td><?= htmlspecialchars((string)($row["name"] ?? '')) ?></td>
                                    <td><?= htmlspecialchars((string)($row["name_product"] ?? '')) ?></td>
                                    <td><?= htmlspecialchars((string)($row["sn_edc"] ?? '')) ?></td>
                                    <td><?= htmlspecialchars((string)($row["tid"] ?? '')) ?></td>
                                    <td><?= htmlspecialchars((string)($row["mid"] ?? '')) ?></td>
                                    <td>
                                        <?= htmlspecialchars((string)($row["merchant_name"] ?? '')) ?><br>
                                        <small><?= htmlspecialchars((string)($row["addres_name"] ?? '')) ?></small>
                                    </td>
                                    <td><?= htmlspecialchars((string)($row["name_member"] ?? '')) ?></td>
                                    <td>
                                        <?php if (($row["status_edc"] ?? '') === 'Used'): ?>
                                            <span class="badge bg-success">
                                                <?= htmlspecialchars($row["status_edc"]) ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-warning">
                                                <?= htmlspecialchars($row["status_edc"]) ?>
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
