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

$user_id     = $_POST['user_id'] ?? 'all';
$status_merchant  = $_POST['status_merchant'] ?? 'all';

/* =============================
   FILTER
============================= */


if ($user_id != 'all')
    $where[] = "fkm.user_id = '$user_id'";

if ($status_merchant != 'all')
    $where[] = "fkm.status_merchant = '$status_merchant'";


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
                    <h3 class="card-title"><i class="fas fa-chart-bar"></i> Result Search</h3>
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
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data as $row): ?>
                                <tr class="text-center">
                                    <td>
                                        <input type="checkbox" class="check-item" value="<?= $row['id_fkm']; ?>">
                                    </td>
                                    <td><?= htmlspecialchars((string)($row["name"] ?? '')) ?></td>
                                    <td><?= htmlspecialchars((string)($row["tid"] ?? '')) ?></td>
                                    <td><?= htmlspecialchars((string)($row["mid"] ?? '')) ?>
                                    <td>
                                        <?= htmlspecialchars((string)($row["nama_merchant"] ?? '')) ?><br>
                                        <h6 style="font-size:smaller;"><?= htmlspecialchars((string)($row["alamat"] ?? '')) ?></h6>
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
                                        <!-- <?php if ($_SESSION['role'] === 'Admin'): ?>
                                            <div class="dropdown">
                                                <button class="btn dropdown-toggle" type="button" data-toggle="dropdown">
                                                    Action
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li>
                                                        <a class="dropdown-item"
                                                            href="<?= base_url('fkm_thermal/edit/' . $row['id_fkm']) ?>">
                                                            <i class="fas fa-edit"></i> Edit
                                                        </a>
                                                    </li>

                                                    <li>
                                                        <button class="dropdown-item tombol-hapus" data-id="<?= $row['id_fkm']; ?>">
                                                            <i class="far fa-trash-alt"></i> Delete
                                                        </button>
                                                    </li>
                                                </ul>
                                            </div>
                                        <?php elseif (
                                                    $_SESSION['role'] === 'User'
                                                ): ?>
                                            <a class="btn btn-success btn-sm"
                                                href="<?= base_url('fkm_thermal/edit/' . $row['id_fkm']) ?>">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        <?php endif; ?> -->
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
