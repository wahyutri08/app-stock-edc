<?php
session_start();
require_once("../auth_check.php");
if (!isset($_SESSION["login"]) || $_SESSION["login"] !== true) {
    header("Location: ../login");
    exit;
}

// Hanya boleh method POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit;
}


$sn_fields = [
    'sn_edc',
    'sn_simcard',
    'sn_samcard1',
    'sn_samcard2',
    'sn_samcard3'
];

$stock_id = (int)($_POST['stock_id'] ?? 0);

$duplicates = [];

foreach ($sn_fields as $field) {
    if (!empty($_POST[$field])) {

        $value = mysqli_real_escape_string($db, $_POST[$field]);

        $q = mysqli_query($db, "
            SELECT id_stock 
            FROM stock 
            WHERE $field = '$value'
            AND id_stock != $stock_id
            LIMIT 1
        ");

        if (mysqli_num_rows($q) > 0) {
            $row = mysqli_fetch_assoc($q);

            $duplicates[] = [
                'field' => $field,
                'sn' => $value,
                'stock_id' => $row['id_stock']
            ];
        }
    }
}

echo json_encode([
    'status' => 'ok',
    'duplicates' => $duplicates
]);
