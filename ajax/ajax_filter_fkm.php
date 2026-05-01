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

$keyword = mysqli_real_escape_string($db, $_POST['search'] ?? '');

$date_periode = $_POST['date_periode'] ?? '';
$user_id = $_POST['user_id'] ?? 'all';
$status_merchant = $_POST['status_merchant'] ?? 'all';

/* =============================
   FILTER
============================= */

if ($user_id != 'all') {
    $user_id = (int)$user_id;
    $where[] = "fkm.user_id = $user_id";
}

if ($status_merchant != 'all') {
    $status_merchant = mysqli_real_escape_string($db, trim($status_merchant));
    $where[] = "fkm.status_merchant = '$status_merchant'";
}

/* =============================
   FILTER DATE PERIODE (🔥 TAMBAHAN)
============================= */

if (!empty($date_periode)) {

    $date_periode = mysqli_real_escape_string($db, trim($date_periode));

    // contoh: 2024-01
    $start = $date_periode . "-01";
    $end   = date("Y-m-t", strtotime($start)); // akhir bulan

    $where[] = "fkm.date_periode BETWEEN '$start' AND '$end'";
}

/* =============================
   KEYWORD SEARCH
============================= */

if ($keyword != '') {
    $where[] = "(
        fkm.tid LIKE '%$keyword%' OR
        fkm.mid LIKE '%$keyword%' OR
        fkm.nama_merchant LIKE '%$keyword%'
    )";
}

$whereSQL = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

/* =============================
   QUERY
============================= */

$query = "SELECT
          fkm.*,
          users.name,
          users.role
          FROM fkm
          LEFT JOIN users 
            ON fkm.user_id = users.id
          $whereSQL
          ORDER BY fkm.id_fkm DESC";

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
                    <h3 class="card-title">
                        <i class="fas fa-chart-bar"></i>
                        Result Search (<?= count($data); ?> Data)
                    </h3>
                </div>
                <div class="card-body table-responsive">
                    <table id="example1" class="table table-bordered table-hover">
                        <thead>
                            <tr class="text-center">
                                <th><input type="checkbox" id="checkAll"></th>
                                <th>Name</th>
                                <th>TID</th>
                                <th>MID</th>
                                <th>Merchant</th>
                                <th>Periode</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data as $row): ?>
                                <tr class="text-center">
                                    <td>
                                        <!-- 🔥 FIX CLASS -->
                                        <input type="checkbox" class="checkbox-item" value="<?= $row['id_fkm']; ?>">
                                    </td>

                                    <td><?= htmlspecialchars((string)($row["name"] ?? '')) ?></td>

                                    <td><?= htmlspecialchars((string)($row["tid"] ?? '')) ?></td>

                                    <!-- 🔥 FIX TD -->
                                    <td><?= htmlspecialchars((string)($row["mid"] ?? '')) ?></td>

                                    <td>
                                        <?= htmlspecialchars((string)($row["nama_merchant"] ?? '')) ?><br>
                                        <h6 style="font-size:smaller;">
                                            <?= htmlspecialchars((string)($row["alamat"] ?? '')) ?>
                                        </h6>
                                    </td>
                                    <!-- 🔥 FORMAT BULAN TAHUN -->
                                    <td>
                                        <?= date('Y-m', strtotime($row['date_periode'])) ?>
                                    </td>
                                    <td>
                                        <?php if (($row["status_merchant"] ?? '') === 'Active'): ?>
                                            <span class="badge bg-success">
                                                <?= htmlspecialchars((string)$row["status_merchant"]) ?>
                                            </span>
                                        <?php elseif (($row["status_merchant"] ?? '') === 'Not Active'): ?>
                                            <span class="badge bg-danger">
                                                <?= htmlspecialchars((string)$row["status_merchant"]) ?>
                                            </span>
                                        <?php endif; ?>
                                    </td>

                                    <td class="text-center">
                                        <!-- ACTION OPTIONAL -->
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
