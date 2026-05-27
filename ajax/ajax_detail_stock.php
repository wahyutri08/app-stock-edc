<?php
if (
    !isset($_SERVER['HTTP_X_REQUESTED_WITH']) ||
    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest'
) {
    http_response_code(403);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit;
}

session_start();
require_once("../auth_check.php");

if (!isset($_SESSION["login"]) || $_SESSION["login"] !== true) {
    http_response_code(403);
    exit;
}

header('Content-Type: text/html; charset=UTF-8');

$id_stock = (int) ($_POST['id_stock'] ?? 0);

if (!$id_stock) {
    http_response_code(404);
    exit;
}

$query = mysqli_query($db, "
    SELECT stock.*, 
           users.name,
           product_type.name_product,
           color_type.name_color,
           member_bank.name_member,
           detail_list_stock.*
    FROM stock
    LEFT JOIN users ON stock.user_id = users.id
    LEFT JOIN product_type ON stock.id_product_name = product_type.id_product
    LEFT JOIN color_type ON stock.id_edc_color = color_type.id_color
    LEFT JOIN detail_list_stock ON stock.id_stock = detail_list_stock.stock_id
    LEFT JOIN member_bank ON detail_list_stock.id_member_bank = member_bank.id_member
    WHERE stock.id_stock = $id_stock
");

if (!$query || mysqli_num_rows($query) == 0) {
    http_response_code(404);
    exit;
}

$row = mysqli_fetch_assoc($query);
?>

<table class="table table-bordered">
    <tbody>
        <tr>
            <th>Requirements</th>
            <td><?= htmlspecialchars($row['requirements'] ?? '') ?></td>
        </tr>
        <tr>
            <th>Product Type</th>
            <td><?= htmlspecialchars($row['name_product'] ?? '') ?></td>
        </tr>
        <tr>
            <th>SN EDC</th>
            <td><?= htmlspecialchars($row['sn_edc'] ?? '') ?></td>
        </tr>
        <tr>
            <th>Simcard</th>
            <td><?= htmlspecialchars($row['sn_simcard'] ?? '') ?></td>
        </tr>
        <tr>
            <th>Samcard (Mandiri)</th>
            <td><?= htmlspecialchars($row['sn_samcard1'] ?? '') ?></td>
        </tr>
        <tr>
            <th>Samcard (BRI)</th>
            <td><?= htmlspecialchars($row['sn_samcard2'] ?? '') ?></td>
        </tr>
        <tr>
            <th>Samcard (BNI)</th>
            <td><?= htmlspecialchars($row['sn_samcard3'] ?? '') ?></td>
        </tr>
        <tr>
            <th>TID</th>
            <td><?= htmlspecialchars($row['tid'] ?? '') ?></td>
        </tr>
        <tr>
            <th>MID</th>
            <td><?= htmlspecialchars($row['mid'] ?? '') ?></td>
        </tr>
        <tr>
            <th>Merchant</th>
            <td><?= htmlspecialchars($row['merchant_name'] ?? '') ?></td>
        </tr>
        <tr>
            <th>Address</th>
            <td><?= htmlspecialchars($row['addres_name'] ?? '') ?></td>
        </tr>
        <tr>
            <th>Member Bank</th>
            <td><?= htmlspecialchars($row['name_member'] ?? '') ?></td>
        </tr>
        <tr>
            <th>Work Type</th>
            <td><?= htmlspecialchars($row['work_type'] ?? '') ?></td>
        </tr>
        <tr>
            <th>Status</th>
            <td>
                <?php if (($row["status_edc"] ?? '') === 'Used'): ?>
                    <span class="badge bg-success">
                        <?= htmlspecialchars((string)$row["status_edc"]) ?>
                    </span>
                <?php elseif (($row["status_edc"] ?? '') === 'None'): ?>
                    <span class="badge bg-danger">
                        <?= htmlspecialchars((string)$row["status_edc"]) ?>
                    </span>
                <?php elseif (($row["status_edc"] ?? '') === 'Not yet used'): ?>
                    <span class="badge bg-warning">
                        <?= htmlspecialchars((string)$row["status_edc"]) ?>
                    </span>
                <?php elseif (($row["status_edc"] ?? '') === 'Terlink'): ?>
                    <span class="badge bg-primary">
                        <?= htmlspecialchars((string)$row["status_edc"]) ?>
                    </span>
                <?php elseif (($row["status_edc"] ?? '') === 'Send To HO'): ?>
                    <span class="badge bg-indigo">
                        <?= htmlspecialchars((string)$row["status_edc"]) ?>
                    </span>
                <?php elseif (($row["status_edc"] ?? '') === 'HO Santana'): ?>
                    <span class="badge bg-info">
                        <?= htmlspecialchars((string)$row["status_edc"]) ?>
                    </span>
                <?php endif; ?>
            </td>
        </tr>
        <tr>
            <th>Status Condition</th>
            <td><?= htmlspecialchars($row['status_condition'] ?? '') ?></td>
        </tr>
        <tr>
            <th>Note</th>
            <td><?= htmlspecialchars($row['note'] ?? '') ?></td>
        </tr>
    </tbody>
</table>